<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangeProfileFormType extends AbstractType
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'disabled' => !$this->security->isGranted('ROLE_ADMIN'),
                'label' => 'Username',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 65,
                        'maxMessage' => 'Your username cannot have more than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'readonly' => !$this->security->isGranted('ROLE_ADMIN'),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email address',
                    ]),
                    new Email([
                        'message' => 'The email "{{ value }}" is not a valid email.',
                    ]),
                    new Regex([
                        'pattern' => '/@example\.com$/i',
                        'match' => false,
                        'message' => 'You cannot use example.com email addresses.',
                    ]),
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Your username cannot have more than {{ limit }} characters',
                    ]),
                ],
                'empty_data' => '',
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Your username cannot have more than {{ limit }} characters',
                    ]),
                ],
                'empty_data' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
