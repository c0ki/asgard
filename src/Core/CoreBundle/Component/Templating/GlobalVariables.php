<?php

namespace Core\CoreBundle\Component\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables as FrameworkGlobalVariables;
use Symfony\Component\HttpFoundation\Request;

class GlobalVariables extends FrameworkGlobalVariables
{
    /**
     * Returns the master request.
     *
     * @return Request|null The HTTP request object
     */
    public function getMasterRequest() {
        return $this->container->get('request_stack')->getMasterRequest();
    }

    public function getLayoutTheme() {
        if ($this->container->hasParameter('theme_layout')) {
            return $this->container->getParameter('theme_layout');
        }
    }

    public function getAttributes() {
        return $this->getMasterRequest()->attributes->all();
    }

    public function getSiteaccesses() {
        if ($this->container->hasParameter('asgard.siteaccesses')) {
            return $this->container->getParameter('asgard.siteaccesses');
        }
    }

    public function getSiteaccess() {
        if ($this->getMasterRequest()->attributes->has('@siteaccess')) {
            return $this->getMasterRequest()->attributes->get('@siteaccess');
        }
    }

    public function getRoute($name = null) {
        if (!$name) {
            $name = $this->getMasterRequest()->attributes->get('_route');
        }

        return $this->container->get('router')->getRouteCollection()->get($name);
    }

    public function get_route() {
        return $this->getMasterRequest()->attributes->get('_route');
    }

    public function get_route_params(array $params = array()) {
        return array_merge($this->getMasterRequest()->attributes->get('_route_params'), $params);
    }

    public function getLayout($template) {
        $prevBundle = array();
        foreach ($this->container->getParameter('kernel.bundles') as $name => $path) {
            if (preg_match('/^Core/', $name)) {
                $prevBundle[] = $name;
            }
            $path = substr($path, 0, strrpos($path, '\\'));
            if (substr($this->getMasterRequest()->attributes->get('_controller'), 0, strlen($path)) === $path) {
                $prevBundle[] = $name;
                break;
            }
            elseif ($template->getTemplateName() === $name . '::layout.html.twig') {
                $prevBundle[] = $name;
                break;
            }
        }
        $prevBundle = array_reverse($prevBundle);
        foreach ($prevBundle as $name) {
            $bundleLayout = "{$name}::layout.html.twig";
            if ($template->getTemplateName() == $bundleLayout) {
                continue;
            }
            if ($this->container->get('request_stack')->getMasterRequest()->query->has('popin')) {
                $bundleLayout = "{$name}::layout_popin.html.twig";
            }
            if ($template->getTemplateName() == $bundleLayout) {
                continue;
            }
            if ($this->container->get('templating')->exists($bundleLayout)) {
                return $bundleLayout;
            }
        }
    }
}
