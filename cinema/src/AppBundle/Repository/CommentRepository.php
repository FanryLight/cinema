<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;
use DateTime;

class CommentRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUserReviews(User $user)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Comment', 'c')
            ->where('c.rating > 0')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findAverageRating($movie)
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('avg(c.rating) as rating')
            ->from('AppBundle:Comment', 'c')
            ->where('c.rating > 0')
            ->andWhere('c.movie = :movie')
            ->setParameter('movie', $movie)
            ->getQuery()
            ->getOneOrNullResult();
        return $result['rating'];
    }

    public function findCommentsForMonth($difference)
    {
        $now = new DateTime(\date('Y-m-d'));
        $comments = $this->findAll();
        $array = array();
        foreach ($comments as $comment) {
            $date = $comment->getDate();
            if (date_diff($now, new DateTime($date))->m == $difference) {
                $array[] = $comment;
            }
        }
        return $array;
    }

    public function findUsersReviewedMovie($movie)
    {
        $comments = $this->findBy(['movie' => $movie]);
        $objects = [];
        foreach ($comments as $comment) {
            if ($comment->getRating() > 0) {
                $objects[$comment->getUser()->getId()]['user'] = $comment->getUser();
                $objects[$comment->getUser()->getId()]['rating'] = $comment->getRating();
            }
        }
        return $objects;
    }
}
