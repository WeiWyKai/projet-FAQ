<?php

namespace App\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class QuestionVoter extends Voter
{   
    public const ADD = 'ADD_QUESTION';
    public const DELETE = 'DELETE_QUESTION';
    public const EDIT = 'EDIT_QUESTION';

    public function __construct(
        private Security $security
    ){
        
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        //Si l'attribut correspond à la valeur de la constante "ADD, on applique les règles du voter
        if($attribute === self::ADD){
            return true;
        }
        return in_array($attribute, [self::DELETE, self::EDIT])
            && $subject instanceof \App\Entity\Question;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
            $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ADD:
                return $this->security->isGranted('ROLE_USER');  
                break;
            case self::EDIT: //pas de logique du coup reprend la logique du case suivant
                return $subject->getUtilisateur() === $user;
                break;
            case self::DELETE:
                return $subject->getUtilisateur() === $user || $this->security->isGranted('ROLE_ADMIN') ;
                break;
        }

        return false;
    }
}
