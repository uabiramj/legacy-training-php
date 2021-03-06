<?php

namespace App\Controller;

use App\Model\User;
use App\Repository\DoctrineUserRepository;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRegistrationController
{
    /** @var DoctrineUserRepository */
    public static $orm;

    public function index(Request $request)
    {
        if (strlen($request->get('password')) <= 8 || strpos($request->get('password'), '_') === false) {
            return new Response('Password is not valid', Response::HTTP_BAD_REQUEST);
        }
        if ($this->orm()->findByEmail($request->get('email')) !== null) {
            return new Response("The email is already in use", Response::HTTP_BAD_REQUEST);
        }

        $user = new User(rand(0, 10000), $request->get('name'), $request->get('email'), $request->get('password'));
        $this->orm()->save($user);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp1.example.com;smtp2.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'user@example.com';
            $mail->Password = 'secret';

            $mail->setFrom("noreply@codium.team", 'Codium Team');
            $mail->addAddress($request->get('email'), $request->get('name'));

            $mail->isHTML(true);
            $mail->Subject = "Welcome to Codium";
            $mail->Body = 'This is the HTML message body <b>in bold!</b>';
//        $mail->send();
        } catch (Exception $e) {
        }

        $response = ['email' => $request->get('email'), 'name' => $request->get('name')];
        return new JsonResponse($response, Response::HTTP_CREATED);
    }

    private function orm(): DoctrineUserRepository
    {
        if (self::$orm == null) {
            self::$orm = new DoctrineUserRepository();
        }
        return self::$orm;
    }
}