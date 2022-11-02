<?php

namespace App\Event\Listener;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct( TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Replaces the data in the generated
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var User $user  */
        $user = $event->getUser();
        // add new data
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getFirstname().' '.$user->getLastname();
        $payload['email'] = $user->getEmail();
        $payload['roles'] = $user->getRoles();
        $payload['isActive'] = $user->getActive();
        $event->setData($payload);
    }
}