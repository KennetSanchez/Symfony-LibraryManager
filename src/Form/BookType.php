<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'empty_data' => 'Entre fantasmas',
                'label' => 'Título'
            ])

            ->add('author', TextType::class, [
                'required' => true,
                'empty_data' => 'Fernando Vallejo Rendón',
                'label' => 'Autor'
            ])
            ->add('synopsis', TextareaType::class, [
                'required' => true,
                'empty_data' => 'Novela que narra la vida del autor desde que se mudó a México',
                'label' => 'Sinopsis'
            ])
            ->add('publishDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de publicación'
            ])
            ->add('publisher', TextType::class, [
                'required' => true,
                'empty_data' => 'Alfaguara',
                'label' => 'Editorial'
            ])
            ->add('ISBN', IntegerType::class, [
                'required' => true,
                'empty_data' => '958-614-378-3',
                'label' => 'Código ISBN'
            ])
            ->add('copies', IntegerType::class, [
                'required' => true,
                'empty_data' => '999',
                'label' => 'Existencias',
                'constraints' => [
                    new GreaterThanOrEqual(0, message: 'Las unidades no pueden ser inferiores  a 0')
                ]
            ])
            ->add('library', EntityType::class, [
                'class' => Library::class,
                'choice_label' => 'name',
                'label' => 'Biblioteca'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);

        $resolver->setAllowedTypes('required', 'bool');
    }
}
