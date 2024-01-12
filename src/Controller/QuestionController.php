<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    
    #[Route('/question/{id}', name: 'question_details')]
    public function questionDetails(Question $question, ReponseRepository $reponseRepository): Response
    {
        $reponses = $reponseRepository->findBy([],['dateCreation' => 'DESC']);

        return $this->render('question/details.html.twig', [
            'question' => $question,
            'reponses' =>$reponses
        ]);
    }
}
