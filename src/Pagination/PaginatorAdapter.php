<?php

namespace Brouwers\LaravelDoctrine\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaginatorAdapter
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @param Query  $query
     * @param string $perPage
     * @param string $pageName
     * @param bool   $fetchJoinCollection
     *
     * @return LengthAwarePaginator
     */
    public function make(Query $query, $perPage = '15', $pageName = 'page', $fetchJoinCollection = true)
    {
        $this->query($query)
                ->skip($this->getSkipAmount($perPage, $pageName))
                ->take($perPage);

        return $this->convertToLaravelPaginator(
            $this->getDoctrinePaginator($fetchJoinCollection),
            $perPage,
            $pageName
        );
    }

    /**
     * @param   $query
     *
     * @return $this
     */
    protected function query($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $start
     *
     * @return $this
     */
    protected function skip($start)
    {
        $this->getQuery()->setFirstResult($start);

        return $this;
    }

    /**
     * @param $perPage
     *
     * @return $this
     */
    protected function take($perPage)
    {
        $this->getQuery()->setMaxResults($perPage);

        return $this;
    }

    /**
     * @param        $perPage
     * @param string $pageName
     *
     * @return int
     */
    protected function getSkipAmount($perPage, $pageName = 'page')
    {
        return ($this->getCurrentPage($pageName) - 1) * $perPage;
    }

    /**
     * @param $fetchJoinCollection
     *
     * @return DoctrinePaginator
     */
    private function getDoctrinePaginator($fetchJoinCollection)
    {
        return new DoctrinePaginator(
            $this->getQuery(),
            $fetchJoinCollection
        );
    }

    /**
     * @param DoctrinePaginator $doctrinePaginator
     * @param                   $perPage
     * @param string            $pageName
     *
     * @return LengthAwarePaginator
     */
    protected function convertToLaravelPaginator(DoctrinePaginator $doctrinePaginator, $perPage, $pageName = 'page')
    {
        $results     = $this->getResults($doctrinePaginator);
        $currentPage = $this->getCurrentPage($pageName);
        $path        = Paginator::resolveCurrentPath();

        return new LengthAwarePaginator(
            $results,
            $doctrinePaginator->count(),
            $perPage,
            $currentPage,
            compact('path')
        );
    }

    /**
     * @param DoctrinePaginator $doctrinePaginator
     *
     * @return array
     */
    protected function getResults(DoctrinePaginator $doctrinePaginator)
    {
        $results = [];
        foreach ($doctrinePaginator as $entity) {
            $results[] = $entity;
        };

        return $results;
    }

    /**
     * @param $pageName
     *
     * @return int
     */
    protected function getCurrentPage($pageName)
    {
        $page = Paginator::resolveCurrentPage($pageName);

        return $page > 0 ? $page : 1;
    }
}
