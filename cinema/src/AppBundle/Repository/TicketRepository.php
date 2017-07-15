<?php

namespace AppBundle\Repository;
use DateTime;

class TicketRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTicketsForMonth($difference)
    {
        $now = new DateTime(\date('Y-m-d'));
        $tickets = $this->findAll();
        $array = array();
        foreach ($tickets as $ticket) {
            $date = $ticket->getSeance()->getDate();
            if (date_diff($now, new DateTime($date))->m == $difference) {
                $array[] = $ticket;
            }
        }
        return $array;
    }
}
