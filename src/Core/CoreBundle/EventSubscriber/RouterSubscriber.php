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
    protected $sites;

    /**
     * @param RouterInterface     $matcher
     * @param RequestContext|null $context
     * @param array               $sites
     */
    public function __construct(RouterInterface $matcher, RequestContext $context = null, $sites = array())
    {
        $this->context = $context ?: $matcher->getContext();
        $this->sites = $sites;
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

        foreach ($this->sites as $site) {
            if (preg_match("#^/{$site}\b(.*)$#", $event->getRequest()->server->get('REQUEST_URI'), $matches)) {
                $event->getRequest()->server->set('REQUEST_URI', $matches[1]);
                $event->getRequest()->attributes->set('_site', $site);
                $this->context->setParameter('_site', $site);
                break;
            }
        }

        return;
    }
}