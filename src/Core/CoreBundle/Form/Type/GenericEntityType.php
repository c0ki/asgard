<?php

namespace Core\CoreBundle\Form\Type;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $resolver->setDefaults(array(
                                   'fixed_values' => array()
                               ));
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
        $this->resolver->setDefaults(
            array(
                'data_class' => $options['data_class'],
            ));

        // Add fields
        foreach ($entityMetadata->getFieldNames() as $fieldName) {
            if ($entityMetadata->isIdentifier($fieldName) && $entityMetadata->isIdGeneratorIdentity()) {
                continue;
            }
            if (array_key_exists($fieldName,
                                 $options['fixed_values']) && !is_null($options['fixed_values'][$fieldName])
            ) {
                $builder->add($fieldName,
                              'text',
                              array(
                                  'data' => $options['fixed_values'][$fieldName],
                                  'read_only' => true,
                              ));
                continue;
            }
            if ($entityMetadata->getTypeOfField($fieldName) == 'url') {
                $builder->add($fieldName, 'url');
                continue;
            }
            $builder->add($fieldName);
        }

        // Add associations fields
        foreach ($entityMetadata->getAssociationNames() as $associationName) {
            $targetClass = $entityMetadata->getAssociationTargetClass($associationName);
            if (array_key_exists($associationName,
                                 $options['fixed_values']) && !is_null($options['fixed_values'][$associationName])
            ) {
                $builder->add($associationName,
                              'entity',
                              array(
                                  'data' => $options['fixed_values'][$associationName],
                                  'disabled' => true,
                                  'class' => $targetClass,
                              ));
                continue;
            }
            if ($entityMetadata->isCollectionValuedAssociation($associationName)) {
                $builder->add($associationName);
                continue;
            }
            elseif ($entityMetadata->isSingleValuedAssociation($associationName)) {
                $builder->add($associationName);
                continue;
            }
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

        if (!empty($options['fixed_values'])) {
            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($options) {
                    $data = $event->getData();
                    foreach ($options['fixed_values'] as $name => $value) {
                        call_user_func(array($data, 'set' . $name), $value);
                    }
                    $event->setData($data);
                }
            );
        }
    }

    public function getName()
    {
        return 'generic_entity';
    }

}