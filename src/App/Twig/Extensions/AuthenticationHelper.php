<?php
/**
 * @file
 * Contains App\Twig\Extensions\AuthenticationHelper.
 */

namespace App\Twig\Extensions;

use App\Entity\Profile;
use App\Helper\AuthenticationMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationHelper extends \Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'auth_helper';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_authorized', [$this, 'isAuthorized']),
            new \Twig_SimpleFunction('username', [$this, 'username']),
        ];
    }

    public function isAuthorized(ServerRequestInterface $request)
    {
        $profile = $request->getAttribute(AuthenticationMiddleware::PROFILE_ATTRIBUTE);
        return $profile instanceof Profile;
    }

    public function username(ServerRequestInterface $request)
    {
        $profile = $request->getAttribute(AuthenticationMiddleware::PROFILE_ATTRIBUTE);
        if (!$profile instanceof Profile) {
            return '';
        }
        return $profile->nickname();
    }
}