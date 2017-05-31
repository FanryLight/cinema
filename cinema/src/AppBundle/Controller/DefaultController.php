<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository('AppBundle:News')->findLastNews();
        $movies = $this->getDoctrine()->getRepository('AppBundle:Movie')->findLastMovies();
        return $this->render('site/index.html.twig', array('movies' => $movies, 'news' => $news));
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render('site/about.html.twig');
    }

    /**
     * @Route("/contact_us", name="contact_us")
     */
    public function contactUsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $text = $request->get('text');
            $message = new Message();
            $message->setText($text);
            $message->setUser($this->getUser());
            $message->setDate(\date('Y-m-d'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            return new JsonResponse(['targetUrl' => '/about']);
        }
        return $this->redirectToRoute('about');
    }
}
