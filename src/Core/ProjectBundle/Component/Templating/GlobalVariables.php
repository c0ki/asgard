<?php

namespace Core\ProjectBundle\Component\Templating;

use Core\CoreBundle\Component\Templating\GlobalVariables as CoreGlobalVariables;

class GlobalVariables extends CoreGlobalVariables
{

    public function getProjects()
    {
        return $this->container->get('project_helper')->listProjects();
    }

    public function getProject()
    {
        return $this->container->get('project_helper')->getProject();
    }

    public function getEnvironments()
    {
        return $this->container->get('project_helper')->listEnvironments();
    }

    public function getEnvironment()
    {
        return $this->container->get('project_helper')->getEnvironment();
    }

}
