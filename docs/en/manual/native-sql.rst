..  vim: set ts=4 sw=4 tw=79 ff=unix :

**********
Native SQL
**********

============
Introduction
============

:php:class:`Doctrine_RawSql` provides a convenient interface for building raw
sql queries. Similar to :php:class:`Doctrine_Query`, :php:class:`Doctrine_RawSql`
provides means for fetching arrays and objects. Whichever way you
prefer.

Using raw sql for fetching might be useful when you want to utilize
database specific features such as query hints or the ``CONNECT``
keyword in Oracle.

Creating a :php:class:`Doctrine_RawSql` object is easy:

::

    // test.php
    $q = new Doctrine_RawSql();

Optionally a connection parameter can be given and it accepts an
instance of :php:class:`Doctrine_Connection`. You learned how to create
connections in the :doc:`connections` chapter.

::

    // test.php
    $conn = Doctrine_Manager::connection();
    $q    = new Doctrine_RawSql($conn);

=================
Component Queries
=================

The first thing to notice when using :php:class:`Doctrine_RawSql` is that you
always have to place the fields you are selecting in curly brackets ``{}``.
Also for every selected component you have to call ``addComponent()``.

The following example should clarify the usage of these:

::

    // test.php
    $q->select('{u.*}') ->from('user u') ->addComponent('u', 'User');
    $users = q->execute();

    print_r($users->toArray());

.. note::

    Note above that we tell that ``user`` table is bound to
    class called ``User`` by using the ``addComponent()`` method.

Pay attention to following things:

-  Fields must be in curly brackets.
-  For every selected table there must be one ``addComponent()`` call.

=================================
Fetching from Multiple Components
=================================

When fetching from multiple components the ``addComponent()`` calls
become a bit more complicated as not only do we have to tell which
tables are bound to which components, we also have to tell the parser
which components belongs to which.

In the following example we fetch all ``users`` and their
``phonenumbers``. First create a new :php:class:`Doctrine_RawSql` object and add
the select parts:

::

    // test.php
    $q = new Doctrine_RawSql();
    $q->select('{u.*}, {p.*}');

Now we need to add the ``FROM`` part to the query with the join to the
phonenumber table from the user table and map everything together:

::

    // test.php
    $q->from('user u LEFT JOIN phonenumber p ON u.id = p.user_id')

Now we tell that ``user`` table is bound to class called ``User`` we
also add an alias for ``User`` class called ``u``. This alias will be
used when referencing the ``User`` class.

::

    // test.php
    $q->addComponent('u', 'User u');

Now we add another component that is bound to table ``phonenumber``:

::

    // test.php
    $q->addComponent('p', 'u.Phonenumbers p');

.. note::

    Notice how we reference that the ``Phonenumber`` class is the ``User``'s phonenumber.

Now we can execute the :php:class:`Doctrine_RawSql` query just like if you were
executing a :php:class:`Doctrine_Query` object:

::

    // test.php
    $users = $q->execute();
    echo get_class($users) . "";
    echo get_class($users[0]) . "\n";
    echo get_class($users[0]['Phonenumbers'][0]) . "";

The above example would output the following when executed:

.. code-block:: sh

    $ php test.php Doctrine_Collection User Phonenumber

==========
Conclusion
==========

This chapter may or may not be useful for you right now. In most cases
the Doctrine Query Language is plenty sufficient for retrieving the
complex data sets you require. But if you require something outside the
scope of what :php:class:`Doctrine_Query` is capable of then
:php:class:`Doctrine_RawSql` can help you.

In the previous chapters you've seen a lot of mention about ``YAML`` schema
files and have been given examples of schema files but haven't really
been trained on how to write your own. The next chapter explains in
great detail how to maintain your models as :doc:`yaml-schema-files`.