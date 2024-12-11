<?php

namespace App\Form;

use App\Entity\Sondage;
use App\Entity\Theme;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SondageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre',TextType::class, ['label' => 'Titre du sondage'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('publique' , CheckboxType::class, ['label' => 'Sondage publique ?', 'required' => false])
            ->add('date_fin', null, [
                'widget' => 'single_text',
            ])

            ->add('id_owner', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])

            /*->add('imageChoixFile', FileType::class, [
                'label' => 'Image du choix (facultatif)',
                'required' => false,
                'mapped' => false, // L'image n'est pas directement mappée à l'entité
            ])*/
            ->add('imageFile', FileType::class, [
                'label' => 'Image du sondage',
                'mapped' => true,
                'required' => false,

            ])
            ->add('themes', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'libelle',
                'multiple' => true, // Permet la sélection multiple
                'expanded' => true, // Utilise des cases à cocher pour la sélection
                'label' => 'Thèmes disponibles',
            ]);




    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sondage::class,
            'csrf_protection' => false,

        ]);
    }
}
