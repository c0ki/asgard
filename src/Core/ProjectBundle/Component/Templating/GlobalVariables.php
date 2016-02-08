<?php

namespace Core\ProjectBundle\Component\Templating;

use Core\LayoutBundle\Component\Templating\GlobalVariables as LayoutGlobalVariables;

class GlobalVariables extends LayoutGlobalVariables
{

    public function getProjects()
    {
        return $this->container->get('project_helper')->listProjects();
    }

    public function getProject()
    {
        return $this->container->get('project_helper')->getProject();
    }

    public function getDomains()
    {
        return $this->container->get('project_helper')->listDomains();
    }

    public function getDomain()
    {
        return $this->container->get('project_helper')->getDomain();
    }

    public function getDaemons()
    {
        return $this->container->get('daemon_helper')->listDaemons();
    }

}
