<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\AnswerFormType;
use App\Form\QuestionFormType;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function questionDetails(Question $question, ReponseRepository $reponseRepository, EntityManagerInterface $entityManager, Request $request): Response
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
            $answerForm = $emptyForm;
        }

        return $this->render('question/details.html.twig', [
            'question' => $question,
            'reponses' =>$reponses,
            'answerForm' => $answerForm
        ]);
    }
    #[IsGranted('ROLE_USER')]
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
}
