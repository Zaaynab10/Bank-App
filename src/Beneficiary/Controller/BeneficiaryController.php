<?php

namespace App\Beneficiary\Controller;

use App\Account\Repository\AccountRepository;
use App\Beneficiary\Entity\Beneficiary;
use App\Form\BeneficiaryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BeneficiaryController extends AbstractController
{
    #[Route('/add', name: 'add_beneficiary')]
    public function addBeneficiary(
        Request $request,
        EntityManagerInterface $entityManager,
        AccountRepository $accountRepository
    ): Response {
        $user = $this->getUser();
        $beneficiary = new Beneficiary();
        $beneficiary->setMember($user);

        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccountNumber = trim($form->get('bankAccountNumber')->getData());
            $beneficiaryName = strtolower(trim($form->get('name')->getData()));

            $existingAccount = $accountRepository->findOneBy(['account_number' => $bankAccountNumber]);

            if (!$existingAccount) {
                $this->addFlash('error', 'Le compte bancaire n\'existe pas dans notre base de données.');
                return $this->redirectToRoute('add_beneficiary');
            }

            $accountOwnerName = strtolower(trim($existingAccount->getOwner()->getName()));

            if ($accountOwnerName !== $beneficiaryName) {
                $this->addFlash('error', 'Le nom du bénéficiaire ne correspond pas au nom associé au compte bancaire.');
                return $this->redirectToRoute('add_beneficiary');
            }

            $entityManager->persist($beneficiary);
            $entityManager->flush();

            $this->addFlash('success', 'Bénéficiaire ajouté avec succès.');
            return $this->redirectToRoute('list_beneficiaries');
        }

        return $this->render('@Beneficiary/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/list', name: 'list_beneficiaries')]
    public function listBeneficiaries(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $beneficiaries = $entityManager->getRepository(Beneficiary::class)->findBy(['member' => $user]);

        return $this->render('@Beneficiary/list.html.twig', [
            'beneficiaries' => $beneficiaries,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_beneficiary')]
    public function deleteBeneficiary(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $beneficiary = $entityManager->getRepository(Beneficiary::class)->find($id);

        if (!$beneficiary || $beneficiary->getMember() !== $user) {
            $this->addFlash('error', 'Ce bénéficiaire n\'existe pas ou ne vous appartient pas.');
            return $this->redirectToRoute('list_beneficiaries');
        }

        $entityManager->remove($beneficiary);
        $entityManager->flush();

        $this->addFlash('success', 'Bénéficiaire supprimé avec succès.');
        return $this->redirectToRoute('list_beneficiaries');
    }
}
