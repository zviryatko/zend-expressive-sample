<?php
/**
 * @file
 * Contains App\Action\SignInPageAction.
 */

namespace App\Action;

use App\Entity\Profile;
use App\Helper\AuthenticationMiddleware;
use App\Service\Alert;
use App\Service\AlertsInterface;
use App\Service\FormAlert;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Exception\ExceptionInterface;

class SignInPageAction extends AbstractPageAction
{
    /**
     * @var \App\Service\AlertsInterface
     */
    protected $alerts;

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
        AlertsInterface $alerts,
        EntityRepository $repository
    ) {
        parent::__construct($router, $renderer);
        $this->alerts = $alerts;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function factory(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);
        $repository = $entityManager->getRepository(Profile::class);
        return new static(
            $container->get(RouterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(AlertsInterface::class),
            $repository
        );
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getAttribute(AuthenticationMiddleware::PROFILE_ATTRIBUTE)) {
            return new RedirectResponse($this->router->generateUri('home'));
        }
        $params = $request->getParsedBody() ?: [];
        if ($request->getMethod() === 'POST' && ($data = $this->validateData($params)) !== false && !empty($data['profile'])) {
            /** @var Profile $profile */
            $profile = $data['profile'];
            /** @var \PSR7Sessions\Storageless\Session\SessionInterface $session */
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            $session->set(AuthenticationMiddleware::USER_ID_SESSION_KEY, $profile->id());
            return new RedirectResponse($this->router->generateUri('home'));
        }
        $response->getBody()->write($this->renderer->render("app/sign-in.html.twig", $params));
        return $response;
    }

    /**
     * Validate contact form data and set alerts if fails.
     *
     * @param array $data
     *   Contact form post data.
     *
     * @return bool
     *   false if validation fails.
     */
    protected function validateData(array $data)
    {
        $hasError = false;
        $fields = [
            'email' => FILTER_SANITIZE_EMAIL,
            'password' => FILTER_SANITIZE_ENCODED,
        ];
        $data = filter_var_array($data, $fields, true);

        if (count(array_filter($data)) !== count($fields)) {
            $this->alerts->addDanger(new FormAlert('All fields are required.', 'sign_in_form'));
            $hasError = true;
        }

        if (!$hasError && !empty($data['email'])) {
            $profile = $this->repository->findOneBy(['mail' => $data['email']]);
            if ($profile instanceof Profile && $profile->verifyPassword($data['password'])) {
                $data['profile'] = $profile;
            } else {
                $this->alerts->addDanger(new FormAlert('Invalid credentials.', 'sign_in_form'));
                $hasError = true;
            }
        }

        return $hasError ? false : $data;
    }
}
