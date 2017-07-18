<?php

/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Service;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

class Browser
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseRepair
     */
    private $repair;

    /**
     * @var string
     */
    private $host;

    /**
     * @param Client         $client
     * @param ResponseRepair $repair
     * @param string         $host
     * @param string         $app_client
     */
    public function __construct(Client $client, ResponseRepair $repair, $host, $app_client)
    {
        $this->client = $client;
        $this->repair = $repair;
        $this->host = $host;

        // set HTTP User-Agent
        if ($app_client) {
            $this->setUserAgent($app_client);
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function get($path)
    {
        /* @var $response Response */
        $response = $this->client->get($this->host.$path)->send();

        if ($response->isError()) {
            throw new \RuntimeException('Failed to query the server ' . $this->host);
        }

        if ($response->getStatusCode() != 200 || !($content = $response->getBody(true))) {
            return '';
        }

        return $this->repair->repair($content);
    }
}
