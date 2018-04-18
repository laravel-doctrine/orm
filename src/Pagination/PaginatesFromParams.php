<?php

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;

trait PaginatesFromParams
{
    /**
     * @param int $perPage
     * @param int $page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateAll($perPage = 15, $page = 1)
    {
        $query = $this->createQueryBuilder('o')->getQuery();

        return $this->paginate($query, $perPage, $page, false);
    }

    /**
     * @param AbstractQuery $query
     * @param int           $perPage
     * @param int           $page
     * @param bool          $fetchJoinCollection
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(AbstractQuery $query, $perPage, $page = 1, $fetchJoinCollection = true)
    {
        return PaginatorAdapter::fromParams(
            $query,
            $perPage,
            $page,
            $fetchJoinCollection
        )->make();
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy The index for the from.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);
}
