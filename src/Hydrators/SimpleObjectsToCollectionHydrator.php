<?php

namespace LaravelDoctrine\ORM\Hydrators;

use Doctrine\ORM\Internal\Hydration\SimpleObjectHydrator;
use Illuminate\Support\Collection;

class SimpleObjectsToCollectionHydrator extends SimpleObjectHydrator
{
    /**
     * Hydrates all rows from the current statement instance at once.
     *
     * @return Collection
     */
    protected function hydrateAllData()
    {
        return new Collection(
            parent::hydrateAllData()
        );
    }
}
