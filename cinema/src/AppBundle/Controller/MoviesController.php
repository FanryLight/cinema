<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MoviesController extends Controller
{
    const COINS_AMOUNT = 25;

    /**
     * @Route("/movie/{movieName}", name="movie")
     * @param Request $request
     * @param $movieName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function movieAction(Request $request, $movieName)
    {
        $movie = $this->getDoctrine()
            ->getRepository('AppBundle:Movie')
            ->findOneBy(array('originalName' => $movieName));
        if (!$movie) {
            return $this->redirectToRoute('homepage');
        }
        $comments = $this->getDoctrine()
            ->getRepository('AppBundle:Comment')
            ->findBy(array('movie' => $movie));
        $count = 0;
        $sum = 0;
        foreach ($comments as $comment) {
            if ($comment->getRating() != 0) {
                $sum += $comment->getRating();
                $count++;
            }
        }
        if ($count == 0) {
            $rating = 0;
        } else {
            $rating = $sum / $count;
        }
        $rating = number_format($rating, 1);
            if ($text = $request->get('text')) {
                $message = "Wrong ticket code!";
            } else {
                $message = "";
            }
        return $this->render('site/movie.html.twig', array(
            'movie' => $movie,
            'comments' => $comments,
            'rating' => $rating,
            'text' => $text,
            'message' => $message
        ));
    }

    /**
     * @Route("/leave_comment/{movieId}", name="leave_comment", requirements={"movieId": "\d+"})
     * @param Request $request
     * @param $movieId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveCommentAction(Request $request, $movieId)
    {
        $movie = $this->getDoctrine()->getRepository('AppBundle:Movie')->find($movieId);
        if (!$movie) {
            return $this->redirectToRoute('homepage');
        }
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('homepage');
        }
        $text = $request->get('text');
        $rating = $request->get('rating');
        if (!$text) {
            return $this->redirectToRoute('homepage');
        }
        $ticketCode = $request->get('ticketCode');
        if ($ticketCode) {
            $ticket = $this->getDoctrine()
                ->getRepository('AppBundle:Ticket')
                ->findOneBy(['ticketCode' => $ticketCode]);
            if ($ticket && $ticket->getSeance()->getMovie() == $movie) {
                $user->setCoins($user->getCoins() + $this::COINS_AMOUNT);
                $ticket->setTicketCode(null);
                $this->getDoctrine()->getManager()->flush();
            } else {
                return $this->redirectToRoute('movie', array('request' => $request,
                    'movieName' => $movie->getOriginalName(),
                ), 307);
            }
        }
        $date = \date('Y-m-d');
        $comment = new Comment();
        $comment->setRating($rating);
        $comment->setDate($date);
        $comment->setMovie($movie);
        $comment->setUser($user);
        $comment->setText($text);
        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('movie', array('movieName' => $movie->getOriginalName()));
    }

    /**
     * @Route("/cinema/{cinemaName}/{date}", name="cinema_seances")
     * @param $cinemaName
     * @param $date
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cinemaSeancesAction($cinemaName, $date = NULL)
    {
        if (!$date) {
            $date = \date('Y-m-d');
        }
        $seances = $this->getDoctrine()
            ->getRepository('AppBundle:Seance')
            ->findByCinemaAndDate($cinemaName, $date);
        $seanceInfo = array();
        $nowTime = \date('H:i');
        $nowDate = \date('Y-m-d');
        foreach ($seances as $seance) {
            if ($date > $nowDate || ($date === $nowDate && $seance->getTime() >= $nowTime)) {
                $id = $seance->getMovie()->getId();
                $movie = $seance->getMovie();
                $movieSeances = isset($seanceInfo[$id]['seances']) ? $seanceInfo[$id]['seances']: array();
                $movieSeances[] = array('time' => $seance->getTime(), 'id' => $seance->getId());
                $seanceInfo[$id] = array(
                    'name' => $movie->getName(),
                    'originalName' => $movie->getOriginalName(),
                    'picture' => $movie->getPicture(),
                    'seances' => $movieSeances
                );
            }
        }
        $dates = $this->getNextSevenDates();
        return $this->render('site/cinema.html.twig', array(
            'seances' => $seanceInfo,
            'cinema' => $cinemaName,
            'dates' => $dates
        ));
    }

    private function getNextSevenDates()
    {
        $dates = array();
        $date = new \DateTime(\date('Y-m-d'));
        for ($i = 0; $i < 7; $i++) {
            $interval = $i == 0 ? "P0D": "P1D";
            $date->add(new \DateInterval($interval));
            $dates[] = array('url' => $date->format('Y-m-d'), 'label' => $date->format('j F'));
        }
        return $dates;
    }
}
