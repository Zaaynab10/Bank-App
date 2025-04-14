<?php

namespace App\Account\Form;

use App\Account\Entity\Account;
use App\Account\Enum\AccountType as EnumBankAccountType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Savings' => EnumBankAccountType::SAVINGS,
                    'Current' => EnumBankAccountType::CURRENT,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Select Account Type',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }

}
