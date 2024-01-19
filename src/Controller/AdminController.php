<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN', null, ('Vous ne disposez pas des droits nécessaires'))]
class AdminController extends AbstractController
{
    #[Route('/index', name:'a_accueil')]
    public function index():Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/users', name:'a_users')]
    public function allUsers(UserRepository $userRepository):Response
    {
        $users = $userRepository->findBy(
            [],
            ['nom' =>'ASC']
        );
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/user/{id}', name:'a_delete_user')]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $entityManager, ):RedirectResponse
    {
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        if ($method === 'DELETE' && $this->isCsrfTokenValid('delete_user-'. $user->getId(), $token)){
            
            $entityManager->remove($user);
            $entityManager->flush();

            
            if($user === $this->getUser()){
                //Invalidation de la session utilisateur
                $session = $request->getSession();
                $session->invalidate();
                
                // Annule le token de sécurité utilisateur qui était lié à la session de connexion
                $this->container->get('security.token_storage')->setToken(null);
                
                $this->addFlash('success', 'le compte a bien été supprimé!');
                
                return $this->redirectToRoute('all_questions');
                
            }
            $this->addFlash('success', 'le compte a bien été supprimé!');
            
        }
        
        return $this->redirectToRoute('a_users');
    }

    #[Route('/user/{id}/role', name:'name_admin')]
    public function roleAdmin(User $user, EntityManagerInterface $entityManager)
    {
        $user->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getNom()} est maintenant un Admin");

        return $this->redirectToRoute('a_users');
    }

    #[Route('/reporting/questions', name:'reporting_questions')]
    public function reportingQuestions(QuestionRepository $questionRepository) :Response
    {   
        $questions = $questionRepository->getQuestionsOnFire();
        
        return $this->render('/admin/reports/questions.html.twig',[
            'questions' => $questions
        ]);
    }

    #[Route('/reporting/reponses', name:'reporting_reponses')]
    public function reportingReponses(ReponseRepository $reponseRepository) :Response
    {   
        $reponses = $reponseRepository->getReponsesOnFire();
        
        return $this->render('/admin/reports/reponses.html.twig',[
            'reponses' => $reponses
        ]);
    }
}
