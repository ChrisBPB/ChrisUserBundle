<?php

namespace Chris\ChrisUserBundle\Controller;

use Chris\ChrisUserBundle\Entity\User;
use Chris\ChrisUserBundle\Form\RegistrationFormType;
use Chris\ChrisUserBundle\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="chrisuser_register")
     */
    public function register(TranslatorInterface $translator, Request $request, AuthorizationCheckerInterface $auth, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, \Swift_Mailer $mailer): Response
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

            $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );

            if($auth->isGranted("ROLE_USER")){
                $this->addFlash(
                    'danger', $translator->trans('alerts.registrationSuccess', [], 'ChrisUserBundle')
                );
                return $this->redirectToRoute('chrisuser_email_pending');
            } else {
                $this->addFlash(
                    'danger', $translator->trans('alerts.registrationError', [], 'ChrisUserBundle')
                );
                return $this->redirectToRoute('chrisuser_logout');
            }
        }

        return $this->render('@ChrisUser/registration/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/email-pending", name="chrisuser_email_pending")
     */
    public function emailPending(TranslatorInterface $translator){
        if($this->getUser()->getEmailValidated()){
            $this->addFlash(
                'danger', $translator->trans('alerts.emailValidated', [], 'ChrisUserBundle')
            );
            return $this->redirectToRoute('index');
        } else {
            return $this->render('@ChrisUser/registration/email_pending.html.twig', [
            ]);
        }
    }

    /**
     * @Route("/register/email-validate/{code}/{email}", name="chrisuser_email_validate")
     */
    public function validateEmail(string $code, string $email){
        $code = trim($code);
        $email = trim($email);

        $qb = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qb->select('u');
        $qb->from('ChrisUserBundle:User', 'u');
        $qb->where('u.email=:email');
        $qb->setMaxResults(1);
        $qb->setParameter(':email', $email);
        $user = $qb->getQuery()->getSingleResult();
        $result = 1;

        if(!$user->getEmailValidated()){
            if($user->getEmailValidationCode()==$code){
                $em = $this->container->get('doctrine')->getManager();
                $user->setEmailValidated(true);
                $user->setEmailValidationCode("");
                $user->removeRole("ROLE_PENDING");
                $em->persist($user);
                $em->flush();
            } else {
                $result = 0;
            }
        }

        return $this->render('@ChrisUser/registration/email_validated.html.twig', [
            "success"=>$result>0
        ]);

    }
}
