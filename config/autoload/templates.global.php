<?php

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' => Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            Zend\Expressive\Template\TemplateRendererInterface::class => Zend\Expressive\Twig\TwigRendererFactory::class,
            App\Twig\Extensions\ActiveClass::class => [App\Twig\Extensions\ActiveClass::class, 'factory']
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
            \App\Twig\Extensions\ActiveClass::class
        ],
    ],
];
