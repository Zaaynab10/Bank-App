<?php 

namespace App\Auth\Service;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserSearchService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function searchUsers(string $query)
    {
        if (empty($query)) {
            return $this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->orderBy('u.id', 'DESC')
                ->setMaxResults(50)
                ->getQuery()
                ->getResult();
        } else {
            return $this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('LOWER(u.email) LIKE LOWER(:query)')
                ->orWhere('LOWER(u.firstName) LIKE LOWER(:query)')
                ->orWhere('LOWER(u.lastName) LIKE LOWER(:query)')
                ->orWhere('u.phone LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('u.id', 'DESC')
                ->setMaxResults(50)
                ->getQuery()
                ->getResult();
        }
    }
}
