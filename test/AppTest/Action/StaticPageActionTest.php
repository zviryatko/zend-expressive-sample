<?php
/**
 * @file
 *
 */

namespace AppTest\Action;

use App\Action\StaticPageAction;
use AppTest\WebTestCase;
use Zend\Diactoros\Response\HtmlResponse;
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
        $response = new HtmlResponse('');
        $route = $this->getMockWithoutInvokingTheOriginalConstructor(RouteResult::class);
        $router->expects($this->once())->method('match')->with($request)->willReturn($route);
        $route->expects($this->once())->method('getMatchedRouteName')->willReturn('test-route-name');
        $renderer->expects($this->once())->method('render')->with('app/test-route-name.html.twig')->willReturn('html');
        $controller = new StaticPageAction($router, $renderer);
        $controller($request, $response, [$this, 'never']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('html', (string) $response->getBody());
    }

    public function testStaticPagesAccess()
    {
        foreach ([
                     '/' => 'A Warm Welcome!',
                     '/about' => 'About our company',
                     '/services' => 'Latest Features'
                 ] as $url => $page_title) {
            $response = $this->handleRequest('GET', $url);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals(1, $this->crawler($response)->filterXPath("html[contains(., \"{$page_title}\")]")->count(), "Expects that page {$url} contain title \"{$page_title}\"");
        }
    }
}
