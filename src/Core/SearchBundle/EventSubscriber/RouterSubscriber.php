<?php

namespace Core\SearchBundle\EventSubscriber;

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
     * @param RouterInterface     $matcher
     * @param RequestContext|null $context
     */
    public function __construct(RouterInterface $matcher, RequestContext $context = null)
    {
        $this->context = $context ?: $matcher->getContext();
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            'kernel.request' => array(
                array('onKernelRequest', 1),
            )
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($event->getRequest()->attributes->has('query')) {
            $event->getRequest()->attributes->set('query', str_replace('§', '/', $event->getRequest()->attributes->get('query')));
        }

        return;
    }
}