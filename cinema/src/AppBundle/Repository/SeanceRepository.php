<?php

namespace AppBundle\Repository;
use DateTime;

class SeanceRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByCinemaAndDate($cinemaName, $date)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from('AppBundle:Seance', 's')
            ->innerJoin('s.hall','h')
            ->where('s.date = :date')
            ->andWhere('h.cinemaName = :cinemaName')
            ->setParameter('date', $date)
            ->setParameter('cinemaName', $cinemaName)
            ->getQuery()
            ->getResult();
    }
}
