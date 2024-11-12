==========
Validation
==========

Laravel provides several different approaches to validate your
application's incoming data. By default, Laravel's base controller class
uses a ValidatesRequests trait which provides a
convenient method to validate incoming HTTP request with a variety of
powerful validation rules.

Both ``unique`` and ``exists`` validation rules, require the database to be
queried. This packages provides this integration.

Unique
======

The field under validation must be unique for a given Entity. If the column
option is not specified, the field name will be used.

* entity - fully qualified namespace of your entity
* column - the column that should be unqiue entity
* exceptId - the id that should be excluded from the query [optional]
* idColumn - alternative id column (defaults to id) [optional]

.. code-block:: php

  /**
  * Store a new blog post.
  *
  * @param  Request  $request
  * @return Response
  */
  public function store(Request $request)
  {
      $this->validate($request, [
          'username' => 'required|unique:App\Entities\User,username',
      ]);
  }


Forcing A Unique Rule To Ignore A Given ID
-------------------------------------------

Sometimes, you may wish to ignore a given ID during the unique check.
For example, consider an "update profile" screen that includes the user's
name, e-mail address, and location. Of course, you will want to verify that
the e-mail address is unique. However, if the user only changes the name
field and not the e-mail field, you do not want a validation error to be
thrown because the user is already the owner of the e-mail address. You only
want to throw a validation error if the user provides an e-mail address that
is already used by a different user. To tell the unique rule to ignore the
user's ID, you may pass the ID as the third parameter.

.. code-block:: php

  'email' => 'unique:users,email_address,' . $user->id


Adding Additional Where Clauses
-------------------------------

You may also specify more conditions that will be added as "where"
clauses to the query:

.. code-block:: php

  'email' => 'unique:users,email_address,NULL,id,account_id,1'


In the rule above, only rows with an account_id of 1 would be included
in the unique check.


Exists
======

The field under validation must exist on a given database table.

* entity - fully qualified namespace of your entity
* column - the column that should be unqiue
* exceptId - the id that should be excluded from the query [optional]
* idColumn - alternative id column (defaults to id) [optional]

.. code-block:: php

  /**
  * Store a new blog post.
  *
  * @param  Request  $request
  * @return Response
  */
  public function update($id, Request $request)
  {
      $this->validate($request, [
          'username' => 'required|exists:App\Entities\User,username',
      ]);
  }


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
