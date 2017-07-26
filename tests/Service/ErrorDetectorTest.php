<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Tests\Service;

use AnimeDb\Bundle\WorldArtBrowserBundle\Service\ErrorDetector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ErrorDetectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    private $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StreamInterface
     */
    private $stream;

    /**
     * @var ErrorDetector
     */
    private $detector;

    protected function setUp()
    {
        $this->response = $this->getMock(ResponseInterface::class);
        $this->stream = $this->getMock(StreamInterface::class);

        $this->detector = new ErrorDetector();
    }

    /**
     * @expectedException \AnimeDb\Bundle\WorldArtBrowserBundle\Exception\NotFoundException
     */
    public function testStatusNotFound()
    {
        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(404))
        ;

        $this->detector->detect($this->response, '');
    }

    /**
     * @return array
     */
    public function banns()
    {
        return [
            ['<foo>url=http://www.world-art.ru/not_connect.html</foo>'],
            ['<foo>NETGEAR ProSecure</foo>'],
        ];
    }

    /**
     * @dataProvider banns
     *
     * @expectedException \AnimeDb\Bundle\WorldArtBrowserBundle\Exception\BannedException
     *
     * @param string $content
     */
    public function testBanned($content)
    {
        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;
        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->stream))
        ;

        $this->stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content))
        ;

        $this->detector->detect($this->response, '');
    }

    /**
     * @return array
     */
    public function notAkira()
    {
        return [
            ['/animation/animation.php', ['query' => ['id' => 123]]],
            ['/animation/animation.php?id=123', []],
        ];
    }

    /**
     * @dataProvider notAkira
     *
     * @expectedException \AnimeDb\Bundle\WorldArtBrowserBundle\Exception\NotFoundException
     *
     * @param string $path
     * @param array  $options
     */
    public function testNotAkira($path, array $options)
    {
        $content = '<foo>/animation/img/1000/1/1.jpg</foo>';

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;
        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->stream))
        ;

        $this->stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content))
        ;

        $this->detector->detect($this->response, $path, $options);
    }

    /**
     * @return array
     */
    public function akira()
    {
        return [
            ['/animation/animation.php', ['query' => ['id' => 1]]],
            ['/animation/animation.php?id=1', []],
        ];
    }

    /**
     * @dataProvider akira
     *
     * @param string $path
     * @param array  $options
     */
    public function testNoErrors($path, array $options)
    {
        $content = 'foo';

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;
        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->stream))
        ;

        $this->stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($content))
        ;

        $this->assertEquals($content, $this->detector->detect($this->response, $path, $options));
    }
}
