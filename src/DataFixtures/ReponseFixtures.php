<?php

namespace App\DataFixtures;
use App\Entity\Reponse;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
;

class ReponseFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies():array
    {
        return [
            // Il faut que les fixtures User et les reponse soient généré avant de générer les reponses
            UserFixtures::class,
            QuestionFixtures::class
        ];
    }
    public function load(ObjectManager $manager): void
    {
        //Instance de Faker
        $faker= Faker\Factory::create('fr_FR');

        //Création de 1000reponses
        for ($i = 0; $i < 1000; $i++) {

            $number = $faker->numberBetween(0, 49); 
            $user=$this->getReference("user-$number"); 
            $question=$this->getReference("question-{$faker->numberBetween(0, 199)}");
            $dateCreationQuestion = $question->getDateCreation()->format('Y-m-d H:i:s');

            $reponse = new Reponse();
            $reponse->setContenu($faker->realText(200,2));
            $reponse->setDateCreation($faker->dateTimeBetween($dateCreationQuestion, 'now'));
            $reponse->setUtilisateur($user);
            $reponse->setQuestion($question);

            $manager->persist($reponse);
        }

    $manager->flush();
    }
}
