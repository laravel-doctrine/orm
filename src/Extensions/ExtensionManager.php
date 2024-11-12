<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;

use function assert;
use function count;
use function is_array;

class ExtensionManager
{
    /** @var string[] */
    protected array $extensions = [];

    /** @var string[] */
    protected array $bootedExtensions = [];

    public function __construct(protected Container $container)
    {
    }

    /**
     * Boot the extensions
     */
    public function boot(ManagerRegistry $registry): void
    {
        foreach ($registry->getManagers() as $connection => $em) {
            assert($em instanceof EntityManagerInterface);
            foreach ($this->extensions as $extension) {
                $extension = $this->container->make($extension);

                if (! $this->notBootedYet($connection, $extension)) {
                    continue;
                }

                $this->bootExtension(
                    $connection,
                    $extension,
                    $em,
                    $em->getEventManager(),
                    $em->getConfiguration(),
                );
            }
        }
    }

    public function needsBooting(): bool
    {
        return count($this->extensions) > 0;
    }

    public function register(mixed $extension): void
    {
        $this->extensions[] = $extension;
    }

    protected function bootExtension(
        mixed $connection,
        Extension $extension,
        EntityManagerInterface $em,
        EventManager $evm,
        Configuration $configuration,
    ): void {
        $extension->addSubscribers($evm, $em);

        if (is_array($extension->getFilters())) {
            foreach ($extension->getFilters() as $name => $filter) {
                $configuration->addFilter($name, $filter);
                $em->getFilters()->enable($name);
            }
        }

        $this->markAsBooted($connection, $extension);
    }

    protected function notBootedYet(string $connection, Extension $extension): bool
    {
        return ! isset($this->bootedExtensions[$connection][$extension::class]);
    }

    protected function markAsBooted(string $connection, Extension $extension): void
    {
        $this->bootedExtensions[$connection][$extension::class] = true;
    }

    /** @return array|Extension[] */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /** @return string[] */
    public function getBootedExtensions(): array
    {
        return $this->bootedExtensions;
    }
}
