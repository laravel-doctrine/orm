
==========
Pagination
==========

If you want to add easy pagination, you can add the
``LaravelDoctrine\ORM\Pagination\PaginatesFromRequest`` trait to your
repositories. It offers two methods:
``paginateAll($perPage = 15, $pageName = 'page')``
and
``paginate($query, $perPage, $pageName = 'page', $fetchJoinCollection = true)``.

``paginateAll`` will work out of the box, and will return all
(non-deleted, when soft-deletes are enabled) entities inside Laravel's
``LengthAwarePaginator``.

If you want to add pagination to your custom queries, you will have to pass
the query object through ``paginate()``.

.. code-block:: php

  public function paginateAllPublishedScientists($perPage = 15, $pageName = 'page')
  {
      $builder = $this->createQueryBuilder('o');
      $builder->where('o.status = 1');

      return $this->paginate($builder->getQuery(), $perPage, $pageName);
  }

The ``PaginatesFromRequest`` trait uses Laravel's ``Request`` object to
fetch current page, just as ``Eloquent`` does by default. If this doesn't
fit your scenario, you can also take advantage of pagination in Doctrine
with the ``LaravelDoctrine\ORM\Pagination\PaginatesFromParams`` trait:

.. code-block:: php

  namespace App\Repositories;

  use Illuminate\Contracts\Pagination\LengthAwarePaginator;
  use LaravelDoctrine\ORM\Pagination\PaginatesFromParams;

  class DoctrineScientistRepository
  {
      use PaginatesFromParams;

      /**
      * @return Scientist[]|LengthAwarePaginator
      */
      public function all(int $limit = 8, int $page = 1): LengthAwarePaginator
      {
          // paginateAll is already public, you may use it directly as well.
          return $this->paginateAll($limit, $page);
      }

      /**
      * @return Scientist[]|LengthAwarePaginator
      */
      public function findByName(string $name, int $limit = 8, int $page = 1): LengthAwarePaginator
      {
          $query = $this->createQueryBuilder('s')
              ->where('s.name LIKE :name')
              ->orderBy('s.name', 'asc')
              ->setParameter('name', "%$name%")
              ->getQuery();

          return $this->paginate($query, $limit, $page);
      }
  }

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
