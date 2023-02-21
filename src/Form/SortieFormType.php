<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today= new \DateTime('now');
        $todayPlus4h = $today->add(new \DateInterval('PT4H'));

        $builder
         //   ->add('nom',null,['mapped'=>false,'attr' => ['class' => 'input-field inline']])
            ->add('nom',null)
            ->add('debutSortie',DateTimeType::class,
                [ 'date_widget'=>'single_text',
                'required' => true,
                    'invalid_message' => 'La sortie ne peut pas être antérieure à aujourdhui.',
                    'constraints' => [
                        new GreaterThan([
                            'value' => $todayPlus4h,
                            'message' => "la sortie doit démarrer au plus tôt dans 3 heures."
                        ])]    ]
            )
            ->add('dateLimiteInscription',DateTimeType::class,
                ['date_widget'=>'single_text',
                'required' => true,
                'invalid_message' => 'La date limite ne peut pas être antérieure à aujourdhui.',
                   ])
            ->add('nombreInscriptionsMax',null,
                ['constraints' => [ new GreaterThan([  'value' => 0 ]),
                                    new LessThan([  'value' => 1001])] ]
)
            ->add('infosSortie',null,[
                        'required' => false,])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner un campus',
                'required' => false,
            ])
        ;
    }
}
