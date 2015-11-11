<?php

namespace Core\CoreBundle\Component\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as ComponentUrlGenerator;

class UrlGenerator extends ComponentUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        if ($name{0} != '_' && (array_key_exists('_site', $parameters) || $this->getContext()->hasParameter('_site'))) {
            $site = null;
            if (!empty($defaults) && array_key_exists('_site', $defaults)) {
                $site = $defaults['_site'];
                if (array_key_exists('_site', $parameters)) {
                    unset($parameters['_site']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('_site')) {
                    $site = $this->getContext()->getParameter('_site');
                }
                if (array_key_exists('_site', $parameters)) {
                    $site = $parameters['_site'];
                    unset($parameters['_site']);
                }
            }
            if (!empty($site)) {
                array_push($tokens, array('text', "/{$site}"));
            }
        }

        $url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);

        return $url;
    }

}
