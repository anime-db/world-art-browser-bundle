<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Service;

use AnimeDb\Bundle\WorldArtBrowserBundle\Exception\BannedException;
use AnimeDb\Bundle\WorldArtBrowserBundle\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

class ErrorDetector
{
    /**
     * @param ResponseInterface $response
     * @param string            $path
     * @param array             $options
     *
     * @return string
     */
    public function detect(ResponseInterface $response, $path, array $options = [])
    {
        if ($response->getStatusCode() == 404) {
            throw NotFoundException::page();
        }

        $content = $response->getBody()->getContents();

        if ($this->isBanned($content)) {
            throw BannedException::banned();
        }

        // return anime Akira page if anime not found
        // example http://www.world-art.ru/animation/animation.php?id=10000000
        if (strpos($path, '/animation/animation.php') !== false &&
            (
                // check ID in options
                (isset($options['query']['id']) && $this->isNotAkira($id = $options['query']['id'], $content)) ||
                // check ID in path
                (preg_match('/\?id=(\d+)/', $path, $match) && $this->isNotAkira($id = $match[1], $content))
            )
        ) {
            throw NotFoundException::anime($id);
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    private function isBanned($content)
    {
        return
            strpos($content, 'url=http://www.world-art.ru/not_connect.html') !== false ||
            strpos($content, 'NETGEAR ProSecure') !== false
        ;
    }

    /**
     * Is not a Akira.
     *
     * @see http://www.world-art.ru/animation/animation.php?id=1
     *
     * @param int    $id
     * @param string $content
     *
     * @return bool
     */
    private function isNotAkira($id, $content)
    {
        return $id != 1 && strpos($content, '/animation/img/1000/1/1.jpg') !== false;
    }
}
