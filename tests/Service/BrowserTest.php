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

/**
 * Test browser
 *
 * @package AnimeDb\Bundle\WorldArtBrowserBundle\Tests\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class BrowserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Host
     *
     * @var string
     */
    protected $host;

    /**
     * HTTP client
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * Tidy
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tidy;

    /**
     * Request stack
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request_stack;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->host = 'example.org';
        $this->client = $this->getMock('\Guzzle\Http\Client');
        $this->tidy = $this->getMock('\tidy');
        $this->request_stack = $this->getMock('\Symfony\Component\HttpFoundation\RequestStack');
    }

    /**
     * Test has request but not user agent
     */
    public function testNoUserAgent()
    {
        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $request->server
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('HTTP_USER_AGENT'))
            ->willReturn('');
        $this->request_stack
            ->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        $this->client
            ->expects($this->never())
            ->method('setDefaultOption');

        $this->getBrowser();
    }

    /**
     * Test set user agent to client
     */
    public function testHasUserAgent()
    {
        $user_agent = 'Example user agent';
        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->server = $this->getMock('\Symfony\Component\HttpFoundation\ServerBag');
        $request->server
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('HTTP_USER_AGENT'))
            ->willReturn($user_agent);
        $this->request_stack
            ->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with(
                $this->equalTo('headers/User-Agent'),
                $this->equalTo($user_agent)
            );

        $this->getBrowser();
    }

    /**
     * Test get host
     */
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
            ->with(
                $this->equalTo('headers/User-Agent'),
                $this->equalTo($user_agent)
            );
        $this->getBrowser()->setUserAgent($user_agent);
    }

    /**
     * Test error get
     *
     * @expectedException \RuntimeException
     */
    public function testGetError()
    {
        $path = '/example/path/';
        $this->getResponse($path, true);
        $this->getBrowser()->get($path);
    }

    /**
     * Test get error bad status
     */
    public function testGetErrorBadStatus()
    {
        $path = '/example/path/';
        $this->getResponse($path, false, 301);
        $this->assertEmpty($this->getBrowser()->get($path));
    }

    /**
     * Test get error empty body
     */
    public function testGetErrorEmptyBody()
    {
        $path = '/example/path/';
        $this->getResponse($path, false, 200, '');
        $this->assertEmpty($this->getBrowser()->get($path));
    }

    /**
     * Test get
     */
    public function testGet()
    {
        $path = '/example/path/';
        $html = 'dsafflkjasdbf';
        $html_tidy = 'dsafflkjasdbf<noembed>asdasd</noembed> bvgfdgdfg <noindex>xczxc</noindex>sdsdad';
        $expected = 'dsafflkjasdbf bvgfdgdfg sdsdad';
        $node = new \stdClass();
        $this->tidy
            ->expects($this->once())
            ->method('parseString')
            ->willReturn($html_tidy)
            ->with(
                $this->equalTo($html),
                $this->equalTo(array(
                    'output-xhtml' => true,
                    'indent' => true,
                    'indent-spaces' => 0,
                    'fix-backslash' => true,
                    'hide-comments' => true,
                    'drop-empty-paras' => true,
                    'wrap' => false
                )),
                $this->equalTo('utf8')
            );
        $this->tidy
            ->expects($this->once())
            ->method('cleanRepair');
        $this->tidy
            ->expects($this->once())
            ->method('root')
            ->willReturn($node);
        $node->value = $html_tidy;

        $this->getResponse($path, false, 200, $html);
        $this->assertEquals($expected, $this->getBrowser()->get($path));
    }

    /**
     * Get browser
     *
     * @return \AnimeDb\Bundle\WorldArtBrowserBundle\Service\Browser
     */
    protected function getBrowser()
    {
        return new Browser(
            $this->client,
            $this->tidy,
            $this->request_stack,
            $this->host
        );
    }

    /**
     * Get response
     *
     * @param string $path
     * @param boolean $is_error
     * @param integer $status_code
     * @param string $body
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponse($path, $is_error, $status_code = 0, $body = '')
    {
        $request = $this->getMockBuilder('\Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $response = $this->getMockBuilder('\Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($request)
            ->with($this->equalTo($path));
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response);
        $response
            ->expects($this->once())
            ->method('isError')
            ->willReturn($is_error);

        if (!$is_error) {
            $response
                ->expects($this->once())
                ->method('getStatusCode')
                ->willReturn($status_code);
            if ($status_code == 200) {
                $response
                    ->expects($this->once())
                    ->method('getBody')
                    ->willReturn($body)
                    ->with($this->equalTo(true));
            }
        }
        return $response;
    }
}
