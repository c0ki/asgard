<?php

namespace Core\ProjectBundle\Component\Helper;

use Core\LayoutBundle\Component\Helper\BreadcrumbHelper as CoreBreadcrumbHelper;

class BreadcrumbHelper extends CoreBreadcrumbHelper
{
    /**
     * @var ProjectHelper
     */
    protected $projectHelper;

    public function setProjectHelper(ProjectHelper $projectHelper) {
        $this->projectHelper = $projectHelper;
    }

    public function getBreadcrumbData(array $params = array()) {
        $routes = parent::getBreadcrumbData($params);
        $rootRoute = array_shift($routes);
        if ($this->projectHelper->hasDomain()) {
            $domain = $this->projectHelper->getDomain();
            $element = array(
                'route'  => 'core_project_domain',
                'params' => array('@domain' => $domain->getName()),
                'label'  => $domain,
                'logo'   => $domain->getLogo(),
            );
            array_unshift($routes, $element);
        }
        if ($this->projectHelper->hasProject()) {
            $project = $this->projectHelper->getProject();
            $element = array(
                'route'  => 'core_project_project',
                'params' => array('@project' => $project->getName()),
                'label'  => $project,
                'logo'   => $project->getLogo(),
            );
            array_unshift($routes, $element);
        }
        $rootRoute['params'] = array('@project' => null, '@domain' => null);
        array_unshift($routes, $rootRoute);

        return $routes;
    }

}