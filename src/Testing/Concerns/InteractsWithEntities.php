<?php

namespace LaravelDoctrine\ORM\Testing\Concerns;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;

trait InteractsWithEntities
{
    /**
     * @param string $class
     * @param mixed  $id
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return object
     */
    public function entityExists($class, $id)
    {
        $entity = $this->entityManager()->find($class, $id);

        Assert::assertNotNull($entity, "A [$class] entity was not found by id: " . print_r($id, true));

        return $entity;
    }

    /**
     * @param string $class
     * @param mixed  $id
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return void
     */
    public function entityDoesNotExist($class, $id)
    {
        Assert::assertNull(
            $this->entityManager()->find($class, $id),
            "A [$class] entity was found by id: " . print_r($id, true)
        );
    }

    /**
     * @param string   $class
     * @param array    $criteria
     * @param int|null $count
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return object[]
     */
    public function entitiesMatch($class, array $criteria, $count = null)
    {
        $entities = $this->entityManager()->getRepository($class)->findBy($criteria);

        Assert::assertNotEmpty($entities, "No [$class] entities were found with the given criteria: " . $this->outputCriteria($criteria));

        if ($count !== null) {
            Assert::assertCount(
                $count,
                $entities,
                "Expected to find $count [$class] entities, but found " . count($entities) .
                ' with the given criteria: ' . $this->outputCriteria($criteria)
            );
        }

        return $entities;
    }

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return void
     */
    public function noEntitiesMatch($class, array $criteria)
    {
        Assert::assertEmpty(
            $this->entityManager()->getRepository($class)->findBy($criteria),
            "Some [$class] entities were found with the given criteria: " . $this->outputCriteria($criteria)
        );
    }

    /**
     * Replaces entities with their ids in the criteria array and print_r them
     *
     * @param  array  $criteria
     * @return string
     */
    private function outputCriteria(array $criteria)
    {
        $criteria = collect($criteria)->map(function ($value) {
            if (!is_object($value)) {
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

    /**
     * @throws \PHPUnit_Framework_SkippedTestError
     * @return EntityManager
     */
    protected function entityManager()
    {
        if (!isset($this->app)) {
            Assert::markTestSkipped(
                'Tests that interact with entities through Doctrine need to have Laravel\'s Application object.' . PHP_EOL .
                'Please extend Laravel\'s TestCase to use this trait.'
            );
        }

        return $this->app->make('em');
    }
}
