<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\HeaderAuthGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack,HttpClientInterface $client,HeaderAuthGenerator $headerAuthGenerator)
    {
        $this->requestStack = $requestStack;
        $this->client = $client;
        $this->headerAuthGenerator = $headerAuthGenerator;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var $user User */
        $user = $event->getUser();
        if(!$user)
            throw new AccessDeniedException('You need to activate your account');
        $roles =$user->getRoles();
            // add new data
            $payload['id'] = $user->getId();
            $payload['username'] = $user->getUsername();
            $payload['email'] = $user->getEmail();
            $payload['roles'] = $user->getRoles();
            $payload['active'] = $user->getActive();
            
        $event->setData($payload);
    }
}