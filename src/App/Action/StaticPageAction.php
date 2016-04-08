<?php
/**
 * @file
 * Contains App\Action\HomePageAction.
 */

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StaticPageAction extends AbstractPageAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $this->router->match($request);
        $routeName = $route->getMatchedRouteName();
        $response->getBody()->write($this->renderer->render("app/{$routeName}.html.twig"));
        return $response;
    }
}