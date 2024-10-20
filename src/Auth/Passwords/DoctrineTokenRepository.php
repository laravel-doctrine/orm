<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Str;

use function hash_hmac;

class DoctrineTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The Hasher implementation.
     */
    protected HasherContract $hasher;

    /**
     * The token database table.
     */
    protected string $table;

    /**
     * The hashing key.
     */
    protected string $hashKey;

    /**
     * The number of seconds a token should last.
     */
    protected int $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     */
    protected int $throttle;

    /**
     * Create a new token repository instance.
     */
    public function __construct(
        Connection $connection,
        HasherContract $hasher,
        string $table,
        string $hashKey,
        int $expires = 60,
        int $throttle = 60,
    ) {
        $this->table      = $table;
        $this->hasher     = $hasher;
        $this->hashKey    = $hashKey;
        $this->expires    = $expires * 60;
        $this->connection = $connection;
        $this->throttle   = $throttle;
    }

    /**
     * Create a new token record.
     */
    public function create(CanResetPassword $user): string
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->getTable()
             ->insert($this->table)
             ->values([
                 'email'      => ':email',
                 'token'      => ':token',
                 'created_at' => ':date',
             ])
             ->setParameters([
                 'email' => $email,
                 'token' => $this->hasher->make($token),
                 'date'  => new Carbon('now'),
             ])
             ->executeStatement();

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     */
    protected function deleteExisting(CanResetPassword $user): int
    {
        return (int) $this->getTable()
                    ->delete($this->table)
                    ->where('email = :email')
                    ->setParameter('email', $user->getEmailForPasswordReset())
                    ->executeStatement();
    }

    /**
     * Determine if a token record exists and is valid.
     */
    // phpcs:disable
    public function exists(CanResetPassword $user, $token): bool
    {
        // phpcs:enable
        $email = $user->getEmailForPasswordReset();

        $record = $this->getTable()
                      ->select('*')
                      ->from($this->table)
                      ->where('email = :email')
                      ->setParameter('email', $email)
                      ->setMaxResults(1)
                      ->executeQuery()->fetchAssociative();

        return $record
            && ! $this->tokenExpired($record['created_at'])
            && $this->hasher->check($token, $record['token']);
    }

    /**
     * Determine if the token has expired.
     */
    protected function tokenExpired(mixed $createdAt): bool
    {
        $expiresAt = Carbon::parse($createdAt)->addSeconds($this->expires);

        return $expiresAt->isPast();
    }

    /**
     * Determine if the given user recently created a password reset token.
     */
    public function recentlyCreatedToken(CanResetPassword $user): bool
    {
        $record = $this->getTable()
                       ->select('*')
                       ->from($this->table)
                       ->where('email = :email')
                       ->setParameter('email', $user->getEmailForPasswordReset())
                       ->executeQuery()->fetchAssociative();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * Determine if the token was recently created.
     */
    protected function tokenRecentlyCreated(mixed $createdAt): bool
    {
        if ($this->throttle <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds(
            $this->throttle,
        )->isFuture();
    }

    /**
     * Delete a token record by token.
     */
    public function delete(CanResetPassword $user): void
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete expired tokens.
     */
    public function deleteExpired(): void
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()
             ->delete($this->table)
             ->where('created_at < :expiredAt')
             ->setParameter('expiredAt', $expiredAt)
             ->executeStatement();
    }

    /**
     * Create a new token for the user.
     */
    public function createNewToken(): string
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     */
    protected function getTable(): QueryBuilder
    {
        $schema = $this->connection->createSchemaManager();

        if (! $schema->tablesExist([$this->table])) {
            // @codeCoverageIgnoreStart
            $schema->createTable($this->getTableDefinition());
            // @codeCoverageIgnoreEnd
        }

        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Get the database connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @throws Exception
     *
     * @codeCoverageIgnoreStart
     * */
    protected function getTableDefinition(): Table
    {
        return (new PasswordResetTable($this->table))->build();
    }

    // @codeCoverageIgnoreEnd
}
