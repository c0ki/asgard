<?php

namespace Core\CoreBundle\Component\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class MessageHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request = null;

    const CookieName = 'asgard_core_message';

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * @return array
     */
    public function all()
    {
        $messages = array();
        if ($this->request->cookies->has(self::CookieName)) {
            $messages = unserialize($this->request->cookies->get(self::CookieName));
            $this->purge();
        }
        return $messages;
    }

    public function purge()
    {
        $response = new Response();
        $response->headers->clearCookie(self::CookieName);
        $response->sendHeaders();
        $this->request->cookies->remove(self::CookieName);

        return $this;
    }

    public function add($message)
    {
        $messages = $this->all();
        array_push($messages, $message);

        $this->request->cookies->set(self::CookieName, serialize($messages));
        $cookie = new Cookie(self::CookieName, serialize($messages));
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->sendHeaders();

        return $this;
    }

}