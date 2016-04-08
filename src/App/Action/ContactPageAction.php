<?php
/**
 * @file
 * Contains App\Action\ContactPageAction.
 */

namespace App\Action;

use App\Service\Alert;
use App\Service\AlertsInterface;
use App\Service\FormAlert;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ContactPageAction extends StaticPageAction
{
    /**
     * @var \App\Service\AlertsInterface
     */
    protected $alerts;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        RouterInterface $router,
        TemplateRendererInterface $renderer,
        AlertsInterface $alerts
    ) {
        parent::__construct($router, $renderer);
        $this->alerts = $alerts;
    }

    /**
     * {@inheritdoc}
     */
    public static function factory(ContainerInterface $container)
    {
        return new static(
            $container->get(RouterInterface::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(AlertsInterface::class)
        );
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getMethod() === 'POST') {
            $this->validateData($request->getParsedBody() ?: []);
        }
        return parent::__invoke($request, $response, $next);
    }

    protected function validateData($data)
    {
        $hasError = false;
        $fields = [
            'name' => FILTER_SANITIZE_ENCODED,
            'email' => FILTER_SANITIZE_EMAIL,
            'website' => FILTER_SANITIZE_URL,
            'message' => FILTER_SANITIZE_ENCODED,
        ];
        $data = filter_var_array($data, $fields, true);

        if (count(array_filter($data)) !== count($fields)) {
            $this->alerts->addDanger(new FormAlert('All fields are required.', 'contact_form'));
            $hasError = true;
        }

        if (!empty($data['name']) && (strlen($data['name']) < 3 || strlen($data['name']) > 100)) {
            $this->alerts->addDanger(new FormAlert(sprintf("Name is not valid, we think you can't have a name with %d symbols",
                strlen($data['name'])), 'name'));
            $hasError = true;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->alerts->addDanger(new FormAlert('Email address is not valid', 'email'));
            $hasError = true;
        }

        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $this->alerts->addDanger(new FormAlert('URL address is not valid', 'website'));
            $hasError = true;
        }

        if (!empty($data['message']) && (strlen($data['message']) < 10 || strlen($data['message']) > 200)) {
            $this->alerts->addDanger(new FormAlert('Please enter at least 10 characters and no more than 200',
                'message'));
            $hasError = true;
        }

        if (!$hasError) {
            $this->alerts->addSuccess(new Alert('Success <i class="glyphicon glyphicon-thumbs-up"></i> Thanks for contacting us, we will get back to you shortly.'));
        }

        return $hasError;
    }
}
