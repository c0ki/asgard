<?php

namespace Core\ProjectBundle\Component\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as ComponentUrlGenerator;

class UrlGenerator extends ComponentUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        $url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
        if (!preg_match('/^core_/', $name) &&
            (array_key_exists('project', $parameters) || $this->getContext()->hasParameter('_project'))
        ) {
            $project = null;
            if ($this->getContext()->hasParameter('_project')) {
                $project = $this->getContext()->getParameter('_project');
            }
            if (array_key_exists('project', $parameters)) {
                $project = $parameters['project'];
            }
            if ($referenceType == self::ABSOLUTE_PATH) {
                $url = "/{$project}{$url}";
            } else {
                $infoUrl = parse_url($url);
                $url = str_replace($infoUrl['path'], "/{$project}{$infoUrl['path']}", $url);
            }
        }

        return $url;
    }

}
