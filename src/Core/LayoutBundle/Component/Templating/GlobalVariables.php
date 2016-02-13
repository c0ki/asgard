<?php

namespace Core\LayoutBundle\Component\Templating;

use Core\CoreBundle\Component\Templating\GlobalVariables as CoreGlobalVariables;

class GlobalVariables extends CoreGlobalVariables
{

    public function getTools()
    {
        return $this->container->get('tool_helper')->listTools();
    }

    public function getTool()
    {
        return $this->container->get('tool_helper')->getTool();
    }

    public function getIsPopin() {
        return $this->container->get('request_stack')->getMasterRequest()->query->has('popin');
    }

}
