<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Actual Password',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'New password',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ]);
    }
}
