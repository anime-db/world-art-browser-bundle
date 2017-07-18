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
use AnimeDb\Bundle\WorldArtBrowserBundle\Service\ResponseRepair;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|StreamInterface
     */
    private $stream;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MessageInterface
     */
    private $message;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResponseRepair
     */
    private $repair;

    /**
     * @var Browser
     */
    private $browser;

    protected function setUp()
    {
        $this->client = $this->getMock(HttpClient::class);
        $this->stream = $this->getMock(StreamInterface::class);
        $this->message = $this->getMock(MessageInterface::class);
        $this->repair = $this
            ->getMockBuilder(ResponseRepair::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->browser = new Browser($this->client, $this->repair, $this->host, $this->app_client);
    }

    public function testGet()
    {
        $path = '/foo';
        $params = ['bar' => 'baz'];
        $options = $params + [
            'headers' => [
                'User-Agent' => $this->app_client,
            ],
        ];
        $content = 'Hello, world!';
        $repair = 'foo';

        $this->stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content))
        ;

        $this->message
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->stream))
        ;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', $this->host.$path, $options)
            ->will($this->returnValue($this->message))
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
