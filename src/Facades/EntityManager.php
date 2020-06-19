<?php

namespace LaravelDoctrine\ORM\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object|null find(string $className, mixed $id)
 * @method static void persist(object $object)
 * @method static void remove(object $object)
 * @method static object merge(object $object)
 * @method static void clear(string $objectName = null)
 * @method static void detach(object $object)
 * @method static void refresh(object $object)
 * @method static void flush()
 * @method static \Doctrine\Persistence\ObjectRepository getRepository(string $className)
 * @method static \Doctrine\Persistence\Mapping\ClassMetadata getClassMetadata(string $className)
 * @method static \Doctrine\Persistence\Mapping\ClassMetadataFactory getMetadataFactory()
 * @method static void initializeObject(object $obj)
 * @method static bool contains(object $object)
 * @method static \Doctrine\ORM\Cache|null getCache()
 * @method static \Doctrine\DBAL\Connection getConnection()
 * @method static \Doctrine\ORM\Query\Expr getExpressionBuilder()
 * @method static \Doctrine\ORM\Utility\IdentifierFlattener getIdentifierFlattener()
 * @method static void beginTransaction()
 * @method static mixed transactional(callable $func)
 * @method static void commit()
 * @method static void rollback()
 * @method static \Doctrine\ORM\Query createQuery(string $dql = '')
 * @method static \Doctrine\ORM\Query createNamedQuery(string $name)
 * @method static \Doctrine\ORM\NativeQuery createNativeQuery(string $sql, \Doctrine\ORM\Query\ResultSetMapping $rsm)
 * @method static \Doctrine\ORM\NativeQuery createNamedNativeQuery(string $name)
 * @method static \Doctrine\ORM\QueryBuilder createQueryBuilder()
 * @method static object getReference(string $entityName, mixed $id)
 * @method static object getPartialReference(string $entityName, mixed $identifier)
 * @method static void close()
 * @method static void copy(object $entity, bool $deep = false)
 * @method static void lock(object $entity, int $lockMode, int $lockVersion = null)
 * @method static \Doctrine\Common\EventManager getEventManager()
 * @method static \Doctrine\ORM\Configuration getConfiguration()
 * @method static bool isOpen()
 * @method static \Doctrine\ORM\UnitOfWork getUnitOfWork()
 * @method static \Doctrine\ORM\Internal\Hydration\AbstractHydrator getHydrator(int $ĥydrationMode)
 * @method static \Doctrine\ORM\Internal\Hydration\AbstractHydrator newHydrator(int $ĥydrationMode)
 * @method static \Doctrine\ORM\Proxy\ProxyFactory getProxyFactory()
 * @method static \Doctrine\ORM\Query\FilterCollection getFilters()
 * @method static bool isFiltersStateClean()
 * @method static bool hasFilters()
 */
class EntityManager extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'em';
    }
}
