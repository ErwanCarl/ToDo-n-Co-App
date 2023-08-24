# P8 Openclassrooms - ToDo & Co - Erwan Carlini

[![SymfonyInsight](https://insight.symfony.com/projects/2d63117b-20b3-40e8-aa3a-dc7d6d0d0d18/big.svg)](https://insight.symfony.com/projects/2d63117b-20b3-40e8-aa3a-dc7d6d0d0d18)

---------------

## Starting project

### Project

Recovery of an obsolete application to manage daily tasks. New features have been integrated as well as correction of several anomalies to improve its overall quality and user experience. Unit and functional tests have been carried out in order to verify the correct behavior of the application, and performance tests have been carried out in order to improve its performance even more.  
Code quality and performance as well as the reduction of technical debt is the focus of this project.

### Requirements

- PHP : ⩾ 8.1.0 
- MySQL ⩾ 8.0.30
- Composer
- Symfony 6.3
- Symfony CLI

### Packages Installation

First, clone project and place the project in a new folder, then install all composer packages with command line : ``composer install``.  

### Database datas

First, you will need to change the value of DATABASE_URL in the file .env to match with your database parameters, then create your database.  

To get all necessaries datas :  
* Run ``symfony console doctrine:database:create`` in command to create your database  
* Run ``symfony console doctrine:migration:migrate`` in command to create your tables in your DB from the entities files  
* Run ``php bin/console doctrine:fixtures:load`` to get the basic datas of this project  
* Run ``symfony serve -d`` to use symfony CLI server  

### Authentication  
  
In order to authenticate yourselves, you will need either add your own user in the datas fixtures or to use the Super Admin created for this purpose.  
It will allow you to create or modify all the user you need directly in the application, or manage all the tasks by yourselves, all the permissions are given to this superadmin user and circumvents some restrictions that even an admin user has.
To log in in superadmin view, you will have to go to the login page and use his identifiers : login = super.admin@orange.fr / password = password.

### Unit and functional tests

#### Testing environnement 

To set up your testing environnement, you will have follow the following steps : 
* First of all you will have to go to your .env.test file and change the database name. Usually you use the same database name as in your .env file adding "_test" at the end.
* Lastly, you will have to change the APP_ENV value to 'test' in the .env file  
* !! Don't forget to clear the cache !! 
* DAMA DoctrineTestBundle is used in order to roll back the actions which impacts the dabatase, which means the tests will never change the datas in database and will remain clean, so it won't interfer with the following tests launch. The configuration is available in the file config\packages\test\dama_doctrine_test_bundle.yaml and is activated thanks to the line in phpunit.xml.dist :  
``<extensions>``
    ``<bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>``
``</extensions>``   
* Then you will have to use the following commands to set up your test dabatase :
** ``php bin/console --env=test doctrine:database:create``     
** ``php bin/console --env=test doctrine:scyehema:create``   
** ``php bin/console --env=test doctrine:fixtures:load``    
* You're now ready to use your application in test mode. Notice that the datafixtures are the same used in dev mode, but some of them are randomly generated except for the superadmin user who will remain the same.
 
#### Tests launch

In order to launch your tests, you will have to open a terminal and use the following command : ``vendor/bin/phpunit``.  
If you want to perform a single test on a targeted method, you will have to use the option ``--filter`` as : ``vendor/bin/phpunit --filter=testMethodTargeted``.  

#### Tests coverage 

To get the global tests coverage for the application, you will just have to do the following command in your terminal : ``vendor/bin/phpunit --coverage-html public/test-coverage`` and open the index.html file in your browser, located at public\test-coverage\index.html.

## Libraries list

* Faker  
* Php Unit  
* Php CS Fixer  
* Php Stan  
* DAMA DoctrineTestBundle  
* Blackfire
