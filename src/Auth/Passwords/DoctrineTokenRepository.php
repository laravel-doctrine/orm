<?php

namespace LaravelDoctrine\ORM\Auth\Passwords;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Str;

class DoctrineTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new token repository instance.
     *
     * @param Connection $connection
     * @param string     $table
     * @param string     $hashKey
     * @param int        $expires
     */
    public function __construct(Connection $connection, $table, $hashKey, $expires = 60)
    {
        $this->table      = $table;
        $this->hashKey    = $hashKey;
        $this->expires    = $expires * 60;
        $this->connection = $connection;
    }

    /**
     * Create a new token record.
     *
     * @param  CanResetPassword $user
     * @return string
     */
    public function create(CanResetPassword $user)
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
                 'created_at' => ':date'
             ])
             ->setParameters([
                 'email' => $email,
                 'token' => $token,
                 'date'  => new Carbon('now')
             ])
             ->execute();

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  CanResetPassword $user
     * @return int
     */
    protected function deleteExisting(CanResetPassword $user)
    {
        return $this->getTable()
                    ->delete($this->table)
                    ->where('email = :email')
                    ->setParameter('email', $user->getEmailForPasswordReset())
                    ->execute();
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  CanResetPassword $user
     * @param  string           $token
     * @return bool
     */
    public function exists(CanResetPassword $user, $token)
    {
        $email = $user->getEmailForPasswordReset();

        $token = $this->getTable()
                      ->select('*')
                      ->from($this->table)
                      ->where('email = :email')
                      ->andWhere('token = :token')
                      ->setParameter('email', $email)
                      ->setParameter('token', $token)
                      ->execute()->fetch();

        return $token && !$this->tokenExpired($token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        $expiresAt = Carbon::parse($token['created_at'])->addSeconds($this->expires);

        return $expiresAt->isPast();
    }

    /**
     * Delete a token record by token.
     *
     * @param  CanResetPassword $user
     * @return void
     */
    public function delete(CanResetPassword $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()
             ->delete($this->table)
             ->where('created_at < :expiredAt')
             ->setParameter('expiredAt', $expiredAt)
             ->execute();
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function getTable()
    {
        $schema = $this->connection->getSchemaManager();

        if (!$schema->tablesExist($this->table)) {
            $schema->createTable($this->getTableDefinition());
        }

        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Get the database connection instance.
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @return Table
     */
    protected function getTableDefinition()
    {
        return (new PasswordResetTable($this->table))->build();
    }
}
