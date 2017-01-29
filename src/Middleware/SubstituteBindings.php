<?php

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityNotFoundException;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Route;
use ReflectionParameter;

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
            $class = $parameter->getClass()->getName();

            if ($em = $this->registry->getManagerForClass($class)) {
                $entity = $em->find($class, $id);

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
                return !$parameter->getClass();
            })->toArray();
    }
}
