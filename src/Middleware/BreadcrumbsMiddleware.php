<?php

namespace Crumby\Breadcrumbs\Middleware;
use Crumby\Breadcrumbs\Breadcrumbs as Breadcrumbs;
use Closure;

class BreadcrumbsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // set Home
        \Breadcrumbs::addCrumb(trans('breadcrumbs.home'), "/");
        
        // if current route is not home add next level
        if (\Route::current()->getName() != 'home') {
            \Breadcrumbs::addCrumbRecurcive();
        }
        
        return $next($request);
    }
}
