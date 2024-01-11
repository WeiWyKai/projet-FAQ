<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;


class QuestionFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies():array
    {
        return [
            // Il faut que les fixtures User soient généré avant de générer les Questions
            UserFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
          //Instance de Faker
          $faker= Faker\Factory::create('fr_FR');

          //Création de 200 questions
          for ($i = 0; $i < 200; $i++) {

            $number = $faker->numberBetween(0, 49); //0 à 49 car objet $user avec 50 valeur
            $user=$this->getReference("user-$number");

            $question = new Question();
            $question->setTitre($faker->sentence( 10, true));
            $question->setContenu("$faker->realText(200, 2) ?");
            $question->setDateCreation($faker->dateTimeBetween('-3 years', 'now'));
            $question->setUtilisateur($user);

             

              $manager->persist($question);

              $this->addReference("question-$i", $question);
          }

        $manager->flush();
    }
}
