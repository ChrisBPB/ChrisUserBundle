<?php


namespace Chris\ChrisUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * TODO:
     *      login
     *      register
     *      forgotten password
     *      change password
     *      view profile
     *      edit profile
     *      roles ?
     *      register confirm or not
     */

    /**
     * @Route("/user", name="chris_user_index")
     */
    public function indexAction(Request $request)
    {

    }
}