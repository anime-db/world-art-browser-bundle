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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Browser
 *
 * @package AnimeDb\Bundle\WorldArtBrowserBundle\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Browser
{
    /**
     * Host
     *
     * @var string
     */
    private $host;

    /**
     * HTTP client
     *
     * @var \Guzzle\Http\Client
     */
    private $client;

    /**
     * Tidy
     *
     * @var \tidy
     */
    private $tidy;

    /**
     * Construct
     *
     * @param \Guzzle\Http\Client $client
     * @param \tidy $tidy
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     * @param string $host
     */
    public function __construct(
        Client $client,
        \tidy $tidy,
        RequestStack $request_stack,
        $host
    ) {
        $this->client = $client;
        $this->tidy = $tidy;
        $this->host = $host;

        // set HTTP User-Agent
        if (($request = $request_stack->getMasterRequest()) &&
            ($user_agent = $request->server->get('HTTP_USER_AGENT'))
        ) {
            $this->setUserAgent($user_agent);
        }
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set HTTP User-Agent
     *
     * @param string $user_agent
     */
    public function setUserAgent($user_agent)
    {
        $this->client->setDefaultOption('headers/User-Agent', $user_agent);
    }

    /**
     * Set timeout
     *
     * @param integer $timeout
     */
    public function setTimeout($timeout)
    {
        $this->client->setDefaultOption('timeout', $timeout);
    }

    /**
     * Get data from path
     *
     * @param string $path
     *
     * @return string
     */
    public function get($path)
    {
        /* @var $response \Guzzle\Http\Message\Response */
        $response = $this->client->get($path)->send();
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
