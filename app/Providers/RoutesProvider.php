<?php

namespace Application\Providers;

use Closure;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\RouteRegistrar;

/**
 * Class RoutesProvider
 * @package Application\Providers
 */
class RoutesProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'Application\Controllers';

    /**
     * Contains the routes for the application.
     */
    private static function routes(): void
    {
    }

    /**
     * Map routes for the application.
     */
    public function map(): void
    {
        $routeRegistrar = new RouteRegistrar(app('router'));
        $routeRegistrar->attribute('middleware', 'web');
        $routeRegistrar->attribute('namespace', $this->namespace);
        $routeRegistrar->group(Closure::fromCallable([ $this, 'routes' ]));
    }
}
