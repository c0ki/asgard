<?php

namespace Core\SearchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query')
            ->add('search', 'submit')
        ;
    }

    public function getName()
    {
        return 'core_search';
    }
}