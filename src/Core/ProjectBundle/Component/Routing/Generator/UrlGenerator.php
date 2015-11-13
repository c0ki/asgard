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
        if ($name{0} != '_' && (array_key_exists('@environment', $parameters) || $this->getContext()->hasParameter('@environment'))) {
            $environment = null;
            if (!empty($defaults) && array_key_exists('@environment', $defaults)) {
                $environment = $defaults['@environment'];
                if (array_key_exists('@environment', $parameters)) {
                    unset($parameters['@environment']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('@environment')) {
                    $environment = $this->getContext()->getParameter('@environment');
                }
                if (array_key_exists('@environment', $parameters)) {
                    $environment = $parameters['@environment'];
                    unset($parameters['@environment']);
                }
            }
            if (!empty($environment)) {
                array_push($tokens, array('text', "/@{$environment}"));
            }
        }

        if ($name{0} != '_' && (array_key_exists('@project', $parameters) || $this->getContext()->hasParameter('@project'))) {
            $project = null;
            if (!empty($defaults) && array_key_exists('@project', $defaults)) {
                $project = $defaults['@project'];
                if (array_key_exists('@project', $parameters)) {
                    unset($parameters['@project']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('@project')) {
                    $project = $this->getContext()->getParameter('@project');
                }
                if (array_key_exists('@project', $parameters)) {
                    $project = $parameters['@project'];
                    unset($parameters['@project']);
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
