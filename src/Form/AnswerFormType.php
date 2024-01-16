<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;

class AnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu',TextareaType::class,[
                'label' => 'Votre réponse: ',
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre mot de passe',

                    ]),
                    New Length([
                        'min' => 10,
                        'minMessage' => 'La réponse doit contenir au minimum {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider votre réponse',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ])
         
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
