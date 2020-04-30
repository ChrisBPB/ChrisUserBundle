<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 29/04/2020
 * Time: 13:19
 */

namespace Chris\ChrisUserBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('chris_user');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('email_validation')->end()
                ->scalarNode('user_class')->defaultValue('App\Entity\User')->end()
            ->end();



        return $treeBuilder;
    }

}