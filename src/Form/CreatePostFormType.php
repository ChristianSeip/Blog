<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreatePostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Your title should be at least {{ limit }} characters',
                        'max' => 50,
                        'maxMessage' => 'Your title cannot have more than {{ limit }} characters',
                    ]),
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a content',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Your content should be at least {{ limit }} characters',
                        'max' => 64000,
                        'maxMessage' => 'Your content cannot have more than {{ limit }} characters',
                    ]),
                ]
            ]);
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
