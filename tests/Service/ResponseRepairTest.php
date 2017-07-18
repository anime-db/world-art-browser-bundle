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

use AnimeDb\Bundle\WorldArtBrowserBundle\Service\ResponseRepair;

class ResponseRepairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\tidy
     */
    private $tidy;

    /**
     * @var ResponseRepair
     */
    private $repair;

    protected function setUp()
    {
        $this->tidy = $this->getMock(\tidy::class);

        $this->repair = new ResponseRepair($this->tidy);
    }

    public function testRepairNoContent()
    {
        $this->tidy
            ->expects($this->never())
            ->method('parseString')
        ;
        $this->tidy
            ->expects($this->never())
            ->method('cleanRepair')
        ;

        $this->assertEmpty($this->repair->repair(''));
    }

    public function testRepair()
    {
        $html = 'dsafflkjasdbf';
        $html_tidy = 'dsafflkjasdbf<noembed>asdasd</noembed> bvgfdgdfg <noindex>xczxc</noindex>sdsdad';
        $expected = 'dsafflkjasdbf bvgfdgdfg sdsdad';
        $node = new \stdClass(); // can't mock tidyNode
        $config = [
            'output-xhtml' => true,
            'indent' => true,
            'indent-spaces' => 0,
            'fix-backslash' => true,
            'hide-comments' => true,
            'drop-empty-paras' => true,
            'wrap' => false
        ];

        $this->tidy
            ->expects($this->once())
            ->method('parseString')
            ->with($html, $config, 'utf8')
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

        $this->assertEquals($expected, $this->repair->repair($html));
    }
}
