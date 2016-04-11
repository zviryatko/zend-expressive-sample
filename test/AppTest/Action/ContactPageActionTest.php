<?php
/**
 * @file
 *
 */

namespace AppTest\Action;

use App\Action\ContactPageAction;
use App\Service\Alert;
use App\Service\Alerts;
use App\Service\AlertsInterface;
use App\Service\FormAlert;
use AppTest\WebTestCase;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\ServiceManager\ServiceManager;

class ContactPageActionTest extends WebTestCase
{
    protected function getValidData()
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'website' => 'http://example.com',
            'message' => 'Lorem ipsum dolor sit amet'
        ];
    }
    public function testContactPageAccess()
    {
        $response = $this->handleRequest('GET', '/contact');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFactory()
    {
        $container = $this->getMock(ServiceManager::class, ['get']);
        $container->expects($this->exactly(4))->method('get')->will($this->returnValueMap([
            [RouterInterface::class, $this->getMockForAbstractClass(RouterInterface::class)],
            [TemplateRendererInterface::class, $this->getMockForAbstractClass(TemplateRendererInterface::class)],
            [AlertsInterface::class, $this->getMockForAbstractClass(AlertsInterface::class)],
            [TransportInterface::class, $this->getMockForAbstractClass(TransportInterface::class)],
        ]));
        $this->assertInstanceOf(ContactPageAction::class, ContactPageAction::factory($container));
    }

    public function testRequestGet()
    {
        $request = $this->getMock(ServerRequest::class, ['getMethod', 'getParsedBody']);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getParsedBody');
        $response = new HtmlResponse('');
        $next = [$this, 'never'];
        $router = $this->getMockForAbstractClass(RouterInterface::class, ['match', 'getMatchedRouteName']);
        $route = $this->getMockWithoutInvokingTheOriginalConstructor(RouteResult::class);
        $router->expects($this->once())->method('match')->with($request)->willReturn($route);
        $route->expects($this->once())->method('getMatchedRouteName')->willReturn('contact');
        $renderer = $this->getMockForAbstractClass(TemplateRendererInterface::class, ['render']);
        $renderer->expects($this->once())->method('render')->with('app/contact.html.twig')->willReturn('html');
        $action = $this->getMock(ContactPageAction::class, ['validateData'], [
            $router,
            $renderer,
            $this->getMockForAbstractClass(AlertsInterface::class),
            $this->getMockForAbstractClass(TransportInterface::class),
        ]);
        $action->expects($this->never())->method('validateData');
        $action($request, $response, $next);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('html', $response->getBody()->getContents());
    }

    public function testRequestPost()
    {
        $test_data = $this->getValidData();
        $request = $this->getMock(ServerRequest::class, ['getMethod', 'getParsedBody']);
        $request->expects($this->once())->method('getMethod')->willReturn('POST');
        $request->expects($this->once())->method('getParsedBody')->willReturn($test_data);
        $response = new HtmlResponse('');
        $next = [$this, 'never'];
        $router = $this->getMockForAbstractClass(RouterInterface::class, ['match', 'getMatchedRouteName']);
        $route = $this->getMockWithoutInvokingTheOriginalConstructor(RouteResult::class);
        $router->expects($this->once())->method('match')->with($request)->willReturn($route);
        $route->expects($this->once())->method('getMatchedRouteName')->willReturn('contact');
        $renderer = $this->getMockForAbstractClass(TemplateRendererInterface::class, ['render']);
        $renderer->expects($this->once())->method('render')->with('app/contact.html.twig')->willReturn('html');
        $mail = $this->getMockForAbstractClass(TransportInterface::class, ['send']);
        $mail->expects($this->once())->method('send')->with(
            (new Message())
                ->addReplyTo($test_data['email'])
                ->addTo('sanya.davyskiba@gmail.com')
                ->setBody($test_data['message'])
                ->setSubject('Contact form message')
        );
        $alerts = $this->getMock(Alerts::class, ['addDanger', 'addSuccess']);
        $alerts->expects($this->never())->method('addDanger');
        $alerts->expects($this->once())
            ->method('addSuccess')
            ->with(new Alert('Success <i class="glyphicon glyphicon-thumbs-up"></i> Thanks for contacting us, we will get back to you shortly.'));
        $action = $this->getMock(ContactPageAction::class, ['validateData'], [
            $router,
            $renderer,
            $alerts,
            $mail,
        ]);
        $action->expects($this->once())->method('validateData')->with($test_data);
        $action($request, $response, $next);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('html', $response->getBody()->getContents());
    }

    protected function prepareObjectForValidateData($alerts, $data = [])
    {
        $request = $this->getMock(ServerRequest::class, ['getMethod', 'getParsedBody']);
        $response = new HtmlResponse('');
        $router = $this->getMockForAbstractClass(RouterInterface::class, ['match', 'getMatchedRouteName']);
        $route = $this->getMockWithoutInvokingTheOriginalConstructor(RouteResult::class);
        $router->expects($this->once())->method('match')->with($request)->willReturn($route);
        $route->expects($this->once())->method('getMatchedRouteName')->willReturn('contact');
        $renderer = $this->getMockForAbstractClass(TemplateRendererInterface::class, ['render']);
        $renderer->expects($this->once())->method('render')->with('app/contact.html.twig')->willReturn('html');
        $transport = $this->getMockForAbstractClass(TransportInterface::class);
        $next = [$this, 'never'];
        $request->expects($this->once())->method('getMethod')->willReturn('POST');
        $request->expects($this->once())->method('getParsedBody')->willReturn($data);
        $action = new ContactPageAction($router, $renderer, $alerts, $transport);
        $action($request, $response, $next);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('html', $response->getBody()->getContents());
    }

    public function testValidateDataAllEmpty()
    {
        $alerts = $this->getMock(Alerts::class, ['addDanger']);
        $alerts->expects($this->once())->method('addDanger')->with(new FormAlert('All fields are required.', 'contact_form'));
        $this->prepareObjectForValidateData($alerts);
    }

    public function testValidateDataNameIsNotLessThanTwoSymbols()
    {
        $alerts = $this->getMock(Alerts::class, ['addDanger']);
        $alerts->expects($this->once())->method('addDanger')->with(new FormAlert("Name is not valid, we think you can't have a name with 2 symbols", 'name'));
        $this->prepareObjectForValidateData($alerts, ['name' => 'ab']);
    }

    public function testValidateDataValid()
    {
        $alerts = $this->getMock(Alerts::class, ['addDanger', 'addSuccess']);
        $alerts->expects($this->never())->method('addDanger');
        $alerts->expects($this->once())
            ->method('addSuccess')
            ->with(new Alert('Success <i class="glyphicon glyphicon-thumbs-up"></i> Thanks for contacting us, we will get back to you shortly.'));
        $this->prepareObjectForValidateData($alerts, $this->getValidData());
    }
}
