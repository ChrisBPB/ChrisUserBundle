<?php

namespace Chris\ChrisUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Date;

class SecurityController extends AbstractController
{

    private $user_class;

    public function __construct($user_class)
    {
        $this->user_class = $user_class;
    }

    /**
     * @Route("/login", name="chrisuser_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
         //   return $this->redirectToRoute('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@ChrisUser/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/login/forgotten-password", name="chrisuser_forgotten_password")
     */
    public function forgottenPassword(\Swift_Mailer $mailer, Request $request): Response
    {
        //EMAIL
        //generate a change code + a timestamp (2hours)

        $email = $request->request->get('email', null);

        if($email != null) {
            $qb = $this->container->get('doctrine')->getManager()->createQueryBuilder();
            $qb->select('u');
            $qb->from($this->user_class, 'u');
            $qb->where('u.email=:email');
            $qb->setMaxResults(1);
            $qb->setParameter(':email', trim($email));
            $result = $qb->getQuery()->getSingleResult();

            if ($result != null) {
                $em = $this->container->get('doctrine')->getManager();

                $code = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
                $expire = new \DateTime("+2 hour");

                $result->setPasswordToken($code);
                $result->setPasswordTokenExpire($expire);
                $em->persist($result);
                $em->flush();


                $this->addFlash(
                    'success', $this->get('translator')->trans('alerts.emailSent', [], 'ChrisUserBundle')
                );

                $message = (new \Swift_Message($this->get('translator')->trans('emails.changePasswordTitle', [], 'ChrisUserBundle')))
                    ->setFrom("sponsor@powerbot.org")
                    ->setTo(trim($result->getEmail()))
                    ->setBody(
                        $this->renderView(
                            '@ChrisUser/emails/reset_password.html.twig',
                            ['code' => $result->getPasswordToken(),
                                'email' => $result->getEmail()
                            ]
                        ),
                        'text/html'
                    );

                $mailer->send($message);

                return $this->redirectToRoute('index');

            }
        }



        return $this->render('@ChrisUser/security/forgotten_password.html.twig', []);
    }

    /**
     * @Route("/login/reset-password/{code}/{email}", name="chrisuser_reset_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $code, string $email){
        $code = trim($code);
        $email = trim($email);

        $qb = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qb->select('u');
        $qb->from($this->user_class, 'u');
        $qb->where('u.email=:email AND u.passwordToken=:code');
        $qb->setMaxResults(1);
        $qb->setParameter(':email', $email);
        $qb->setParameter(':code', $code);
        $result = $qb->getQuery()->getSingleResult();

        if($result!=null){

            if($result->getPasswordTokenExpire() > new \DateTime()){
                $newPassword = $request->request->get('newpassword', null);

                if($newPassword != null){
                    $newPassword = $passwordEncoder->encodePassword(
                        $result,
                        $newPassword
                    );

                    $em = $this->container->get('doctrine')->getManager();
                    $result->setPassword($newPassword);
                    $result->setPasswordToken("");
                    $result->setPasswordTokenExpire(new \DateTime("-1 hour")); //immediately invalidate
                    $em->persist($result);
                    $em->flush();

                    $this->addFlash(
                        'success', $this->get('translator')->trans('alerts.passwordChanged', [], 'ChrisUserBundle')
                    );

                    return $this->redirectToRoute('index');

                } else {
                    $result = null; //error
                }

            } else {
                $result = null; //error

                $this->addFlash(
                    'danger', $this->get('translator')->trans('alerts.passwordTokenExpired', [], 'ChrisUserBundle')
                );
            }
        }

        return $this->render('@ChrisUser/security/reset_password.html.twig', [
            "error"=>$result==null
        ]);

    }

    /**
     * @Route("/logout", name="chrisuser_logout")
     */
    public function logout()
    {

    }
}
