<?php

namespace Chris\ChrisUserBundle\Controller;

use Chris\ChrisUserBundle\Entity\User;
use Chris\ChrisUserBundle\Form\RegistrationFormType;
use Chris\ChrisUserBundle\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="chrisuser_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = (new \Swift_Message('Register Validate'))
                ->setFrom("sponsor@powerbot.org")
                ->setTo(trim($user->getEmail()))
                ->setBody(
                    $this->renderView(
                        '@ChrisUser/emails/email_validate.html.twig',
                        ['code' => $user->getEmailValidationCode(),
                            'email'=>$user->getEmail()
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('@ChrisUser/registration/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/email-validate/{code}/{email}", name="chrisuser_email_validate")
     */
    public function validateEmail(string $code, string $email){
        $code = trim($code);
        $email = trim($email);

        $qb = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qb->update('ChrisUserBundle:User', 'u');
        $qb->set('u.emailValidated', 1);
        $qb->set('u.emailValidationCode', null);
        $qb->where('u.email = :email AND u.emailValidationCode=:code');
        $qb->setParameter(':email', $email);
        $qb->setParameter(':code', $code);
        $result = $qb->getQuery()->execute();

        if($result == 0){
            //failed, why? Possibly already validated
            $qb = $this->container->get('doctrine')->getManager()->createQueryBuilder();
            $qb->select('u.id');
            $qb->from('ChrisUserBundle:User', 'u');
            $qb->where('u.email=:email');
            $qb->setMaxResults(1);
            $qb->setParameter(':email', $email);
            $result = $qb->getQuery()->getSingleResult();
        }

        return $this->render('@ChrisUser/registration/email_validated.html.twig', [
            "success"=>$result>0
        ]);

    }
}
