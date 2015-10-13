<?php

namespace DisplayBundle\Component\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DisplayUrlType extends AbstractType
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $servers = $this->container->getParameter('portail.checker.server');
        foreach ($servers as $groupId => $group) {
            $groupServers = array();
            foreach ($group as $server) {
                $groupServers[$server] = $server;
            }
            $servers[$groupId] = $groupServers;
        }

        $builder->add('servers',
                      'choice',
                      array(
                          'label' => "Server",
                          'choices' => $servers,
                          'multiple' => true,
                          'expanded' => false,
                      ));

        $builder->add('relativeurl',
            'text',
            array(
                'label' => "Relative url",
            ));

//        $builder->add('resulttype',
//            'choice',
//            array(
//                'label' => "Result type",
//                'choices' => array(
//                    'contentdisplay' => 'Display content',
//                    'contentsearch' => 'Search content',
//                ),
//            ));

//        $builder->add('contentsearch',
//            'text',
//            array(
//                'label' => "Search content",
//                'required' => false,
//            ));
    }

    public function getName()
    {
        return 'displayurl';
    }

}