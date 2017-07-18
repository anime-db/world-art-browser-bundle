<?php

/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Tests\DependencyInjection;

use AnimeDb\Bundle\WorldArtBrowserBundle\DependencyInjection\AnimeDbWorldArtBrowserExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
