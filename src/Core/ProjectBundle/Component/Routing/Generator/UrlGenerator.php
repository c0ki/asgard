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
        if ($name{0} != '_' && (array_key_exists('@domain', $parameters) || $this->getContext()->hasParameter('@domain'))) {
            $domain = null;
            if (!empty($defaults) && array_key_exists('@domain', $defaults)) {
                $domain = $defaults['@domain'];
                if (array_key_exists('@domain', $parameters)) {
                    unset($parameters['@domain']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('@domain')) {
                    $domain = $this->getContext()->getParameter('@domain');
                }
                if (array_key_exists('@domain', $parameters)) {
                    $domain = $parameters['@domain'];
                    unset($parameters['@domain']);
                }
            }
            if (!empty($domain)) {
                array_push($tokens, array('text', "/@{$domain}"));
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
