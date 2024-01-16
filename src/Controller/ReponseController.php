<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\AnswerFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[Route('/question/{id}')]
class ReponseController extends AbstractController
{  
    #[IsGranted('REPONSE_EDIT', 'reponse',"YOU SHALL NOT PASS!!! modification impossible")]
    #[Route('/reponse/{id}/edit', name: 'edit_reponse')]
    public function editReponse(Request $request, EntityManagerInterface $entityManager, Reponse $reponse): Response
    {
        // if(!$this->isGranted('REPONSE_EDIT', $reponse))
        // {
        //     throw $this->createAccessDeniedException("YOU SHALL NOT PASS!!! modification impossible");
        // }
        $editForm = $this->createForm(AnswerFormType::class, $reponse);
        $editForm->handleRequest($request);

        
        if ($editForm->isSubmitted() && $editForm->isValid()){

            $reponse->setDateEdit(new \DateTime());
         
            $entityManager->persist($reponse);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse modifiée!');

            return $this->redirectToRoute('question_details', [
                'id' => $reponse->getQuestion()->getId()
            ]);
        }
        return $this->render('reponse/edit.html.twig', [
            'editForm' => $editForm
        ]);
    }

    // #[Route('/reponse/{id}/delete', name:'delete_reponse')]
    // public function deleteReponse()
    // {

    // }
}
