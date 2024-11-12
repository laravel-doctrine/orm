=============
Notifications
=============

Laravel has a notification system with support for custom channels like
mail, slack, sms, etc.  Laravel Doctrine offers a doctrine channel so
notifications can also be stored in your database.

Notification entity
===================

Create a `Notification` entity in your project that
extends `LaravelDoctrine\ORM\Notifications\Notification`.
It might look like this:

.. code-block:: php

  namespace App\Entities;

  use Doctrine\ORM\Mapping AS ORM;

  #[ORM\Entity]
  class Notification extends \LaravelDoctrine\ORM\Notifications\Notification
  {
      #[ORM\ManyToOne(targetEntity: \App\Entities\User::class)]

      protected $user;
  }

``LaravelDoctrine\ORM\Notifications\Notification`` offers a couple of fluent methods:

.. code-block:: php

  $n->to($user)
    ->success();

  $n->error();

  $n->level('info')
    ->message('Your notification message')
    ->action('Click here', 'http://yoururl.com');


It also has getters to retrieve information about the notification:

.. code-block:: php

  $n->getId();
  $n->getUser();
  $n->getLevel();
  $n->getMessage();
  $n->getActionText();
  $n->getActionUrl();


Publishing notifications on the Doctrine channel
================================================

It's recommended you read the Laravel docs on this subject:
https://laravel.com/docs/notifications

The Doctrine channel is available as:
``LaravelDoctrine\ORM\Notifications\DoctrineChannel::class``

When adding this channel you need to provide a ``toEntity`` method. This
method should return a new instance of your ``Notification`` class.
You can use the fluent methods as described above.

.. code-block:: php

  namespace App\Notifications;

  class InvoicePaid extends \Illuminate\Notifications\Notification
  {
      /**
      * Get the notification's delivery channels.
      *
      * @param  mixed $notifiable
      * @return array
      */
      public function via($notifiable)
      {
          return [\LaravelDoctrine\ORM\Notifications\DoctrineChannel::class];
      }

      /**
      * @param $notifiable
      * @return $this
      */
      public function toEntity($notifiable)
      {
          return (new \App\Entities\Notification)
              ->to($notifiable)
              ->success()
              ->message('Some message')
              ->action('Bla', 'http://test.net');
      }
  }


Notifiable Entity
=================

Your Notifiable entity should use the
``LaravelDoctrine\ORM\Notifications\Notifiable`` trait.

Now you will be able to do ``$user->notify(new InvoicePaid);``

.. code-block:: php

  class User
  {
      use LaravelDoctrine\ORM\Notifications\Notifiable;
  }


Custom Entity Manager
=====================

By default the Doctrine Channel will find the first suitable EM to persist
the Notification by using the ``ManagerRegistry``.

If you want more control over it, you can specify it inside your notifiable
entity (most likely your User entity). Usage of the
``LaravelDoctrine\ORM\Notifications\Notifiable`` is required.

.. code-block:: php

  public function routeNotificationForDoctrine()
  {
      return 'custom';
  }


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
