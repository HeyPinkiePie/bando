<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus')
            ->add('nom', null,['mapped'    => false, 'required' => false])
            ->add('debutSortie',null, [
                'date_widget'=>'single_text',
                'data' => new \DateTime('00:00:01'),
            ])
            ->add('finSortie', null, [
                'date_widget'=>'single_text',
                'data' => (new \DateTime('23:59:59'))->modify('+1 month'),
            ])
            ->add('organisateur', CheckboxType::class, [
                'mapped'    => false,
                'required'  => false,
                'label'     => "Sorties dont je suis l'organisateur/trice",
                'data'      => true,
            ])
            ->add('inscrit', CheckboxType::class, [
                'mapped'    => false,
                'required'  => false,
                'label'     => "Sorties auxquelles je suis inscrit/e",
                'data'      => true,
            ])
            ->add('non_inscrit', CheckboxType::class, [
                'mapped'    => false,
                'required'  => false,
                'label'     => "Sorties auxquelles je ne suis pas inscrit/e",
                'data'      => true,
            ])
            ->add('sorties_passees', CheckboxType::class, [
                'mapped'    => false,
                'required'  => false,
                'label'     => "Sorties passées",
                'data'      => true,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
