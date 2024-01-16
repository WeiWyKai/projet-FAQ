<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/profil', name: 'edit_profil')]
    public function index(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {   
        $user=$this->getUser();
        $profilForm = $this->createForm(RegistrationFormType::class, $user, [
            'is_profil' => true
        ]);
        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()){

            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié!');

            return $this->redirectToRoute('all_questions');
        }
        return $this->render('user/profil.html.twig',[
            'profilForm' => $profilForm
        ]);
    }
}
