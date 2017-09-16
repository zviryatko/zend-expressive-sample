<?php
/**
 * @file
 * Contains App\Action\ProfilePageAction.
 */

namespace App\Action;

use App\Entity\Profile;
use App\Helper\AuthenticationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

class ProfilePageAction extends AbstractPageAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $profile = $request->getAttribute(AuthenticationMiddleware::PROFILE_ATTRIBUTE);
        if (!$profile instanceof Profile) {
            return new RedirectResponse($this->router->generateUri('sign-in'));
        }
        $response->getBody()->write($this->renderer->render("app/profile.html.twig", ['profile' => $profile]));
        return $response;
    }
}