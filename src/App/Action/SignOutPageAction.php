<?php
/**
 * @file
 * Contains App\Action\SignOutPageAction.
 */

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Zend\Diactoros\Response\RedirectResponse;

class SignOutPageAction extends AbstractPageAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var \PSR7Sessions\Storageless\Session\SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->clear();
        return new RedirectResponse($this->router->generateUri('home'));
    }
}
