<?php

namespace App\Controller;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\User;
use App\Event\UsersEvent;
use App\Form\UserType;

//use App\Service\fileUploader;
use App\Service\FileUploader;
use App\Service\HeaderAuthGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Event\EmailEvent;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class UserController
 *
 * @package App\Controller
 * @Route("/api", name="api_users_")
 */
class UserController extends MainController
{

    /**
     * @Route("/users", name="get_all_users", methods={"GET"})
     */
    public function shoUsers(): Response
    {
        $lists = [];
        $datas = $this->em->getRepository(User::class)->findAll();
        foreach ($datas as $data) {
            $lists[] = ["id" => $data->getId(), "email" => $data->getEmail(),
                "username" => $data->getUsername(), "firstname" => $data->getFirstname(),
                "lastname" => $data->getLastname(), "image" => $data->getImage(),
                "active" => $data->getActive(), "roles" => $data->getRoles()];
        }
        return $this->successResponse($lists);
    }

    /**
     * @Route("/users/{id}", name="get_users", methods={"GET"})
     */
    public function show($id)
    {
        $lists = [];
        $user = $this->em->getRepository(User::class)->findBy(["id"=>$id]);
        try{
        if (isset($user)) {
            foreach ($user as $data) {
                $lists [] = ["id" => $data->getId(),
                    "email" => $data->getEmail(),
                    "username" => $data->getUsername(), "firstname" => $data->getFirstname(),
                    "lastname" => $data->getLastname(),
                    "image" => $data->getImage(), "active" => $data->getActive(),
                    "roles" => $data->getRoles()];
            }
            return $this->successResponse($lists);
        }
        } catch (NotEncodableValueException $e) {
            return $this->successResponse(["code" => 409, "message" => $e->getMessage()], 409);
        }
    }

    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function create(Request $request)
    {
        $data = $this->jsonDecode($request);
        $user = new User();
        try {
            $this->insert($request, UserType::class, $user, $data);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
            $this->em->persist($user);
            $this->em->flush();
            return $this->successResponse($data);
        } catch (NotEncodableValueException $e) {
            return $this->successResponse(["code" => 409, "message" => $e->getMessage()], 409);
        }
    }

    /**
     * @Route("/users/{id}", name="edit_user", methods={"POST","PUT"})
     */
    public function edit(Request $request, $id, HeaderAuthGenerator $headerAuthGenerator, FileUploader $fileUploader, HttpClientInterface $client)
    {
        $data = $this->jsonDecode($request);
        $user = $this->em->getRepository(User::class)->find($id);
        $email = $request->request->get('email');
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        try {
            $this->update($request, UserType::class, $user, $data);
            $file = $fileUploader->upload($request);
            if ($file != null) {
                $user->setImage($file['image']);
            }
                $user->setRoles(array('ROLE_ADMIN','ROLE_USER'));
            if (isset($email)) {
                $user->setEmail($email);
            }
            if (isset($firstName)) {
                $user->setFirstName($firstName);
            }
            if (isset($lastName)) {
                $user->setLastName($lastName);
            }
            $this->em->persist($user);
            $this->em->flush();
            return $this->successResponse(["code" => 200, "message" => 'user successfully edited']);
        } catch (NotEncodableValueException $e) {
            return $this->successResponse(["code" => 409, "message" => $e->getMessage()], 409);
        }
    }


    /**
     * @Route("/users/{id}", name="delete_user", methods={"DELETE"})
     */
    public function delete($id, HeaderAuthGenerator $headerAuthGenerator, HttpClientInterface $client)
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (isset($user)) {
            $this->em->remove($user);
            $this->em->flush();
            return $this->successResponse(["code" => 200, "message" => 'user successfully deleted'], 200);
        }
    }


    /**
     * @Route("/updatePassword/{id}", name="edit_passwrd_user", methods={"POST"})
     */
    public function changeUserPassword(Request $request, UserPasswordHasherInterface $passwordEncoder, $id)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $id]);
        $data = $this->jsonDecode($request);
        $this->update($request, UserType::class, $user, $data);
        $checkPass = $passwordEncoder->isPasswordValid($user, $data['oldPassword']);
        if ($checkPass === true) {
            $user->setPassword($passwordEncoder->hashPassword($user, $data['newPassword']));
            $this->em->persist($user);
            $this->em->flush();
            return $this->successResponse(["message" => 'password successfully edit'], 200);
        } else {
            return $this->successResponse(['message' => 'The current password is incorrect.'], 409);
        }
    }

}
