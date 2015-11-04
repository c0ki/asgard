<?php

namespace Core\CoreBundle\Form\Type;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericEntityType extends AbstractType
{

    /**
     * @var OptionsResolverInterface
     */
    private $resolver;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        $resolver->setRequired(array('entity'));
//        $resolver->setDefaults(array(
//                                   'data_class' => 'Core\ProjectBundle\Entity\Project'
//                               ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Check entity
        $ORMmanager = $this->container->get('doctrine')->getManager();
        try {
            $entityMetadata = $ORMmanager->getClassMetadata($options['entity']);
        }
        catch (\Exception $e) {
            throw EntityNotFoundException::fromClassNameAndIdentifier($options['entity'], array());
        }

        // Add defaults data_class
        $this->resolver->setDefaults(array(
                                         'data_class' => $options['entity']
                                     ));

        foreach ($entityMetadata->fieldMappings as $fieldName => $fieldMetadata) {
            if ($entityMetadata->isIdentifier($fieldName) && $entityMetadata->isIdGeneratorIdentity()) {
                continue;
            }
            $builder->add($fieldName);

//            $options = array('label' => ucfirst($fieldName));
//            if (array_key_exists('nullable', $fieldMetadata) && $fieldMetadata['nullable']) {
//                $options['required'] = false;
//            }
//            switch ($fieldMetadata['type']) {
//                case 'string':
//                    $builder->add($fieldName, 'text', $options);
//                    break;
//                case 'text':
//                    $builder->add($fieldName, 'textarea', $options);
//                    break;
//                default:
//                    throw new ORMInvalidArgumentException("TODO type '{$entityMetadata->getTypeOfField($fieldName)}' no register");
//            }
        }
    }


    public function getName()
    {
        return 'generic_entity';
    }

}