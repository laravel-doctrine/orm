<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use LaravelDoctrine\ORM\Contracts\UrlRoutable;
use ReflectionNamedType;
use ReflectionParameter;

use function array_key_exists;
use function call_user_func;
use function class_exists;
use function collect;
use function is_a;

class SubstituteBindings
{
    /**
     * The router instance.
     */
    protected Registrar $router;

    public function __construct(Registrar $router, protected ManagerRegistry $registry)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();

        $this->router->substituteBindings($route);

        $this->substituteImplicitBindings($route);

        return $next($request);
    }

    /**
     * Substitute the implicit Doctrine entity bindings for the route.
     *
     * @throws EntityNotFoundException
     */
    protected function substituteImplicitBindings(Route $route): void
    {
        $parameters = $route->parameters();

        foreach ($this->signatureParameters($route) as $parameter) {
            $id    = $parameters[$parameter->name];
            $class = $this->getClassName($parameter);

            if (! $class) {
                continue;
            }

            $repository = $this->registry->getRepository($class);

            if (is_a($class, UrlRoutable::class, true)) {
                $name = call_user_func([$class, 'getRouteKeyNameStatic']);

                $entity = $repository->findOneBy([$name => $id]);
            } else {
                $entity = $repository->find($id);
            }

            if ($entity === null && ! $parameter->isDefaultValueAvailable()) {
                throw EntityNotFoundException::fromClassNameAndIdentifier($class, ['id' => $id]);
            }

            $route->setParameter($parameter->name, $entity);
        }
    }

    /** @return ReflectionParameter[] */
    private function signatureParameters(Route $route): array
    {
        return collect($route->signatureParameters())
            ->reject(static function (ReflectionParameter $parameter) use ($route) {
                return ! array_key_exists($parameter->name, $route->parameters());
            })
            ->reject(function (ReflectionParameter $parameter) {
                return ! $this->getClassName($parameter);
            })->toArray();
    }

    /** @return class-string */
    private function getClassName(ReflectionParameter $parameter): string|null
    {
        $type = $parameter->getType();
        if ($type && $type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $class = $type->getName();

            return class_exists($class) ? $class : null;
        }

        return null;
    }
}
