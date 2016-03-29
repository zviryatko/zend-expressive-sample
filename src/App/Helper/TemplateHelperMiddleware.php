<?php
/**
 * @file
 * Contains App\Helper\TwigHelperMiddleware.
 */

namespace App\Helper;

use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TemplateHelperMiddleware
{
    /**
     * @var \Zend\Expressive\Template\TemplateRendererInterface
     */
    protected $renderer;


    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Inject the ServerRequestInterface instance to twig layout.
     *
     * Injects the ServerUrlHelper with the incoming request URI, and then invoke
     * the next middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->renderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'request', $request);
        $this->renderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'response', $response);
        return $next($request, $response);
    }
}