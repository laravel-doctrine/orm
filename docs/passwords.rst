===============
Password Resets
===============

Most web applications provide a way for users to reset their forgotten
passwords. Rather than forcing you to re-implement this on each application,
Laravel provides convenient methods for sending password reminders and
performing password resets.

First off you have to replace Laravel's
``PasswordResetServiceProvider`` in ``config/app.php`` by
``LaravelDoctrine\ORM\Auth\Passwords\PasswordResetServiceProvider``.
This will make sure the querying is handled by Doctrine.

To get started, verify that your ``User`` model implements the
``Illuminate\Contracts\Auth\CanResetPassword`` contract.
You can use the ``Illuminate\Auth\Passwords\CanResetPassword``
trait, which provides the methods the interface requires. The trait assumes
your ``email`` property is called ``email``.

Read more about the usage in the `Laravel documentation <https://laravel.com/docs/passwords>`_.


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
