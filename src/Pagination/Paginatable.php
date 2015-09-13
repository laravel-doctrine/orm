<?php

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\Query;

trait Paginatable
{
    /**
     * @param int    $perPage
     * @param string $pageName
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateAll($perPage = 15, $pageName = 'page')
    {
        $query = $this->createQueryBuilder('o')->getQuery();

        return $this->paginate($query, $perPage, $pageName);
    }

    /**
     * @param Query  $query
     * @param int    $perPage
     * @param bool   $fetchJoinCollection
     * @param string $pageName
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(Query $query, $perPage, $pageName = 'page', $fetchJoinCollection = true)
    {
        return (new PaginatorAdapter())->make(
            $query,
            $perPage,
            $pageName,
            $fetchJoinCollection
        );
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
