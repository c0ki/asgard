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
        if ($this->getMasterRequest()->attributes->has('_project')) {
            return $this->container->get('project_helper')->getProjectByName($this->getMasterRequest()->attributes->get('_project'));
        }
    }

    public function getEnvironments()
    {
        return $this->container->get('project_helper')->listEnvironments();
    }

    public function getEnvironment()
    {
        if ($this->getMasterRequest()->attributes->has('_environment')) {
            return $this->container->get('project_helper')->getEnvironmentByName($this->getMasterRequest()->attributes->get('_environment'));
        }
    }

}
