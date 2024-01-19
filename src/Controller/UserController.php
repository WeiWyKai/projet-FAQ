<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER', null, 'Veuillez vous connecter pour accéder à cette partie')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
        
    }

    #[Route('/user/profil', name: 'edit_profil')]
    public function profil(Request $request, UploadService $uploadService): Response
    {   /** @var User $user */
        $user=$this->getUser();
        $profilForm = $this->createForm(RegistrationFormType::class, $user, [
            'is_profil' => true
        ]);
        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()){
            
            $avatarFile = $profilForm->get('avatarFile')->getData();

            //Si une image a été soumise, on traite celle ci
            if($avatarFile){
                $fileName = $uploadService->upload($avatarFile, $user->getAvatar());
                $user->setAvatar($fileName);
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil modifié!');

            return $this->redirectToRoute('all_questions');
        }
        return $this->render('user/profil.html.twig',[
            'profilForm' => $profilForm,
            'user' => $user
        ]);
    }

    #[Route('/user/{id}/delete', name: 'delete_profil')]
    public function deleteProfil(User $user, Request $request): RedirectResponse{

        // Récupération du jeton CSRF du formulaire
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        if ($method === 'DELETE' && $this->isCsrfTokenValid('delete_user', $token)){
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            
            $filesystem = new Filesystem();
            if($user->getAvatar() !== 'imgs/user_default.jpg'){
                // unlink($user->getAvatar());
                $filesystem->remove($user->getAvatar());
            }
    
            //Invalidation de la session utilisateur
            $session = $request->getSession();
            $session->invalidate();

            // Annule le token de sécurité utilisateur qui était lié à la session de connexion
            $this->container->get('security.token_storage')->setToken(null);

            $this->addFlash('success', 'Votre compte a bien été supprimé!');
    
            return $this->redirectToRoute('all_questions');
        }
        //Retour vers la page de profil si le token est invalide
        $this->addFlash('error', 'Jeton CSRF invalide');

        return $this->redirectToRoute('edit_profil');
    }
    
}

