<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    #[Route('/', name: 'all_questions')]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findBy(['dateCreation'=>'DESC']); 
        return $this->render('question/index.html.twig', [
            'questions' => $questions
        ]);
    }
}
