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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * Class for get HTTP User-Agent
 *
 * @package AnimeDb\Bundle\WorldArtBrowserBundle\Service
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class UserAgent
{
    /**
     * Request stack
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $request_stack;

    /**
     * User agent
     *
     * @var string
     */
    protected $user_agent = '';

    /**
     * Default user agent
     *
     * @var string
     */
    protected $user_agent_default = '';

    /**
     * Path to user agent file
     *
     * @var string
     */
    protected $user_agent_file = '';

    /**
     * List user agents from file
     *
     * @var array
     */
    protected $user_agents = [];

    /**
     * Construct
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     * @param \Symfony\Component\Config\FileLocatorInterface $file_locator
     * @param string $user_agent_defaul
     * @param string $user_agent_file
     */
    public function __construct(
        RequestStack $request_stack,
        FileLocatorInterface $file_locator,
        $user_agent_defaul,
        $user_agent_file
    ) {
        $this->request_stack = $request_stack;
        $this->user_agent_default = $user_agent_defaul;
        if ($user_agent_file) {
            $this->user_agent_file = (string) $file_locator->locate($user_agent_file);
        }
    }

    /**
     * Get user agent
     *
     * @param string $force
     */
    public function get($force = false)
    {
        if ($force || !$this->user_agent) {
            if ($request = $this->request_stack->getMasterRequest()) {
                $this->user_agent = $request->server->get('HTTP_USER_AGENT', '');
            }
            if (!$this->user_agent && $this->user_agent_default) {
                $this->user_agent = $this->user_agent_default;
            }
            if (!$this->user_agent && ($agents = $this->getUserAgentsFromFile())) {
                $this->user_agent = array_rand($agents);
            }
        }

        return $this->user_agent;
    }

    /**
     * Get user agents from file
     *
     * @return array
     */
    protected function getUserAgentsFromFile()
    {
        if (!$this->user_agents && $this->user_agent_file) {
            $this->user_agents = (array) file($this->user_agent_file);
            $this->user_agents = array_map('trim', $this->user_agents);
        }
        return $this->user_agents;
    }
}
