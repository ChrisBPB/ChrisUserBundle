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

class ChangeEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('existingPassword', PasswordType::class, [
                'translation_domain' => 'ChrisUserBundle',
                'label' => 'forms.changeEmail.existingPassword',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.changeEmail.passwordRequired',
                    ])
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type'=>EmailType::class,
                'translation_domain' => 'ChrisUserBundle',
                'invalid_message' => 'forms.changeEmail.emailMismatch',
                'first_options' => [
                    'label' => 'forms.changeEmail.email',
                ],
                'second_options' => [
                    'label' => 'forms.changeEmail.repeatEmail',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.changeEmail.emailRequired',
                    ]),
                    new Length([
                        'min' => 9,
                        'minMessage' => 'forms.changeEmail.emailLength',
                        'max' => 255,
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
            'csrf_token_id'   => 'email_item',
        ]);
    }
}
