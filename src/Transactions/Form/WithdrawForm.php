<?php

namespace App\Transactions\Form;

use App\Account\Repository\AccountRepository;
use App\Transactions\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class WithdrawForm extends AbstractType {
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository) {
        $this->accountRepository = $accountRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $user = $options['user'];

        $accounts = $this->accountRepository->findBy(['owner' => $user]);

        if (empty($accounts)) {
            throw new \Exception("No bank accounts found for this user.");
        }

        $builder
            ->add('account', ChoiceType::class, [
                'choices' => array_flip(array_map(function ($account) {
                    return $account->getId() . ' - ' . $account->getType()->value . '-' . $account->getAccountNumber();
                }, $accounts)),

                'mapped' => false,
                'label' => 'Select Account',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Deposit Amount',
                'attr' => ['min' => 1],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
        ]);
    }

}
