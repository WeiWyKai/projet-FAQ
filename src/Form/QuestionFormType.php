<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre',TextType::class,[
                'label' => 'Votre question: ',
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre question.',

                    ]),
                    New Length([
                        'max' => 250,
                        'maxMessage' => 'Question trop longue! Détails dans le champs ci-dessous'
                    ])
                ]
            ])

            ->add('contenu',TextareaType::class,[
                'required' => false,
                'label' => 'Les détails de votre question: ',
                'attr' => [
                    'rows' => 10
                ],
                'constraints'=> [
                    New Length([
                        'min' => 10,
                        'minMessage' => '{{ limit }} caractères minimum'
                    ])
                ]
            ])   
                     
            ->add('save', SubmitType::class, [
                'label' => $options['labelButton'],
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'labelButton' =>'Poster ma question'
        ]);
    }
}
