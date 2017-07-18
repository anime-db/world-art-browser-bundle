<?php

/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Tests\Service;

use AnimeDb\Bundle\WorldArtBrowserBundle\Service\Browser;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class BrowserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Client
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\tidy
     */
    private $tidy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private $request_stack;

    /**
     * @var Browser
     */
    private $browser;

    protected function setUp()
    {
        $this->host = 'example.org';
        $this->client = $this->getMock('\Guzzle\Http\Client');
        $this->tidy = $this->getMock('\tidy');
        $this->request_stack = $this->getMock('\Symfony\Component\HttpFoundation\RequestStack');
    }

    public function testNoUserAgent()
    {
        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');

        $request->server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $request->server
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('HTTP_USER_AGENT'))
            ->willReturn('')
        ;

        $this->request_stack
            ->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request)
        ;

        $this->client
            ->expects($this->never())
            ->method('setDefaultOption')
        ;

        $this->getBrowser();
    }

    public function testHasUserAgent()
    {
        $user_agent = 'Example user agent';

        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');

        $request->server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $request->server
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('HTTP_USER_AGENT'))
            ->willReturn($user_agent)
        ;

        $this->request_stack
            ->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request)
        ;

        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('headers/User-Agent', $user_agent)
        ;

        $this->getBrowser();
    }

    public function testGetHost()
    {
        $this->assertEquals($this->host, $this->getBrowser()->getHost());
    }

    public function testSetUserAgent()
    {
        $user_agent = 'Example user agent';
        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('headers/User-Agent', $user_agent)
        ;

        $this->assertEquals($this->getBrowser(), $this->getBrowser()->setUserAgent($user_agent));
    }

    public function testSetTimeout()
    {
        $timeout = 123;
        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('timeout', $timeout)
        ;

        $this->assertEquals($this->getBrowser(), $this->getBrowser()->setTimeout($timeout));
    }

    public function testSetProxy()
    {
        $proxy = '127.0.0.1';

        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('proxy', $proxy)
        ;

        $this->assertEquals($this->getBrowser(), $this->getBrowser()->setProxy($proxy));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetError()
    {
        $path = '/example/path/';
        $this->getResponse($path, true);
        $this->getBrowser()->get($path);
    }

    public function testGetErrorBadStatus()
    {
        $path = '/example/path/';
        $this->getResponse($path, false, 301);

        $this->assertEmpty($this->getBrowser()->get($path));
    }

    public function testGetErrorEmptyBody()
    {
        $path = '/example/path/';
        $this->getResponse($path, false, 200, '');

        $this->assertEmpty($this->getBrowser()->get($path));
    }

    public function testGet()
    {
        $path = '/example/path/';
        $html = 'dsafflkjasdbf';
        $html_tidy = 'dsafflkjasdbf<noembed>asdasd</noembed> bvgfdgdfg <noindex>xczxc</noindex>sdsdad';
        $expected = 'dsafflkjasdbf bvgfdgdfg sdsdad';
        $node = new \stdClass(); // can't mock tidyNode

        $this->tidy
            ->expects($this->once())
            ->method('parseString')
            ->willReturn($html_tidy)
            ->with(
                $html,
                array(
                    'output-xhtml' => true,
                    'indent' => true,
                    'indent-spaces' => 0,
                    'fix-backslash' => true,
                    'hide-comments' => true,
                    'drop-empty-paras' => true,
                    'wrap' => false
                ),
                'utf8'
            )
        ;
        $this->tidy
            ->expects($this->once())
            ->method('cleanRepair')
        ;
        $this->tidy
            ->expects($this->once())
            ->method('root')
            ->willReturn($node)
        ;

        $node->value = $html_tidy;

        $this->getResponse($path, false, 200, $html);

        $this->assertEquals($expected, $this->getBrowser()->get($path));
    }

    /**
     * @return Browser
     */
    private function getBrowser()
    {
        if (!$this->browser) {
            $this->browser = new Browser(
                $this->client,
                $this->tidy,
                $this->request_stack,
                $this->host
            );
        }

        return $this->browser;
    }

    /**
     * @param string  $path
     * @param boolean $is_error
     * @param integer $status_code
     * @param string  $body
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponse($path, $is_error, $status_code = 0, $body = '')
    {
        $request = $this
            ->getMockBuilder('\Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $response = $this
            ->getMockBuilder('\Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($request)
            ->with($path)
        ;

        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('isError')
            ->willReturn($is_error)
        ;

        if (!$is_error) {
            $response
                ->expects($this->once())
                ->method('getStatusCode')
                ->willReturn($status_code)
            ;

            if ($status_code == 200) {
                $response
                    ->expects($this->once())
                    ->method('getBody')
                    ->willReturn($body)
                    ->with(true)
                ;
            }
        }

        return $response;
    }
}
