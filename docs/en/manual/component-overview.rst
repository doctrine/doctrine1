..  vim: set ts=4 sw=4 tw=4 :

******************
Component Overview
******************

This chapter is intended to give you a birds eye view of all the main
components that make up Doctrine and how they work together. We've
discussed most of the components in the previous chapters but after this
chapter you will have a better idea of all the components and what their
jobs are.

.. _component-overview-manager:

=======
Manager
=======

The :php:class:`Doctrine_Manager` class is a singleton and is the root of the
configuration hierarchy and is used as a facade for controlling several
aspects of Doctrine. You can retrieve the singleton instance with the
following code.

::

    $manager = Doctrine_Manager::getInstance();

----------------------
Retrieving Connections
----------------------

::

    $connections = $manager->getConnections();
    foreach (connections as $connection) {
        echo $connection->getName() . "";
    }


The :php:class:`Doctrine_Manager` implements an iterator so you can simple loop
over the $manager variable to loop over the connections.

::

    foreach ($manager as $connection) {
        echo $connection->getName() . "";
    }

.. _component-overview-connection:

==========
Connection
==========

:php:class:`Doctrine_Connection` is a wrapper for database connection. The
connection is typically an instance of PDO but because of how Doctrine
is designed, it is possible to design your own adapters that mimic the
functionality that PDO provides.

The :php:class:`Doctrine_Connection` class handles several things:

*  Handles database portability things missing from PDO (eg. LIMIT /
   OFFSET emulation)
*  Keeps track of :php:class:`Doctrine_Table` objects
*  Keeps track of records
*  Keeps track of records that need to be updated / inserted / deleted
*  Handles transactions and transaction nesting
*  Handles the actual querying of the database in the case of INSERT /
   UPDATE / DELETE operations
*  Can query the database using DQL. You will learn more about DQL in
   the :doc:`dql-doctrine-query-language` chapter.
*  Optionally validates transactions using Doctrine_Validator and gives
   full information of possible errors.

-----------------
Available Drivers
-----------------

Doctrine has drivers for every PDO-supported database. The supported
databases are:

*  FreeTDS / Microsoft SQL Server / Sybase
*  Firebird/Interbase 6
*  Informix
*  Mysql
*  Oracle
*  Odbc
*  PostgreSQL
*  Sqlite

--------------------
Creating Connections
--------------------

::

    // bootstrap.php
    $conn = Doctrine_Manager::connection('mysql://username:password@localhost/test', 'connection 1');

.. note::

    We have already created a new connection in the previous chapters. You can
    skip the above step and use the connection we've already created. You can
    retrieve it by using the :php:meth:`Doctrine_Manager::connection` method.

-----------------------
Flushing the Connection
-----------------------

When you create new ``User`` records you can flush the connection and save all
un-saved objects for that connection. Below is an example::

    $conn = Doctrine_Manager::connection();

    $user1 = new User();
    $user1->username = 'Jack';

    $user2 = new User();
    $user2->username = 'jwage';

    $conn->flush();

Calling :php:meth:`Doctrine_Connection::flush` will save all unsaved record
instances for that connection. You could of course optionally call
:php:meth:`save` on each record instance and it would be the same thing.

::

    $user1->save();
    $user2->save();

.. _component-overview-table:

=====
Table
=====

:php:class:`Doctrine_Table` holds the schema information specified by the given
component (record). For example if you have a ``User`` class that extends
:php:class:`Doctrine_Record`, each schema definition call gets delegated to a
unique table object that holds the information for later use.

Each :php:class:`Doctrine_Table` is registered by
:php:class:`Doctrine_Connection`. You can retrieve the table object for each
component easily which is demonstrated right below.

For example, lets say we want to retrieve the table object for the User class.
We can do this by simply giving ``User`` as the first argument for the
:php:meth:`Doctrine_Core::getTable` method.

----------------------
Getting a Table Object
----------------------

In order to get table object for specified record just call
:php:meth:`Doctrine_Record::getTable`.

::

    // test.php
    $accountTable = Doctrine_Core::getTable('Account');

--------------------------
Getting Column Information
--------------------------

You can retrieve the column definitions set in :php:class:`Doctrine_Record` by
using the appropriate :php:class:`Doctrine_Table` methods. If you need all
information of all columns you can simply use::

    // test.php
    $columns = $accountTable->getColumns();

    foreach ($columns as $column) {
        print_r($column);
    }

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Array (
        [type] => integer
        [length] => 20
        [autoincrement] => 1
        [primary] => 1
    )
    Array (
        [type] => string
        [length] => 255
    )
    Array (
        [type] => decimal
        [length] => 18
    )

Sometimes this can be an overkill. The following example shows how to
retrieve the column names as an array::

    // test.php
    $names = $accountTable->getColumnNames();
    print_r($names);

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Array (
        [0] => id
        [1] => name
        [2] => amount
    )

----------------------------
Getting Relation Information
----------------------------

You can also get an array of all the ``Doctrine_Relation`` objects by
simply calling :php:meth:`Doctrine_Table::getRelations` like the following::

    // test.php
    $userTable = Doctrine_Core::getTable('User');
    $relations = $userTable->getRelations();
    foreach ($relations as $name => $relation) {
        echo $name . ":\n";
        echo "Local - " . $relation->getLocal() . "\n";
        echo "Foreign - " .    $relation->getForeign() . "\n\n";
    }

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Email:
    Local - id
    Foreign - user_id

    Phonenumbers:
    Local - id
    Foreign - user_id

    Groups:
    Local - user_id
    Foreign - group_id

    Friends:
    Local - user1
    Foreign - user2

    Addresses:
    Local - id
    Foreign - user_id

    Threads:
    Local - id
    Foreign - user_id

You can get the ``Doctrine_Relation`` object for an individual relationship by
using the :php:meth:`Doctrine_Table::getRelation` method.

::

    // test.php
    $relation = $userTable->getRelation('Phonenumbers');

    echo 'Name: ' . $relation['alias'] . "\n";
    echo 'Local - ' . $relation['local'] . "\n";
    echo 'Foreign - ' .  $relation['foreign'] . "\n";
    echo 'Relation Class - ' . get_class($relation);

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Name: Phonenumbers
    Local - id
    Foreign - user_id
    Relation Class - Doctrine_Relation_ForeignKey

.. note::

    Notice how in the above examples the ``$relation`` variable
    holds an instance of ``Doctrine_Relation_ForeignKey`` yet we can
    access it like an array. This is because, like many Doctrine
    classes, it implements ``ArrayAccess``.

You can debug all the information of a relationship by using the
:php:meth:`toArray` method and using :php:meth:`print_r` to inspect it.

::

    $array = $relation->toArray();
    print_r($array);

--------------
Finder Methods
--------------

:php:class:`Doctrine_Table` provides basic finder methods. These finder methods
are very fast to write and should be used if you only need to fetch data
from one database table. If you need queries that use several components
(database tables) use :php:meth:`Doctrine_Connection::query`.

You can easily find an individual user by its primary key by using the
:php:meth:`find` method::

    $user = $userTable->find(2);
    print_r($user->toArray());

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Array (
        [id] => 2
        [is_active] => 1
        [is_super_admin] => 0
        [first_name] =>
        [last_name] =>
        [username] => jwage
        [password] =>
        [type] =>
        [created_at] => 2009-01-21 13:29:12
        [updated_at] => 2009-01-21 13:29:12
    )

You can also use the :php:meth:`findAll` method to retrieve a collection of
all ``User`` records in the database::

    foreach ($userTable->findAll() as $user) {
        echo $user->username . "\n";
    }

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Jack
    jwage

.. caution::

    The :php:meth:`findAll` method is not recommended as it will
    Return all records in the database and if you need to retrieve
    information from relationships it will lazily load that data causing
    high query counts. You can learn how to retrieve records and their related
    records efficiently by reading the :doc:`dql-doctrine-query-language`
    chapter.

You can also retrieve a set of records with a DQL where condition by
using the :php:meth:`findByDql` method::

    $users = $userTable->findByDql('username LIKE ?', '%jw%');

    foreach($users as $user) {
        echo $user->username . "";
    }

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    jwage

Doctrine also offers some additional magic finder methods that can be
read about in the [doc dql-doctrine-query-language:magic-finders :name]
section of the DQL chapter.

.. note::

    All of the finders below provided by :php:class:`Doctrine_Table`
    use instances of :php:class:`Doctrine_Query` for executing the queries. The
    objects are built dynamically internally and executed.

    Using :php:class:`Doctrine_Query` instances are highly recommend when
    accessing multiple objects through relationships. If you don't you will
    have high query counts as the data will be lazily loaded. You can read more
    about this in the :doc:`dql-doctrine-query-language` chapter.


^^^^^^^^^^^^^^^^^^^^
Custom Table Classes
^^^^^^^^^^^^^^^^^^^^

Adding custom table classes is very easy. Only thing you need to do is name the
classes as ``[componentName]Table`` and make them extend
:php:class:`Doctrine_Table`. So for the ``User`` model we would create a class
like the following::

    // models/UserTable.php
    class UserTable extends Doctrine_Table
    {
    }

--------------
Custom Finders
--------------

You can add custom finder methods to your custom table object. These
finder methods may use fast :php:class:`Doctrine_Table` finder methods or [doc
dql-doctrine-query-language DQL API] (:php:meth:`Doctrine_Query::create`).

::

    // models/UserTable.php
    class UserTable extends Doctrine_Table
    {
        public function findByName($name)
        {
            return Doctrine_Query::create()
                    ->from('User u')
                    ->where('u.name LIKE ?', "%$name%")
                    ->execute();
        }
    }

Doctrine will check if a child :php:class:`Doctrine_Table` class called
``UserTable`` exists when calling :php:meth:`getTable` and if it does, it will
return an instance of that instead of the default :php:class:`Doctrine_Table`.

.. note::

    In order for custom {{Doctrine_Table}} classes to be
    loaded you must enable the ``autoload_table_classes`` attribute in
    your :file:`bootstrap.php` file like done below.

    ::

        // boostrap.php
        // ...
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);

Now when we ask for the ``User`` table object we will get the following::

    $userTable = Doctrine_Core::getTable('User');

    echo get_class($userTable); // UserTable

    $users = $userTable->findByName("Jack");

.. note::

    The above example where we add a {{findByName()}} method is
    made possible automatically by the magic finder methods. You can
    read about them in the [doc
    dql-doctrine-query-language:magic-finders :name] section of the DQL
    chapter.

.. _component-overview-record:

======
Record
======

Doctrine represents tables in your RDBMS with child
:php:class:`Doctrine_Record` classes. These classes are where you define your
schema information, options, attributes, etc. Instances of these child classes
represents records in the database and you can get and set properties on these
objects.

----------
Properties
----------

Each assigned column property of :php:class:`Doctrine_Record` represents a
database table column. You will learn more about how to define your models in
the :doc:`defining-models` chapter.

Now accessing the columns is easy::

    // test.php
    $userTable = Doctrine_Core::getTable('User');
    $user = $userTable->find(1);

* Access property through overloading::

    // ...
    echo $user->username;

* Access property with get()::

    // ...
    echo $user->get('username);

* Access property with ArrayAccess::

    // ...
    echo $user['username'];

.. tip::

    The recommended way to access column values is by using the
    ArrayAccess as it makes it easy to switch between record and array
    fetching when needed.

Iterating through the properties of a record can be done in similar way
as iterating through an array - by using the ``foreach`` construct. This
is possible since :php:class:`Doctrine_Record` implements a magic
``IteratorAggregate`` interface.

::

    foreach ($user as $field => $value) {
        echo $field . ': ' . $value . "";
    }

As with arrays you can use the :php:meth:`isset` for checking if given
property exists and :php:meth:`unset` for setting given property to null.

We can easily check if a property named 'name' exists in a if conditional::

    if (isset($user['username'])) {
    }

If we want to unset the name property we can do it using the :php:meth:`unset`
function in php::

    unset($user['username']);

When you have set values for record properties you can get an array of
the modified fields and values using :php:meth:`Doctrine_Record::getModified`::

    // test.php
    $user['username'] = 'Jack Daniels';
    print_r($user->getModified());

The above example would output the following when executed:

.. code-block:: text

    $ php test.php
    Array (
        [username] => Jack Daniels
    )

You can also simply check if a record is modified by using the
:php:meth:`Doctrine_Record::isModified` method::

    echo $user->isModified() ? 'Modified' : 'Not Modified';

Sometimes you may want to retrieve the column count of given record. In
order to do this you can simply pass the record as an argument for the
:php:meth:`count` function. This is possible since :php:class:`Doctrine_Record`
implements a magic Countable interface. The other way would be calling
the :php:meth:`count` method.

::

    echo $record->count();
    echo count($record);

:php:class:`Doctrine_Record` offers a special method for accessing the
identifier of given record. This method is called :php:meth:`identifier` and
it returns an array with identifier field names as keys and values as
the associated property values.

::

    $user['username'] = 'Jack Daniels';
    $user->save();

    print_r($user->identifier()); // array('id' => 1)

A common case is that you have an array of values which you need to
assign to a given record. It may feel awkward and clumsy to set these
values separately. No need to worry though, :php:class:`Doctrine_Record` offers
a way for merging a given array or record to another

The :php:meth:`merge` method iterates through the properties of the given
record or array and assigns the values to the object

::

    $values = array(
        'username' => 'someone',
        'age' => 11,
    );

    $user->merge($values);

    echo $user->username; // someone
    echo $user->age; // 11

You can also merge a one records values in to another like the
following::

    $user1 = new User();
    $user1->username = 'jwage';

    $user2 = new User();
    $user2->merge($user1);

    echo $user2->username; // jwage

.. note::

    :php:class:`Doctrine_Record` also has a :php:meth:`fromArray` method
    which is identical to :php:meth:`merge` and only exists for consistency
    with the :php:meth:`toArray` method.

----------------
Updating Records
----------------

Updating objects is very easy, you just call the
:php:meth:`Doctrine_Record::save` method. The other way is to call
:php:meth:`Doctrine_Connection::flush` which saves all objects. It should be
noted though that flushing is a much heavier operation than just calling
save method.

::

    $userTable = Doctrine_Core::getTable('User');
    $user = $userTable->find(2);

    if ($user !== false) {
        $user->username = 'Jack Daniels';
        $user->save();
    }

Sometimes you may want to do a direct update. In direct update the
objects aren't loaded from database, rather the state of the database is
directly updated. In the following example we use DQL UPDATE statement
to update all users.

Run a query to make all user names lowercase::

    $q = Doctrine_Query::create()
            ->update('User u')
            ->set('u.username', 'LOWER(u.name)');

    $q->execute();

You can also run an update using objects if you already know the identifier of
the record. When you use the :php:meth:`Doctrine_Record::assignIdentifier`
method it sets the record identifier and changes the state so that calling
:php:meth:`Doctrine_Record::save` performs an update instead of insert.

::

    $user = new User();
    $user->assignIdentifier(1);
    $user->username = 'jwage';
    $user->save();

-----------------
Replacing Records
-----------------

Replacing records is simple. If you instantiate a new object and save it
and then late instantiate another new object with the same primary key
or unique index value which already exists in the database, then it will
replace/update that row in the database instead of inserting a new one.
Below is an example.

First, imagine a ``User`` model where username is a unique index.

::

    // test.php
    $user = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();

Issues the following query

.. code-block:: sql

    INSERT INTO user
        (username, password)
    VALUES
        (?, ?),
        ('jwage', 'changeme')

Now lets create another new object and set the same username but a
different password.

::

    $user = new User();
    $user->username = 'jwage';
    $user->password = 'newpassword';
    $user->replace();

Issues the following query

.. code-block:: sql

    REPLACE INTO user
        (id, username, password)
    VALUES
        (?, ?, ?),
        (null, 'jwage', 'newpassword')

The record is replaced/updated instead of a new one being inserted

------------------
Refreshing Records
------------------

Sometimes you may want to refresh your record with data from the
database, use :php:meth:`Doctrine_Record::refresh`.

::

    $user = Doctrine_Core::getTable('User')->find(2);
    $user->username = 'New name';

Now if you use the :php:meth:`Doctrine_Record::refresh` method it will select
the data from the database again and update the properties of the
instance.

::

    $user->refresh();

------------------------
Refreshing relationships
------------------------

The :php:meth:`Doctrine_Record::refresh` method can also refresh the already
loaded record relationships, but you need to specify them on the
original query.

First lets retrieve a ``User`` with its associated ``Groups``::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Groups')
            ->where('id = ?');

    $user = $q->fetchOne(array(1));

Now lets retrieve a ``Group`` with its associated ``Users``::

    $q = Doctrine_Query::create()
            ->from('Group g')
            ->leftJoin('g.Users')
            ->where('id = ?');

    $group = $q->fetchOne(array(1));

Now lets link the retrieved ``User`` and ``Group`` through a
``UserGroup`` instance::

    $userGroup = new UserGroup();
    $userGroup->user_id = $user->id;
    $userGroup->group_id = $group->id;
    $userGroup->save();

You can also link a ``User`` to a ``Group`` in a much simpler way, by
simply adding the ``Group`` to the ``User``. Doctrine will take care of
creating the ``UserGroup`` instance for you automatically::

    $user->Groups[] = $group;
    $user->save()

Now if we call ``Doctrine_Record::refresh(true)`` it will refresh the
record and its relationships loading the newly created reference we made
above::

    $user->refresh(true);
    $group->refresh(true);

You can also lazily refresh all defined relationships of a model using
:php:meth:`Doctrine_Record::refreshRelated`::

    $user = Doctrine_Core::getTable('User')->findOneByName('jon');
    $user->refreshRelated();

If you want to refresh an individual specified relationship just pass
the name of a relationship to the :php:meth:`refreshRelated` function and it
will lazily load the relationship::

    $user->refreshRelated('Phonenumber');

----------------
Deleting Records
----------------

Deleting records in Doctrine is handled by :php:meth:`Doctrine_Record::delete`,
:php:meth:`Doctrine_Collection::delete` and
:php:meth:`Doctrine_Connection::delete` methods.

::

    $userTable = Doctrine_Core::getTable("User");

    $user = $userTable->find(2);

    // deletes user and all related composite objects
    if($user !== false) {
        $user->delete();
    }

If you have a :php:class:`Doctrine_Collection` of ``User`` records you can call
:php:meth:`delete` and it will loop over all records calling
:php:meth:`Doctrine_Record::delete` for you.

::

    $users = $userTable->findAll();

Now you can delete all users and their related composite objects by calling
:php:meth:`Doctrine_Collection::delete`. It will loop over all ``Users`` in the
collection calling delete one each one::

    $users->delete();

.. _component-overview-using-expression-values:

-----------------------
Using Expression Values
-----------------------

There might be situations where you need to use SQL expressions as
values of columns. This can be achieved by using
``Doctrine_Expression`` which converts portable DQL expressions to your
native SQL expressions.

Lets say we have a class called event with columns
``timepoint(datetime)`` and ``name(string)``. Saving the record with the
current timestamp can be achieved as follows::

    // test.php
    $user = new User();
    $user->username = 'jwage';
    $user->updated_at = new Doctrine_Expression('NOW()');
    $user->save();

The above code would issue the following SQL query:

.. code-block:: sql

    INSERT INTO user (username, updated_at) VALUES ('jwage', NOW())

.. tip::

    When you use ``Doctrine_Expression`` with your objects in
    order to get the updated value you will have to manually call
    :php:meth:`refresh` to get the updated value from the database.

    ::

        $user->refresh();

--------------------
Getting Record State
--------------------

Every :php:class:`Doctrine_Record` has a state. First of all records can be
transient or persistent. Every record that is retrieved from database is
persistent and every newly created record is considered transient. If a
:php:class:`Doctrine_Record` is retrieved from database but the only loaded
property is its primary key, then this record has a state called proxy.

Every transient and persistent :php:class:`Doctrine_Record` is either clean or
dirty. :php:class:`Doctrine_Record` is clean when none of its properties are
changed and dirty when at least one of its properties has changed.

A record can also have a state called locked. In order to avoid infinite
recursion in some rare circular reference cases Doctrine uses this state
internally to indicate that a record is currently under a manipulation
operation.

Below is a table containing all the different states a record can be in
with a short description of it:

==========================================  ===================================
Name                                        Description
==========================================  ===================================
:php:const:`Doctrine_Record::STATE_PROXY`   Record is in proxy state meaning
                                            its persistent but not all of its
                                            properties are loaded from the
                                            database.
:php:const:`Doctrine_Record::STATE_TCLEAN`  Record is transient clean, meaning
                                            its transient and none of its
                                            properties are changed.
:php:const:`Doctrine_Record::STATE_TDIRTY`  Record is transient dirty, meaning
                                            its transient and some of its
                                            properties are changed.
:php:const:`Doctrine_Record::STATE_DIRTY`   Record is dirty, meaning its
                                            persistent and some of its
                                            properties are changed.
:php:const:`Doctrine_Record::STATE_CLEAN`   Record is clean, meaning its
                                            persistent and none of its
                                            properties are changed.
:php:const:`Doctrine_Record::STATE_LOCKED`  Record is locked.
==========================================  ===================================

You can easily get the state of a record by using the
:php:meth:`Doctrine_Record::state` method::

    $user = new User();

    if ($user->state() == Doctrine_Record::STATE_TDIRTY) {
        echo 'Record is transient dirty';
    }

.. note::

    values specified in the schema. If we use an object that has no
    default values and instantiate a new instance it will return
    ``TCLEAN``.

::

    $account = new Account();

    if ($account->state() == Doctrine_Record::STATE_TCLEAN) {
        echo 'Record is transient clean';
    }

-------------------
Getting Object Copy
-------------------

Sometimes you may want to get a copy of your object (a new object with
all properties copied). Doctrine provides a simple method for this:
:php:meth:`Doctrine_Record::copy`.

::

    $copy = $user->copy();

Notice that copying the record with :php:meth:`copy` returns a new record
(state ``TDIRTY``) with the values of the old record, and it copies the
relations of that record. If you do not want to copy the relations too,
you need to use ``copy(false)``.

Get a copy of user without the relations::

    $copy = $user->copy(false);

Using the PHP ``clone`` functionality simply uses this :php:meth:`copy`
functionality internally::

    $copy = clone $user;

---------------------
Saving a Blank Record
---------------------

By default Doctrine doesn't execute when :php:meth:`save` is being called on
an unmodified record. There might be situations where you want to
force-insert the record even if it has not been modified. This can be
achieved by assigning the state of the record to
:php:const:`Doctrine_Record::STATE_TDIRTY`::

    $user = new User();
    $user->state('TDIRTY');
    $user->save();

.. note::

    When setting the state you can optionally pass a string for
    the state and it will be converted to the appropriate state
    constant. In the example above, ``TDIRTY`` is actually converted to
    :php:const:`Doctrine_Record::STATE_TDIRTY`.

---------------------
Mapping Custom Values
---------------------

There might be situations where you want to map custom values to
records. For example values that depend on some outer sources and you
only want these values to be available at runtime not persisting those
values into database. This can be achieved as follows::

    $user->mapValue('isRegistered', true);

    $user->isRegistered; // true

-----------
Serializing
-----------

Sometimes you may want to serialize your record objects (possibly for
caching purposes)::

    $string = serialize($user);

    $user = unserialize($string);

------------------
Checking Existence
------------------

Very commonly you'll need to know if given record exists in the
database. You can use the :php:meth:`exists` method for checking if given
record has a database row equivalent::

    $record = new User();

    echo $record->exists() ? 'Exists' : 'Does Not Exist'; // Does Not Exist

    $record->username = 'someone'; $record->save();

    echo $record->exists() ? 'Exists' : 'Does Not Exist'; // Exists

------------------------------
Function Callbacks for Columns
------------------------------

:php:class:`Doctrine_Record` offers a way for attaching callback calls for
column values. For example if you want to trim certain column, you can
simply use::

    $record->call('trim', 'username');

.. _component-overview-collection:

==========
Collection
==========

:php:class:`Doctrine_Collection` is a collection of records (see
Doctrine_Record). As with records the collections can be deleted and
saved using :php:meth:`Doctrine_Collection::delete` and
:php:meth:`Doctrine_Collection::save` accordingly.

When fetching data from database with either DQL API (see
:php:class:`Doctrine_Query`) or rawSql API (see ``Doctrine_RawSql``) the
methods return an instance of :php:class:`Doctrine_Collection` by default.

The following example shows how to initialize a new collection::

    $users = new Doctrine_Collection('User');

Now add some new data to the collection::

    $users[0]->username = 'Arnold';
    $users[1]->username = 'Somebody';

Now just like we can delete a collection we can save it::

    $users->save();

------------------
Accessing Elements
------------------

You can access the elements of :php:class:`Doctrine_Collection` with
:php:meth:`set` and :php:meth:`get` methods or with ArrayAccess interface.

::

    $userTable = Doctrine_Core::getTable('User');
    $users = $userTable->findAll();

* Accessing elements with ArrayAccess interface::

    $users[0]->username = "Jack Daniels";
    $users[1]->username = "John Locke";

* Accessing elements with :php:meth:`get`::

    echo $users->get(1)->username;

-------------------
Adding new Elements
-------------------

When accessing single elements of the collection and those elements
(records) don't exist Doctrine auto-adds them.

In the following example we fetch all users from database (there are 5)
and then add couple of users in the collection.

As with PHP arrays the indexes start from zero.

::

    // test.php
    $users = $userTable->findAll();

    echo count($users); // 5

    $users[5]->username = "new user 1";
    $users[6]->username = "new user 2";

You could also optionally omit the 5 and 6 from the array index and it
will automatically increment just as a PHP array would::

    $users[]->username = 'new user 3'; // key is 7
    $users[]->username = 'new user 4'; // key is 8

------------------------
Getting Collection Count
------------------------

The :php:meth:`Doctrine_Collection::count` method returns the number of
elements currently in the collection::

    $users = $userTable->findAll();

    echo $users->count();

Since :php:class:`Doctrine_Collection` implements Countable interface a valid
alternative for the previous example is to simply pass the collection as
an argument for the count() function::

    echo count($users);

---------------------
Saving the Collection
---------------------

Similar to :php:class:`Doctrine_Record` the collection can be saved by calling
the :php:meth:`save` method. When :php:meth:`save` gets called Doctrine issues
:php:meth:`save` operations an all records and wraps the whole procedure in a
transaction.

::

    $users = $userTable->findAll();

    $users[0]->username = 'Jack Daniels';
    $users[1]->username = 'John Locke';

    $users->save();

-----------------------
Deleting the Collection
-----------------------

Doctrine Collections can be deleted in very same way is Doctrine Records
you just call :php:meth:`delete` method. As for all collections Doctrine knows
how to perform single-shot-delete meaning it only performs one database
query for the each collection.

For example if we have collection of users. When deleting the collection
of users doctrine only performs one query for this whole transaction.
The query would look something like:

.. code-block:: sql

    DELETE FROM user WHERE id IN (1, 2, 3, ... , N)

-----------
Key Mapping
-----------

Sometimes you may not want to use normal indexing for collection elements. For
example in some cases mapping primary keys as collection keys might be useful.
The following example demonstrates how this can be achieved.

* Map the ``id`` column::

    // test.php
    $userTable = Doctrine_Core::getTable('User');
    $userTable->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'id');

* Now user collections will use the values of id column as element indexes::

    $users = $userTable->findAll();

    foreach($users as $id => $user) {
        echo $id . $user->username;
    }

* You may want to map the ``name`` column::

    $userTable = Doctrine_Core::getTable('User');
    $userTable->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'username');

* Now user collections will use the values of ``name`` column as element
  indexes::

    $users = $userTable->findAll();

    foreach($users as $username => $user) {
        echo $username . ' - ' . $user->created_at . "";
    }

.. caution::

    Note this would only be advisable if the ``username`` column is specified
    as unique in your schema otherwise you will have cases where data cannot be
    hydrated properly due to duplicate collection keys.

-----------------------
Loading Related Records
-----------------------

Doctrine provides means for efficiently retrieving all related records
for all record elements. That means when you have for example a
collection of users you can load all phonenumbers for all users by
simple calling the :php:meth:`loadRelated` method.

However, in most cases you don't need to load related elements
explicitly, rather what you should do is try to load everything at once
by using the DQL API and JOINS.

The following example uses three queries for retrieving users, their
phonenumbers and the groups they belong to.

::

    $q = Doctrine_Query::create()
            ->from('User u');

    $users = $q->execute();

Now lets load phonenumbers for all users::

    $users->loadRelated('Phonenumbers');

    foreach($users as $user) {
        echo $user->Phonenumbers[0]->phonenumber;
        // no additional db queries needed here
    }

The :php:meth:`loadRelated` works an any relation, even associations::

    $users->loadRelated('Groups');

    foreach($users as $user) {
        echo $user->Groups[0]->name;
    }

The example below shows how to do this more efficiently by using the DQL
API.

Write a :php:class:`Doctrine_Query` that loads everything in one query::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Phonenumbers p')
            ->leftJoin('u.Groups g');

    $users = $q->execute();

Now when we use the ``Phonenumbers`` and ``Groups`` no additional database
queries are needed::

    foreach($users as $user) {
        echo $user->Phonenumbers[0]->phonenumber;
        echo $user->Groups[0]->name;
    }

=========
Validator
=========

Validation in Doctrine is a way to enforce your business rules in the model
part of the MVC architecture. You can think of this validation as a gateway
that needs to be passed right before data gets into the persistent data store.
The definition of these business rules takes place at the record level, that
means in your active record model classes (classes derived from
:php:class:`Doctrine_Record`). The first thing you need to do to be able to use
this kind of validation is to enable it globally. This is done through the
:php:class:`Doctrine_Manager`.

::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);

Once you enabled validation, you'll get a bunch of validations
automatically:

*  Data type validations: All values assigned to columns are checked for
   the right type. That means if you specified a column of your record
   as type 'integer', Doctrine will validate that any values assigned to
   that column are of this type. This kind of type validation tries to
   be as smart as possible since PHP is a loosely typed language. For
   example 2 as well as "7" are both valid integers whilst "3f" is not.
   Type validations occur on every column (since every column definition
   needs a type).

*  Length validation: As the name implies, all values assigned to
   columns are validated to make sure that the value does not exceed the
   maximum length.

You can combine the following constants by using bitwise operations:
``VALIDATE_ALL``, ``VALIDATE_TYPES``, ``VALIDATE_LENGTHS``,
``VALIDATE_CONSTRAINTS``, ``VALIDATE_NONE``.

For example to enable all validations except length validations you
would use::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, VALIDATE_ALL & ~VALIDATE_LENGTHS);

You can read more about this topic in the :doc:`data-validation`
chapter.

---------------
More Validation
---------------

The type and length validations are handy but most of the time they're
not enough. Therefore Doctrine provides some mechanisms that can be used
to validate your data in more detail.

Validators are an easy way to specify further validations. Doctrine has
a lot of predefined validators that are frequently needed such as
``email``, ``country``, ``ip``, ``range`` and ``regexp`` validators. You
find a full list of available validators in the [doc data-validation
:name] chapter. You can specify which validators apply to which column
through the 4th argument of the :php:meth:`hasColumn` method. If that is still
not enough and you need some specialized validation that is not yet
available as a predefined validator you have three options:

*  You can write the validator on your own.
*  You can propose your need for a new validator to a Doctrine
   developer.
*  You can use validation hooks.

The first two options are advisable if it is likely that the validation
is of general use and is potentially applicable in many situations. In
that case it is a good idea to implement a new validator. However if the
validation is special it is better to use hooks provided by Doctrine:

*  :php:meth:`validate` (Executed every time the record gets validated)
*  :php:meth:`validateOnInsert` (Executed when the record is new and gets
   validated)
*  :php:meth:`validateOnUpdate` (Executed when the record is not new and gets
   validated)

If you need a special validation in your active record you can simply override
one of these methods in your active record class (a descendant of
:php:class:`Doctrine_Record`). Within these methods you can use all the power
of PHP to validate your fields. When a field does not pass your validation you
can then add errors to the record's error stack. The following code snippet
shows an example of how to define validators together with custom validation:

::

    // models/User.php
    class User extends BaseUser
    {
        protected function validate()
        {
            if ($this->username == 'God') {
                // Blasphemy! Stop that! ;-)
                // syntax: add(<fieldName>, <error code/identifier>)
                $errorStack = $this->getErrorStack();
                $errorStack->add('name', 'You cannot use this username!');
            }
        }
    }


::

    // models/Email.php
    class Email extends BaseEmail
    {
        // ...

        public function setTableDefinition()
        {
            parent::setTableDefinition();

            // ...

            // validators 'email' and 'unique' used
            $this->hasColumn('address', 'string', 150, array('email', 'unique'));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Email:
      columns:
        address:
          type: string(150)
          email: true
          unique: true

------------------
Valid or Not Valid
------------------

Now that you know how to specify your business rules in your models, it
is time to look at how to deal with these rules in the rest of your
application.

^^^^^^^^^^^^^^^^^^^
Implicit Validation
^^^^^^^^^^^^^^^^^^^

Whenever a record is going to be saved to the persistent data store (i.e.
through calling :php:meth:`$record->save`) the full validation procedure is
executed. If errors occur during that process an exception of the type
:php:exc:`Doctrine_Validator_Exception` will be thrown. You can catch that
exception and analyze the errors by using the instance method
:php:meth:`Doctrine_Validator_Exception::getInvalidRecords`. This method
returns an ordinary array with references to all records that did not pass
validation. You can then further explore the errors of each record by analyzing
the error stack of each record. The error stack of a record can be obtained
with the instance method :php:meth:`Doctrine_Record::getErrorStack`. Each error
stack is an instance of the class ``Doctrine_Validator_ErrorStack``. The error
stack provides an easy to use interface to inspect the errors.

^^^^^^^^^^^^^^^^^^^
Explicit Validation
^^^^^^^^^^^^^^^^^^^

You can explicitly trigger the validation for any record at any time.
For this purpose :php:class:`Doctrine_Record` provides the instance method
:php:meth:`Doctrine_Record::isValid`. This method returns a boolean value
indicating the result of the validation. If the method returns false,
you can inspect the error stack in the same way as seen above except
that no exception is thrown, so you simply obtain the error stack of the
record that didnt pass validation through
:php:meth:`Doctrine_Record::getErrorStack`.

The following code snippet shows an example of handling implicit
validation which caused a :php:exc:`Doctrine_Validator_Exception`.

::

    // test.php
    $user = new User();

    try {
        $user->username = str_repeat('t', 256);
        $user->Email->address = "drink@@notvalid..";
        $user->save();
    } catch(Doctrine_Validator_Exception $e) {
        $userErrors = $user->getErrorStack();
        $emailErrors = $user->Email->getErrorStack();

        foreach($userErrors as $fieldName => $errorCodes) {
            echo $fieldName . " - " . implode(', ', $errorCodes) . "\n";
        }
        foreach($emailErrors as $fieldName => $errorCodes) {
            echo $fieldName . " - " . implode(', ', $errorCodes) . "\n";
        }
    }

.. tip::

    You could also use :php:meth:`$e->getInvalidRecords`. The direct
    way used above is just more simple when you know the records you're
    dealing with.

You can also retrieve the error stack as a nicely formatted string for
easy use in your applications::

    // test.php
    echo $user->getErrorStackAsString();

It would output an error string that looks something like the following:

.. code-block:: text

    Validation failed in class User

    1 field had validation error:

    * 1 validator failed on username (length)

========
Profiler
========

``Doctrine_Connection_Profiler`` is an event listener for
:php:class:`Doctrine_Connection`. It provides flexible query profiling. Besides
the SQL strings the query profiles include elapsed time to run the queries.
This allows inspection of the queries that have been performed without the need
for adding extra debugging code to model classes.

``Doctrine_Connection_Profiler`` can be enabled by adding it as an event
listener for Doctrine_Connection::

    $profiler = new Doctrine_Connection_Profiler();

    $conn = Doctrine_Manager::connection();
    $conn->setListener($profiler);

-----------
Basic Usage
-----------

Perhaps some of your pages is loading slowly. The following shows how to
build a complete profiler report from the connection::

    // test.php
    $time = 0;
    foreach ($profiler as $event) {
        $time += $event->getElapsedSecs();

        printf(
            "%s %f\n%s\n",
            $event->getName(),
            $event->getElapsedSecs(),
            $event->getQuery()
        );

        $params = $event->getParams();
        if (!empty($params)) {
            print_r($params);
        }
    }
    echo "Total time: " . $time . "\n";


.. tip::

    Frameworks like `symfony <http://www.symfony-project.com>`_, `Zend
    <http://framework.zend.com>`_, etc. offer web debug toolbars that use this
    functionality provided by Doctrine for reporting the number of queries
    executed on every page as well as the time it takes for each query.

===============
Locking Manager
===============

.. note::

    The term 'Transaction' does not refer to database transactions here but to
    the general meaning of this term.

Locking is a mechanism to control concurrency. The two most well known locking
strategies are optimistic and pessimistic locking. The following is a short
description of these two strategies from which only pessimistic locking is
currently supported by Doctrine.

------------------
Optimistic Locking
------------------

The state/version of the object(s) is noted when the transaction begins.  When
the transaction finishes the noted state/version of the participating objects
is compared to the current state/version. When the states/versions differ the
objects have been modified by another transaction and the current transaction
should fail. This approach is called 'optimistic' because it is assumed that it
is unlikely that several users will participate in transactions on the same
objects at the same time.

-------------------
Pessimistic Locking
-------------------

The objects that need to participate in the transaction are locked at the
moment the user starts the transaction. No other user can start a transaction
that operates on these objects while the locks are active.  This ensures that
the user who starts the transaction can be sure that no one else modifies the
same objects until he has finished his work.

Doctrine's pessimistic offline locking capabilities can be used to control
concurrency during actions or procedures that take several HTTP request and
response cycles and/or a lot of time to complete.

--------
Examples
--------

The following code snippet demonstrates the use of Doctrine's pessimistic
offline locking capabilities.

At the page where the lock is requested get a locking manager instance::

    // test.php
    $lockingManager = new Doctrine_Locking_Manager_Pessimistic();

.. tip::

    Ensure that old locks which timed out are released before we
    try to acquire our lock 300 seconds = 5 minutes timeout. This can be
    done by using the :php:meth:`releaseAgedLocks` method::

        // test.php
        $user = Doctrine_Core::getTable('User')->find(1);

        try {
            $lockingManager->releaseAgedLocks(300);
            $gotLock = $lockingManager->getLock($user, 'jwage');

            if ($gotLock){
                echo "Got lock!";
            } else {
                echo "Sorry, someone else is currently working on this record";
            }
        } catch(Doctrine_Locking_Exception $dle) {
            echo $dle->getMessage(); // handle the error
        }

At the page where the transaction finishes get a locking manager
instance::

    // test.php
    $user = Doctrine_Core::getTable('User')->find(1);

    $lockingManager = new Doctrine_Locking_Manager_Pessimistic();

    try {
        if ($lockingManager->releaseLock($user, 'jwage')) {
            echo "Lock released";
        } else {
            echo "Record was not locked. No locks released.";
        }
    } catch(Doctrine_Locking_Exception $dle) {
        echo $dle->getMessage(); // handle the error
    }

-----------------
Technical Details
-----------------

The pessimistic offline locking manager stores the locks in the database
(therefore 'offline'). The required locking table is automatically
created when you try to instantiate an instance of the manager and the
``ATTR_CREATE_TABLES`` is set to TRUE. This behavior may change in the
future to provide a centralized and consistent table creation procedure
for installation purposes.

.. _component-overview-views:

=====
Views
=====

Database views can greatly increase the performance of complex queries.  You
can think of them as cached queries. ``Doctrine_View`` provides integration
between database views and DQL queries.

-----------
Using Views
-----------

Using views on your database using Doctrine is easy. We provide a nice
``Doctrine_View`` class which provides functionality for creating,
dropping and executing views.

The ``Doctrine_View`` class integrates with the :php:class:`Doctrine_Query`
class by saving the SQL that would be executed by :php:class:`Doctrine_Query`.

First lets create a new :php:class:`Doctrine_Query` instance to work with::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Phonenumber p')
            ->limit(20);

Now lets create the ``Doctrine_View`` instance and pass it the
:php:class:`Doctrine_Query` instance as well as a ``name`` for identifying that
database view::

    $view = new Doctrine_View($q, 'RetrieveUsersAndPhonenumbers');

Now we can easily create the view by using the
:php:meth:`Doctrine_View::create` method::

    try {
        $view->create();
    } catch (Exception $e) {
    }

Alternatively if you want to drop the database view you use the
:php:meth:`Doctrine_View::drop` method::

    try {
        $view->drop();
    } catch (Exception $e) {
    }

Using views are extremely easy. Just use the
:php:meth:`Doctrine_View::execute` for executing the view and returning the
results just as a normal :php:class:`Doctrine_Query` object would::

    $users = $view->execute();

    foreach ($users as $user) {
        print_r($us->toArray());
    }

==========
Conclusion
==========

We now have been exposed to a very large percentage of the core
functionality provided by Doctrine. The next chapters of this book are
documentation that cover some of the optional functionality that can
help make your life easier on a day to day basis.

Lets move on to the :doc:`next chapter <native-sql>` where we can learn
about how to use native SQL to hydrate our data in to arrays and objects
instead of the Doctrine Query Language.