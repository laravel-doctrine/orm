<?php

namespace LaravelDoctrine\ORM\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;

class BootExtensionsMiddleware
{
    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    /**
     * @param $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->extensionManager->boot();

        return $next($request);
    }
}
