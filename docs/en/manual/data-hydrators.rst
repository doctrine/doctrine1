..  vim: set ts=4 sw=4 tw=79 :

**************
Data Hydrators
**************

Doctrine has a concept of data hydrators for transforming your
:php:class:`Doctrine_Query` instances to a set of PHP data for the user to take
advantage of. The most obvious way to hydrate the data is to put it into
your object graph and return models/class instances. Sometimes though
you want to hydrate the data to an array, use no hydration or return a
single scalar value. This chapter aims to document and demonstrate the
different hydration types and even how to write your own new hydration
types.

======================
Core Hydration Methods
======================

Doctrine offers a few core hydration methods to help you with the most
common hydration needs.

------
Record
------

The first type is ``HYDRATE_RECORD`` and is the default hydration type.
It will take the data from your queries and hydrate it into your object
graph. With this type this type of thing is possible.

::

    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->leftJoin('u.Email e')
        ->where('u.username = ?', 'jwage');

    $user = $q->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);

    echo $user->Email->email;

The data for the above query was retrieved with one query and was
hydrated into the object graph by the record hydrator. This makes it
much easier to work with data since you are dealing with records and not
result sets from the database which can be difficult to work with.

-----
Array
-----

The array hydration type is represented by the ``HYDRATE_ARRAY``
constant. It is identical to the above record hydration except that
instead of hydrating the data into the object graph using PHP objects it
uses PHP arrays. The benefit of using arrays instead of objects is that
they are much faster and the hydration does not take as long.

So if you were to run the same example you would have access to the same
data but it would be via a PHP array.

::

    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->leftJoin('u.Email e')
        ->where('u.username = ?', 'jwage');

    $user = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

    echo $user['Email']['email'];

------
Scalar
------

The scalar hydration type is represented by the ``HYDRATE_SCALAR``
constant and is a very fast and efficient way to hydrate your data. The
downside to this method is that it does not hydrate your data into the
object graph, it returns a flat rectangular result set which can be
difficult to work with when dealing with lots of records.

::

    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->where('u.username = ?', 'jwage');

    $user = $q->fetchOne(array(), Doctrine_Core::HYDRATE_SCALAR);

    echo $user['u_username'];

The above query would produce a data structure that looks something like
the following:

::

    $user = array(
        'u_username' => 'jwage',
        'u_password' => 'changeme',
        // ...
    );

If the query had a many relationship joined than the data for the user
would be duplicated for every record that exists for that user. This is
the downside as it is difficult to work with when dealing with lots of
records.

-------------
Single Scalar
-------------

Often times you want a way to just return a single scalar value. This is
possible with the single scalar hydration method and is represented by
the ``HYDRATE_SINGLE_SCALAR`` attribute.

With this hydration type we could easily count the number of
phonenumbers a user has with the following:

::

    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->select('COUNT(p.id)')
        ->leftJoin('u.Phonenumber p')
        ->where('u.username = ?', 'jwage');

    $numPhonenumbers = $q->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    echo $numPhonenumbers;

This is much better than hydrating the data with a more complex method
and grabbing the value from those results. With this it is very fast and
efficient to get the data you really want.

---------
On Demand
---------

If you wish to use a less memory intensive hydration method you can use
the on demand hydration which is represented by the
``HYDRATE_ON_DEMAND`` constant. It will only hydrate one records graph
at a time so that means less memory footprint overall used.

::

    // Returns instance of Doctrine_Collection_OnDemand
    $result = $q->execute(array(), Doctrine_Core::HYDRATE_ON_DEMAND);
    foreach ($result as $obj)
    {
        // ...
    }

:php:class:`Doctrine_Collection_OnDemand` hydrates each object one at a time as
you iterate over it so this results in less memory being used because we
don't have to first load all the data from the database to PHP then
convert it to the entire data structure to return.

---------------------------
Nested Set Record Hierarchy
---------------------------

For your models which use the nested set behavior you can use the record
hierarchy hydration method to hydrate your nested set tree into an
actual hierarchy of nested objects.

::

    $categories = Doctrine_Core::getTable('Category')
        ->createQuery('c')
        ->execute(array(), Doctrine_Core::HYDRATE_RECORD_HIERARCHY);

Now you can access the children of a record by accessing the mapped
value property named ``__children``. It is named with the underscores
prefixed to avoid any naming conflicts.

::

    foreach ($categories->getFirst()->get('__children') as $child)
    {
        // ...
    }

--------------------------
Nested Set Array Hierarchy
--------------------------

If you wish to hydrate the nested set hierarchy into arrays instead of
objects you can do so using the ``HYDRATE_ARRAY_HIERARCHY`` constant.
It is identical to the ``HYDRATE_RECORD_HIERARCHY`` except that it
uses PHP arrays instead of objects.

::

    $categories = Doctrine_Core::getTable('Category')
        ->createQuery('c')
        ->execute(array(), Doctrine_Core::HYDRATE_ARRAY_HIERARCHY);

Now you can do the following:

::

    foreach ($categories[0]['__children'] as $child)
    {
        // ...
    }

========================
Writing Hydration Method
========================

Doctrine offers the ability to write your own hydration methods and
register them with Doctrine for use. All you need to do is write a class
that extends :php:class:`Doctrine_Hydrator_Abstract` and register it with
:php:class:`Doctrine_Manager`.

First lets write a sample hydrator class:

::

    class Doctrine_Hydrator_MyHydrator extends Doctrine_Hydrator_Abstract
    {
        public function hydrateResultSet($stmt)
        {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // do something to with $data
            return $data;
        }
    }

To use it make sure we register it with :php:class:`Doctrine_Manager`:

::

    // bootstrap.php

    // ...
    $manager->registerHydrator('my_hydrator', 'Doctrine_Hydrator_MyHydrator');

Now when you execute your queries you can pass ``my_hydrator`` and it
will use your class to hydrate the data.

::

    $q->execute(array(), 'my_hydrator');
