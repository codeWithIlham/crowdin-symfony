<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur']),
                    new Length(['min' => 3, 'max' => 255]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un email']),
                    new Email(['message' => 'Adresse email invalide']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label'  => 'Mot de passe',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit faire au moins 6 caractères']),
                ],
            ])
            ->add('firstName', TextType::class, ['required' => false, 'label' => 'Prénom'])
            ->add('lastName', TextType::class, ['required' => false, 'label' => 'Nom'])
            ->add('roleChoice', ChoiceType::class, [
                'mapped'   => false,
                'label'    => 'Je suis...',
                'choices'  => [
                    'Traducteur'                    => 'translator',
                    'Chef de projet'                => 'project_manager',
                    'Chef de projet & Traducteur'   => 'both',
                ],
                'expanded' => true,
                'multiple' => false,
                'data'     => 'translator',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
