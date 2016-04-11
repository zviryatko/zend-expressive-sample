<?php
/**
 * @file
 * Contains AppTest\WebTestCase.
 */

namespace AppTest;

use Helmich\Psr7Assert\Psr7Assertions;
use Interop\Container\ContainerInterface;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Application;
use Zend\ServiceManager\ServiceManager;

class WebTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $config;
    /**
     * @var ContainerInterface
     */
    protected static $container;
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var ResponseInterface
     */
    protected $response;

    public static function setUpBeforeClass()
    {
        // Load configuration
        $config_path = getcwd() . '/config/config.php';
        $config = file_exists($config_path) ? require $config_path : [];
        // Override config settings
        $config['debug'] = true;
        $config['config_cache_enabled'] = false;
        $dependencies = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        // Build container
        self::$container = new ServiceManager($dependencies);
    }

    public static function tearDownAfterClass()
    {
        // Clean up
        self::$container = null;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|null $parameters
     *
     * @return ResponseInterface
     */
    protected function handleRequest($method, $uri, array $parameters = null)
    {
        // Create request
        $request = (new ServerRequest(['SCRIPT_NAME' => '/index.php']))
            ->withMethod($method)
            ->withUri(new Uri($uri));
        // Set post parameters
        if ($parameters !== null) {
            $request = $request->withParsedBody($parameters);
        }
        // Get application from container
        $app = self::$container->get(Application::class);
        if (!is_callable($app)) {
            throw new \UnexpectedValueException("Can't initialize Application.");
        }
        // Invoke the request
        return $app($request, new Response());
    }
}