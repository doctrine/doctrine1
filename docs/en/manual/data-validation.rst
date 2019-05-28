..  vim: set ts=4 sw=4 tw=79 ff=unix :

***************
Data Validation
***************

============
Introduction
============

.. hint::

    `PostgreSQL Documentation
    <http://www.postgresql.org/docs/8.2/static/ddl-constraints.html>`_: Data
	types are a way to limit the kind
    of data that can be stored in a table. For many applications,
    however, the constraint they provide is too coarse. For example, a
    column containing a product price should probably only accept
    positive values. But there is no standard data type that accepts
    only positive numbers. Another issue is that you might want to
    constrain column data with respect to other columns or rows. For
    example, in a table containing product information, there should be
    only one row for each product number.

Doctrine allows you to define *portable* constraints on columns and
tables. Constraints give you as much control over the data in your
tables as you wish. If a user attempts to store data in a column that
would violate a constraint, an error is raised. This applies even if the
value came from the default value definition.

Doctrine constraints act as database level constraints as well as
application level validators. This means double security: the database
doesn't allow wrong kind of values and neither does the application.

Here is a full list of available validators within Doctrine:

======================  =====================  ===========
validator(arguments)    constraints            description
======================  =====================  ===========
``notnull``             ``NOT NULL``           Ensures the 'not null' constraint in both application and database level
``email``                                      Checks if value is valid email.
``notblank``            ``NOT NULL``           Checks if value is not blank.
``nospace``                                    Checks if value has no space chars.
``past``                ``CHECK`` constraint   Checks if value is a date in the past.
``future``                                     Checks if value is a date in the future.
``minlength(length)``                          Checks if value satisfies the minimum length.
``country``                                    Checks if value is a valid country code.
``ip``                                         Checks if value is valid IP (internet protocol) address.
``htmlcolor``                                  Checks if value is valid html color.
``range(min, max)``     ``CHECK`` constraint   Checks if value is in range specified by arguments.
``unique``              ``UNIQUE`` constraint  Checks if value is unique in its database table.
``regexp(expression)``                         Checks if value matches a given regexp.
``creditcard``                                 Checks whether the string is a well formated credit card number
``digits(int, frac)``   Precision and scale    Checks if given value has //int// number of integer digits and //frac// number of fractional digits
``date``                                       Checks if given value is a valid date.
``readonly``                                   Checks if a field is modified and if it is returns false to force a field as readonly
``unsigned``                                   Checks if given integer value is unsigned.
``usstate``                                    Checks if given value is a valid US state code.
===

Below is an example of how you use the validator and how to specify the
arguments for the validators on a column.

In our example we will use the ``minlength`` validator.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'minlength' => 12
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          minlength: 12
    # ...

========
Examples
========

---------
 Not Null
---------

A not-null constraint simply specifies that a column must not assume the
null value. A not-null constraint is always written as a column
constraint.

The following definition uses a ``notnull`` constraint for column name.
This means that the specified column doesn't accept null values.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'notnull' => true,
                    'primary' => true,
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          notnull: true
          primary: true
    # ...

When this class gets exported to database the following SQL statement
would get executed (in MySQL):

::

    CREATE TABLE user (username VARCHAR(255) NOT NULL,
    PRIMARY KEY(username))

The ``notnull`` constraint also acts as an application level validator.
This means that if Doctrine validators are turned on, Doctrine will
automatically check that specified columns do not contain null values
when saved.

If those columns happen to contain null values
:php:class:`Doctrine_Validator_Exception` is raised.

------
 Email
------

The e-mail validator simply validates that the inputted value is indeed
a valid e-mail address and that the MX records for the address domain
resolve as a valid e-mail address.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('email', 'string', 255, array(
                    'email'   => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        email:
          type: string(255)
          email: true
    # ...

Now when we try and create a user with an invalid email address it will
not validate:

::

    // test.php
    // ...
    $user           = new User();
    $user->username = 'jwage';
    $user->email    = 'jonwage';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid!';
    }

The above code will throw an exception because ``jonwage`` is not a
valid e-mail address. Now we can take this even further and give a valid
e-mail address format but an invalid domain name:

::

    // test.php
    // ...
    $user           = new User();
    $user->username = 'jwage';
    $user->email    = 'jonwage@somefakedomainiknowdoesntexist.com';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid!';
    }

Now the above code will still fail because the domain
``somefakedomainiknowdoesntexist.com`` does not exist and the php
function `checkdnsrr() <http://www.php.net/checkdnsrr>`_ returned
``false``.

----------
 Not Blank
----------

The not blank validator is similar to the not null validator except that
it will fail on empty strings or strings with white space.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'notblank'   => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          notblank: true
    # ...

Now if we try and save a ``User`` record with a username that is a
single blank white space, validation will fail:

::

    // test.php

    // ...
    $user           = new User();
    $user->username = ' ';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid!';
    }

---------
 No Space
---------

The no space validator is simple. It checks that the value doesn't
contain any spaces.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'nospace'   => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          nospace: true
    # ...

Now if we try and save a ``User`` with a ``username`` that has a space
in it, the validation will fail:

::

    $user           = new User();
    $user->username = 'jon wage';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid!';
    }

-----
 Past
-----

The past validator checks if the given value is a valid date in the
past. In this example we'll have a ``User`` model with a ``birthday``
column and we want to validate that the date is in the past.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('birthday', 'timestamp', null, array(
                    'past' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        birthday:
          type: timestamp
          past: true
    # ...

Now if we try and set a birthday that is not in the past we will get a
validation error.

-------
 Future
-------

The future validator is the opposite of the past validator and checks if
the given value is a valid date in the future. In this example we'll
have a ``User`` model with a ``next_appointment_date`` column and we
want to validate that the date is in the future.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('next_appointment_date', 'timestamp', null, array(
                    'future' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        next_appointment_date:
          type: timestamp
          future: true
    # ...

Now if we try and set an appointment date that is not in the future we
will get a validation error.

-----------
 Min Length
-----------

The min length does exactly what it says. It checks that the value
string length is greater than the specified minimum length. In this
example we will have a ``User`` model with a ``password`` column where
we want to make sure the length of the ``password`` is at least 5
characters long.

::

    // models/User.php
    class User extends BaseUser
    {
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('password', 'timestamp', null, array(
                    'minlength' => 5
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        password:
          type: timestamp
          minlength: 5
    # ...

Now if we try and save a ``User`` with a ``password`` that is shorter
than 5 characters, the validation will fail.

::

    // test.php

    // ...
    $user           = new User();
    $user->username = 'jwage';
    $user->password = 'test';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because "test" is only 4 characters long!';
    }

--------
 Country
--------

The country validator checks if the given value is a valid country code.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('country', 'string', 2, array(
                    'country' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        country:
          type: string(2)
          country: true
    # ...

Now if you try and save a ``User`` with an invalid country code the
validation will fail.

::

    // test.php

    // ...
    $user               = new User();
    $user->username     = 'jwage';
    $user->country_code = 'zz';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because "zz" is not a valid country code!';
    }

-----------
 IP Address
-----------

The ip address validator checks if the given value is a valid ip
address.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('ip_address', 'string', 15, array(
                    'ip' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        ip_address:
          type: string(15)
          ip: true
    # ...

Now if you try and save a ``User`` with an invalid ip address the
validation will fail.

::

    $user             = new User();
    $user->username   = 'jwage';
    $user->ip_address = '123.123';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because "123.123" is not a valid ip address';
    }

-----------
 HTML Color
-----------

The html color validator checks that the given value is a valid html hex
color.

::

    // models/User.php
    class User extends BaseUser
    {
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('favorite_color', 'string', 7, array(
                    'htmlcolor' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block::

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        favorite_color:
          type: string(7)
          htmlcolor: true
    # ...

Now if you try and save a ``User`` with an invalid html color value for
the ``favorite_color`` column the validation will fail.

::

    // test.php

    // ...
    $user                 = new User();
    $user->username       = 'jwage';
    $user->favorite_color = 'red';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because "red" is not a valid hex color';
    }

------
 Range
------

The range validator checks if value is within given range of numbers.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('age', 'integer', 3, array(
                    'range' => array(10, 100)
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block::  yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        age:
          type: integer(3)
          range: [10, 100]
    # ...

Now if you try and save a ``User`` with an age that is less than 10 or
greater than 100, the validation will fail.

::

    // test.php

    // ...
    $user           = new User();
    $user->username = 'jwage';
    $user->age      = '3';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because "3" is less than the minimum of "10"';
    }

You can use the ``range`` validator to validate max and min values by
omitting either one of the ``0`` or ``1`` keys of the range array. Below
is an example:

::

    // models/User.php
    class User extends BaseUser
    {
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('age', 'integer', 3, array(
                    'range' => array(1 => 100)
                )
            );
        }
    }

The above would make it so that age has a max of 100. To have a minimum
value simple specify ``0`` instead of ``1`` in the range array.

The YAML syntax for this would look like the following:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        age:
          type: integer(3)
          range:
            1: 100
    # ...

-------
 Unique
-------

Unique constraints ensure that the data contained in a column or a group
of columns is unique with respect to all the rows in the table.

In general, a unique constraint is violated when there are two or more
rows in the table where the values of all of the columns included in the
constraint are equal. However, two null values are not considered equal
in this comparison. That means even in the presence of a unique
constraint it is possible to store duplicate rows that contain a null
value in at least one of the constrained columns. This behavior conforms
to the SQL standard, but some databases do not follow this rule. So be
careful when developing applications that are intended to be portable.

The following definition uses a ``unique`` constraint for column
username.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'unique' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          unique: true
    # ...

.. note::

    You should only use unique constraints for columns other
    than the primary key because they are always unique already.

-------------------
 Regular Expression
-------------------

The regular expression validator is a simple way to validate column
values against your own provided regular expression. In this example we
will make sure the username contains only valid letters or numbers.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('username', 'string', 255, array(
                    'regexp' => '/[a-zA-Z0-9]/'
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
        username:
          type: string(255)
          regexp: '/^[a-zA-Z0-9]+$/'
    # ...

Now if we were to try and save a ``User`` with a ``username`` that has
any other character than a letter or number in it, the validation will
fail:

::

    // test.php

    // ...
    $user           = new User();
    $user->username = '[jwage';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because the username contains a [ character';
    }

------------
 Credit Card
------------

The credit card validator simply checks that the given value is indeed a
valid credit card number.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...

        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('cc_number', 'integer', 16, array(
                    'creditcard' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        cc_number:
          type: integer(16)
          creditcard: true
    # ...

----------
 Read Only
----------

The read only validator will fail validation if you modify a column that
has the ``readonly`` validator enabled on it.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('readonly_value', 'string', 255, array(
                    'readonly' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        readonly_value:
          type: integer(16)
          readonly: true
    # ...

Now if I ever try and modify the column named ``readonly_value`` from a
``User`` object instance, validation will fail.

---------
 Unsigned
---------

The unsigned validator checks that the given integer value is unsigned.

::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...
            $this->hasColumn('age', 'integer', 3, array(
                    'unsigned' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    User:
      columns:
    # ...
        age:
          type: integer(3)
          unsigned: true
    # ...

Now if I try and save a ``User`` with a negative age the validation will
fail:

::

    // test.php

    // ...
    $user           = new User();
    $user->username = 'jwage';
    $user->age      = '-100';

    if ( ! $user->isValid() )
    {
        echo 'User is invalid because -100 is signed';
    }

---------
 US State
---------

The us state validator checks that the given string is a valid US state
code.

::

    // models/State.php
    class State extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
            $this->hasColumn('code', 'string', 2, array(
                    'usstate' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    State:
      columns:
        name: string(255)
        code:
          type: string(2)
          usstate: true

Now if I try and save a ``State`` with an invalid state code then the
validation will fail.

::

    $state       = new State();
    $state->name = 'Tennessee';
    $state->code = 'ZZ';

    if ( ! $state->isValid() )
    {
        echo 'State is invalid because "ZZ" is not a valid state code';
    }

==========
Conclusion
==========

If we want Doctrine to validate our data before being persisted to the
database, now we have the knowledge on how to do it. We can use the
validators that are provided with the Doctrine core to perform common
validations of our data.

The :doc:`inheritance` is an important one as we will
discuss a great feature of Doctrine, :doc:`inheritance`!
Inheritance is a great way accomplish complex functionality with minimal
code. After we discuss inheritance we will move on to a custom strategy
that provides even better functionality than inheritance, called :doc:`behaviors`.