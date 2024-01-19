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
        for ($i = 0; $i < 75; $i++) {
            $user = new User();
            $user->setPassword($this->passwordHasher->hashPassword($user, 'secret'));
            $user->setEmail($faker->email);
            $user->setNom($faker->name);
            $user->setIsVerified($faker->boolean);
            $manager->persist($user);

            //Enregistre l'objet $user dans une référence avec un nom unique!
            $this->addReference("user-$i", $user);
        }
        //Création d'un Admin de test
        $admin = new User();
        $admin->setPassword($this->passwordHasher->hashPassword($user, 'secret'));
        $admin->setNom('WeiWyKai');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setEmail('WeiWyKai@Wei.com');
        $admin->setIsVerified(true);

        $manager->persist($admin);
        
        //MAJ BDD
        $manager->flush();
    }
}
