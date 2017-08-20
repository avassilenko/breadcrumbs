Installation:
-------------
```
> composer require crumby/breadcrumbs:"dev-master"
> php artisan vendor:publish --provider="Crumby\Breadcrumbs\BreadcrumbsServiceProvider" --tag=config
> php artisan vendor:publish --provider="Crumby\Breadcrumbs\BreadcrumbsServiceProvider" --tag=lang
```

Register service and facade:
----------------------------
File: config/app.php

```
'providers' => [
    ......................
    'Crumby\Breadcrumbs\BreadcrumbsServiceProvider',
    ........................
 ];
 
 'aliases' => [ 
    ......................
    'Breadcrumbs' => 'Crumby\Breadcrumbs\Facades\Breadcrumbs',
    ......................
 ];
```

Configuration:
-------------     
- Automatic Breadcrumbs generation config/crumby-crumbs/route-breadcrumbs.php        
<?php
return [
    'routes' => [
        'package' => [
            'childOf' => [
                'route' => 'packages'
            ]   
        ],
        'article' => [
            'childOf' => [
                'route' => 'package'
            ]   
        ],
    ]
];

- 'label' key can be used to resolve the Breadcrumb display name for route static url. 
- If there is(are) dynamic parameter(s) in url, RouteResolver should be configured.

        
Example:
--------
```
    class StaticPagesController extends Controller {
        public function __construct()
        {
            ...........................
            $this->middleware('crumbs');
            ...........................
        }
    }
```
