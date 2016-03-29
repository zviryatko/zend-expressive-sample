<?php
/**
 * @file
 * Contains App\Action\AbstractPageAction.
 */

namespace App\Action;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\ServiceManager;

abstract class AbstractPageAction
{
    /**
     * @var \Zend\Expressive\Router\RouterInterface
     */
    protected $router;

    /**
     * @var \Zend\Expressive\Template\TemplateRendererInterface
     */
    protected $renderer;

    public function __construct(RouterInterface $router, TemplateRendererInterface $renderer)
    {
        $this->router = $router;
        $this->renderer = $renderer;
    }

    public static function factory(ServiceManager $serviceManager)
    {
        return new static(
            $serviceManager->get(RouterInterface::class),
            $serviceManager->get(TemplateRendererInterface::class)
        );
    }

    abstract public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next);
}