<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\MemberDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<MemberDto>
 */
final class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('email', TextType::class, ['label' => 'Email'])
            ->add('company', TextType::class, ['label' => 'Nom de l\'entreprise'])
            ->add('address', TextType::class, ['label' => 'Adresse'])
            ->add('postalCode', TextType::class, ['label' => 'Code postal'])
            ->add('city', TextType::class, ['label' => 'Ville'])
            ->add('phone', TextType::class, ['label' => 'Téléphone'])
            ->add('siret', TextType::class, ['label' => 'SIRET'])
            ->add('received', CheckboxType::class, [
                'label' => 'Bulletin reçu ?',
                'required' => false,
            ])
            ->add('returnedAt', DateType::class, [
                'label' => 'Date de réception',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberDto::class,
        ]);
    }
}
