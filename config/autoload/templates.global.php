<?php

return [
    'dependencies' => [
        'invokables' => [
            App\Twig\Extensions\AuthenticationHelper::class => App\Twig\Extensions\AuthenticationHelper::class,
        ],
        'factories' => [
            'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            Zend\Expressive\Template\TemplateRendererInterface::class => Zend\Expressive\Twig\TwigRendererFactory::class,
            Twig_Environment::class => Zend\Expressive\Twig\TwigEnvironmentFactory::class,
            App\Twig\Extensions\ActiveClass::class => [App\Twig\Extensions\ActiveClass::class, 'factory'],
            App\Twig\Extensions\ElementError::class => [App\Twig\Extensions\ElementError::class, 'factory'],
        ],
    ],
    'templates' => [
        'debug' => true,
        'extension' => 'html.twig',
        'paths' => [['templates']],
    ],
    'twig' => [
        //'cache_dir'      => 'data/cache/twig',
        'assets_url' => '/',
        'assets_version' => null,
        'extensions' => [
            \App\Twig\Extensions\ActiveClass::class,
            \App\Twig\Extensions\ElementError::class,
            \App\Twig\Extensions\AuthenticationHelper::class,
        ],
    ],
];
