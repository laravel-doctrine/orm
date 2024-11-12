=======
Testing
=======

Entity Factories
================

When testing or demonstrating your application you may need to insert some
dummy data into the database. To help with
this Laravel Doctrine provides Entity Factories, which are similar to
Laravel's Model Factories. These allow you
to define values for each property of your Entities and quickly generate
many of them.

Place your Factory files in ``database/factories``. Each file in this
directory will be run with the variable ``$factory``
being an instance of ``LaravelDoctrine\ORM\Testing\Factory``.

Entity Definitions
------------------

To define an Entity simple pass the factory its classname and a callback
which details what its properties should be set to

.. code-block:: php

  $factory->define(App\Entities\User::class, function(Faker\Generator $faker) {
      return [
          'name' => $faker->name,
          'emailAddress' => $faker->email
      ];
  });


Faker allows you to get Entities with different values each time you
generate one. Note that as usual with Doctrine,
we reference class property (field) names, and not database columns!

The factory allows you to define multiple types of the same Entity using
``defineAs``

.. code-block:: php

  $factory->defineAs(App\Entities\User::class, 'admin', function(Faker\Generator $faker) {
      return [
          'name' => $faker->name,
          'emailAddress' => $faker->email,
          'isAdmin' => true
      ];
  });


Using Entity Factories in Seeds and Tests
-----------------------------------------

After you have defined your Entities you can then use the factory to generate
them or insert them directly into the database.

A helper named ``entity()`` is defined to aid you in this or you can use
the factory directly.

To make an instance of an Entity (but not persist it to the database), call

.. code-block:: php

  entity(App\Entities\User::class)->make();

  // OR

  $factory->of(App\Entities\User::class)->make();


If you need an Entity of a specific type (see the 'admin' example above)

.. code-block:: php

  entity(App\Entities\User::class, 'admin')->make();

  // OR

  $factory->of(App\Entities\User::class, 'admin')->make();


If you need multiple Entities

.. code-block:: php

  entity(App\Entities\User::class, 2)->make();
  entity(App\Entities\User::class, 'admin', 2)->make();

  // OR

  $factory->of(App\Entities\User::class)->times(2)->make();


These methods will return an instance of ``Illuminate\Support\Collection``
containing your Entities.

If you want to instead persist the Entity before being given an instance of
it, replace calls to ``->make()`` with ``->create()``

.. code-block:: php

  entity(App\Entities\User::class)->create(); // The User is now in the database


Passing Extra Attributes to Factories
-------------------------------------

Factory definition callbacks may receive an optional second argument of attributes.

.. code-block:: php

  $factory->define(App\Entities\User::class, function(Faker\Generator $faker, array $attributes) {
      return [
          'name' => isset($attributes['name']) ? $attributes['name'] : $faker->name,
          'emailAddress' => $faker->email
      ];
  });

  $user = entity(App\Entities\User::class)->make(['name' => 'Taylor']);

  // $user->getName() = 'Taylor'


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
