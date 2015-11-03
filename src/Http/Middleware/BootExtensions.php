<?php

namespace LaravelDoctrine\ORM\Http\Middleware;

use Closure;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;

class BootExtensions
{
    /**
     * @var ExtensionManager
     */
    protected $manager;

    /**
     * @param ExtensionManager $manager
     */
    public function __construct(ExtensionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->manager->boot();

        return $next($request);
    }
}
