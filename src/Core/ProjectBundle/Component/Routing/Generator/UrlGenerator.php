<?php

namespace Core\ProjectBundle\Component\Routing\Generator;

use Core\CoreBundle\Component\Routing\Generator\UrlGenerator as CoreUrlGenerator;

class UrlGenerator extends CoreUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        if ($name{0} != '_' && (array_key_exists('_environment', $parameters) || $this->getContext()->hasParameter('_environment'))) {
            $environment = null;
            if (!empty($defaults) && array_key_exists('_environment', $defaults)) {
                $environment = $defaults['_environment'];
                if (array_key_exists('_environment', $parameters)) {
                    unset($parameters['_environment']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('_environment')) {
                    $environment = $this->getContext()->getParameter('_environment');
                }
                if (array_key_exists('_environment', $parameters)) {
                    $environment = $parameters['_environment'];
                    unset($parameters['_environment']);
                }
            }
            if (!empty($environment)) {
                array_push($tokens, array('text', "/_{$environment}"));
            }
        }

        if ($name{0} != '_' && (array_key_exists('_project', $parameters) || $this->getContext()->hasParameter('_project'))) {
            $project = null;
            if (!empty($defaults) && array_key_exists('_project', $defaults)) {
                $project = $defaults['_project'];
                if (array_key_exists('_project', $parameters)) {
                    unset($parameters['_project']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('_project')) {
                    $project = $this->getContext()->getParameter('_project');
                }
                if (array_key_exists('_project', $parameters)) {
                    $project = $parameters['_project'];
                    unset($parameters['_project']);
                }
            }
            if (!empty($project)) {
                array_push($tokens, array('text', "/{$project}"));
            }
        }

        $url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);

        return $url;
    }

}
