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

        if (
            strpos($path, '/animation/animation.php') !== false &&
            ($id = $this->isAkiraSubstitution($content, $path, $options))
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
     * Return anime Akira page if anime not found.
     *
     * @see http://www.world-art.ru/animation/animation.php?id=10000000
     *
     * @param string $content
     * @param string $path
     * @param array  $options
     *
     * @return int
     */
    private function isAkiraSubstitution($content, $path, array $options)
    {
        // check ID in options
        if (isset($options['query']['id']) && $this->isNotAkira($options['query']['id'], $content)) {
            return $options['query']['id'];
        }

        // check ID in path
        if (preg_match('/\?id=(\d+)/', $path, $match) && $this->isNotAkira($match[1], $content)) {
            return $match[1];
        }

        return 0;
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
