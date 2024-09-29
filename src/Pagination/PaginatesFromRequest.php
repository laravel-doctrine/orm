<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatesFromRequest
{
    public function paginateAll(int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        $query = $this->createQueryBuilder('o')->getQuery();

        return $this->paginate($query, $perPage, $pageName, false);
    }

    public function paginate(AbstractQuery $query, int $perPage, string $pageName = 'page', bool $fetchJoinCollection = true): LengthAwarePaginator
    {
        return PaginatorAdapter::fromRequest(
            $query,
            $perPage,
            $pageName,
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
