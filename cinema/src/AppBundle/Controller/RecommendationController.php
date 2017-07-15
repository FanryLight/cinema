<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Encoding;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RecommendationController extends Controller
{
    private $activeMovies;

    private $averageRatings;

    /**
     * @Route("/send_recommendations", name="send_recommendations")
     */
    public function sendRecommendation()
    {
        $this->findActiveMovies();
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();
        $this->findAverageRatings($users);
        foreach ($users as $user) {
            $movies = $this->getRecommendations($user);
            $message = \Swift_Message::newInstance()
                ->setSubject('Movies you might like!')
                ->setFrom(['starcinema.uk@gmail.com' => 'Star Cinema'])
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/recommendations.html.twig',
                        array('movies' => $movies, 'username' => $user->getUsername())
                    ),
                    'text/html'
                );
            $message->setEncoder(Swift_Encoding::getBase64Encoding());
            $this->get('mailer')->send($message);
        }
        return $this->redirectToRoute('dispatch');
    }

    private function findAverageRatings($users)
    {
        $average = array();
        $rep = $this->getDoctrine()
            ->getRepository('AppBundle:Comment');
        foreach ($users as $user) {
            $sum = 0;
            $comments = $rep->findUserReviews($user);
            foreach ($comments as $comment) {
                $sum += $comment->getRating();
            }
            if ($sum) {
                $average[$user->getId()] = $sum / count($comments);
            } else {
                $average[$user->getId()] = 0;
            }
        }
        $this->setAverageRatings($average);
    }

    private function findActiveMovies()
    {
        $date = new \DateTime(\date("Y-m-d"));
        $movies = $this->getDoctrine()->getRepository("AppBundle:Movie")->findAll();
        $activeMovies = array();
        foreach ($movies as $movie) {
            if ($date->diff(new \DateTime($movie->getPremiereDate()))->m === 0) {
                $activeMovies[] = $movie;
            }
        }
        $this->setActiveMovies($activeMovies);
    }

    public function getRecommendations($user)
    {
        $userReviews = $this->getDoctrine()
            ->getRepository("AppBundle:Comment")
            ->findUserReviews($user);
        $moviesToRec = $this->findMoviesToRec($userReviews);
        if (count($moviesToRec) < 3) {
            return $moviesToRec;
        }
        if (count($userReviews) < 3) {
            return $this->getDefaultRecoms($moviesToRec);
        }
        $predictions = $this->predictRating($user, $moviesToRec);
        arsort($predictions);
        $predictions = array_slice($predictions, 0, 2, true);
        $movies = [];
        foreach (array_keys($predictions) as $movieId) {
            $movies[] = $this->getDoctrine()->getRepository('AppBundle:Movie')->find($movieId);
        }
        return $movies;
    }

    private function predictRating($user, $moviesToRec)
    {
        $prediction = [];
        $average = $this->getAverageRatings();
        $rep = $this->getDoctrine()->getRepository('AppBundle:Comment');
        foreach ($moviesToRec as $movie) {
            $objects = $rep->findUsersReviewedMovie($movie);
            $numerator = 0;
            $denominator = 0;
            foreach ($objects as $object) {
                $similarity = $this->calculateSimilarity($user, $object['user']);
                $denominator += $similarity;
                $numerator += $similarity * ($object['rating'] - $average[$object['user']->getId()]);
            }
            if ($denominator) {
                $prediction[$movie->getId()] = $average[$user->getId()] + $numerator/$denominator;
            } else {
                $prediction[$movie->getId()] = 0;
            }
        }
        return $prediction;
    }

    private function calculateSimilarity($user, $object)
    {
        $userReviews = $this->getDoctrine()
            ->getRepository("AppBundle:Comment")
            ->findUserReviews($user);
        $reviews = $this->getDoctrine()
            ->getRepository("AppBundle:Comment")
            ->findUserReviews($object);
        $similar = 0;
        $count = 0;
        foreach ($userReviews as $ur) {
            foreach ($reviews as $r) {
                if ($ur->getMovie() == $r->getMovie()) {
                    $similar += 1 - abs($ur->getRating() - $r->getRating()) * 0.2;
                    $count++;
                }
            }
        }

        if ($count == 0) {
            return 0;
        }
        return $count/count($userReviews) + $similar/$count;
    }

    private function findMoviesToRec($userReviews)
    {
        $movies = [];
        $reviewedMovies = [];
        foreach ($userReviews as $ur) {
            $reviewedMovies[] = $ur->getMovie();
        }
        $active = $this->getActiveMovies();
        foreach ($active as $am) {
            if (!in_array($am, $reviewedMovies)) {
                $movies[] = $am;
            }
        }
        array_unique($movies);
        return $movies;
    }

    private function getDefaultRecoms($moviesToRec)
    {
        $rating = [];
        foreach ($moviesToRec as $movie) {
            $rating[$movie->getId()] = $this->getDoctrine()
                ->getRepository('AppBundle:Comment')
                ->findAverageRating($movie);
        }
        arsort($rating);
        $rating = array_slice($rating, 0, 2, true);
        $movies = [];
        foreach (array_keys($rating) as $movieId) {
            $movies[] = $this->getDoctrine()
                ->getRepository('AppBundle:Movie')->find($movieId);
        }

        return $movies;
    }

    /**
     * @param array $activeMovies
     */
    private function setActiveMovies($activeMovies)
    {
        $this->activeMovies = $activeMovies;
    }

    /**
     * @return array
     */
    private function getActiveMovies()
    {
        return $this->activeMovies;
    }

    /**
     * @param array $averageRatings
     */
    private function setAverageRatings($averageRatings)
    {
        $this->averageRatings = $averageRatings;
    }

    /**
     * @return array
     */
    private function getAverageRatings()
    {
        return $this->averageRatings;
    }
}
