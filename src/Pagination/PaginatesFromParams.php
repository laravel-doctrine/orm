<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatesFromParams
{
    public function paginateAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $query = $this->createQueryBuilder('o')->getQuery();

        return $this->paginate($query, $perPage, $page, false);
    }

    public function paginate(AbstractQuery $query, int $perPage, int $page = 1, bool $fetchJoinCollection = true): LengthAwarePaginator
    {
        return PaginatorAdapter::fromParams(
            $query,
            $perPage,
            $page,
            $fetchJoinCollection,
        )->make();
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $indexBy The index for the from.
     */
    abstract public function createQueryBuilder(string $alias, string|null $indexBy = null): QueryBuilder;
}
