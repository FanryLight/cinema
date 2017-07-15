<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use AppBundle\Entity\News;
use AppBundle\Entity\Seance;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use Doctrine\DBAL\Types\DateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Encoding;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction()
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Ticket');
        $all = count($rep->findAll());
        $ordinary = count($rep->findBy(['ticketType' => Ticket::TYPE_DEFAULT]))*100/$all;
        $student = count($rep->findBy(['ticketType' => Ticket::TYPE_STUDENT]))*100/$all;
        $coins = count($rep->findBy(['ticketType' => Ticket::TYPE_COINS]))*100/$all;
        $booking1 = count($rep->findTicketsForMonth(0));
        $booking2 = count($rep->findTicketsForMonth(1));
        $booking3 = count($rep->findTicketsForMonth(2));
        $month1 = \date('Y-m');
        $month2 = date_create("last day of -1 month")->format('Y-m');
        $month3 = date_create("last day of -2 month")->format('Y-m');
        $rep = $this->getDoctrine()->getRepository('AppBundle:Comment');
        $star1 = count($rep->findBy(['rating' => 1]));
        $star2 = count($rep->findBy(['rating' => 2]));
        $star3 = count($rep->findBy(['rating' => 3]));
        $star4 = count($rep->findBy(['rating' => 4]));
        $star5 = count($rep->findBy(['rating' => 5]));
        $comments1 = count($rep->findCommentsForMonth(0));
        $comments2 = count($rep->findCommentsForMonth(1));
        $comments3 = count($rep->findCommentsForMonth(2));
        return $this->render('admin/index.html.twig', [
            'ticketTypes' => json_encode(
                [['Ordinary', $ordinary], ['Student', $student], ['Payed with coins', $coins]]
            ),
            'movieRatings' => json_encode(
                [['5 Stars', $star5], ['4 Stars', $star4],['3 Stars', $star3],['2 Stars', $star2],['1 Star', $star1]]
            ),
            'threeMonth' => json_encode(
                [[$month3, $booking3, $comments3], [$month2, $booking2, $comments2], [$month1, $booking1, $comments1]]
            )
        ]);
    }

    /**
     * @Route("/admin/dispatch", name="dispatch")
     */
    public function dispatchAction()
    {
        return $this->render('admin/dispatch.html.twig');
    }

    /**
     * @Route("/admin/messages", name="messages")
     */
    public function messagesAction()
    {
        $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findAll();
        return $this->render('admin/messages.html.twig', ['messages' => $messages]);
    }

    /**
     * @Route("/admin/answer", name="answer")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function answerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $messageId = $request->get('messageId');
        $answer = $request->get('answer');
        $message = $em->getRepository('AppBundle:Message')->find($messageId);
        $message->setAnswer($answer);
        $message->setIsRead(true);
        $em->flush();
        $mail = \Swift_Message::newInstance()
            ->setSubject('The reply to your contact message')
            ->setFrom(['starcinema.uk@gmail.com' => 'Star Cinema'])
            ->setTo($message->getUser()->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/answer.html.twig',
                    array('message' => $message)
                ),
                'text/html'
            );
        $mail->setEncoder(Swift_Encoding::getBase64Encoding());
        $this->get('mailer')->send($mail);
        return $this->redirectToRoute('messages');
    }

    /**
     * @Route("/admin/delete_message/{messageId}", name="delete_message")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMessageAction($messageId)
    {
        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($messageId);
        $this->getDoctrine()->getManager()->remove($message);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('messages');
    }

    /**
     * @Route("/admin/add_movie", name="add_movie")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addMovieAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $movie = new Movie();
            $movie->setName($request->get("name"));
            $movie->setOriginalName($request->get("original_name"));
            $movie->setGenres($request->get("genres"));
            $movie->setDirector($request->get("director"));
            $movie->setStarring($request->get("starring"));
            $movie->setCountry($request->get("country"));
            $movie->setDescription($request->get("description"));
            $movie->setPicture($request->get("picture"));
            $movie->setTrailer($request->get("trailer"));
            $movie->setPremiereDate($request->get("date"));

            $this->getDoctrine()->getManager()->persist($movie);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('movies');
        }
        $movie = new Movie();
        return $this->render('admin/movie.html.twig', ['action' => '/admin/add_movie','button' => 'Add', 'movie' => $movie]);
    }

    /**
     * @Route("/admin/add_seance", name="add_seance")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addSeanceAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $hall = $this->getDoctrine()->getRepository("AppBundle:Hall")->find($request->get('hall'));
            $movie = $this->getDoctrine()->getRepository("AppBundle:Movie")->find($request->get('movie'));
            $time = $request->get('time');
            $price = $request->get('price');
            $date = new \DateTime($request->get('date'));
            for ($i = 0; $i < 7; $i++) {
                $seance = new Seance();
                $seance->setHall($hall);
                $seance->setMovie($movie);
                $seance->setTime($time);
                $seance->setPrice($price);
                $interval = $i == 0 ? "P0D": "P1D";
                $date->add(new \DateInterval($interval));
                $seance->setDate($date->format("Y-m-d"));
                $em = $this->getDoctrine()->getManager();
                $em->persist($seance);
                $em->flush();
            }
            return $this->redirectToRoute("seances");
        }
        $date = new \DateTime(\date("Y-m-d"));
        $movies = $this->getDoctrine()->getRepository("AppBundle:Movie")->findAll();
        $activeMovies = array();
        foreach ($movies as $movie) {
            if ($date->diff(new \DateTime($movie->getPremiereDate()))->m === 0) {
                $activeMovies[] = array("id" => $movie->getId(), "name" => $movie->getName());
            }
        }
        $halls = $this->getDoctrine()->getRepository("AppBundle:Hall")->findAll();
        $hallOptions = array();
        foreach ($halls as $hall) {
            $hallOptions[] = array("id" => $hall->getId(), "name" => $hall->getCinemaName()." - ".$hall->getHallName());
        }
        return $this->render('admin/seance.html.twig', array("movies" => $activeMovies, "halls" => $hallOptions));
    }

    /**
     * @Route("/admin/add_news", name="add_news")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addNewsAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $news = new News();
            $news->setDate(\date('Y-m-d'));
            $news->setTitle($request->get('title'));
            $news->setText($request->get('text'));
            $this->getDoctrine()->getManager()->persist($news);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("news");
        }
        $news = new News();
        return $this->render('admin/new.html.twig',['button' => 'Add', 'action' => '/admin/add_news', 'new' => $news]);
    }

    /**
     * @Route("/admin/movies", name="movies")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moviesAction()
    {
        $movies = $this->getDoctrine()->getRepository('AppBundle:Movie')->findAll();
        return $this->render('admin/movies.html.twig', ['movies' => $movies]);
    }

    /**
     * @Route("/admin/edit_movie/{movieId}", name="edit_movie")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editMovieAction(Request $request, $movieId)
    {
        $movie = $this->getDoctrine()->getRepository('AppBundle:Movie')->find($movieId);
        if ($request->getMethod() == "POST") {
            $movie->setName($request->get("name"));
            $movie->setOriginalName($request->get("original_name"));
            $movie->setGenres($request->get("genres"));
            $movie->setDirector($request->get("director"));
            $movie->setStarring($request->get("starring"));
            $movie->setCountry($request->get("country"));
            $movie->setDescription($request->get("description"));
            $movie->setPicture($request->get("picture"));
            $movie->setTrailer($request->get("trailer"));
            $movie->setPremiereDate($request->get("date"));

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('movies');
        }
        return $this->render('admin/movie.html.twig', ['action' => '/admin/edit_movie/'.$movieId,'button' => 'Save', 'movie' => $movie]);
    }

    /**
     * @Route("/admin/delete_movie/{movieId}", name="delete_movie")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMovieAction($movieId)
    {
        $movie = $this->getDoctrine()->getRepository('AppBundle:Movie')->find($movieId);
        $this->getDoctrine()->getManager()->remove($movie);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('movies');
    }

    /**
     * @Route("/admin/seances", name="seances")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function seancesAction()
    {
        $seances = $this->getDoctrine()->getRepository('AppBundle:Seance')->findAll();
        return $this->render('admin/seances.html.twig', ['seances' => $seances]);
    }

    /**
     * @Route("/admin/delete_seance/{seanceId}", name="delete_seance")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSeanceAction($seanceId)
    {
        $seance = $this->getDoctrine()->getRepository('AppBundle:Seance')->find($seanceId);
        $this->getDoctrine()->getManager()->remove($seance);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('seances');
    }

    /**
     * @Route("/admin/news", name="news")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newsAction()
    {
        $news = $this->getDoctrine()->getRepository('AppBundle:News')->findAll();
        return $this->render('admin/news.html.twig', ['news' => $news]);
    }

    /**
     * @Route("/admin/edit_news/{newId}", name="edit_news")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editNewsAction(Request $request, $newId)
    {
        $news = $this->getDoctrine()->getManager()->getRepository('AppBundle:News')->find($newId);
        if ($request->getMethod() == "POST") {
            $news->setTitle($request->get('title'));
            $news->setText($request->get('text'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("news");
        }
        return $this->render('admin/new.html.twig',['button' => 'Save', 'action' => '/admin/edit_news/'.$newId, 'new' => $news]);
    }

    /**
     * @Route("/admin/delete_news/{newsId}", name="delete_news")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteNewsAction($newsId)
    {
        $news = $this->getDoctrine()->getRepository('AppBundle:News')->find($newsId);
        $this->getDoctrine()->getManager()->remove($news);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('news');
    }

    /**
     * @Route("/admin/comments", name="comments")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function commentsAction()
    {
        $comments = $this->getDoctrine()->getManager()->getRepository('AppBundle:Comment')->findAll();
        return $this->render('admin/comments.html.twig',['comments' => $comments]);
    }

    /**
     * @Route("/admin/block_user/{userId}", name="block_user")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function blockUserAction($userId)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if ($user->getRole() != User::ROLE_SUPER_ADMIN) {
            $user->setEnabled(false);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirectToRoute('comments');
    }

    /**
     * @Route("/admin/delete_comment/{commentId}", name="delete_comment")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommentAction($commentId)
    {
        $comment = $this->getDoctrine()->getRepository('AppBundle:Comment')->find($commentId);
        $this->getDoctrine()->getManager()->remove($comment);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('comments');
    }

    /**
     * @Route("/admin/users", name="users")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction()
    {
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findAll();
        $roles = [];
        foreach ($users as $user) {
            if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
                $roles[$user->getId()] = "admin";
            } else if ($user->hasRole(User::ROLE_ADMIN)) {
                $roles[$user->getId()] = "moderator";
            } else {
                $roles[$user->getId()] = "user";
            }
        }
        return $this->render('admin/users.html.twig',['users' => $users, 'roles' => $roles]);
    }

    /**
     * @Route("/admin/change_role", name="change_role")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeRoleAction(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        $userId = $params["id"];
        $role = $params["role"];
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($userId);

        if ($role == "moderator") {
            $user->addRole(User::ROLE_ADMIN);
        } else if ($role == "user") {
            $user->removeRole(User::ROLE_ADMIN);
        } else if ($role == "admin") {
            $user->addRole(User::ROLE_SUPER_ADMIN);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('users');
    }


}
