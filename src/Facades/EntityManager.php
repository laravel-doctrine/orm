<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Facades;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Utility\IdentifierFlattener;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
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
 * @method static EntityRepository getRepository(string $className)
 * @method static ClassMetadata getClassMetadata(string $className)
 * @method static ClassMetadataFactory getMetadataFactory()
 * @method static void initializeObject(object $obj)
 * @method static bool contains(object $object)
 * @method static Cache|null getCache()
 * @method static Connection getConnection()
 * @method static Expr getExpressionBuilder()
 * @method static IdentifierFlattener getIdentifierFlattener()
 * @method static void beginTransaction()
 * @method static mixed transactional(callable $func)
 * @method static void commit()
 * @method static void rollback()
 * @method static Query createQuery(string $dql = '')
 * @method static Query createNamedQuery(string $name)
 * @method static NativeQuery createNativeQuery(string $sql, ResultSetMapping $rsm)
 * @method static NativeQuery createNamedNativeQuery(string $name)
 * @method static QueryBuilder createQueryBuilder()
 * @method static object getReference(string $entityName, mixed $id)
 * @method static object getPartialReference(string $entityName, mixed $identifier)
 * @method static void close()
 * @method static void copy(object $entity, bool $deep = false)
 * @method static void lock(object $entity, int $lockMode, int $lockVersion = null)
 * @method static EventManager getEventManager()
 * @method static Configuration getConfiguration()
 * @method static bool isOpen()
 * @method static UnitOfWork getUnitOfWork()
 * @method static AbstractHydrator getHydrator(int $ĥydrationMode)
 * @method static AbstractHydrator newHydrator(int $ĥydrationMode)
 * @method static ProxyFactory getProxyFactory()
 * @method static FilterCollection getFilters()
 * @method static bool isFiltersStateClean()
 * @method static bool hasFilters()
 */
class EntityManager extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'em';
    }
}
