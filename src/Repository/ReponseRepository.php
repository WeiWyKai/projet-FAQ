<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 *
 * @method Reponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reponse[]    findAll()
 * @method Reponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    //Permet de savoir si un utilisateur a déjà voter pour une réponse sous une question
    public function hasVoted(User $user, Question $question)
    {
        $results = $this->createQueryBuilder(('reponse')) //SELECT * FROM
            ->innerJoin('reponse.voters', 'voter') //INNER JOIN user_reponse ON user_reopnse.id = reponse.id
            ->where('voter.id = :user') //WHERE user_reponse.user_id = ?
            ->andWhere('reponse.Question = :question ') //ANDreponse.question.id = ?
            ->setParameter('user', $user)
            ->setParameter('question', $question)
            ->getQuery() //Execute
            ->getResult() //Retourne tous les résultats trouvés
        ;

        Return count($results) > 0;
    }
//    /**
//     * @return Reponse[] Returns an array of Reponse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reponse
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
