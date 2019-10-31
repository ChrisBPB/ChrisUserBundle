#######################
Security.yaml:

providers:
    users:
        entity:
            # the class of the entity that represents users
            class: 'Chris\ChrisUserBundle\Entity\User'

encoders:
    Chris\ChrisUserBundle\Entity\User:
        algorithm: bcrypt
        cost: 12
        
firewalls:
        main:
            anonymous: true
            guard:
                authenticators:
                    - Chris\ChrisUserBundle\Security\LoginFormAuthenticator
            logout:
                path: index
            
access_control:
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        
#######################

#######################
bundles.php
        
Chris\ChrisUserBundle\ChrisUserBundle::class => ['all' => true],
#######################

#######################
routes.yaml

ChrisUserBundle:
      resource: '../lib/ChrisUserBundle/Controller/'
      type:     annotation
#######################

#######################
services.yaml

Chris\ChrisUserBundle\:
        resource: '../lib/ChrisUserBundle/*'
        exclude: '../lib/ChrisUserBundle/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}'

Chris\ChrisUserBundle\Controller\:
        resource: '../lib/ChrisUserBundle/src/Controller'
        tags: ['controller.service_arguments']
#######################

#######################
(wont be needed after)
composer.json

"psr-4": {
        "Chris\\ChrisUserBundle\\": "lib/ChrisUserBundle/",
        }
#######################

composer update
make migrations 
do migrations
clear cache

