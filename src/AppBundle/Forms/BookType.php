<?php

namespace AppBundle\Forms;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Title'])
            ->add('description', TextType::class, ['label' => 'Description'])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input'  => 'datetime'
            ])
            ->add('authors', EntityType::class, [
                'class' => 'AppBundle:Author',
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'empty_data' => false,
                'required' => true,
                'data_class' => null
            ])
            ->add('save', SubmitType::class, ['label' => 'Create Book']);
    }

}