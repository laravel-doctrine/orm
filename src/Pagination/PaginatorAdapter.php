<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use function call_user_func;

class PaginatorAdapter
{
    /** @var callable */
    private $pageResolver;

    /** @param mixed[] $queryParams */
    private function __construct(protected AbstractQuery $query, private int $perPage, callable $pageResolver, private bool $fetchJoinCollection, private array $queryParams = [])
    {
        $this->pageResolver = $pageResolver;
    }

    /** @param mixed[] $queryParams */
    public static function fromRequest(AbstractQuery $query, int $perPage = 15, string $pageName = 'page', bool $fetchJoinCollection = true, array $queryParams = []): PaginatorAdapter
    {
        return new self(
            $query,
            $perPage,
            static function () use ($pageName) {
                return Paginator::resolveCurrentPage($pageName);
            },
            $fetchJoinCollection,
            $queryParams,
        );
    }

    /** @param mixed[] $queryParams */
    public static function fromParams(AbstractQuery $query, int $perPage = 15, int $page = 1, bool $fetchJoinCollection = true, array $queryParams = []): PaginatorAdapter
    {
        return new self(
            $query,
            $perPage,
            static function () use ($page) {
                return $page;
            },
            $fetchJoinCollection,
            $queryParams,
        );
    }

    public function make(): LengthAwarePaginator
    {
        $page = $this->getCurrentPage();

        $this->query($this->query)
             ->skip($this->getSkipAmount($this->perPage, $page))
             ->take($this->perPage);

        return $this->convertToLaravelPaginator(
            $this->getDoctrinePaginator(),
            $this->perPage,
            $page,
        );
    }

    /** @return $this */
    protected function query(AbstractQuery $query)
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery(): AbstractQuery|Query
    {
        return $this->query;
    }

    /** @param mixed[] $params */
    public function queryParams(array $params = []): self
    {
        $this->queryParams = $params;

        return $this;
    }

    /** @return mixed[] */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    protected function skip(int $start): self
    {
        $this->getQuery()->setFirstResult($start);

        return $this;
    }

    protected function take(int $perPage): self
    {
        $this->getQuery()->setMaxResults($perPage);

        return $this;
    }

    protected function getSkipAmount(int $perPage, int $page): int
    {
        return ($page - 1) * $perPage;
    }

    private function getDoctrinePaginator(): DoctrinePaginator
    {
        return new DoctrinePaginator(
            $this->getQuery(),
            $this->fetchJoinCollection,
        );
    }

    protected function convertToLaravelPaginator(DoctrinePaginator $doctrinePaginator, int $perPage, int $page): LengthAwarePaginator
    {
        $path  = Paginator::resolveCurrentPath();
        $query = $this->queryParams;

        return new LengthAwarePaginator(
            $doctrinePaginator->getQuery()->getResult(),
            $doctrinePaginator->count(),
            $perPage,
            $page,
            [
                'path' => $path,
                'query' => $query,
            ],
        );
    }

    protected function getCurrentPage(): int
    {
        $page = call_user_func($this->pageResolver);

        return $page > 0 ? $page : 1;
    }
}
