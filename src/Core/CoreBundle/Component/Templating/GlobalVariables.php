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
    public function getMasterRequest()
    {
        return $this->container->get('request_stack')->getMasterRequest();
    }

    public function getLayoutTheme()
    {
        if ($this->container->hasParameter('theme_layout')) {
            return $this->container->getParameter('theme_layout');
        }
    }

    public function getAttributes()
    {
        return $this->getMasterRequest()->attributes->all();
    }

    public function getSites()
    {
        if ($this->container->hasParameter('asgard.sites')) {
            return $this->container->getParameter('asgard.sites');
        }
    }

    public function getSite() {
        if ($this->getMasterRequest()->attributes->has('_site')) {
            return $this->getMasterRequest()->attributes->get('_site');
        }
    }

}
