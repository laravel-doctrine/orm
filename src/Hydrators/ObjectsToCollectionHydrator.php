<?php

namespace LaravelDoctrine\ORM\Hydrators;

use Doctrine\ORM\Internal\Hydration\ObjectHydrator;
use Illuminate\Support\Collection;

class ObjectsToCollectionHydrator extends ObjectHydrator
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
