<?php


namespace Chris\ChrisUserBundle\Controller;

use Chris\ChrisUserBundle\Form\ChangeEmailFormType;
use Chris\ChrisUserBundle\Form\ChangePasswordFormType;
use Chris\ChrisUserBundle\Form\MarketingFormType;
use Chris\ChrisUserBundle\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserController extends AbstractController
{

    private $user_class, $email;

    public function __construct($user_class, $email)
    {
        $this->user_class = $user_class;
        $this->email = $email;
    }

    /**
     * @Route("/user", name="chrisuser_index")
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('@ChrisUser/user/view_profile.html.twig', [

        ]);
    }

    /**
     * @Route("/user/resend-email", name="chrisuser_resend_email")
     */
    public function resendEmail(\Swift_Mailer $mailer, Request $request){
        $user = $this->getUser();
        if(!$this->getUser()->getEmailValidated()) {
            $timer = $request->getSession()->get('emailTimer', 0);
            $time = time();
            if($time-$timer>300) {

                $request->getSession()->set('emailTimer', $time);

                $message = (new \Swift_Message($this->get('translator')->trans('emails.verifyEmailTitle', [], 'ChrisUserBundle')))
                    ->setFrom($this->email)
                    ->setTo(trim($user->getEmail()))
                    ->setBody(
                        $this->renderView(
                            '@ChrisUser/emails/email_validate.html.twig',
                            ['code' => $user->getEmailValidationCode(),
                                'email' => $user->getEmail()
                            ]
                        ),
                        'text/html'
                    );

                $mailer->send($message);
            } else {
                $this->addFlash(
                    'danger', $this->get('translator')->trans('alerts.emailResendRapid', [], 'ChrisUserBundle')
                );
            }
        } else {
            $this->addFlash(
                'danger', $this->get('translator')->trans('alerts.emailResendNotRequired', [], 'ChrisUserBundle')
            );
        }
        return $this->redirectToRoute('chrisuser_index');

    }

    /**
     * @Route("/user/email-settings", name="chrisuser_opt_marketing")
     */
    public function optMarketing(Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $form = $this->createForm(MarketingFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $marketing = $form->get('agreeMarketing')->getData();

            $user->setAgreeMarketing($marketing);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success', $this->get('translator')->trans('alerts.marketingPreferences', [], 'ChrisUserBundle')
            );
            return $this->redirectToRoute('chrisuser_index');
        }

        return $this->render('@ChrisUser/user/change_marketing.html.twig', [
            'changeMarketingForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/change-email", name="chrisuser_change_email")
     */
    public function changeEmail(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $form = $this->createForm(ChangeEmailFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingPassword = $form->get('existingPassword')->getData();
            $email = $form->get('email')->getData();
            if($passwordEncoder->isPasswordValid($user, $existingPassword) && strlen($email)>0){
                $user->performEmailChange($form->get('email')->getData());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $message = (new \Swift_Message($this->get('translator')->trans('emails.changeEmailTitle', [], 'ChrisUserBundle')))
                    ->setFrom($this->email)
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

                $this->addFlash(
                    'danger', $this->get('translator')->trans('alerts.emailChanged', [], 'ChrisUserBundle')
                );

                return $this->redirectToRoute('chrisuser_index');

            } else {
                $this->addFlash(
                    'danger', $this->get('translator')->trans('alerts.emailChangeError', [], 'ChrisUserBundle')
                );
            }
        }

        return $this->render('@ChrisUser/user/change_email.html.twig', [
            'changeEmailForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/change-password", name="chrisuser_change_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingPassword = $form->get('existingPassword')->getData();

           if( $passwordEncoder->isPasswordValid($user, $existingPassword)){
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

               $this->addFlash(
                   'danger', $this->get('translator')->trans('alerts.passwordChanged', [], 'ChrisUserBundle')
               );

                return $this->redirectToRoute('chrisuser_index');

            } else {
               $this->addFlash(
                   'danger', $this->get('translator')->trans('alerts.passwordChangeError', [], 'ChrisUserBundle')
               );
            }
        }

        return $this->render('@ChrisUser/user/change_password.html.twig', [
            'changePasswordForm' => $form->createView()
        ]);

    }


}