==============
Authentication
==============

Configuration
=============

Implementing Authenticatable
----------------------------

First you must extend Laravel's authentication contract on the entity you
wish to use with authentication.

.. code-block:: php

  class User implements \Illuminate\Contracts\Auth\Authenticatable
  {
      #[ORM\Id]
      #[ORM\Column(type: "integer")]
      #[ORM\GeneratedValue(strategy: "AUTO")]
      protected $id;

      public function getAuthIdentifierName()
      {
          return 'id';
      }

      public function getAuthIdentifier()
      {
          return $this->id;
      }

      public function getPassword()
      {
          return $this->password;
      }
  }


You may also use the provided trait ``LaravelDoctrine\ORM\Auth\Authenticatable``
in your entity and override where necessary.


.. code-block:: php

  class User implements \Illuminate\Contracts\Auth\Authenticatable
  {
      use \LaravelDoctrine\ORM\Auth\Authenticatable;

      #[ORM\Id]
      #[ORM\Column(type: "integer")]
      #[ORM\GeneratedValue(strategy: "AUTO")]
      protected $userId;

      public function getAuthIdentifierName()
      {
          return 'userId';
      }
  }


Configuring Laravel
-------------------

Edit Laravel's Auth configuration ``/config/auth.php`` to set up use with Doctrine.


.. code-block:: php

  return [

    /*
    |--------------------------------------------------------------------------
    | Default Authentication Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the authentication driver that will be utilized.
    | This driver manages the retrieval and authentication of the users
    | attempting to get access to protected areas of your application.
    |
    |
    */

    'driver' => 'doctrine',

    /*
    |--------------------------------------------------------------------------
    | Authentication Model
    |--------------------------------------------------------------------------
    |
    | This is the entity that has implemented Authenticatable
    |
    */

    'model' => App\Entities\User::class,


    /*
    |--------------------------------------------------------------------------
    | Password Reset Settings
    |--------------------------------------------------------------------------
    |
    | Here you may set the options for resetting passwords including the view
    | that is your password reset e-mail. You can also set the name of the
    | table that maintains all of the reset tokens for your application.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

      'password' => [
          'email' => 'emails.password',
          'table' => 'password_resets',
          'expire' => 60,
      ],

  ];


Password hashing
================

Password hashing must be handled by your application. Laravel's authentication
and LaravelDoctrine will treat passwords as nothing more than strings. We would
recommend decoupling the operation of hashing of the password (and any other
procedures, like validating strength) from its storage by implementing a separate
service to handle any password-related actions.

.. code-block:: php
  use \Illuminate\Contracts\Hashing\Hasher;

  class PasswordService
  {
      private $hasher;
      private $passwordStrengthValidator;

      /**
      * @param Hasher $hasher
      * @param MyPasswordStrengthValidator $passwordStrength
      */
      public function __construct(
        Hasher $hasher,
        MyPasswordStrengthValidator $passwordStrength
      ) {
        $this->hasher = $hasher;
        $this->passwordStrengthValidator = $passwordStrength
      }

      /**
      * Validate and change the given users password
      *
      * @param User $user
      * @param string $password
      * @throws PasswordTooWeakException
      * @return void
      */
      public function changePassword(User $user, $password)
      {
          if ($this->passwordStrengthValidator->isStrongEnough($password)) {
              $user->setPassword($this->hasher->make($password))
          } else {
              throw new PasswordTooWeakException();
          }
      }
  }


Using Authentication
====================

Authentication usage is covered by
`Laravel's Documentation <https://laravel.com/docs/authentication>`_.


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
