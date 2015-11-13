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
        if ($name{0} != '_' && (array_key_exists('@site', $parameters) || $this->getContext()->hasParameter('@site'))) {
            $site = null;
            if (!empty($defaults) && array_key_exists('@site', $defaults)) {
                $site = $defaults['@site'];
                if (array_key_exists('@site', $parameters)) {
                    unset($parameters['@site']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('@site')) {
                    $site = $this->getContext()->getParameter('@site');
                }
                if (array_key_exists('@site', $parameters)) {
                    $site = $parameters['@site'];
                    unset($parameters['@site']);
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
