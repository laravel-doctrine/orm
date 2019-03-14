<?php

namespace LaravelDoctrine\ORM\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use ReflectionClass;

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
     * @param string                 $entity
     */
    public function __construct(Hasher $hasher, EntityManagerInterface $em, $entity)
    {
        $this->hasher = $hasher;
        $this->entity = $entity;
        $this->em     = $em;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     *
     * @return Authenticatable|null
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
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->getRepository()->findOneBy([
            $this->getEntity()->getAuthIdentifierName() => $identifier,
            $this->getEntity()->getRememberTokenName()  => $token
        ]);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param Authenticatable $user
     * @param string          $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
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
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $criteria = [];
        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $criteria[$key] = $value;
            }
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param Authenticatable $user
     * @param array           $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
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
     * @return Authenticatable
     */
    protected function getEntity()
    {
        $refEntity = new ReflectionClass($this->entity);

        return $refEntity->newInstanceWithoutConstructor();
    }

    /**
     * Returns entity namespace.
     * @return string
     */
    public function getModel()
    {
        return $this->entity;
    }
}
