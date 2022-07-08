<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AnimalType extends AbstractType {

    public  function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo', TextType::class,
                ['label' => 'Código do gado: ',
                    'row_attr' => ['class' => 'col-sm-2'],
                    'attr' => ['placeholder' => '0000']])
            ->add('leite', TextType::class,
                ['label' => 'Leite por semana(L): ',
                    'row_attr' => ['class' => 'col-sm-2'],
                    'attr' => ['placeholder' => '0.0 L']])
            ->add('racao', TextType::class,
                ['label' => 'Ração por semana:',
                    'row_attr' => ['class' => 'col-sm-2'],
                    'attr' => ['placeholder' => '0.0 Kg']])
            ->add('peso', TextType::class,
                ['label' => 'Peso:',
                    'row_attr' => ['class' => 'col-md-1'],
                    'attr' => ['placeholder' => '0.0 Kg']])
            ->add('nascimento', DateType::class,
                ['label' => 'Data de Nascimento: ',
                    'html5' => true,
                    'widget' => 'single_text',
                    'attr' => ['placeholder' => 'dd/mm/aaaa'],
                    'row_attr' => ['class' => 'col-sm-2']])
            ->add('Salvar',SubmitType::class, array('row_attr' => ['class' => 'col-auto align-self-end mt-2'], 'attr' => ['class' => 'btn btn-primary px-5'] ) );

    }

}
?>