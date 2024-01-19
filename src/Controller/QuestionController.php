<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\AnswerFormType;
use App\Form\QuestionFormType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuestionController extends AbstractController
{
    #[Route('/', name: 'all_questions')]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findBy([],['dateCreation' => 'DESC']);
        

        return $this->render('question/index.html.twig', [
            'questions' => $questions
        ]);
    }   
    
    #[Route('/question/{id}', name: 'question_details', requirements: ['id' => '\d+'])]
    public function questionDetails(Question $question, ReponseRepository $reponseRepository, EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer): Response
    {
        $reponses = $reponseRepository->findBy(
            ['Question' => $question->getId()],
            ['dateCreation' => 'DESC']
        );

        $reponse = new Reponse();
        $answerForm = $this->createForm(AnswerFormType::class, $reponse);

        $emptyForm = clone $answerForm;
        $answerForm->handleRequest($request);

        if ($answerForm->isSubmitted() && $answerForm->isValid()) {
            $reponse->setUtilisateur($this->getUser());
            $reponse->setDateCreation(new \DateTime());
            $reponse->setQuestion($question);

            $entityManager->persist($reponse);
            $entityManager->flush();

            $this->addFlash('success', "Votre réponse a été enregistré!");

            //Ne pas envoyer de mail si la reponse est posté par l'auteur de la question
            if($reponse->getUtilisateur() !== $question->getUtilisateur())
            {
                //Envoyer un email à l'auteur de la question
                $email = (new TemplatedEmail())
                ->to(new Address($question->getUtilisateur()->getEmail(), $question->getUtilisateur()->getNom()))
                ->from(new Address('blabla@blabla.com', 'FAQ'))
                ->subject('Une nouvelle reponse à votre question')
                ->htmlTemplate('emails/new_reponse.html.twig')
                ->context([
                    'name' => $question->getUtilisateur()->getNom(),
                    'question' => $question->getTitre(),
                    'url' => $this->generateUrl(
                        'question_details' , 
                        ['id' => $question->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ]);

                $mailer->send($email);
            }

            //On reclone notre objet formulaire vide dans l'objet départ
            $answerForm = clone $emptyForm;

            return $this->redirectToRoute('question_details', [
                'id' => $question->getId()
            ]);
        }
        //Verifie si l'utilisateur connecté a eja voté pour une reponse a cette question
            $user = $this->getUser();
            if($user !== null)
            {
                $hasVoted = $reponseRepository->hasVoted($user, $question);
            }

        return $this->render('question/details.html.twig', [
            'question' => $question,
            'reponses' =>$reponses,
            'answerForm' => $answerForm,
            'hasVoted' => $hasVoted ?? false //Coalescence des nuls
        ]);
    }
    #[IsGranted('ADD_QUESTION',null, 'Connecter vous ou inscriver vous pour poser une question.')]
    #[Route('/new/question', name: 'new_question')]
    public function newQuestion(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        $question = new Question();
        $questionForm = $this->createForm(QuestionFormType::class, $question);
        $questionForm->handleRequest($request);
        
        if ($questionForm->isSubmitted() && $questionForm->isValid()) {
            $question->setUtilisateur($this->getUser());
            $question->setDateCreation(new \DateTime());

            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', "Votre question a bien été postée!");
            return $this->redirectToRoute('question_details',[
                'id' => $question->getId()
            ]);
        }

        return $this->render('question/new.html.twig', [
            'questionForm' =>$questionForm            
        ]);
    }   

    #[IsGranted('EDIT_QUESTION','question' , 'Modification impossible ')]
    #[Route('/question/{id}/edit', name: 'edit_question', requirements: ['id' => '\d+'])]
    public function editReponse(Request $request, EntityManagerInterface $entityManager, Question $question): Response
    {
        $editForm = $this->createForm(QuestionFormType::class, $question,[
            'labelButton' => ' Modifier ma réponse'
        ]);
        $editForm->handleRequest($request);

        
        if ($editForm->isSubmitted() && $editForm->isValid()){

            $question->setDateEdit(new \DateTime());
         
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', 'Question modifiée!');

            return $this->redirectToRoute('question_details', [
                'id' => $question->getId()
            ]);
        }
        return $this->render('question/edit.html.twig', [
            'editForm' => $editForm
        ]);
    }
    
    #[IsGranted('DELETE_QUESTION','question' , 'Suppression impossible ')]
    #[Route('/question/{id}/delete', name: 'delete_question', requirements: ['id' => '\d+'])]
    public function deleteQuestion(Request $request, EntityManagerInterface $entityManager, Question $question): RedirectResponse
    {
        //Récupère des champs cachés dans le formulaire
        $token = $request->request->get('_token');
        $method = $request->request->get('_method');

        //Vérifie si la méthode et le jeton reçu sont corrects
        if ($method === 'DELETE' && $this->isCsrfTokenValid('delete_question', $token)){

            //Effectue la suppression
            $entityManager->remove($question);
            $entityManager->flush();
            
            $this->addFlash('success', 'Question supprimée!!! ');

            return $this->redirectToRoute('all_questions');
        }

        //Sinon on génère un message d'erreur et on redirige l'utilisateur vers le détail de la question
        $this->addFlash('error', 'Vous ne pouvez pas supprimmer cette question');

        return $this->redirectToRoute('question_details', [
            'id' => $question->getId()
        ]);
    }
}
