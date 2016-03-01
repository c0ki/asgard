<?php

namespace Core\SearchengineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Core\SearchengineBundle\DependencyInjection\SearchengineExtension;

class CoreSearchengineBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SearchengineExtension();
    }
}
