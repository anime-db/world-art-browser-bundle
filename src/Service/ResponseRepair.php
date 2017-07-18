<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Service;

class ResponseRepair
{
    /**
     * @var \tidy
     */
    private $tidy;

    /**
     * @param \tidy $tidy
     */
    public function __construct(\tidy $tidy)
    {
        $this->tidy = $tidy;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function repair($content)
    {
        if (!$content) {
            return '';
        }

        $content = iconv('windows-1251', 'utf-8', $content);

        // clean content
        $config = [
            'output-xhtml' => true,
            'indent' => true,
            'indent-spaces' => 0,
            'fix-backslash' => true,
            'hide-comments' => true,
            'drop-empty-paras' => true,
            'wrap' => false,
        ];
        $this->tidy->parseString($content, $config, 'utf8');
        $this->tidy->cleanRepair();
        $content = $this->tidy->root()->value;

        // ignore blocks
        $content = preg_replace('/<noembed>.*?<\/noembed>/is', '', $content);
        $content = preg_replace('/<noindex>.*?<\/noindex>/is', '', $content);

        return $content;
    }
}
