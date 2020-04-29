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

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('existingPassword', PasswordType::class, [
                'translation_domain' => 'ChrisUserBundle',
                'label' => 'forms.changePassword.existingPassword',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.changePassword.passwordRequired',
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'=>PasswordType::class,
                'translation_domain' => 'ChrisUserBundle',
                'invalid_message' => 'forms.changePassword.passwordMismatch',
                'first_options' => [
                    'label' => 'forms.changePassword.password',
                ],
                'second_options' => [
                    'label' => 'forms.changePassword.repeatPassword',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.changePassword.passwordRequired',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'forms.changePassword.passwordLength',
                        'max' => 100,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
