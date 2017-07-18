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

use GuzzleHttp\Client;

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
     * @var string
     */
    private $app_client;

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
        $this->app_client = $app_client;
    }

    /**
     * @param string $path
     * @param array  $options
     *
     * @return string
     */
    public function get($path, array $options = [])
    {
        if ($this->app_client) {
            $options['headers'] = isset($options['headers']) ? $options['headers'] : [];
            $options['headers']['User-Agent'] = $this->app_client;
        }

        $response = $this->client->request('GET', $this->host.$path, $options);

        $content = $response->getBody()->getContents();

        return $this->repair->repair($content);
    }
}
