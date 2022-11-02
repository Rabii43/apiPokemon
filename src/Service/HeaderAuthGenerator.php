<?php

namespace App\Service;

use App\Repository\ConfigKeysRepository;
/**
 * Class HeaderAuthGenerator
 * 
 * Prepare the header 'Authorization' and 'X-WSSE' to connect to the "courtagecredit"
 */

class HeaderAuthGenerator
{
    public $repository;

    public function __construct(ConfigKeysRepository $repository)
    {
     $this->repository = $repository;
    }

    public function getXWSSEHeader()
    {
        $config = $this->repository->findOneBy(['name' => 'auth_api_ficodev']); // get keys from DB
        $userName = $config->getUserName();
        $userApiKey = $config->getUserKey();
        $nonce = base64_encode(substr(md5(uniqid()), 0, 16));
        $created  = date('c');
        $digest   = base64_encode(sha1(base64_decode($nonce) . $created . $userApiKey, true));

        $wsseHeader = sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $userName,
            $digest,
            $nonce,
            $created
        );

        $data = [
            "Authorization"=> "Authorization: WSSE profile=\"UsernameToken\"",
            "X-WSSE" => $wsseHeader
        ];

        return $data;
    }
}