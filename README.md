# Editor.md

![](https://img.shields.io/github/issues/ChrisBPB/ChrisUserBundle.svg) 


## Features
- Login
- Register
- Email validation
- Password reset
- Profile editing

## What else?
ChrisUserBundle also utilised translations properly, and whilst only en will be provided - it is ofcourse easy to add your own.

### Want to use ChrisUserBundle?
Here are the first steps to take:



>####Security.yaml:
<pre>
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
</pre>        


>####bundles.php
<pre>        
Chris\ChrisUserBundle\ChrisUserBundle::class => ['all' => true],
</pre>


>####routes.yaml
<pre>
ChrisUserBundle:
      resource: '../lib/ChrisUserBundle/Controller/'
      type:     annotation
</pre>


>####services.yaml
<pre>
Chris\ChrisUserBundle\:
        resource: '../lib/ChrisUserBundle/*'
        exclude: '../lib/ChrisUserBundle/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}'

Chris\ChrisUserBundle\Controller\:
        resource: '../lib/ChrisUserBundle/src/Controller'
        tags: ['controller.service_arguments']
</pre>

#>How To Get It?
>composer require chris/chris-user-bundle

>composer update

>php bin/console make:migration

>php bin/console do:migration

>php bin/console cache:clear

