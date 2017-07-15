<?php

namespace AppBundle\Listener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class FOSUserListener
 */
class RegistrationListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS     => [
                ['onRegistrationSuccess', -10],
            ],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->isValid()) {
            $event->setResponse(new JsonResponse(['success' => "true", 'targetUrl' => '/']));
            return;
        }
        $message = (string)$form->getErrors(true, true);
        $response = new JsonResponse(['success' => "false", 'message' => $message]);
        $event->setResponse($response);
    }
}