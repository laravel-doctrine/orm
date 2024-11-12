<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Validation;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Str;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use InvalidArgumentException;

use function mb_substr;
use function sprintf;
use function str_replace;
use function substr;

class DoctrinePresenceVerifier implements DatabasePresenceVerifierInterface
{
    /**
     * The database connection to use.
     */
    protected mixed $connection = null;

    public function __construct(protected ManagerRegistry $registry)
    {
    }

    /**
     * Count the number of objects in a collection having the given value.
     */
    // phpcs:disable
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
    {
        // phpcs:enable
        $builder = $this->selectCount($collection);
        $builder->where('e.' . $column . ' = :' . $this->prepareParam($column));

        if ($excludeId !== null && $excludeId !== 'NULL') {
            $idColumn = $idColumn ?: 'id';
            $builder->andWhere('e.' . $idColumn . ' <> :' . $this->prepareParam($idColumn));
        }

        $this->queryExtraConditions($extra, $builder);

        $query = $builder->getQuery();
        $query->setParameter($this->prepareParam($column), $value);

        if ($excludeId !== null && $excludeId !== 'NULL') {
            $query->setParameter($this->prepareParam($idColumn), $excludeId);
        }

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Count the number of objects in a collection with the given values.
     */
    // phpcs:disable
    public function getMultiCount($collection, $column, array $values, array $extra = [])
    {
        // phpcs:enable
        $builder = $this->selectCount($collection);
        $builder->where($builder->expr()->in('e.' . $column, $values));

        $this->queryExtraConditions($extra, $builder);

        return (int) $builder->getQuery()->getSingleScalarResult();
    }

    protected function selectCount(string $entity): QueryBuilder
    {
        $em      = $this->getEntityManager($entity);
        $builder = $em->createQueryBuilder();

        $builder->select('count(e)')->from($entity, 'e');

        return $builder;
    }

    /** @param mixed[] $extra */
    protected function queryExtraConditions(array $extra, QueryBuilder $builder): void
    {
        foreach ($extra as $key => $extraValue) {
            if ($extraValue === 'NULL') {
                $builder->andWhere('e.' . $key . ' IS NULL');
            } elseif ($extraValue === 'NOT_NULL') {
                $builder->andWhere('e.' . $key . ' IS NOT NULL');
            } elseif (Str::startsWith($extraValue, '!')) {
                $builder->andWhere('e.' . $key . ' != :' . $this->prepareParam($key));
                $builder->setParameter($this->prepareParam($key), mb_substr($extraValue, 1));
            } else {
                $builder->andWhere('e.' . $key . ' = :' . $this->prepareParam($key));
                $builder->setParameter($this->prepareParam($key), $extraValue);
            }
        }
    }

    protected function getEntityManager(string $entity): mixed
    {
        if ($this->connection !== null) {
            return $this->registry->getManager($this->connection);
        }

        if (substr($entity, 0, 1) === '\\') {
            $entity = substr($entity, 1);
        }

        $em = $this->registry->getManagerForClass($entity);

        if ($em === null) {
            throw new InvalidArgumentException(sprintf('No Entity Manager could be found for [%s].', $entity));
        }

        return $em;
    }

    protected function prepareParam(string $column): string
    {
        return str_replace('.', '', $column);
    }

    /**
     * Set the connection to be used.
     *
     * Required by DatabasePresenceVerifierInterface
     *
     * @codeCoverageIgnoreStart
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
    public function setConnection($connection): void
    {
        // phpcs:enable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
        $this->connection = $connection;
    }

    /** @codeCoverageIgnoreStart */
}
