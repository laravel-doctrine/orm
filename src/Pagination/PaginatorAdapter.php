<?php

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaginatorAdapter
{
    /**
     * @var AbstractQuery
     */
    protected $query;

    /**
     * @param AbstractQuery $query
     * @param int           $perPage
     * @param string        $pageName
     * @param bool          $fetchJoinCollection
     *
     * @return LengthAwarePaginator
     */
    public function make(AbstractQuery $query, $perPage = 15, $pageName = 'page', $fetchJoinCollection = true)
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
     * @param AbstractQuery $query
     *
     * @return $this
     */
    protected function query(AbstractQuery $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return AbstractQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param int $start
     *
     * @return $this
     */
    protected function skip($start)
    {
        $this->getQuery()->setFirstResult($start);

        return $this;
    }

    /**
     * @param int $perPage
     *
     * @return $this
     */
    protected function take($perPage)
    {
        $this->getQuery()->setMaxResults($perPage);

        return $this;
    }

    /**
     * @param int    $perPage
     * @param string $pageName
     *
     * @return int
     */
    protected function getSkipAmount($perPage, $pageName = 'page')
    {
        return ($this->getCurrentPage($pageName) - 1) * $perPage;
    }

    /**
     * @param bool $fetchJoinCollection
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
     * @param int               $perPage
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
     * @param int $pageName
     *
     * @return int
     */
    protected function getCurrentPage($pageName)
    {
        $page = Paginator::resolveCurrentPage($pageName);

        return $page > 0 ? $page : 1;
    }
}
