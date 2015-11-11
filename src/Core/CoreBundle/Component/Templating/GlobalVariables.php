<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        if ($this->container->has('request_stack')) {
            return $this->container->get('request_stack')->getMasterRequest();
        }
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

}
