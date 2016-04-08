<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
        'factories' => [
            App\Action\StaticPageAction::class => [App\Action\StaticPageAction::class, 'factory'],
            App\Action\DynamicPageAction::class => [App\Action\DynamicPageAction::class, 'factory'],
            App\Action\ContactPageAction::class => [App\Action\ContactPageAction::class, 'factory'],
        ],
    ],
    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\StaticPageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'about',
            'path' => '/about',
            'middleware' => App\Action\StaticPageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'services',
            'path' => '/services',
            'middleware' => App\Action\StaticPageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'contact',
            'path' => '/contact',
            'middleware' => App\Action\ContactPageAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'dynamic-page',
            'path' => '/{name:.+}',
            'middleware' => App\Action\DynamicPageAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
