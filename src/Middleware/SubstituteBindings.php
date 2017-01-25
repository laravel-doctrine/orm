<?php

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\Common\Persistence\Proxy;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Middleware\SubstituteBindings as IllumintateSubstituteBindings;
use LaravelDoctrine\ORM\IlluminateRegistry;
use ReflectionMethod;
use ReflectionFunction;

class SubstituteBindings extends IllumintateSubstituteBindings
{
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
        parent::__construct($router);

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
        $this->router->substituteBindings($route = $request->route());

        $this->router->substituteImplicitBindings($route);

        $this->substituteImplicitBindings($route);

        return $next($request);
    }

    /**
     * Substitute the implicit Doctrine entity bindings for the route.
     *
     * @param  \Illuminate\Routing\Route $route
     * @return void
     */
    protected function substituteImplicitBindings($route)
    {
        $parameters = $route->parameters();

        $action = $route->getAction();

        foreach ($this->getParameters($action['uses']) as $parameter) {
            $class = $parameter->getClass()->getName();

            // Try to find the entity manager for the given class.
            if (is_null($entityManager = $this->getDoctrineEntityManagerByClass($class))) {
                continue;
            }

            if (array_key_exists($parameter->name, $parameters)) {
                $entity = $entityManager->find($class, $parameters[$parameter->name]);

                $route->setParameter($parameter->name, $entity);
            }
        }
    }

    /**
     * Reflect the parameters of the method or function of the route.
     *
     * @param  string | Closure       $uses
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

    /**
     * Try to fetch the entity manager of the given class.
     *
     * @param  string                                     $class
     * @return \Doctrine\Common\Persistence\ObjectManager | null
     */
    protected function getDoctrineEntityManagerByClass($class)
    {
        if (is_object($class)) {
            $class = ($class instanceof Proxy) ? get_parent_class($class) : get_class($class);
        }

        return $this->registry->getManagerForClass($class);
    }
}
