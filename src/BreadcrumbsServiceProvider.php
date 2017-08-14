<?php

namespace Crumby\Breadcrumbs;

use Illuminate\Support\ServiceProvider;

class BreadcrumbsServiceProvider extends ServiceProvider
{
    const BREADCRUMBS_VAR_NAME = 'breadcrumbs';
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::BREADCRUMBS_VAR_NAME, function ($app) {
            $breadcrumbs = new Breadcrumbs();

            \View::share(self::BREADCRUMBS_VAR_NAME, $breadcrumbs);
            return $breadcrumbs;
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/crumby-crumbs/breadcrumbs.php' => config_path('crumby-crumbs/breadcrumbs.php'),
                __DIR__.'/config/crumby-crumbs/route-breadcrumbs.php' => config_path('crumby-crumbs/route-breadcrumbs.php')
            ], 'config');
            $this->publishes([
                __DIR__.'/resources/lang/en/breadcrumbs.php' => resource_path('lang/en/breadcrumbs.php')
            ], 'lang');
        }
        
        $this->app->alias(self::BREADCRUMBS_VAR_NAME, 'Crumby\Breadcrumbs\Breadcrumbs');
        \Breadcrumbs::loadConfig();
    }
}
