<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Exception;

class NotFoundException extends ErrorException
{
    /**
     * @param string $id
     *
     * @return NotFoundException
     */
    public static function anime($id)
    {
        return new self(sprintf('Anime with id "%s" not found.', $id));
    }

    /**
     * @return NotFoundException
     */
    public static function page()
    {
        return new self('Page not found.');
    }
}
