..  vim: set ts=4 sw=4 tw=79 :

*********************
Improving Performance
*********************

============
Introduction
============

Performance is a very important aspect of all medium to large sized
applications. Doctrine is a large abstraction library that provides a database
abstraction layer as well as object-relational mapping. While this provides a
lot of benefits like portability and ease of development it's inevitable that
this leads to drawbacks in terms of performance.  This chapter tries to help
you to get the best performance out of Doctrine.

.. _improving-performance-compile:

=======
Compile
=======

Doctrine is quite big framework and usually dozens of files are being included
on each request. This brings a lot of overhead. In fact these file operations
are as time consuming as sending multiple queries to database server. The clean
separation of class per file works well in developing environment, however when
project goes commercial distribution the speed overcomes the clean separation
of class per file convention.

Doctrine offers method called :php:meth:`compile` to solve this issue. The
compile method makes a single file of most used Doctrine components which can
then be included on top of your script. By default the file is created into
Doctrine root by the name :file:`Doctrine.compiled.php`.

Compiling is a method for making a single file of most used doctrine runtime
components including the compiled file instead of multiple files (in worst
cases dozens of files) can improve performance by an order of magnitude. In
cases where this might fail, a :php:class:`Doctrine_Exception` is throw detailing the
error.

Lets create a compile script named :file:`compile.php` to handle the
compiling of DoctrineL:

::

    // compile.php

    require_once( '/path/to/doctrine/lib/Doctrine.php' );
    spl_autoload_register( array( 'Doctrine', 'autoload' ) );

    Doctrine_Core::compile( 'Doctrine.compiled.php' );

Now we can execute :file:`compile.php` and a file named
:file:`Doctrine.compiled.php` will be generated in the root of your
``doctrine_test`` folder:

.. code-block:: sh

    $ php compile.php

If you wish to only compile in the database drivers you are using you
can pass an array of drivers as the second argument to :php:meth:`compile`.
For this example we are only using MySQL so lets tell Doctrine to only
compile the ``mysql`` driver:

::

    // compile.php

    // ...
    Doctrine_Core::compile( 'Doctrine.compiled.php', array( 'mysql' ) );

Now you can change your :file:`bootstrap.php` script to include the compiled
Doctrine:

::

    // bootstrap.php

    // ...
    require_once( 'Doctrine.compiled.php' );
    // ...

=====================
Conservative Fetching
=====================

Maybe the most important rule is to be conservative and only fetch the data you
actually need. This may sound trivial but laziness or lack of knowledge about
the possibilities that are available often lead to a lot of unnecessary
overhead.

Take a look at this example:

::

    $record = $table->find( $id );

How often do you find yourself writing code like that? It's convenient but it's
very often not what you need. The example above will pull all columns of the
record out of the database and populate the newly created object with that
data. This not only means unnecessary network traffic but also means that
Doctrine has to populate data into objects that is never used.

I'm sure you all know why a query like the following is not ideal:

.. code-block:: sql

    SELECT
    *
    FROM my_table

The above is bad in any application and this is also true when using Doctrine.
In fact it's even worse when using Doctrine because populating objects with
data that is not needed is a waste of time.

Another important rule that belongs in this category is: **Only fetch objects
when you really need them**. Doctrine has the ability to fetch "array graphs"
instead of object graphs. At first glance this may sound strange because why
use an object-relational mapper in the first place then? Take a second to think
about it. PHP is by nature a precedural language that has been enhanced with a
lot of features for decent OOP.  Arrays are still the most efficient data
structures you can use in PHP.  Objects have the most value when they're used
to accomplish complex business logic. It's a waste of resources when data gets
wrapped in costly object structures when you have no benefit of that. Take a
look at the following code that fetches all comments with some related data for
an article, passing them to the view for display afterwards:

::

    $q = Doctrine_Query::create()
        ->select( 'b.title, b.author, b.created_at' )
        ->addSelect( 'COUNT(t.id) as num_comments' )
        ->from( 'BlogPost b' )
        ->leftJoin( 'b.Comments c' )
        ->where( 'b.id = ?' )
        ->orderBy( 'b.created_at DESC' );

    $blogPosts = $q->execute( array( 1 ) );

Now imagine you have a view or template that renders the most recent
blog posts:

.. code-block:: html+php

    <?php foreach ( $blogPosts as $blogPost ): ?>
        <li>
            <strong>
                <?php echo $blogPost['title'] ?>
            </strong>

            - Posted on <?php echo $blogPost['created_at'] ?>
            by <?php echo $blogPost['author'] ?>.

            <small>
                (<?php echo $blogPost['num_comments'] ?>)
            </small>
        </li>
    <?php endforeach; ?>

Can you think of any benefit of having objects in the view instead of
arrays? You're not going to execute business logic in the view, are you?
One parameter can save you a lot of unnecessary processing:

::

    $blogPosts = $q->execute( array( 1 ), Doctrine_Core::HYDRATE_ARRAY );

If you prefer you can also use the :php:meth:`setHydrationMethod` method:

::

    $q->setHydrationMode( Doctrine_Core::HYDRATE_ARRAY );
    $blogPosts = $q->execute( array( 1 ) );

The above code will hydrate the data into arrays instead of objects
which is much less expensive.

.. note::

    One great thing about array hydration is that if you use the
    :php:class:`ArrayAccess` on your objects you can easily switch your queries to use
    array hydration and your code will work exactly the same. For example the
    above code we wrote to render the list of the most recent blog posts would
    work when we switch the query behind it to array hydration.

Sometimes, you may want the direct output from PDO instead of an object or an
array. To do this, set the hydration mode to
:php:const:`Doctrine_Core::HYDRATE_NONE`. Here's an example:

::

    $q = Doctrine_Query::create()
        ->select( 'SUM(d.amount)' )
        ->from( 'Donation d' );

    $results = $q->execute( array(), Doctrine_Core::HYDRATE_NONE );

You will need to print the results and find the value in the array
depending on your DQL query:

::

    print_r( $results );

In this example the result would be accessible with the following code:

::

    $total = $results[0][1];

.. tip::

    There are two important differences between ``HYDRATE_ARRAY`` and
    ``HYDRATE_NONE`` which you should consider before choosing which to use.
    ``HYDRATE_NONE`` is the fastest but the result is an array with numeric
    keys and so results would be referenced as ``$result[0][0]`` instead of
    ``$result[0]['my_field']`` with ``HYDRATE_ARRAY``. Best practice would to
    use ``HYDRATE_NONE`` when retrieving large record sets or when doing many
    similar queries. Otherwise, ``HYDRATE_ARRAY`` is more comfortable and
    should be preferred.

=======================
Bundle your Class Files
=======================

When using Doctrine or any other large OO library or framework the number of
files that need to be included on a regular HTTP request rises significantly.
50-100 includes per request are not uncommon. This has a significant
performance impact because it results in a lot of disk operations. While this
is generally no issue in a dev environment, it's not suited for production. The
recommended way to handle this problem is to bundle the most-used classes of
your libraries into a single file for production, stripping out any unnecessary
whitespaces, linebreaks and comments. This way you get a significant
performance improvement even without a bytecode cache (see next section). The
best way to create such a bundle is probably as part of an automated build
process i.e. with Phing.

====================
Use a Bytecode Cache
====================

A bytecode cache like APC will cache the bytecode that is generated by php
prior to executing it. That means that the parsing of a file and the creation
of the bytecode happens only once and not on every request.  This is especially
useful when using large libraries and/or frameworks.  Together with file
bundling for production this should give you a significant performance
improvement. To get the most out of a bytecode cache you should contact the
manual pages since most of these caches have a lot of configuration options
which you can tweak to optimize the cache to your needs.

============
Free Objects
============

As of version 5.2.5, PHP is not able to garbage collect object graphs that have
circular references, e.g. Parent has a reference to Child which has a reference
to Parent. Since many doctrine model objects have such relations, PHP will not
free their memory even when the objects go out of scope.

For most PHP applications, this problem is of little consequence, since PHP
scripts tend to be short-lived. Longer-lived scripts, e.g. bulk data importers
and exporters, can run out of memory unless you manually break the circular
reference chains. Doctrine provides a :php:meth:`free` function on
:php:class:`Doctrine_Record`, :php:class:`Doctrine_Collection`, and
:php:class:`Doctrine_Query` which eliminates the circular references on those
objects, freeing them up for garbage collection. Usage might look like:

Free objects when mass inserting records:

::

    for ( $i = 0; $i < 1000; $i++ )
    {
        $object = createBigObject();
        $object->save();
        $object->free( true );
    }

You can also free query objects in the same way:

::

    for ( $i = 0; $i < 1000; $i++ )
    {
        $q = Doctrine_Query::create()
            ->from( 'User u' );

        $results = $q->fetchArray();
        $q->free();
    }

Or even better if you can reuse the same query object for each query in
the loop that would be ideal:

::

    $q = Doctrine_Query::create()
        ->from('User u');

    for ( $i = 0; $i < 1000; $i++ )
    {
        $results = $q->fetchArray();
        $q->free();
    }

==========
Other Tips
==========

* Helping the DQL parser

    There are two possible ways when it comes to using DQL. The first one is
    writing the plain DQL queries and passing them to
    ``Doctrine_Connection::query( $dql )``. The second one is to use a
    :php:class:`Doctrine_Query` object and its fluent interface. The latter should
    be preferred for all but very simple queries. The reason is that using the
    :php:class:`Doctrine_Query` object and it's methods makes the life of the DQL
    parser a little bit easier. It reduces the amount of query parsing that needs
    to be done and is therefore faster.

* Efficient relation handling

    When you want to add a relation between two components you should not do something like the following:

    .. note::

        The following example assumes a many-many between ``Role`` and ``User``.

    ::

        $role = new Role();
        $role->name = 'New Role Name';

        $user->Roles[] = $newRole;

    .. caution::

        The above code will load all roles of the user from the
        database if they're not yet loaded! Just to add one new link!

    The following is the recommended way instead:

    ::

        $userRole          = new UserRole();
        $userRole->role_id = $role_id;
        $userRole->user_id = $user_id;
        $userRole->save();

==========
Conclusion
==========

Lots of methods exist for improving performance in Doctrine. It is highly
recommended that you consider some of the methods described above.

Now lets move on to learn about some of the :doc:`technology`
used in Doctrine.