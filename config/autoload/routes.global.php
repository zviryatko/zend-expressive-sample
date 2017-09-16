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
            App\Action\SignUpPageAction::class => [App\Action\SignUpPageAction::class, 'factory'],
            App\Action\SignInPageAction::class => [App\Action\SignInPageAction::class, 'factory'],
            App\Action\SignOutPageAction::class => [App\Action\SignOutPageAction::class, 'factory'],
            App\Action\ProfilePageAction::class => [App\Action\ProfilePageAction::class, 'factory'],
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
            'name' => 'sign-up',
            'path' => '/sign-up',
            'middleware' => App\Action\SignUpPageAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'sign-in',
            'path' => '/sign-in',
            'middleware' => App\Action\SignInPageAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'sign-out',
            'path' => '/sign-out',
            'middleware' => App\Action\SignOutPageAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'profile',
            'path' => '/profile',
            'middleware' => App\Action\ProfilePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'dynamic-page',
            'path' => '/{name:.+}',
            'middleware' => App\Action\DynamicPageAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
