<?php

namespace App\Form;

use App\Entity\Language;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du projet', 'empty_data' => ''])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('sourceLanguage', EntityType::class, [
                'class'        => Language::class,
                'choice_label' => fn(Language $l) => $l->getCode().' — '.$l->getName(),
                'label'        => 'Langue source (langue d\'origine)',
                'placeholder'  => 'Choisir la langue source',
                'required'     => false,
            ])
            ->add('targetLanguages', EntityType::class, [
                'class'        => Language::class,
                'choice_label' => fn(Language $l) => $l->getCode().' — '.$l->getName(),
                'multiple'     => true,
                'expanded'     => true,
                'label'        => 'Langues cibles',
            ])
            ->add('translators', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => fn(User $u) => $u->getUsername() ?? $u->getEmail(),
                'query_builder' => fn(UserRepository $r) => $r->createQueryBuilder('u')
                    ->where('u.roles NOT LIKE :admin AND u.roles NOT LIKE :pm')
                    ->setParameter('admin', '%ROLE_ADMIN%')
                    ->setParameter('pm', '%ROLE_PROJECT_MANAGER%'),
                'multiple'  => true,
                'expanded'  => true,
                'required'  => false,
                'label'     => 'Traducteurs assignés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Project::class]);
    }
}
