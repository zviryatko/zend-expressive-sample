<?php
/**
 * @file
 * Contains App\Action\DynamicPageAction.
 */

namespace App\Action;

use App\Entity\DynamicPage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class DynamicPageAction extends AbstractPageAction
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        RouterInterface $router,
        TemplateRendererInterface $renderer,
        EntityRepository $repository
    ) {
        parent::__construct($router, $renderer);
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function factory(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $repository = $entityManager->getRepository(DynamicPage::class);
        return new static(
            $container->get(RouterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $repository
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $alias = $this->router->match($request)->getMatchedParams()['name'];
        $page = $this->repository->findOneBy(['alias' => $alias]);
        if (!$page instanceof DynamicPage) {
            return $next($request, $response->withStatus(404), 'Page Not Found');
        }
        $response->getBody()->write($this->renderer->render("app/dynamic-page.html.twig"));
        return $response;
    }
}