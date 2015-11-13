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
        if ($name{0} != '_' && (array_key_exists('@siteaccess', $parameters) || $this->getContext()->hasParameter('@siteaccess'))) {
            $siteaccess = null;
            if (!empty($defaults) && array_key_exists('@siteaccess', $defaults)) {
                $siteaccess = $defaults['@siteaccess'];
                if (array_key_exists('@siteaccess', $parameters)) {
                    unset($parameters['@siteaccess']);
                }
            }
            else {
                if ($this->getContext()->hasParameter('@siteaccess')) {
                    $siteaccess = $this->getContext()->getParameter('@siteaccess');
                }
                if (array_key_exists('@siteaccess', $parameters)) {
                    $siteaccess = $parameters['@siteaccess'];
                    unset($parameters['@siteaccess']);
                }
            }
            if (!empty($siteaccess)) {
                array_push($tokens, array('text', "/{$siteaccess}"));
            }
        }

        $url = parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);

        return $url;
    }

}
