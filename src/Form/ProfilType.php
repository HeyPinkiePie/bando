<?php

namespace App\Form;

use App\Entity\Stagiaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', null,
                [
                    'disabled' => true,
                    'attr' => ['class' => 'profilPrenom', 'pattern' => '^[^@&"()!_$*€£`+=\/;?#]+$', 'maxlength' => 150],
                ])
            ->add('nom', null,
                [
                    'disabled' => true,
                    'attr' => ['class' => 'profilNom', 'pattern' => '^[^@&"()!_$*€£`+=\/;?#]+$', 'maxlength' => 150],
                ])
            ->add('telephone', TelType::class,
                [
                    'required' => true,
                    'attr' => ['class' => 'profilPrenom', 'pattern' => '(0|\+33)[1-9]( *[0-9]{2}){4}', 'maxlength' => 10]
                ])
            ->add('email', EmailType::class,
                [
                    'required' => true,
                    'attr' => ['class' => 'profilEmail', 'maxlength' => 255],
                ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required'=>true,
                'options' => [
                    'attr' => [
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci d\'entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au minimu {{ limit }} caracteres',
                            // max length allowed by Symfony for security reasons
                            'max' => 255,
                        ]),
                    ],
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmation mot de passe',
                ],
                'invalid_message' => 'La confirmation du mot de passe doit correspondre au mot de passe.',
                'mapped' => false,
            ])
            ->add('campus', null, [
                'attr' => ['class' => 'profilCampus','readonly' => true],
            ])
            ->add('url_photo', HiddenType::class,
                [
                    'attr' => ['visible' => 'hidden'],
                ])
            ->add('imageFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_label' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'Le fichier est trop grand',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Votre photo doit être au format .jpg ou .jpeg ou .png'
                    ])
                ]
            ])
            ->add('Envoyer', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stagiaire::class,
        ]);
    }
}
