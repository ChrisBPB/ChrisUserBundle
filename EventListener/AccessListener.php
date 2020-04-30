<?php

namespace Chris\ChrisUserBundle\EventListener;


use Chris\ChrisUserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccessListener
{
    private $auth;
    private $router;
    private $req;
    private $token;

    public function __construct(AuthorizationCheckerInterface $auth, RouterInterface $router, RequestStack $req, TokenStorageInterface $token){
        $this->auth = $auth;
        $this->router = $router;
        $this->req = $req;
        $this->token = $token;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        
        $routeName = $this->req->getCurrentRequest()->get('_route');

        if($event->isMasterRequest() && substr($routeName, 0, 1 ) != "_") {
            $user = $this->token->getToken()->getUser();
            if ($user instanceof User && $user->hasRole('ROLE_PENDING')) {
                $url = $this->router->generate('chrisuser_email_pending');

                if($routeName != "chrisuser_email_pending" && $routeName != "chrisuser_email_validate" && $routeName != "chrisuser_change_email" && $routeName != "chrisuser_index" && $routeName != "chrisuser_resend_email"){
                   $event->setResponse(new RedirectResponse($url));
                }
            }
        }
    }

}