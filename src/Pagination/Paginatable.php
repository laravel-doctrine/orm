<?php

namespace LaravelDoctrine\ORM\Pagination;

/**
 * @deprecated Backwards compatibility trait. You should switch to use one of the specific Paginator traits:
 *
 * @see PaginatesFromRequest
 * @see PaginatesFromParams
 */
trait Paginatable
{
    use PaginatesFromRequest;
}
