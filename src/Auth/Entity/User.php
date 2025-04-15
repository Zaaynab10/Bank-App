<?php

namespace App\Auth\Entity;

use App\Account\Entity\Account;
use App\Auth\Repository\UserRepository;
use App\Beneficiary\Entity\Beneficiary;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'owner')]
    private Collection $bankAccounts;

    /**
     * @var Collection<int, Beneficiary>
     */
    #[ORM\OneToMany(targetEntity: Beneficiary::class, mappedBy: 'member', orphanRemoval: true)]
    private Collection $beneficiary;

    public function __construct()
    {
        $this->bankAccounts = new ArrayCollection();
        $this->beneficiary = new ArrayCollection();
    }

    public function getFirstName(): ?string {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): ?string {
        return $this->email;
    }
    public function getName(): ?string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
    
    public function setEmail(string $email): static {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string {
        return (string) $this->email;
    }

    public function getRoles(): array {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): static {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getBankAccounts(): Collection {
        return $this->bankAccounts;
    }

    public function addBankAccount(Account $bankAccount): static {
        if (!$this->bankAccounts->contains($bankAccount)) {
            $this->bankAccounts->add($bankAccount);
            $bankAccount->setOwner($this);
        }
        return $this;
    }

    public function removeBankAccount(Account $bankAccount): static {
        if ($this->bankAccounts->removeElement($bankAccount)) {
            if ($bankAccount->getOwner() === $this) {
                $bankAccount->setOwner(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Beneficiary>
     */
    public function getBeneficiary(): Collection
    {
        return $this->beneficiary;
    }

    public function addBeneficiary(Beneficiary $beneficiary): static
    {
        if (!$this->beneficiary->contains($beneficiary)) {
            $this->beneficiary->add($beneficiary);
            $beneficiary->setMember($this);
        }

        return $this;
    }

    public function removeBeneficiary(Beneficiary $beneficiary): static
    {
        if ($this->beneficiary->removeElement($beneficiary)) {
            if ($beneficiary->getMember() === $this) {
                $beneficiary->setMember(null);
            }
        }

        return $this;
    }

}
