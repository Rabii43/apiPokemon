<?php

namespace App\Controller;


use App\Entity\User;
use App\Event\EmailEvent;
use App\Form\UserType;
use App\Service\HeaderAuthGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class  AuthController extends MainController
{
    /**
     * @Route("/register", name="api_register",methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $data = $this->jsonDecode($request);
        // Get User by email
        $checkUser = $this->userRepository->findOneBy(['email' => $data['email']]);
        try {
            // if user is already exists
            if ($checkUser) {
                return $this->successResponse(["code" => 403, "message" => 'Email already exists!']);
            }
            // Create new user
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
                return $this->successResponse(["code" => 409, "message" => 'Invalid Username or Password or Email']);
            }
            $form->handleRequest($request);
            $form->submit($data);
            $hash = $encoder->encodePassword($user, $data['password']);
            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime());
            $user->setUpdatedAt(new \DateTime());
            $user->setRoles(array('ROLE_USER'));
            // Save the user
            $entityManager = $this->em;
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->successResponse(["code" => 200, "message" => $user], 200);
        } catch (NotEncodableValueException $e) {
            return $this->successResponse(["code" => 409, "message" => $e->getMessage()], 409);
        }
    }

    /**
     * @param UserInterface $user
     * @param JWTTokenManagerInterface $JWTManager
     * @return JsonResponse
     *
     * @Route("/login", name="api_login")
     */
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager)
    {
        if ($user->getActive() == false) {
            // the message passed to this exception is meant to be displayed to the user
            return new JsonResponse(['status' => $this->successResponse(), 'message' => "account blocked"]);
        }
        return new JsonResponse(['status' => $this->successResponse(), 'token' => $JWTManager->create($user)]);
    }


    /**
     * @Route ("/api/activateAccount",name="activate_account",methods={"POST"})
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function activateAccount(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (isset($user)) {
            $password = $data['password'];
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setActive(false);
            $this->em->persist($user);
            $this->em->flush();
        }
        return $this->successResponse('user activated successfully');
    }

    /**
     * @Route("/reset/reset-password", name="app_forgotten_password",methods={"POST"})
     * @param Request $request
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function resetPassword(Request $request, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $data = json_decode($request->getContent(), true);
        // On cherche un utilisateur ayant cet e-mail
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        // Si l'utilisateur n'existe pas
        if ($user === null) {
            return $this->successResponse(["code" => 409, "message" => 'Invalid Email'], 409);
        } else {
            $token = $this->randomPassword(20);
            $user->setResetToken($token);
            $this->em->persist($user);
            $this->em->flush();
            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->getParameter('app_cc_reset_password_endpoint') . '/reset/password/' . $token;
            $messages = (new Email())
                ->from('no-reply@fidoc.com')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié')
                ->html("Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site
Fidoc.com. Veuillez cliquer sur le lien suivant : " . $url, 'text/html');
            $mailer->send($messages);
            return $this->successResponse(["code" => 200, "message" => 'mail successfully send']);
        }
    }

    /**
     * @Route("/reset/edit_password/{token}", name="app_reset_password")
     * @param Request $request
     * @param string $token
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    public function editPassword(Request $request, string $token, UserPasswordHasherInterface $passwordHasher)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->em->getRepository(User::class)->findOneBy(['reset_token' => $token]);
        $data = json_decode($request->getContent(), true);
        // Si l'utilisateur n'existe pas
        if ($user === null) {
            return $this->successResponse(["code" => 409, "message" => 'Invalid token'], 409);
        }
        if (null !== $data) {
            // On supprime le token
            $user->setResetToken(null);
            // On chiffre le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $this->em->persist($user);
            $this->em->flush();
            return $this->successResponse(["code" => 200, "message" => 'password successfully edit']);
        }
    }
}
