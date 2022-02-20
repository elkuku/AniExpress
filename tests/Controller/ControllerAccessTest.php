<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Controller "smoke" test
 */
class ControllerAccessTest extends WebTestCase
{
    private array $exceptions
        = [
            'default'                  => [
                'statusCodes' => ['GET' => 200],
            ],
            'login'                    => [
                'statusCodes' => ['GET' => 200],
            ],
            'connect_google_api_token' => [
                'statusCodes' => ['GET' => 200],
            ],
            'app_catalog_by_tag' => [
                'statusCodes' => ['GET' => 200],
                'routeParams' => ['{name}' => 'Pollo']
            ],
            'app_catalog_store' => [
                'statusCodes' => ['GET' => 200],
                'routeParams' => ['{name}' => 'KFC']
            ],
        ];

    /**
     * @throws Exception
     */
    public function testRoutes(): void
    {
        $client = static::createClient();
        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        foreach (
            new DirectoryIterator(__DIR__.'/../../src/Controller') as $item
        ) {
            if (
                $item->isDot()
                || $item->isDir()
                || in_array(
                    $item->getBasename(),
                    ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $routerClass = 'App\Controller\\'.basename(
                    $item->getBasename(),
                    '.php'
                );
            $routes = $routeLoader->load($routerClass)->all();

            $this->processRoutes($routes, $client);
        }
    }

    private function processRoutes(array $routes, KernelBrowser $browser): void
    {
        foreach ($routes as $routeName => $route) {
            $defaultId = 1;
            $expectedStatusCodes = [];
            if (array_key_exists($routeName, $this->exceptions)
                && array_key_exists(
                    'statusCodes',
                    $this->exceptions[$routeName]
                )
            ) {
                $expectedStatusCodes = $this->exceptions[$routeName]['statusCodes'];
            }

            $methods = $route->getMethods();

            if (!$methods) {
                echo sprintf(
                        'No methods set in controller "%s"',
                        $route->getPath()
                    ).PHP_EOL;
                $methods = ['GET'];
            }

            $path = str_replace('{id}', $defaultId, $route->getPath());

            if (array_key_exists($routeName, $this->exceptions)
                && array_key_exists(
                    'routeParams',
                    $this->exceptions[$routeName]
                )
            ) {
                foreach (
                    $this->exceptions[$routeName]['routeParams'] as $routeParam => $replacement
                ) {
                    $path = str_replace($routeParam, $replacement, $path);
                }
            }
            $out = false;
            foreach ($methods as $method) {
                $expectedStatusCode = 302;
                if (array_key_exists($method, $expectedStatusCodes)) {
                    $expectedStatusCode = $expectedStatusCodes[$method];
                }
                if ($out) {
                    echo sprintf(
                        'Testing: %s - %s Expected: %s ... ',
                        $method,
                        $path,
                        $expectedStatusCode,
                    );
                }

                $browser->request($method, $path);

                if ($out) {
                    echo sprintf(
                            ' got: %s',
                            $browser->getResponse()->getStatusCode()
                        ).PHP_EOL;
                }

                self::assertEquals(
                    $expectedStatusCode,
                    $browser->getResponse()->getStatusCode(),
                    sprintf(
                        'failed: %s (%s) with method: %s',
                        $routeName,
                        $path,
                        $method
                    )
                );
            }
        }
    }
}
