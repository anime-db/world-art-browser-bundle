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
     * @var \tidy
     */
    private $tidy;

    /**
     * @param Client $client
     * @param \tidy  $tidy
     * @param string $host
     * @param string $app_client
     */
    public function __construct(Client $client, \tidy $tidy, $host, $app_client)
    {
        $this->client = $client;
        $this->tidy = $tidy;
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

        if ($response->getStatusCode() != 200 || !($html = $response->getBody(true))) {
            return '';
        }

        $html = iconv('windows-1251', 'utf-8', $html);

        // clean content
        $config = array(
            'output-xhtml' => true,
            'indent' => true,
            'indent-spaces' => 0,
            'fix-backslash' => true,
            'hide-comments' => true,
            'drop-empty-paras' => true,
            'wrap' => false
        );
        $this->tidy->parseString($html, $config, 'utf8');
        $this->tidy->cleanRepair();
        $html = $this->tidy->root()->value;

        // ignore blocks
        $html = preg_replace('/<noembed>.*?<\/noembed>/is', '', $html);
        $html = preg_replace('/<noindex>.*?<\/noindex>/is', '', $html);

        return $html;
    }
}
