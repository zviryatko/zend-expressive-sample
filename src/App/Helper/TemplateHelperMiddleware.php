<?php
/**
 * @file
 * Contains App\Helper\TwigHelperMiddleware.
 */

namespace App\Helper;

use App\Service\AlertsInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TemplateHelperMiddleware
{
    /**
     * @var \Zend\Expressive\Template\TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var \App\Service\AlertsInterface
     */
    protected $alerts;

    public function __construct(TemplateRendererInterface $renderer, AlertsInterface $alerts)
    {
        $this->renderer = $renderer;
        $this->alerts = $alerts;
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
        $this->renderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'errors', $this->alerts);
        return $next($request, $response);
    }
}