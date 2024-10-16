<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Testing\Concerns;

use PHPUnit\Framework\Assert;

use function collect;
use function count;
use function is_object;
use function print_r;

use const PHP_EOL;

trait InteractsWithEntities
{
    /** @return object */
    public function entityExists(string $class, mixed $id): mixed
    {
        $entity = $this->entityManager()->find($class, $id);

        Assert::assertNotNull($entity, 'A [' . $class . '] entity was not found by id: ' . print_r($id, true));

        return $entity;
    }

    public function entityDoesNotExist(string $class, mixed $id): void
    {
        Assert::assertNull(
            $this->entityManager()->find($class, $id),
            'A [' . $class . '] entity was found by id: ' . print_r($id, true),
        );
    }

    /**
     * @param mixed[] $criteria
     *
     * @return mixed[]
     */
    public function entitiesMatch(string $class, array $criteria, int|null $count = null): mixed
    {
        $entities = $this->entityManager()->getRepository($class)->findBy($criteria);

        Assert::assertNotEmpty($entities, 'No [' . $class . '] entities were found with the given criteria: ' . $this->outputCriteria($criteria));

        if ($count !== null) {
            Assert::assertCount(
                $count,
                $entities,
                'Expected to find ' . $count . ' [' . $class . '] entities, but found ' . count($entities) .
                ' with the given criteria: ' . $this->outputCriteria($criteria),
            );
        }

        return $entities;
    }

    /** @param mixed[] $criteria */
    public function noEntitiesMatch(string $class, array $criteria): void
    {
        Assert::assertEmpty(
            $this->entityManager()->getRepository($class)->findBy($criteria),
            'Some [' . $class . '] entities were found with the given criteria: ' . $this->outputCriteria($criteria),
        );
    }

    /**
     * Replaces entities with their ids in the criteria array and print_r them
     *
     * @param mixed[] $criteria
     */
    private function outputCriteria(array $criteria): string
    {
        $criteria = collect($criteria)->map(function ($value) {
            if (! is_object($value)) {
                return $value;
            }

            $unityOfWork = $this->entityManager()->getUnitOfWork();
            if ($unityOfWork->isInIdentityMap($value)) {
                return $unityOfWork->getEntityIdentifier($value);
            }

            return $value;
        })->all();

        return print_r($criteria, true);
    }

    protected function entityManager(): mixed
    {
        if (! isset($this->app)) {
            Assert::markTestSkipped(
                'Tests that interact with entities through Doctrine need to have Laravel\'s Application object.' . PHP_EOL .
                'Please extend Laravel\'s TestCase to use this trait.',
            );
        }

        return $this->app->make('em');
    }
}
