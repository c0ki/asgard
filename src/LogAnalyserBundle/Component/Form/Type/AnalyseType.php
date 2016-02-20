<?php

namespace LogAnalyserBundle\Component\Form\Type;

use LogAnalyserBundle\Component\Helper\LogFileHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnalyseType extends AbstractType
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['logType']) {
            case LogFileHelper::TYPE_EZ_LOG:
                $analyseChoices = array(
                    'status' => 'Nombre de requêtes par statut',
                    'code' => 'Nombre de requêtes par traces',
                );
                break;
            case LogFileHelper::TYPE_APACHE_ERRORLOG:
                $analyseChoices = array(
                    'type' => 'Nombre de requêtes par type',
                    'error' => 'Nombre de requêtes par erreur',
                );
                break;
            case LogFileHelper::TYPE_APACHE_ACCESSLOG:
            default:
                $analyseChoices = array(
                    'page' => 'Nombre de requêtes par page',
                    'code' => 'Nombre de requêtes par code HTTP',
                    '404' => 'Nombre de requêtes par page 404',
                );
                break;
        }
        $analyseChoices['firstlines'] = "50 premières lignes";
        $analyseChoices['lastlines'] = "50 dernières lignes";

        $builder->add('analyse',
            'choice',
            array(
                'label' => "Type d'analyse",
                'empty_value' => "Choisissez votre type d'analyse",
                'choices' => $analyseChoices,
            ));

        $builder->add('static',
            'checkbox',
            array(
                'label' => "Inclure les fichiers statiques",
                'required'  => false,
            ));
    }

    public function getName()
    {
        return 'analyse';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'logType' => '',
        ));
    }

}