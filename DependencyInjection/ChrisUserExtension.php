<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 29/04/2020
 * Time: 12:26
 */

namespace Chris\ChrisUserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ChrisUserExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $emailValidation = $config['email_validation'];

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        if($emailValidation){
            $loader->load('email_validator.yaml');
        }

        $userControllerDefinition = $container->getDefinition('Chris\ChrisUserBundle\Controller\UserController');
        $userControllerDefinition->setArgument(0, $config['user_class']);

        $registrationControllerDefinition = $container->findDefinition('Chris\ChrisUserBundle\Controller\RegistrationController');
        $registrationControllerDefinition->setArgument(0, $config['user_class']);
        $registrationControllerDefinition->setArgument(1, $config['register_form_class']);
        $registrationControllerDefinition->addTag("controller.service_arguments");
        $regDec = $config['registration_controller_class'];
        if($regDec!=null) {
            $registrationControllerDefinition->setClass($regDec);
        }

        $securityControllerDefinition = $container->getDefinition('Chris\ChrisUserBundle\Controller\SecurityController');
        $securityControllerDefinition->setArgument(0, $config['user_class']);
        $securityControllerDefinition->addTag("controller.service_arguments");
        $secDec = $config['security_controller_class'];
        if($secDec!=null) {
            $securityControllerDefinition->setClass($secDec);
        }

        $userControllerDefinition = $container->getDefinition('Chris\ChrisUserBundle\Controller\UserController');
        $userControllerDefinition->setArgument(0, $config['user_class']);
        $userControllerDefinition->addTag("controller.service_arguments");
        $usrDec = $config['user_controller_class'];
        if($secDec!=null) {
            $userControllerDefinition->setClass($usrDec);
        }

        $loginFormAuthenticatorDefinition = $container->getDefinition('Chris\ChrisUserBundle\Security\LoginFormAuthenticator');
        $loginFormAuthenticatorDefinition->setArgument(4, $config['user_class']);

    }

}