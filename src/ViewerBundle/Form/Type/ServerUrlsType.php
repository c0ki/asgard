<?php

namespace ViewerBundle\Form\Type;

use Core\CoreBundle\Form\Type\GenericEntityType;
use Core\ProjectBundle\Component\Helper\ProjectHelper;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ServerUrlsType extends AbstractType
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var ProjectHelper
     */
    private $projectHelper;

    public function __construct(RegistryInterface $doctrine, ProjectHelper $projectHelper)
    {
        $this->doctrine = $doctrine;
        $this->projectHelper = $projectHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fixedValues = array();
        $fixedValues['project'] = $this->projectHelper->getProject();
        $fixedValues['domain'] = $this->projectHelper->getDomain();

        $builder->add('serverUrls',
                      'collection',
                      array(
                          'type' => new GenericEntityType($this->doctrine),
                          'allow_add' => true,
                          'allow_delete' => true,
                          'options' => array('data_class' => 'ViewerBundle\Entity\ServerUrl',
                                             'fixed_values' => $fixedValues),
                          'by_reference' => false,
                      ));

    }

    public function getName()
    {
        return 'server_urls';
    }

}