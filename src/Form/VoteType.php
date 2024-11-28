<?php

namespace App\Form;

use App\Entity\Choix;
use App\Entity\Utilisateur;
use App\Entity\Vote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('idChoix', EntityType::class, [
                'class' => Choix::class,
                'choice_label' => 'id',
            ])
            ->add('idUser', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vote::class,
        ]);
    }
}
