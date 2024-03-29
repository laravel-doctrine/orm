<?php

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Route;
use LaravelDoctrine\ORM\Contracts\UrlRoutable;
use ReflectionParameter;
use function class_exists;
use function is_a;

class SubstituteBindings
{
    /**
     * The router instance.
     *
     * @var Registrar
     */
    protected $router;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param Registrar       $router
     * @param ManagerRegistry $registry
     */
    public function __construct(Registrar $router, ManagerRegistry $registry)
    {
        $this->router   = $router;
        $this->registry = $registry;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();

        $this->router->substituteBindings($route);

        $this->substituteImplicitBindings($route);

        return $next($request);
    }

    /**
     * Substitute the implicit Doctrine entity bindings for the route.
     *
     * @param Route $route
     *
     * @throws EntityNotFoundException
     */
    protected function substituteImplicitBindings(Route $route)
    {
        $parameters = $route->parameters();

        foreach ($this->signatureParameters($route) as $parameter) {
            $id    = $parameters[$parameter->name];
            $class = $this->getClassName($parameter);

            if ($class) {
                $repository = $this->registry->getRepository($class);

                if (is_a($class, UrlRoutable::class, true)) {
                    $name = call_user_func([$class, 'getRouteKeyNameStatic']);

                    $entity = $repository->findOneBy([
                        $name => $id
                    ]);
                } else {
                    $entity = $repository->find($id);
                }

                if (is_null($entity) && !$parameter->isDefaultValueAvailable()) {
                    throw EntityNotFoundException::fromClassNameAndIdentifier($class, ['id' => $id]);
                }

                $route->setParameter($parameter->name, $entity);
            }
        }
    }

    /**
     * @param Route $route
     *
     * @return ReflectionParameter[]
     */
    private function signatureParameters(Route $route)
    {
        return collect($route->signatureParameters())
            ->reject(function (ReflectionParameter $parameter) use ($route) {
                return !array_key_exists($parameter->name, $route->parameters());
            })
            ->reject(function (ReflectionParameter $parameter) {
                return !$this->getClassName($parameter);
            })->toArray();
    }

    /**
     * @return class-string
     */
    private function getClassName(ReflectionParameter $parameter): ?string
    {
        if (($type = $parameter->getType()) && $type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            $class = $type->getName();
            return class_exists($class) ? $class : null;
        }

        return null;
    }
}
