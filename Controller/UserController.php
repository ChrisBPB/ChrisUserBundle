<?php


namespace Chris\ChrisUserBundle\Controller;

use Chris\ChrisUserBundle\Form\ChangePasswordFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * TODO:
     *      change password
     *      view profile
     *      edit profile
     *      roles ?
     */

    /**
     * @Route("/user", name="chrisuser_index")
     */
    public function indexAction(Request $request)
    {

    }

    /**
     * @Route("/user/change-password", name="chrisuser_change_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder){

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