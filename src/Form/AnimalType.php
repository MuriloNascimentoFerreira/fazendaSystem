<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AnimalType extends AbstractType {

    public  function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('leite', TextType::class,
                ['label' => 'Leite por semana: ', 'row_attr' => ['class' => 'col-sm-2'], 'attr' => ['placeholder' => 'Litros']])
            ->add('racao', TextType::class,
                ['label' => 'Ração por semana: ', 'row_attr' => ['class' => 'col-sm-2'], 'attr' => ['placeholder' => 'Kg']])
            ->add('peso', TextType::class, ['label' => 'Peso: ', 'row_attr' => ['class' => 'col-sm-2'], 'attr' => ['placeholder' => 'Kg']])
            ->add('nascimento', TextType::class,
                ['label' => 'Data de Nascimento: ','row_attr' => ['class' => 'col-sm-2'], 'attr' => ['placeholder' => 'dd/mm/yyyy']])
            ->add('Salvar',SubmitType::class, array('row_attr' => ['class' => 'col-auto align-self-end mt-2'], 'attr' => ['class' => 'btn btn-primary px-5'] ) );
    }
}
?>