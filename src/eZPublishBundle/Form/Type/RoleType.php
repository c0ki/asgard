<?php

namespace eZPublishBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleType extends AbstractType
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('loader',
                      'file',
                      array(
                          'required' => false,
                      ));


        $builder->add('right', 'collection', array(
            'type'         => new RightType($this),
            'allow_add'    => true,
        ));

    }

    public function getName()
    {
        return 'ezpublish_role';
    }

}