# Authentication Process  

The authentication process is set inside the security configuration file security.yaml inside the config > packages folder.
The official Symfony documentation can be found [here](https://symfony.com/doc/current/security.html).

The User entity is used for authentication based on its ```email``` property as explained below.  
  
All the users are stored in the database project.  
And, obviously, the User entity also have a ```password``` property which will be used to log in the application, as well as the ```email``` property, as explained in the following sections.

## The password hashers  

The application require a user to log in with a password.  
For these applications, the SecurityBundle provides password hashing and verification functionality.  
  
Thats why User class implements the ```PasswordAuthenticatedUserInterface```.  
The implemented ```PasswordAuthenticatedUserInterface``` which auto-selects and migrates the best possible hashing algorithm thanks to the 'auto' parameter.  
  
This configuration defines how user passwords should be hashed and verified during authentication.  

```yaml
# config/packages/security.yaml
password_hashers:
    Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
```

Symfony knows now how you want to hash the passwords, you can use the UserPasswordHasherInterface service to do this before saving the users to the database.  

## The User provider  
  
Any secured section of your application needs some concept of a user. The user provider loads users from storage, the database of the project, based on a "user identifier".  
  
The user provider is used for optional features like remember me, to reload user from session & other features (e.g. switch_user) and impersonation.  
The set property is the one used for login in, in our case it is set to the email.  
```yaml
# config/packages/security.yaml
providers:
  app_user_provider:
    entity:
      class: App\Entity\User
      property: email
```

This user provider knows how to (re)load users from a storage (e.g. a database) based on a "user identifier" (e.g. the user's email address or username).  
  
The configuration above uses Doctrine to load the User entity using the email property as "user identifier".  

## The firewall

The firewall is the core of securing your application. Every request within the firewall is checked if it needs an authenticated user.  
The firewall also takes care of authenticating this user (e.g. using a login form) and defines which parts of the application are secured and how the users will be able to authenticate.  
  
In the dev mode part, The configuration indicates that URLs starting with /_(profiler|wdt)|css|images|js)/ do not require security and are therefore accessible without restriction.  
This allows access to debugging tools (Profiler, Web Debug Toolbar) as well as static resources (CSS, images, JS) in the development environment without having to worry about authentication.
  
It also configures how the authentication process will be handled. In our case in production mode, the custom authenticator LoginAuthenticator is used based on the app_user_provider mentionned above, which manage the retrieval of user information from the data source, the database.  
  
The login authenticator is responsible for verifying credentials and managing authentication flows.  
There is also parameters for the logout : its path "/logout" and the redirection url via the target parameter.  
  
The 'lazy: true' enables firewall lazy loading. This means that the security process will be triggered only when needed, which can improve performance by avoiding costly security operations if they are not needed for a specific request.

```yaml
# config/packages/security.yaml
firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            custom_authenticator: App\Security\LoginAuthenticator
            logout:
                path: logout
                target: login
```

The login process is managed by the LoginAuthenticator class, which can be found in src/Security folder.  
  
You can change the redirection path post login thanks to the line :
```php
return new RedirectResponse($this->urlGenerator->generate('homepage'));
```
  
You can change the roadname homepage by another one if needed another redirection after log in.  
  
A LoginSubscriber is available in src/EventListener folder to customize the flash messages on login success and logout thanks to the event ```LoginSuccessEvent``` and ```LogoutEvent```.
  
## The access control and role hierarchy

Finally, there are two last points. 

### Access Control  
  
First the access control fine tunes the authorization needed to access certain paths, for example some paths can be made accessible to any user or only to admins users.
It allows to define which parts of the application are accessible by which categories of users according to their roles.  
  
We have to specify rules for the paths (URLs) of the application and the roles needed to access them.
This is called authorization, and its job is to decide if a user can access some resource (a URL, a model object, a method call).  
  
The process of authorization has two different sides:
* The user receives a specific role when logging in (e.g. ROLE_ADMIN).
* You add code so that a resource (e.g. URL, controller) requires a specific "attribute" (e.g. a role like ROLE_ADMIN) in order to be accessed.  
  
For example, you can find the the UserController the following code ```#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_SUPER_ADMIN")'))]```.  
It means that if a user want to access to the page / url linked to this controller method ('/users/list' - name : user_list), he will need to have either the admin role or the super admin role, in another case it will redirect him to a 403 unauthorized error page.  
  
Another case is what you find in the security.yaml in ```access_control``` : ``` { path: ^/users, roles: [ROLE_ADMIN, ROLE_SUPER_ADMIN] } ``` means that all the url paths with '/users' will require the admin or super admin role to access the page, in another case it will redirect him to a 403 unauthorized error page.  
  
### Role Hierarchy   
  
The role hierarchy allows you to establish a hierarchical relationship between the different roles in your system.  
This means that a user with a "parent" role automatically has all the rights and privileges of the "child" roles in the hierarchy.  
  
Instead of giving many roles to each user, we define role inheritance rules by creating a role hierarchy.  
  
In this case : ```ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_USER]``` means that users with the super admin role will also have the ROLE_ADMIN and ROLE_USER role.  


```yaml
# config/packages/security.yaml
role_hierarchy: 
    ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_USER]
access_control:
    - { path: ^/users, roles: [ROLE_ADMIN, ROLE_SUPER_ADMIN] }
```  
