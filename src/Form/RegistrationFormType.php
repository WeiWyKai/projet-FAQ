<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => "Nom: ",
                'attr' => [
                    'class' => 'form-control '
                ],
                'constraints' => [
                   new NotBlank([
                      'message' => 'Veuillez saisir votre nom'
                   ]),
                   new Length([
                      'max' => 50,
                      'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères'
                   ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => "Email: ",
                'attr' => [
                    'class' => 'form-control '
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "L'adresse est requise"
                    ]),
                    new Email([
                        'message' => "L'adresse est invalide"
                    ])
                ]
            ])
            ->add('avatarFile', FileType::class,[
                'required' => false,
                'mapped' => false,
                'label' => 'Photo de profil:',
                'attr' => [
                'class' => 'form-control '
                ],
                'help' => 'Votre photo de profil ne doit pas dépasser 1Mo et doit être de type: PNG, WEP, JPEG ou JPG',
                'constraints' => [
                    new File([
                        'extensions'=> ['png','jpeg', 'jpg', 'webp'],
                        'extensionsMessage' => 'Votre fichier n\' est pas une image acceptée',
                        'maxSize' => '1M',
                        'maxSizeMessage' => "L'image ne doit pas dépasser {{ limit }} en poids"
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ]);

        //Si la clé "is_profil" est a false, alors il s'agit du formulaire d'inscription , donc on ajoute a notre formulaire , le champs de mot de passe et l'acceptation des conditions
        if(!$options['is_profil']){
            $builder    
                ->add('agreeTerms', CheckboxType::class, [
                                    'mapped' => false,
                    'attr' => [
                    'class' => 'form-check-input '
                    ],
                    'constraints' => [
                        new IsTrue([
                            'message' => 'You should agree to our terms.',
                        ]),
                    ],
                ])
                ->add('plainPassword', PasswordType::class, [
                                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false, //plainPassword n'existe pas du coup
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control '],
                    'label' => "Mot de Passe: ",
                    'help' => "Le mot de passe doit contenir minimum 6 caractères",
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuilleez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Le mot de passe doit contenir au minimum {{ limit }} charactères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]);

        }    
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_profil' => false
        ]);
    }
}
