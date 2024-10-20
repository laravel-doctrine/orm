<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use ReflectionClass;

use function assert;
use function method_exists;

class DoctrineUserProvider implements UserProvider
{
    /** @var class-string<Authenticatable> */
    protected $entity;

    /** @param class-string<Authenticatable> $entity */
    public function __construct(protected Hasher $hasher, protected EntityManagerInterface $em, string $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById(mixed $identifier): Authenticatable|null
    {
        return $this->getRepository()->find($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     */
    // phpcs:disable
    public function retrieveByToken(mixed $identifier, $token): Authenticatable|null
    {
        // phpcs:enable
        return $this->getRepository()->findOneBy([
            $this->getEntity()->getAuthIdentifierName() => $identifier,
            $this->getEntity()->getRememberTokenName()  => $token,
        ]);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    // phpcs:disable
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // phpcs:enable
        $user->setRememberToken($token);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param mixed[] $credentials
     */
    public function retrieveByCredentials(array $credentials): Authenticatable|null
    {
        $criteria = [];
        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            $criteria[$key] = $value;
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param mixed[] $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Returns repository for the entity.
     *
     * @return EntityRepository<Authenticatable>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->entity);
    }

    /**
     * Returns instantiated entity.
     */
    protected function getEntity(): Authenticatable
    {
        $refEntity = new ReflectionClass($this->entity);

        return $refEntity->newInstanceWithoutConstructor();
    }

    /**
     * Returns entity namespace.
     *
     * @codeCoverageIgnoreStart
     */
    public function getModel(): string
    {
        return $this->entity;
    }

    // @codeCoverageIgnoreEnd

    /** @param mixed[] $credentials */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if (! $this->hasher->needsRehash($user->getAuthPassword()) && ! $force) {
            return;
        }

        assert(method_exists($user, 'setPassword'));

        $user->setPassword($this->hasher->make($credentials['password']));
        $this->em->persist($user);
        $this->em->flush();
    }
}
