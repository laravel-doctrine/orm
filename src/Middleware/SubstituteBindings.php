<?php

namespace LaravelDoctrine\ORM\Middleware;

use Closure;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Contracts\UrlRoutable;
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

        //$this->substituteImplicitBindings($route);

        $this->resolveForRoute($route);

        return $next($request);
    }

    /**
     * Resolve the implicit route bindings for the given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function resolveForRoute($route)
    {
        $parameters = $route->parameters();

        foreach ($this->signatureParameters($route) as $parameter) {
            if (! $parameterName = static::getParameterName($parameter->name, $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }

            if ($repository = $this->registry->getRepository($parameter->getClass()->name)) {

                $parent = $route->parentOfParameter($parameterName);

                if ($parent !== null && $route->bindingFieldFor($parameterName)) {
                    $entity = $this->resolveChildRouteBinding(
                        $parent,
                        $parameterName,
                        $parameterValue,
                        $route->bindingFieldFor($parameterName)
                    );

                    if (!$entity) {
                        throw EntityNotFoundException::fromClassNameAndIdentifier(
                            $parameter->getClass()->name,
                            [$route->bindingFieldFor($parameterName) => $parameterValue]
                        );
                    }
                } else {
                    if ($route->bindingFieldFor($parameterName)) {
                        $entity = $repository->findOneBy([$route->bindingFieldFor($parameterName) => $parameterValue]);
                    } else {
                        $entity = $repository->find($parameterValue);
                    }
                    if (!$entity) {
                        throw EntityNotFoundException::fromClassNameAndIdentifier(
                            $parameter->getClass()->name,
                            [$route->bindingFieldFor($parameterName) => $parameterValue]
                        );
                    }
                }

                $route->setParameter($parameterName, $entity);
            }
        }
    }
    /**
     * Retrieve the child entity for a bound value.
     *
     * @param  object  $parent
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function resolveChildRouteBinding($parent, $childType, $value, $field)
    {
        // This isn't efficient if the relationship has many associated children
        // It would be better to have the database do the filtering.
        return $parent->{'get' . Str::plural(Str::studly($childType))}()
            ->filter(function ($child) use ($field, $value) {
                return $child->{'get' . Str::studly($field)}() == $value;
            })
            ->first();
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

    /**
     * Return the parameter name if it exists in the given parameters.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return string|null
     */
    protected function getParameterName($name, $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }

        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }
    }
}
