<?php
/**
 * @file
 *
 */

namespace AppTest\Action;

use App\Action\StaticPageAction;
use AppTest\WebTestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class StaticPageActionTest extends WebTestCase
{
    public function testRenderedTemplate()
    {
        $router = $this->getMockForAbstractClass(RouterInterface::class, ['match', 'getMatchedRouteName']);
        $renderer = $this->getMockForAbstractClass(TemplateRendererInterface::class, ['render']);
        $request = new ServerRequest();
        $response = new Response();
        $route = $this->getMockWithoutInvokingTheOriginalConstructor(RouteResult::class);
        $router->expects($this->once())->method('match')->with($request)->willReturn($route);
        $route->expects($this->once())->method('getMatchedRouteName')->willReturn('test-route-name');
        $renderer->expects($this->once())->method('render')->with('app/test-route-name.html.twig')->willReturn('html');
        $controller = new StaticPageAction($router, $renderer);
        $controller($request, $response, [$this, 'never']);
        $this->assertMessageBodyMatches($response, $this->equalTo('html'));
    }

    public function testStaticPagesAccess()
    {
        foreach (['/', '/about', '/services'] as $url) {
            $response = $this->handleRequest('GET', $url);
            $this->assertResponseHasStatus($response, 200);
        }
    }
}