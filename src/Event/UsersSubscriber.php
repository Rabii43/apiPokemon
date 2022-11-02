<?php

namespace App\Event;

use App\Event\UsersEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\HeaderAuthGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UsersSubscriber implements EventSubscriberInterface
{
    /**
     * @var HeaderAuthGenerator
     */
    private $headerAuthGenerator;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param HeaderAuthGenerator $headerAuthGenerator
     */
    public function __construct(HeaderAuthGenerator $headerAuthGenerator, HttpClientInterface $client,EntityManagerInterface $entityManager)
    {
        $this->headerAuthGenerator = $headerAuthGenerator;
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            UsersEvent::USER_FICO_CREATED => 'onCreateBankerInFicoDev',
        ];
    }

    //
    public function onCreateBankerInFicoDev(UsersEvent $event)
    {
        // get the header from a service
        $headres = $this->headerAuthGenerator->getXWSSEHeader();
        $user = $event->getUser();
        $endPoint = $event->getParam();
        // Register the user in the "courtagecredit"
        $request = $this->client->request('POST', $endPoint, [
            'headers' => $headres,
            'body' => [
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "firstName" => $user->getFirstName(),
                "lastName" => $user->getLastName(),
                "roles" => [16], // role Banker
                "enabled" => true
            ]
        ]);

        $response = json_decode($request->getContent(), true);
    }


}
