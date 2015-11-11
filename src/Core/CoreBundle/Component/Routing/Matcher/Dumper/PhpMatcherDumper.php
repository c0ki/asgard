<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\CoreBundle\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper as FrameworkPhpMatcherDumper;

/**
 * PhpMatcherDumper creates a PHP class able to match URLs for a given set of routes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class PhpMatcherDumper extends FrameworkPhpMatcherDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $options = array())
    {


        foreach ($this->getRoutes()->all() as $route) {
            if ($route->hasRequirement('_site')) {
                $sites = explode('|', $route->getRequirement('_site'));
                $requirements = $route->getRequirements();
                unset($requirements['_site']);
                $route->setRequirements($requirements);
                $condition = $route->getCondition() ? $route->getCondition() . ' and ' : '';
                $route->setCondition($condition . "request.attributes.get('_site') in " . json_encode($sites));
            }
        }
        return parent::dump($options);
    }
}
