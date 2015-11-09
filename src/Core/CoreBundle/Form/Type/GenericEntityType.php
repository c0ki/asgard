<?php

namespace Core\CoreBundle\Form\Type;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericEntityType extends AbstractType
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var OptionsResolverInterface
     */
    private $resolver;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Check entity
        $ORMmanager = $this->doctrine->getManager();
        try {
            $entityMetadata = $ORMmanager->getClassMetadata($options['data_class']);
        }
        catch (\Exception $e) {
            throw EntityNotFoundException::fromClassNameAndIdentifier($options['data_class'], array());
        }

        // Add defaults data_class
        $this->resolver->setDefaults(array(
                                         'data_class' => $options['data_class']
                                     ));

        // Add fields
        foreach ($entityMetadata->getFieldNames() as $fieldName) {
            if ($entityMetadata->isIdentifier($fieldName) && $entityMetadata->isIdGeneratorIdentity()) {
                continue;
            }
            $builder->add($fieldName);
        }

        // Add associations fields
        foreach ($entityMetadata->getAssociationNames() as $associationName) {
            if (!$entityMetadata->isAssociationInverseSide($associationName)) {
                continue;
            }
            $targetClass = $entityMetadata->getAssociationTargetClass($associationName);
            $builder->add($associationName,
                          'collection',
                          array(
                              'type' => new self($this->doctrine),
                              'allow_add' => true,
                              'allow_delete' => true,
                              'options' => array('data_class' => $targetClass),
                              'by_reference' => false,
                          ));
        }
    }


    public function getName()
    {
        return 'generic_entity';
    }

}