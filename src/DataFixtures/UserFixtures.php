<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        
    }
    public function load(ObjectManager $manager): void
    {
        //Instance de Faker
        $faker= Faker\Factory::create();

        //Création de 50 Utilisateurs
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setPassword($this->passwordHasher->hashPassword($user, 'secret'));
            $user->setEmail($faker->email);
            $user->setNom($faker->name);
            $manager->persist($user);

            //Enregistre l'objet $user dans une référence avec un nom unique!
            $this->addReference("user-$i", $user);
        }

        //MAJ BDD
        $manager->flush();
    }
}
