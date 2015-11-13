<?php

namespace Core\CoreBundle\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper as FrameworkPhpMatcherDumper;

class PhpMatcherDumper extends FrameworkPhpMatcherDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $options = array())
    {
        foreach ($this->getRoutes()->all() as $name => $route) {
            if (!$route->getRequirements()) {
                continue;
            }
            $internalRequirementNames = preg_grep("/^@\w+/", array_keys($route->getRequirements()));
            if (!$internalRequirementNames) {
                continue;
            }

            foreach ($internalRequirementNames as $internalRequirementName) {
                $internalRequirement = $route->getRequirement($internalRequirementName);
                $condition = $route->getCondition() ? $route->getCondition() . ' and ' : '';
                if ($internalRequirement == '%') {
                    $condition .= "request.attributes.has('{$internalRequirementName}')";
                }
                else {
                    $internalRequirement = explode('|', $internalRequirement);
                    $condition .= "request.attributes.get('{$internalRequirementName}') in " . json_encode($internalRequirement);
                }
                $requirements = $route->getRequirements();
                unset($requirements[$internalRequirementName]);
                $route->setRequirements($requirements);
                $route->setCondition($condition);
            }
        }

        return parent::dump($options);
    }
}
