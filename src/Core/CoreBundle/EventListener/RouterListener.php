<?php

namespace Core\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\EventListener\RouterListener as FrameworkRouterListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class RouterListener extends FrameworkRouterListener
{

    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            parent::onKernelRequest($event);
            if ($event->getRequest()->attributes->has('_route_params')
                && array_key_exists('project', $event->getRequest()->attributes->get('_route_params'))
            ) {
                $params = $event->getRequest()->attributes->get('_route_params');
                $event->getRequest()->attributes->add(array('_project' => $params['project']));
            }
        } catch (NotFoundHttpException $exception) {
            $request = $event->getRequest();
            if ($request->attributes->has('_route')) {
                return;
            }
            if (!preg_match('#^/([^/]+)(/.*)#', $request->getPathInfo(), $matches)) {
                return;
            }
            $project = $matches[1];
            $url = $matches[2];

            $request->server->set('REQUEST_URI', $url);

            $newRequest = new Request($request->query->all(), $request->request->all(), $request->attributes->all(), $request->cookies->all(),
                $request->files->all(), $request->server->all());

            $newEvent = new GetResponseEvent($event->getKernel(), $newRequest, $event->getRequestType());

            parent::onKernelRequest($newEvent);

            $event->getRequest()->attributes->add($newEvent->getRequest()->attributes->all());
            $event->getRequest()->attributes->add(array('_project' => $project));
        }
    }
}