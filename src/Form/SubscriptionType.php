<?php

namespace App\Form;

use App\Entity\Subscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubscriptionType
 * @package App\Form
 */
class SubscriptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.name',

            ])
            ->add('email', null, [
                'label' => 'label.email',
            ])
            ->add('categories', ChoiceType::class, [
                'choices' => Subscription::getAvailableCategories(),
                'expanded'  => true,
                'multiple'  => true,
                'label' => 'label.categories',
                'label_attr' => ['class' => 'checkbox-custom'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['Default', 'Edit']
        ]);
    }
}