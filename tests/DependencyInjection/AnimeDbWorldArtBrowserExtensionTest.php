<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Tests\DependencyInjection;

use AnimeDb\Bundle\WorldArtBrowserBundle\DependencyInjection\AnimeDbWorldArtBrowserExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AnimeDbWorldArtBrowserExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private $container;

    /**
     * @var AnimeDbWorldArtBrowserExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->extension = new AnimeDbWorldArtBrowserExtension();
    }

    /**
     * @return array
     */
    public function config()
    {
        return [
            [
                [],
                'http://www.world-art.ru',
                '',
            ],
            [
                [
                    'anime_db_world_art_browser' => [
                        'host' => 'https://www.world-art.ru',
                        'client' => 'My Custom Bot 1.0',
                    ],
                ],
                'https://www.world-art.ru',
                'My Custom Bot 1.0',
            ],
        ];
    }

    /**
     * @dataProvider config
     *
     * @param array  $config
     * @param string $host
     * @param string $client
     */
    public function testLoad(array $config, $host, $client)
    {
        $browser = $this->getMock(Definition::class);
        $browser
            ->expects($this->at(0))
            ->method('replaceArgument')
            ->with(3, $host)
            ->will($this->returnSelf())
        ;
        $browser
            ->expects($this->at(1))
            ->method('replaceArgument')
            ->with(4, $client)
            ->will($this->returnSelf())
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('anime_db.world_art.browser')
            ->will($this->returnValue($browser))
        ;

        $this->extension->load($config, $this->container);
    }
}
