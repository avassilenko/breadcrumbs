Installation

Configuration
  Package settings
  
  Automatic Breadcrumbs generation
     - settings
        config/crumby-crumbs/route-breadcrumbs.php

<?php
return [
    'routes' => [
        'package' => [
            'bind' => [
                'param' => 'package',
                'modelProp' => 'title'
            ],
            'childOf' => [
                'route' => 'packages'
            ]   
        ],
        'contactus' => [
            'childOf' => [
                'route' => 'aboutus'
            ]   
        ],
    ]
];

'label' key is used to resolve the Breadcrumb display name for route dynamic parameter {name}. 
In the case it is field "name" from model App\Product. If 'label' is not set we will try to resolve the display name by parameter name: 
Example: if dynamic parameter name is {name} then we will try  'product'.'name'.
NOTE: 'bind' => ['param', 'class'] are mostly duplicate of
public function boot()
{
    parent::boot();

    Route::bind('name', function ($value) {
        return App\Product::where('name', $value);
    });
} 
OR find instance by 'id'
public function boot()
{
    parent::boot();

    Route::model('name', App\Product::class);
}
