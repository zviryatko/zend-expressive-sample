<?php
/**
 * @file
 * Contains App\Action\HomePageAction.
 */

namespace App\Action;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StaticPageAction extends AbstractPageAction
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $this->router->match($request);
        $routeName = $route->getMatchedRouteName();
        $response->getBody()->write($this->renderer->render("app/{$routeName}.html.twig"));
        return $response;
    }
}