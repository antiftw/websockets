<?php

namespace App\Form;

use App\Entity\WebsocketConnection;
use App\Entity\WebsocketMessage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsocketMessageType extends AbstractType
{
    public function __construct(private readonly ParameterBagInterface $parameters)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                'label' => 'Content',
                'attr' => [
                    'placeholder' => 'Content',
                ],
            ])
            ->add('from', TextType::class, [
                'label' => 'From',
                'attr' => [
                    'placeholder' => 'From'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WebsocketMessage::class,
        ]);
    }
}
