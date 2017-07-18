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
     * @var string
     */
    private $host;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseRepair
     */
    private $repair;

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
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $user_agent
     *
     * @return self
     */
    public function setUserAgent($user_agent)
    {
        $this->client->setDefaultOption('headers/User-Agent', $user_agent);

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return self
     */
    public function setTimeout($timeout)
    {
        $this->client->setDefaultOption('timeout', $timeout);

        return $this;
    }

    /**
     * @param int $proxy
     *
     * @return self
     */
    public function setProxy($proxy)
    {
        $this->client->setDefaultOption('proxy', $proxy);

        return $this;
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
