<?php
/**
 * @file
 * Contains App\Helper\TwigHelperMiddlewareFactory.
 */

namespace App\Helper;


use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateHelperMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new TemplateHelperMiddleware($container->get(TemplateRendererInterface::class));
    }
}