<?php

namespace Chris\ChrisUserBundle\Form;

use Chris\ChrisUserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('agreeTerms', CheckboxType::class, [
                'translation_domain' => 'ChrisUserBundle',
                'label' => 'forms.registration.agreeToTerms',
                'mapped' => true,
            ])
            ->add('agreeMarketing', CheckboxType::class, [
                'translation_domain' => 'ChrisUserBundle',
                'label' => 'forms.registration.agreeToMarketing',
                'mapped' => true,
                'required'=>false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'=>PasswordType::class,
                'translation_domain' => 'ChrisUserBundle',
                'invalid_message' => 'forms.registration.passwordMismatch',
                'first_options' => [
                    'label' => 'forms.registration.password',
                ],
                'second_options' => [
                    'label' => 'forms.registration.repeatPassword',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.registration.passwordRequired',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'forms.registration.passwordLength',
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'mapped' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.registration.emailRequired',
                    ]),
                    new Length([
                        'max' => 254,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'register_item',
        ]);
    }
}
