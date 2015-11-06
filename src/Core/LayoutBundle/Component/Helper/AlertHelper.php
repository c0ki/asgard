<?php

namespace Core\LayoutBundle\Component\Helper;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class AlertHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request = null;

    const CookieName = 'asgard_core_alert';

    private static $alertTypes = [
        'error' => '¤',
        'warning' => '!',
        'info' => '%',
        'success' => '$',
        'help' => '?',
    ];
    private static $defaultAlertType = 'info';

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * @return array
     */
    public function all()
    {
        $alerts = array();
        if ($this->request->cookies->has(self::CookieName)) {
            $alerts = $this->unserialize($this->request->cookies->get(self::CookieName));
            $this->purge();
        }

        return $alerts;
    }

    public function purge()
    {
        // Clear cookie
        $response = new Response();
        $response->headers->clearCookie(self::CookieName);
        $response->sendHeaders();
        $this->request->cookies->remove(self::CookieName);

        return $this;
    }

    public function add($message, $type = null)
    {
        if (!array_key_exists($type, self::$alertTypes)) {
            $type = '';
        }
        $alert = [
            'type' => $type,
            'text' => $message,
        ];

        // Add message to alerts
        $alerts = $this->all();
        array_push($alerts, $alert);

        // Set cookie
        $this->request->cookies->set(self::CookieName, $this->serialize($alerts));
        $cookie = new Cookie(self::CookieName, $this->serialize($alerts));
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->sendHeaders();

        return $this;
    }

    public function __call($function, $params)
    {
        if (array_key_exists($function, self::$alertTypes)) {
            return $this->add($params[0], $function);
        }
    }

    public function has()
    {
        if ($this->request->cookies->has(self::CookieName)) {
            $alerts = unserialize($this->request->cookies->get(self::CookieName));
            if (!empty($alerts)) {
                return true;
            }
        }

        return false;
    }

    protected function unserialize($alerts)
    {
        $alerts = unserialize($alerts);
        $alertPrefix = array_flip(self::$alertTypes);
        foreach ($alerts as &$alert) {
            if (array_key_exists($alert[0], $alertPrefix)) {
                $type = $alertPrefix[$alert[0]];
                $alert = substr($alert, 1);
            }
            else {
                $type = self::$defaultAlertType;
            }
            $alert = [
                'type' => $type,
                'text' => $alert,
            ];
        }

        return $alerts;
    }

    protected function serialize(array $alerts)
    {
        foreach ($alerts as &$alert) {
            if (array_key_exists($alert['type'], self::$alertTypes)) {
                $alert['text'] = self::$alertTypes[$alert['type']] . $alert['text'];
            }
            $alert = $alert['text'];
        }
        $alerts = serialize($alerts);

        return $alerts;
    }

}