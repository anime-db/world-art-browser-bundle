<?php

/**
 * AnimeDb package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\WorldArtBrowserBundle\Service;

use GuzzleHttp\Client as HttpClient;

class Browser
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var ResponseRepair
     */
    private $repair;

    /**
     * @var ErrorDetector
     */
    private $detector;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $app_client;

    /**
     * @param HttpClient     $client
     * @param ResponseRepair $repair
     * @param ErrorDetector  $detector
     * @param string         $host
     * @param string         $app_client
     */
    public function __construct(
        HttpClient $client,
        ResponseRepair $repair,
        ErrorDetector $detector,
        $host,
        $app_client
    ) {
        $this->client = $client;
        $this->repair = $repair;
        $this->detector = $detector;
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
            $options['headers'] = array_merge(
                ['User-Agent' => $this->app_client],
                isset($options['headers']) ? $options['headers'] : []
            );
        }

        $response = $this->client->request('GET', $this->host.$path, $options);

        $content = $this->detector->detect($response, $path, $options);
        $content = $this->repair->repair($content);

        return $content;
    }
}
