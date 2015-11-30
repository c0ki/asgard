<?php

namespace Core\CoreBundle\Type;

use Doctrine\DBAL\Types\StringType;

class UrlType extends StringType
{
    const URL = 'url';

    public function getName()
    {
        return self::URL;
    }
}