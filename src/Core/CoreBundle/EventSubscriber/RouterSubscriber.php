<?php

namespace Core\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class RouterSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestContext
     */
    private $context;

    /**
     * @var array
     */
    protected $siteaccesses;

    /**
     * @param RouterInterface     $matcher
     * @param RequestContext|null $context
     * @param array               $siteaccesses
     */
    public function __construct(RouterInterface $matcher, RequestContext $context = null, $siteaccesses = array())
    {
        $this->context = $context ?: $matcher->getContext();
        $this->siteaccesses = $siteaccesses;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            'kernel.request' => array(
                array('onKernelRequest', 100),
            )
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        foreach ($this->siteaccesses as $siteaccess) {
            if (preg_match("#^/{$siteaccess}\b(.*)$#", $event->getRequest()->server->get('REQUEST_URI'), $matches)) {
                $event->getRequest()->server->set('REQUEST_URI', $matches[1]);
                $event->getRequest()->attributes->set('@siteaccess', $siteaccess);
                $this->context->setParameter('@siteaccess', $siteaccess);
                break;
            }
        }

        return;
    }
}