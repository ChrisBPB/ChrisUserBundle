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
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ChrisUserExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $emailValidation = $config['email_validation'];


        echo $emailValidation;
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        if($emailValidation){
            $loader->load('email_validator.yaml');
        }


    }

}