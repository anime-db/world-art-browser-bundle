<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Tests\Service;

use AnimeDb\Bundle\WorldArtBrowserBundle\Service\Browser;
use AnimeDb\Bundle\WorldArtBrowserBundle\Service\ErrorDetector;
use AnimeDb\Bundle\WorldArtBrowserBundle\Service\ResponseRepair;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

class BrowserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $host = 'example.org';

    /**
     * @var string
     */
    private $app_client = 'My Custom Bot 1.0';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HttpClient
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    private $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResponseRepair
     */
    private $repair;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ErrorDetector
     */
    private $detector;

    /**
     * @var Browser
     */
    private $browser;

    protected function setUp()
    {
        $this->client = $this->getMock(HttpClient::class);
        $this->response = $this->getMock(ResponseInterface::class);
        $this->detector = $this->getMock(ErrorDetector::class);
        $this->repair = $this
            ->getMockBuilder(ResponseRepair::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->browser = new Browser($this->client, $this->repair, $this->detector, $this->host, $this->app_client);
    }

    /**
     * @return array
     */
    public function appClients()
    {
        return [
            [''],
            ['Override User Agent'],
        ];
    }

    /**
     * @dataProvider appClients
     *
     * @param string $app_client
     */
    public function testGet($app_client)
    {
        $path = '/foo';
        $params = ['bar' => 'baz'];
        $options = $params + [
            'headers' => [
                'User-Agent' => $this->app_client,
            ],
        ];

        if ($app_client) {
            $options['headers']['User-Agent'] = $app_client;
            $params['headers']['User-Agent'] = $app_client;
        }

        $content = 'Hello, world!';
        $repair = 'foo';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', $this->host.$path, $options)
            ->will($this->returnValue($this->response))
        ;

        $this->detector
            ->expects($this->once())
            ->method('detect')
            ->with($this->response, $path, $options)
            ->will($this->returnValue($content))
        ;
        $this->repair
            ->expects($this->once())
            ->method('repair')
            ->with($content)
            ->will($this->returnValue($repair))
        ;

        $this->assertEquals($repair, $this->browser->get($path, $params));
    }
}
