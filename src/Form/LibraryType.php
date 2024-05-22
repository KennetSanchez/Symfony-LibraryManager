<?php

namespace App\Form;

use App\Entity\Library;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null, [
                'label' => 'Nombre'
            ])
            ->add('address',null, [
                'label' => 'Dirección'
            ])
            ->add('city',null, [
                'label' => 'Ciudad'
            ])
            ->add('openTime', null, [
                'widget' => 'single_text',
                'label' => 'Hora de apertura'
            ])
            ->add('closeTime', null, [
                'widget' => 'single_text',
                'label' => 'Hora de cierre'
            ])
            ->add('foundationDate', null, [
                'widget' => 'single_text',
                'label' => 'Fecha de fundación'
            ])
            ->add('libraryRules',null, [
                'label' => 'Normas de la biblioteca'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Library::class,
        ]);
    }
}
