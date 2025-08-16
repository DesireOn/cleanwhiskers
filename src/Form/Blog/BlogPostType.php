<?php

declare(strict_types=1);

namespace App\Form\Blog;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('excerpt', TextareaType::class, ['required' => false])
            ->add('contentHtml', TextareaType::class)
            ->add('canonicalUrl', TextType::class, ['required' => false])
            ->add('metaTitle', TextType::class, ['required' => false])
            ->add('metaDescription', TextType::class, ['required' => false])
            ->add('category', EntityType::class, [
                'class' => BlogCategory::class,
                'choice_label' => 'name',
            ])
            ->add('tags', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'tag1, tag2'],
            ])
            ->add('isPublished', CheckboxType::class, ['required' => false])
            ->add('publishedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}
