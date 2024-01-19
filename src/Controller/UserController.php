<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    #[IsGranted('USER_ACCESS')]
    #[Route('/{type}/{id}/report', name:'reportQA' ,requirements: ['id' => '\d+', 'type' => 'question|reponse'])]
    public function reportQA(string $type ,int $id, MailerInterface $mailer, Request $request, QuestionRepository $questionRepository, ReponseRepository $reponseRepository):RedirectResponse
    {
        if($type === 'question'){
            $question=$questionRepository->find($id);

            //error404
            if(!$question){
                $this->createNotFoundException(('Aucun question sous cet ID'));
            }
            $questionId = $question->getId();

        }else{
            $reponse=$reponseRepository->find($id);

              //error404
              if(!$reponse){
                $this->createNotFoundException(('Aucun reponse sous cet ID'));
            }
            $questionId = $reponse->getQuestion()->getId();
        }

        $token =$request->request->get('_token');

        if($this->isCsrfTokenValid("reportQA-$type-$id", $token)){

            /** @var User $user */
            $user = $this->getUser();
            
            $email = (new TemplatedEmail())
                ->from(new Address($user->getEmail(), $user->getNom()))
                ->to('report@faq.test')
                ->subject('Report enquiry')
                ->htmlTemplate('emails/report.html.twig')
                ->context([
                    'nom' => $user->getNom(),
                    'url' => $this->generateURL(
                        'question_details',
                        ['id' => $questionId],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ])
            ;    
            $mailer->send($email);
    
            $this->addFlash('success', "Report sent! Snitch..."); 

        }else {
            $this->addFlash('error', 'Jeton CSRF invalide');

        }
    
        return $this->redirectToRoute('question_details',[
            'id'=>$questionId
        ]);
    }

}

