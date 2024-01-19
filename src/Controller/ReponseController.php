<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\User;
use App\Form\AnswerFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    #[IsGranted('REPONSE_DELETE', 'reponse',"YOU SHALL NOT PASS!!! modification impossible")]
    #[Route('/reponse/{id}/delete', name:'delete_reponse')]
    public function deleteReponse(Reponse $reponse, EntityManagerInterface $entityManager, Request $request)
    {  
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        if ($method === 'DELETE' && $this->isCsrfTokenValid('reponse_delete-'. $reponse->getId(), $token)){

            //Effectue la suppression
            $entityManager->remove($reponse);
            $entityManager->flush();
            
            $this->addFlash('success', 'Reponse supprimée!!! ');

        }else{
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette réponse');
        }

        return $this->redirectToRoute('question_details', [
            'id' => $reponse->getQuestion()->getId()
        ]);
    }
    #[IsGranted('REPONSE_VOTE', 'reponse', 'Vous avez déjà voté')]
    #[Route('/reponse/{id}/vote', name:'vote_reponse')]
    public function vote(Reponse $reponse, EntityManagerInterface $entityManager, Request $request):RedirectResponse
    {
        $token = $request->request->get('_token');
        
        if($request->getMethod() === 'POST' && $this->isCsrfTokenValid('vote-'.$reponse->getId(), $token))
        {
            /** @var User $user */
            $user= $this->getUser();

            // Associe la réponse à l'utilisateur
            $user->addVote($reponse);

            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success','Merci pour le vote!');
        }else{
            $this->addFlash('error','Vous ne pouvez plus voter ici');
        }
        
        return $this->redirectToRoute('question_details',[
            'id' => $reponse->getQuestion()->getId()
        ]);

    }

}