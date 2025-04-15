<?php

namespace App\Beneficiary\Entity;

use App\Auth\Entity\User;
use App\Beneficiary\Repository\BeneficiaryRepository ;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BeneficiaryRepository::class)]
#[UniqueEntity(
    fields: ['name', 'bankAccountNumber', 'member'],
    message: 'Ce bénéficiaire existe déjà pour cet utilisateur.'
)]
class Beneficiary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $bankAccountNumber = null;

    #[ORM\ManyToOne(inversedBy: 'beneficiaries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $member = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(string $bankAccountNumber): static
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): static
    {
        $this->member = $member;

        return $this;
    }
}
