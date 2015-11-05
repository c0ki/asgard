<?php
namespace TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description');

//        $builder->add('tags', 'collection', array('type' => new TagType(), 'options' => array('data_class' => 'TestBundle\Entity\Tag')));
        $builder->add('tags', 'collection', array('type' => new TagType(), 'options' => array('data_class' => 'TestBundle\Entity\Tag')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                                   'data_class' => 'TestBundle\Entity\Task',
                               ));
    }

    public function getName()
    {
        return 'task';
    }
}