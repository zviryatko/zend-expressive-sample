<?php
/**
 * @file
 * Contains App\Helper\AuthenticationMiddleware.
 */

namespace App\Helper;

use App\Entity\Profile;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;

class AuthenticationMiddleware
{
    const PROFILE_ATTRIBUTE = 'profile';
    const USER_ID_SESSION_KEY = 'user_uuid';

    protected $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
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
        /** @var \PSR7Sessions\Storageless\Session\SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has('user_uuid')) {
            $profile = $this->repository->find($session->get(self::USER_ID_SESSION_KEY));
            if ($profile instanceof Profile) {
                $request = $request->withAttribute(self::PROFILE_ATTRIBUTE, $profile);
            }
        }
        return $next($request, $response);
    }

    /**
     * {@inheritdoc}
     */
    public static function factory(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $repository = $entityManager->getRepository(Profile::class);
        return new static($repository);
    }
}