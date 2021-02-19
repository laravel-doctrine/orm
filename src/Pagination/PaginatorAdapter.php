<?php

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
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
     * @var int
     */
    private $perPage;

    /**
     * @var callable
     */
    private $pageResolver;

    /**
     * @var bool
     */
    private $fetchJoinCollection;

    /**
     * @var array
     */
    private $queryParams;

    /**
     * @param AbstractQuery $query
     * @param int           $perPage
     * @param callable      $pageResolver
     * @param bool          $fetchJoinCollection
     * @param array         $queryParams
     */
    private function __construct(AbstractQuery $query, $perPage, $pageResolver, $fetchJoinCollection, $queryParams = [])
    {
        $this->query               = $query;
        $this->perPage             = $perPage;
        $this->pageResolver        = $pageResolver;
        $this->fetchJoinCollection = $fetchJoinCollection;
        $this->queryParams         = $queryParams;
    }

    /**
     * @param AbstractQuery $query
     * @param int           $perPage
     * @param string        $pageName
     * @param bool          $fetchJoinCollection
     * @param array         $queryParams
     *
     * @return PaginatorAdapter
     */
    public static function fromRequest(AbstractQuery $query, $perPage = 15, $pageName = 'page', $fetchJoinCollection = true, $queryParams = [])
    {
        return new static(
            $query,
            $perPage,
            function () use ($pageName) {
                return Paginator::resolveCurrentPage($pageName);
            },
            $fetchJoinCollection,
            $queryParams
        );
    }

    /**
     * @param AbstractQuery $query
     * @param int           $perPage
     * @param int           $page
     * @param bool          $fetchJoinCollection
     * @param array         $queryParams
     *
     * @return PaginatorAdapter
     */
    public static function fromParams(AbstractQuery $query, $perPage = 15, $page = 1, $fetchJoinCollection = true, $queryParams = [])
    {
        return new static(
            $query,
            $perPage,
            function () use ($page) {
                return $page;
            },
            $fetchJoinCollection,
            $queryParams
        );
    }

    public function make()
    {
        $page = $this->getCurrentPage();

        $this->query($this->query)
             ->skip($this->getSkipAmount($this->perPage, $page))
             ->take($this->perPage);

        return $this->convertToLaravelPaginator(
            $this->getDoctrinePaginator(),
            $this->perPage,
            $page
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
     * @return AbstractQuery|Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function queryParams(array $params = [])
    {
        $this->queryParams = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
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
     * @param int $perPage
     * @param int $page
     *
     * @return int
     */
    protected function getSkipAmount($perPage, $page)
    {
        return ($page - 1) * $perPage;
    }

    /**
     * @return DoctrinePaginator
     */
    private function getDoctrinePaginator()
    {
        return new DoctrinePaginator(
            $this->getQuery(),
            $this->fetchJoinCollection
        );
    }

    /**
     * @param DoctrinePaginator $doctrinePaginator
     * @param int               $perPage
     * @param int               $page
     *
     * @return LengthAwarePaginator
     */
    protected function convertToLaravelPaginator(DoctrinePaginator $doctrinePaginator, $perPage, $page)
    {
        $results     = iterator_to_array($doctrinePaginator);
        $path        = Paginator::resolveCurrentPath();
        $query       = $this->queryParams;

        return new LengthAwarePaginator(
            $results,
            $doctrinePaginator->count(),
            $perPage,
            $page,
            compact('path', 'query')
        );
    }

    /**
     * @return int
     */
    protected function getCurrentPage()
    {
        $page = call_user_func($this->pageResolver);

        return $page > 0 ? $page : 1;
    }
}
