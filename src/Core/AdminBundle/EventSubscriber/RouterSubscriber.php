<?php

namespace Core\AdminBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RequestContext;

class RouterSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestContext
     */
    private $context;

    public function __construct($matcher, RequestContext $context = null)
    {
        $this->context = $context ?: $matcher->getContext();
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

        if (preg_match('#^/admin\b(.*)$#', $event->getRequest()->server->get('REQUEST_URI'), $matches)) {
            $event->getRequest()->server->set('REQUEST_URI', $matches[1]);
            $event->getRequest()->attributes->set('_site', 'admin');
            $this->context->setParameter('_site', 'admin');
        }
        return;
    }
}