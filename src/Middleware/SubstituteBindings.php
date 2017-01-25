<?php

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\ORM\EntityNotFoundException;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Route;
use LaravelDoctrine\ORM\IlluminateRegistry;
use ReflectionFunction;
use ReflectionMethod;

class SubstituteBindings
{
    /**
     * The router instance.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * @var IlluminateRegistry
     */
    protected $registry;

    /**
     * Create a new bindings substitutor.
     *
     * @param Registrar          $router
     * @param IlluminateRegistry $registry
     */
    public function __construct(Registrar $router, IlluminateRegistry $registry)
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
        $this->substituteImplicitBindings($request->route());

        return $next($request);
    }

    /**
     * Substitute the implicit Doctrine entity bindings for the route.
     *
     * @param Route $route
     */
    protected function substituteImplicitBindings(Route $route)
    {
        $parameters = $route->parameters();

        $action = $route->getAction();

        foreach ($this->getParameters($action['uses']) as $parameter) {
            if (! array_key_exists($parameter->name, $parameters)) {
                continue;
            }

            // Make sure this parameter is a class.
            if (! $parameter->getClass()) {
                continue;
            }

            $class = $parameter->getClass()->getName();

            // Try to find the entity manager for the given class.
            if (is_null($entityManager = $this->registry->getManagerForClass($class))) {
                continue;
            }

            // Find the entity by route parameter value.
            $entity = $entityManager->find($class, $parameters[$parameter->name]);

            // When no entity is found check if the route accepts an empty entity.
            if (is_null($entity) && ! $parameter->isDefaultValueAvailable()) {
                throw new EntityNotFoundException(sprintf('No query results for entity [%s]', $class));
            }

            $route->setParameter($parameter->name, $entity);
        }
    }

    /**
     * Reflect the parameters of the method or function of the route.
     *
     * @param  string|callable        $uses
     * @return \ReflectionParameter[]
     */
    protected function getParameters($uses)
    {
        if (is_string($uses)) {
            list($class, $method) = explode('@', $uses);

            return (new ReflectionMethod($class, $method))->getParameters();
        }

        return $parameters = (new ReflectionFunction($uses))->getParameters();
    }
}
