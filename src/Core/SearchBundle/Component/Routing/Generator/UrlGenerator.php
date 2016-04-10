<?php

namespace Core\SearchBundle\Component\Routing\Generator;

use Core\ProjectBundle\Component\Routing\Generator\UrlGenerator as MainUrlGenerator;

class UrlGenerator extends MainUrlGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        if (array_key_exists('query', $parameters)) {
            $parameters['query'] = str_replace('/', '§', $parameters['query']);
        }

        return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }

}
