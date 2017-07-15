<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seance;
use AppBundle\Entity\Ticket;
use Doctrine\DBAL\Types\DateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class BookingController extends Controller
{
    const STUDENT_DISCOUNT = 30;
    /**
     * @Route("/booking/{seanceId}", name="booking")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bookingAction(Request $request, $seanceId)
    {
        $em = $this->getDoctrine()->getManager();
        $seance = $em->getRepository('AppBundle:Seance')->find($seanceId);
        $user = $this->getUser();
        if ($user) {
            $userCoins = $user->getCoins();
        } else {
            $userCoins = 0;
        }
        if ($request->getMethod() == 'POST') {
            $data = json_decode($request->getContent(), true)['data'];
            $dataInfo = json_decode($request->getContent(), true)['dataInfo'];
            if (!$this->ticketsAreAvailable($seance, $data)) {
                return $this->render('site/booking_result.html.twig', [
                    'message' => 'Booking has failed. Tickets are not available.'
                ]);
            }
            $totalCoins = $this->calculateCoins($dataInfo);
            if ($totalCoins > $userCoins) {
                return $this->render('site/booking_result.html.twig', [
                    'message' => 'Booking has failed. Not enough coins.'
                ]);
            }
            $bookingNumber = $this->generateBookingNumber();
            while ($em->getRepository('AppBundle:Ticket')->findBy(['bookingNumber' => $bookingNumber])) {
                $bookingNumber = $this->generateBookingNumber();
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = json_decode($data[$i], true);
                $dataInfo[$i] = json_decode($dataInfo[$i], true);
                $ticket = new Ticket();
                $ticket->setSeance($seance);
                $ticket->setRow($data[$i]['row']);
                $ticket->setSeat($data[$i]['seat']);
                $ticket->setPrice($dataInfo[$i]['price']);
                if ($dataInfo[$i]['type'] == Ticket::TYPE_STUDENT) {
                    $ticket->setTicketType(Ticket::TYPE_STUDENT);
                }
                if ($dataInfo[$i]['coins']) {
                    $ticket->setTicketType(Ticket::TYPE_COINS);
                }
                $ticket->setBookingNumber($bookingNumber);
                $em->persist($ticket);
            }
            if ($user) {
                $user->setCoins($userCoins - $totalCoins);
            }
            $em->flush();
            $tickets = $em->getRepository('AppBundle:Ticket')->findBy(['bookingNumber' => $bookingNumber]);
            return $this->render('site/booking_result.html.twig', [
                    'message' => 'success',
                    'bookingNumber' => $bookingNumber,
                    'seance' => $seance,
                    'tickets' => $tickets
                ]);
        }
        $date = new \DateTime($seance->getDate());
        if ($date->diff(new \DateTime($seance->getMovie()->getPremiereDate()))->d <=7) {
            $premiere = "true";
        } else {
            $premiere = "false";
        }
        $ticketsInfo = [];
        $rows = $seance->getHall()->getRows();
        $seats = $seance->getHall()->getSeats();
        for ($i = 1; $i <= $rows; $i++) {
            for ($j = 1; $j <= $seats; $j++) {
                if ($em->getRepository('AppBundle:Ticket')->findBy([
                    'seance' => $seance,
                    'row' => $i,
                    'seat' => $j
                ])) {
                    $ticketsInfo[$i][$j] = "booked";
                } else {
                    $ticketsInfo[$i][$j] = "free";
                }
            }
        }
        return $this->render('site/booking.html.twig', [
            'rows' => $seance->getHall()->getRows(),
            'seats' => $seance->getHall()->getSeats(),
            'seance' => $seance,
            'coins' => $userCoins,
            'ticketsState' => $ticketsInfo,
            'premiere' => $premiere]
        );
    }

    /**
     * @param $seance Seance
     * @param $data array
     * @return bool
     */
    function ticketsAreAvailable($seance, $data){
        if (count($data) == 0) {
            return false;
        }
        $rep = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Ticket');
        foreach ($data as $info) {
            $info = json_decode($info, true);
            $ticket = $rep->findBy(['seance' => $seance, 'row' => $info['row'], 'seat' =>  $info['seat']]);
            if ($ticket) {
                return false;
            }
        }
        return true;
    }

    function calculateCoins($info) {
        $totalCoins = 0;
        foreach ($info as $item) {
            $item = json_decode($item, true);
            if ($item['coins']) {
                $totalCoins += $item['price'];
            }
        }
        return $totalCoins;
    }

    function generateTicketCode($length = 12) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ceil($length/strlen($x)) )),1,$length);
    }
    function generateBookingNumber($length = 8) {
        return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
    }
}