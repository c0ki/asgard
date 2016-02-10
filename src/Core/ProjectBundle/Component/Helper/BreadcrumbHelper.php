<?php

namespace Core\ProjectBundle\Component\Helper;

use Core\LayoutBundle\Component\Helper\BreadcrumbHelper as CoreBreadcrumbHelper;

class BreadcrumbHelper extends CoreBreadcrumbHelper
{
    /**
     * @var ProjectHelper
     */
    protected $projectHelper;

    public function setProjectHelper(ProjectHelper $projectHelper)
    {
        $this->projectHelper = $projectHelper;
    }

    public function getBreadcrumbData(array $params = array()) {
        $routes = parent::getBreadcrumbData($params);
//        if ($this->projectHelper->hasDomain()) {
//            array_unshift($routes, $this->projectHelper->getDomain());
//        }
//        if ($this->projectHelper->hasProject()) {
//            array_unshift($routes, $this->projectHelper->getDomain());
//        }
        return $routes;
    }

}