<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\Email;
use Symfony\Contracts\EventDispatcher\Event;
use App\Service\HeaderAuthGenerator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The user activated event is dispatched each time user activated
 * in the system.
 */
class UsersEvent extends Event
{
    //
    public const USER_FICO_CREATED = 'user.fico.created';

    protected $user;

    protected $param;


    public function __construct(User $user,  $param)
    {
        $this->user = $user;
        $this->param = $param;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getParam( $param = null)
    {
        return $this->param;
    }
}