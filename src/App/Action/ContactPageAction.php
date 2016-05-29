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
use Zend\Mail\Exception\ExceptionInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;

class ContactPageAction extends AbstractPageAction
{
    /**
     * @var \App\Service\AlertsInterface
     */
    protected $alerts;

    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        RouterInterface $router,
        TemplateRendererInterface $renderer,
        AlertsInterface $alerts,
        TransportInterface $transport
    ) {
        parent::__construct($router, $renderer);
        $this->alerts = $alerts;
        $this->transport = $transport;
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
            $container->get(TransportInterface::class)
        );
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $params = $request->getParsedBody() ?: [];
        if ($request->getMethod() === 'POST' && ($data = $this->validateData($params)) !== FALSE && !empty($data['email'])) {
            try {
                $this->transport->send(
                    (new Message())
                        ->addReplyTo($data['email'])
                        ->addTo('sanya.davyskiba@gmail.com')
                        ->setBody($data['message'])
                        ->setSubject('Contact form message')
                );
            } catch (ExceptionInterface $exception) {
                $this->alerts->addDanger(new Alert('Sorry, email was not sent because site administrator did not configure it =('));
            }
        }
        $response->getBody()->write($this->renderer->render("app/contact.html.twig", $params));
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
            $this->alerts->addDanger(new FormAlert(sprintf("Name is not valid, we think you can't have a name with %d symbols.",
                strlen($data['name'])), 'name'));
            $hasError = true;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->alerts->addDanger(new FormAlert('Email address is not valid' . ($hasError ? ', too' : '') . '.',
                'email'));
            $hasError = true;
        }

        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $this->alerts->addDanger(new FormAlert('URL address is not valid' . ($hasError ? ', too' : '') . '.',
                'website'));
            $hasError = true;
        }

        if (!empty($data['message']) && (strlen($data['message']) < 10 || strlen($data['message']) > 200)) {
            $this->alerts->addDanger(new FormAlert('Please enter the message text at least 10 characters and no more than 200.',
                'message'));
            $hasError = true;
        }

        if (!$hasError) {
            $this->alerts->addSuccess(new Alert('Success <i class="glyphicon glyphicon-thumbs-up"></i> Thanks for contacting us, we will get back to you shortly.'));
        }

        return $hasError ? false : $data;
    }
}
