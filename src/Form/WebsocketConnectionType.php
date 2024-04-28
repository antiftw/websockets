<?php

namespace App\Form;

use App\Entity\WebsocketConnection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsocketConnectionType extends AbstractType
{
    public function __construct(private readonly ParameterBagInterface $parameters)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('host', TextType::class, [
                'label' => 'Host',
                'data' => $this->parameters->get('websocket_host'),
                'attr' => [
                    'placeholder' => 'Host',
                ],
            ])
            ->add('port', NumberType::class, [
                'label' => 'Port',
                'data' => $this->parameters->get('websocket_port'),
                'attr' => [
                    'min' => 1,
                    'max' => 65535,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WebsocketConnection::class,
        ]);
    }
}
