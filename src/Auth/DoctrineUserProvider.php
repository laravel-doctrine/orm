<?php

namespace Brouwers\LaravelDoctrine\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;

class DoctrineUserProvider implements UserProvider
{
    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @param Hasher                 $hasher
     * @param EntityManagerInterface $em
     * @param                        $entity
     */
    public function __construct(Hasher $hasher, EntityManagerInterface $em, $entity)
    {
        $this->hasher = $hasher;
        $this->em     = $em;
        $this->entity = $entity;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->getRepository()->find($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param mixed  $identifier
     * @param string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->getRepository()->findOneBy([
            $this->getEntity()->getKeyName()           => $identifier,
            $this->getEntity()->getRememberTokenName() => $token
        ]);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(AuthenticatableContract $user, $token)
    {
        $user->setRememberToken($token);
        $this->em->persist($user);
        $this->em->flush($user);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $criteria = [];
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $criteria[$key] = $value;
            }
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(AuthenticatableContract $user, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Returns repository for the entity.
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->em->getRepository($this->entity);
    }

    /**
     * Returns instantiated entity.
     * @return mixed
     */
    protected function getEntity()
    {
        return new $this->entity;
    }
}
