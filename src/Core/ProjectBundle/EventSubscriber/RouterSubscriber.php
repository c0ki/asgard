<?php

namespace Core\ProjectBundle\EventSubscriber;

use Core\ProjectBundle\Component\Helper\ProjectHelper;
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
     * @var ProjectHelper
     */
    protected $projectHelper;

    /**
     * @param ProjectHelper     $projectHelper
     * @param RouterInterface     $matcher
     * @param RequestContext|null $context
     */
    public function __construct(ProjectHelper $projectHelper, RouterInterface $matcher, RequestContext $context = null)
    {
        $this->projectHelper = $projectHelper;
        $this->context = $context ?: $matcher->getContext();
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            'kernel.request' => array(
                array('onKernelRequest', 99),
            )
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        foreach ($this->projectHelper->listProjects() as $project) {
            if (preg_match("#^/{$project->getName()}\b(.*)$#", $event->getRequest()->server->get('REQUEST_URI'), $matches)) {
                $event->getRequest()->server->set('REQUEST_URI', $matches[1]);
                $event->getRequest()->attributes->set('@project', $project->getName());
                $this->context->setParameter('@project', $project->getName());
                break;
            }
        }

        foreach ($this->projectHelper->listEnvironments() as $environment) {
            if (preg_match("#^/@{$environment->getName()}\b(.*)$#", $event->getRequest()->server->get('REQUEST_URI'), $matches)) {
                $event->getRequest()->server->set('REQUEST_URI', $matches[1]);
                $event->getRequest()->attributes->set('@environment', $environment->getName());
                $this->context->setParameter('@environment', $environment->getName());
                break;
            }
        }

        return;
    }
}