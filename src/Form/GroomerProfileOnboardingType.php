<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GroomerProfileOnboardingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'placeholder' => 'Select your city',
                'label' => 'City',
            ])
            ->add('businessName', TextType::class, [
                'label' => 'Business name',
            ])
            ->add('about', TextareaType::class, [
                'label' => 'About your business',
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'Phone (optional)',
            ])
            ->add('serviceArea', TextType::class, [
                'required' => false,
                'label' => 'Service area (optional)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}

