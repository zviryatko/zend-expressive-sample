<?php
/**
 * @file
 * Contains App\Action\SignUpPageAction.
 */

namespace App\Action;

use App\Entity\Profile;
use App\Helper\AuthenticationMiddleware;
use App\Service\Alert;
use App\Service\AlertsInterface;
use App\Service\FormAlert;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Exception\ExceptionInterface;

class SignUpPageAction extends AbstractPageAction
{
    /**
     * @var \App\Service\AlertsInterface
     */
    protected $alerts;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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
        EntityManager $em
    ) {
        parent::__construct($router, $renderer);
        $this->alerts = $alerts;
        $this->em = $em;
        $this->repository = $em->getRepository(Profile::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function factory(ContainerInterface $container)
    {
        return new static(
            $container->get(RouterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(AlertsInterface::class),
            $container->get(EntityManager::class)
        );
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getAttribute(AuthenticationMiddleware::PROFILE_ATTRIBUTE)) {
            return new RedirectResponse($this->router->generateUri('home'));
        }
        $params = $request->getParsedBody() ?: [];
        if ($request->getMethod() === 'POST' && ($data = $this->validateData($params)) !== false && !empty($data['email'])) {
            try {
                $profile = new Profile(null, $data['nickname'], $data['email'], $data['password']);
                $this->em->persist($profile);
                $this->em->flush();
                $this->alerts->addSuccess(new Alert(sprintf('Hello, %s <i class="glyphicon glyphicon-thumbs-up"></i>',
                    $profile->nickname())));
            } catch (ExceptionInterface $exception) {
                $this->alerts->addDanger(new Alert('Bla-bla-bla something wrong. Try again.'));
            }
        }
        $response->getBody()->write($this->renderer->render("app/sign-up.html.twig", $params));
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
            'nickname' => FILTER_SANITIZE_ENCODED,
            'email' => FILTER_SANITIZE_EMAIL,
            'password' => FILTER_SANITIZE_ENCODED,
        ];
        $data = filter_var_array($data, $fields, true);

        if (count(array_filter($data)) !== count($fields)) {
            $this->alerts->addDanger(new FormAlert('All fields are required.', 'sign_up_form'));
            $hasError = true;
        }

        if (!empty($data['nickname']) && (strlen($data['nickname']) < 3 || strlen($data['nickname']) > 100)) {
            $this->alerts->addDanger(new FormAlert(sprintf("Name is not valid, we think you can't have a name with %d symbols.",
                strlen($data['nickname'])), 'nickname'));
            $hasError = true;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->alerts->addDanger(new FormAlert('Email address is not valid' . ($hasError ? ', too' : '') . '.',
                'email'));
            $hasError = true;
        }

        if (!empty($data['password']) && (strlen($data['password']) < 3 || strlen($data['password']) > 16)) {
            $this->alerts->addDanger(new FormAlert('Please enter the password at least 3 characters and no more than 16.',
                'password'));
            $hasError = true;
        }

        if ($this->repository->findOneBy(['mail', $data['email']]) || $this->repository->findOneBy(['nickname', $data['nickname']])) {
            $this->alerts->addDanger(new FormAlert('User already exists.', 'sign_up_form'));
            $hasError = true;
        }

        return $hasError ? false : $data;
    }
}
