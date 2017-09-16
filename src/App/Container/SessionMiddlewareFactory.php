<?php
/**
 * @file
 * Contains App\Container\SessionMiddlewareFactory.
 */

namespace App\Container;

use Dflydev\FigCookies\SetCookie;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Time\SystemCurrentTime;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class SessionMiddlewareFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (empty($container->get('config')['session_key'])) {
            throw new ServiceNotCreatedException("Provide session_key configuration option.");
        }
        $key = $container->get('config')['session_key'];
        $expirationTime = 60 * 60 * 24 * 7;
        return new SessionMiddleware(
            new Sha256(),
            $key,
            $key,
            SetCookie::create(SessionMiddleware::DEFAULT_COOKIE)
                ->withSecure(false)
                ->withHttpOnly(true)
                ->withPath('/'),
            new Parser(),
            $expirationTime,
            new SystemCurrentTime()
        );
    }
}