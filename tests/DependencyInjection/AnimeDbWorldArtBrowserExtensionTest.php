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

/**
 * Test DependencyInjection
 *
 * @package AnimeDb\Bundle\WorldArtBrowserBundle\Tests\DependencyInjection
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class AnimeDbWorldArtBrowserExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test load
     */
    public function testLoad()
    {
        $di = new AnimeDbWorldArtBrowserExtension();
        $di->load(array(), $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder'));
    }
}
