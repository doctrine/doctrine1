**************************
Database Abstraction Layer
**************************

The Doctrine Database Abstraction Layer is the underlying framework that
the ORM uses to communicate with the database and send the appropriate
SQL depending on which database type you are using. It also has the
ability to query the database for information like what table a database
has or what fields a table has. This is how Doctrine is able to generate
your models from existing databases so easily.

This layer can be used independently of the ORM. This might be of use
for example if you have an existing application that uses PDO directly
and you want to port it to use the Doctrine Connections and DBAL. At a
later phase you could begin to use the ORM for new things and rewrite
old pieces to use the ORM.

The DBAL is composed of a few different modules. In this chapter we will
discuss the different modules and what their jobs are.

.. _database-abstraction-layer-export:

======
Export
======

The Export module provides methods for managing database structure. The
methods can be grouped based on their responsibility: create, edit
(alter or update), list or delete (drop) database elements. The
following document lists the available methods, providing examples of
their use.

------------
Introduction
------------

Every schema altering method in the Export module has an equivalent
which returns the SQL that is used for the altering operation. For
example ``createTable()`` executes the query / queries returned by
``createTableSql()``.

In this chapter the following tables will be created, altered and
finally dropped, in a database named ``events_db``:

**events**

============  ===============  =========  ================
Name          Type             Primary    Auto Increment
============  ===============  =========  ================
``id``        ``integer``      ``true``   ``true``
``name``      ``string(255)``  ``false``  ``false``
``datetime``  ``timestamp``    ``false``  ``false``
============  ===============  =========  ================

**people**

========  ===============  =========  ================
Name      Type             Primary    Auto Increment
========  ===============  =========  ================
``id``    ``integer``      ``true``   ``true``
``name``  ``string(255)``  ``false``  ``false``
========  ===============  =========  ================

**event_participants**

=============  ===============  ========  ================
Name           Type             Primary   Auto Increment
=============  ===============  ========  ================
``event_id``   ``integer``      ``true``  ``false``
``person_id``  ``string(255)``  ``true``  ``false``
=============  ===============  ========  ================

------------------
Creating Databases
------------------

It is simple to create new databases with Doctrine. It is only a matter
of calling the ``createDatabase()`` function with an argument that is
the name of the database to create.

::

    // test.php

    // ...
    $conn->export->createDatabase('events_db');

Now lets change the connection in our ``bootstrap.php`` file to connect
to the new ``events_db``:

::

    // bootstrap.php

    /**
     * Bootstrap Doctrine.php, register autoloader and specify
     * configuration attributes
     */

     // ...
     $conn = Doctrine_Manager::connection('mysql://root:@localhost/events_db', 'doctrine');

     // ...

---------------
Creating Tables
---------------

Now that the database is created and we've re-configured our connection,
we can proceed with adding some tables. The method ``createTable()``
takes three parameters: the table name, an array of field definition and
some extra options (optional and RDBMS-specific).

Now lets create the ``events`` table:

::

    // test.php

    //
    $definition = array(
        'id' => array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ),
        'name' => array(
            'type' => 'string',
            'length' => 255
        ),
        'datetime' => array(
            'type' => 'timestamp'
        )
    );

    $conn->export->createTable('events', $definition);

The keys of the definition array are the names of the fields in the
table. The values are arrays containing the required key ``type`` as
well as other keys, depending on the value of ``type``. The values for
the ``type`` key are the same as the possible Doctrine datatypes.
Depending on the datatype, the other options may vary.

=============  =======  =======  =========  ========  =============
Datatype        length  default  not null   unsigned  autoincrement
=============  =======  =======  =========  ========  =============
``string``        x        x         x
``boolean``                x         x
``integer``       x        x         x          x           x
``decimal``                x         x
``float``                  x         x
``timestamp``              x         x
``time``                   x         x
``date``                   x         x
``clob``          x                  x
``blob``          x                  x
=============  =======  =======  =========  ========  =============

And now we can go ahead and create the ``people`` table:

::

    // test.php

    // ...
    $definition = array(
        'id' => array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ),
        'name' => array(
            'type' => 'string',
            'length' => 255
        )
    );

    $conn->export->createTable('people', $definition);

You can also specify an array of options as the third argument to the
``createTable()`` method:

::

    // test.php

    // ...
    $options = array(
        'comment'       => 'Repository of people',
        'character_set' => 'utf8',
        'collate'       => 'utf8_unicode_ci',
        'type'          => 'innodb',
    );

    // ...

    $conn->export->createTable('people', $definition, $options);

---------------------
Creating Foreign Keys
---------------------

Creating the ``event_participants`` table with a foreign key:

::

    // test.php

    // ...
    $options = array(
        'foreignKeys' => array(
            'events_id_fk' => array(
                'local' => 'event_id',
                'foreign' => 'id',
                'foreignTable' => 'events',
                'onDelete' => 'CASCADE',
            )
        ),
        'primary' => array( 'event_id', 'person_id'),
    );

    $definition = array(
        'event_id' => array(
            'type' => 'integer',
            'primary' => true
        ),
        'person_id' => array(
            'type' => 'integer',
            'primary' => true
        ),
    );

    $conn->export->createTable('event_participants', $definition, $options);

.. tip::

    In the above example notice how we omit a foreign key for
    the ``person_id``. In that example we omit it so we can show you
    how to add an individual foreign key to a table in the next example.
    Normally it would be best to have both foreign keys defined on the
    in the ``foreignKeys``.

Now lets add the missing foreign key in the ``event_participants``
table the on ``person_id`` column:

::

    // test.php

    // ...
    $definition = array('local' => 'person_id',
                        'foreign' => 'id',
                        'foreignTable' => 'people',
                        'onDelete' => 'CASCADE');

    $conn->export->createForeignKey('event_participants', $definition);

--------------
Altering table
--------------

:php:class:`Doctrine_Export` drivers provide an easy database portable way of
altering existing database tables.

::

    // test.php

    // ...
    $alter = array(
        'add' => array(
            'new_column' => array(
                'type' => 'string',
                'length' => 255
            ),
            'new_column2' => array(
                'type' => 'string',
                'length' => 255
            )
        )
    );

    echo $conn->export->alterTableSql('events', $alter);

The above call to ``alterTableSql()`` would output the following SQL
query:

::

    ALTER TABLE events ADD new_column VARCHAR(255),
    ADD new_column2 VARCHAR(255)

.. note::
    If you only want execute generated sql and not return it,
    use the ``alterTable()`` method.

::

    // test.php

    // ...
    $conn->export->alterTable('events', $alter);

The ``alterTable()`` method requires two parameters and has an optional
third:

============  ===============  ====================================================================================================
Name          Type             Description
============  ===============  ====================================================================================================
**$name**     ``string``       Name of the table that is intended to be changed.
**$changes**  ``array``        Associative array that contains the details of each type of change that is intended to be performed.
============  ===============  ====================================================================================================

An optional third parameter (default: ``false``):

==========  ===========  ======================================================================
Name        Type         Description
==========  ===========  ======================================================================
**$check**  ``boolean``  Check if the DBMS can actually perform the operation before executing.
==========  ===========  ======================================================================

The types of changes that are currently supported are defined as
follows:

===========  =============================================================================================================================================
Change       Description
===========  =============================================================================================================================================
**name**     New name for the table.
**add**      Associative array with the names of fields to be added as indexes of the array. The value of each entry of
             the array should be set to another associative array with the properties of
             the fields to be added. The properties of the fields should be the same as defined by the Doctrine parser.
**remove**   Associative array with the names of fields to be removed as indexes of the array. Currently the values assigned to each entry are ignored. An
             empty array should be used for future compatibility.
**rename**   Associative array with the names of fields to be renamed as indexes of the array. The value of each entry of the array should be set to
             another associative array with the entry named name with the new
             field name and the entry named Declaration that is expected to contain the portion of the field declaration already in DBMS specific SQL code
             as it is used in the ``CREATE TABLE`` statement.
**change**   Associative array with the names of the fields to be changed as indexes of the array. Keep in mind that if it is intended to change
             either the name of a field and any other properties, the change array
             entries should have the new names of the fields as array indexes.
===========  =============================================================================================================================================

The value of each entry of the array should be set to another
associative array with the properties of the fields to that are meant to
be changed as array entries. These entries should be assigned to the new
values of the respective properties. The properties of the fields should
be the same as defined by the Doctrine parser.

::

    // test.php

    // ...
    $alter = array('name' => 'event',
                   'add' => array(
                       'quota' => array(
                           'type' => 'integer',
                           'unsigned' => 1
                       )
                   ),
                   'remove' => array(
                       'new_column2' => array()
                   ),
                   'change' => array(
                       'name' => array(
                           'length' => '20',
                           'definition' => array(
                               'type' => 'string',
                               'length' => 20
                           )
                       )
                   ),
                   'rename' => array(
                       'new_column' => array(
                           'name' => 'gender',
                           'definition' => array(
                               'type' => 'string',
                               'length' => 1,
                               'default' => 'M'
                           )
                       )
                   )
               );

    $conn->export->alterTable('events', $alter);

.. note::

    Notice how we renamed the table to ``event``, lets rename
    it back to ``events``. We only renamed it to demonstrate the
    functionality and we will need the table to be named ``events`` for
    the next examples.

::

    // test.php

    // ...
    $alter = array(
        'name' => 'events'
    );

    $conn->export->alterTable('event', $alter);

----------------
Creating Indexes
----------------

To create an index, the method ``createIndex()`` is used, which has
similar signature as ``createConstraint()``, so it takes table name,
index name and a definition array. The definition array has one key
named ``fields`` with a value which is another associative array
containing fields that will be a part of the index. The fields are
defined as arrays with possible keys: sorting, with values ascending and
descending length, integer value

Not all RDBMS will support index sorting or length, in these cases the
drivers will ignore them. In the test events database, we can assume
that our application will show events occuring in a specific timeframe,
so the selects will use the datetime field in ``WHERE`` conditions. It
will help if there is an index on this field.

::

    // test.php

    // ...
    $definition = array(
        'fields' => array(
            'datetime' => array()
        )
    );

    $conn->export->createIndex('events', 'datetime', $definition);

--------------------------
Deleting database elements
--------------------------

For every ``create*()`` method as shown above, there is a corresponding
``drop*()`` method to delete a database, a table, field, index or
constraint. The ``drop*()`` methods do not check if the item to be
deleted exists, so it's developer's responsibility to check for
exceptions using a try catch block:

::

    // test.php

    // ...
    try {
        $conn->export->dropSequence('nonexisting');
    } catch(Doctrine_Exception $e) {

    }

You can easily drop a constraint with the following code:

::

    // test.php

    // ...
    $conn->export->dropConstraint('events', 'PRIMARY', true);

.. note::

    The third parameter gives a hint that this is a primary key
    constraint.

::

    // test.php

    // ... $conn->export->dropConstraint('event_participants', 'event_id');

You can easily drop an index with the following code:

::

    $conn->export->dropIndex('events', 'event_timestamp');

.. tip::

    It is recommended to not actually execute the next two
    examples. In the next section we will need the ``events_db`` to be
    intact for our examples to work.

Drop a table from the database with the following code:

::

    // test.php

    // ...
    $conn->export->dropTable('events');

We can drop the database with the following code:

::

    // test.php

    // ...
    $conn->export->dropDatabase('events_db');

.. _database-abstraction-layer-import:

======
Import
======

The import module allows you to inspect a the contents of a database
connection and learn about the databases and schemas in each database.

------------
Introduction
------------

To see what's in the database, you can use the ``list*()`` family of
functions in the Import module.

====================================  ===========================================================================================
Name                                  Description
====================================  ===========================================================================================
``listDatabases()``                   List the databases
``listFunctions()``                   List the available functions.
``listSequences($dbName)``            List the available sequences. Takes optional database name as a parameter. If not supplied,
                                      the currently selected database is assumed.
``listTableConstraints($tableName)``  Lists the available tables. takes a table name
``listTableColumns($tableName)``      List the columns available in a table.
``listTableIndexes($tableName)``      List the indexes defined in a table.
``listTables($dbName)``               List the tables in a database.
``listTableTriggers($tableName)``     List the triggers in a table.
``listTableViews($tableName)``        List the views available in a table.
``listUsers()``                       List the users for the database.
``listViews($dbName)``                List the views available for a database.
====================================  ===========================================================================================

Below you will find examples on how to use the above listed functions:

-----------------
Listing Databases
-----------------

::

    // test.php

    // ...
    $databases = $conn->import->listDatabases();
    print_r($databases);

-----------------
Listing Sequences
-----------------

::

    // test.php

    // ... $sequences = $conn->import->listSequences('events_db');
    print_r($sequences);

-------------------
Listing Constraints
-------------------

::

    // test.php

    // ...
    $constraints = $conn->import->listTableConstraints('event_participants');
    print_r($constraints);

---------------------
Listing Table Columns
---------------------

::

    // test.php

    // ... $columns = $conn->import->listTableColumns('events');
    print_r($columns);

---------------------
Listing Table Indexes
---------------------

::

    // test.php

    // ... $indexes = $conn->import->listTableIndexes('events');
    print_r($indexes);

--------------
Listing Tables
--------------

::

    $tables = $conn->import->listTables();
    print_r($tables);

-------------
Listing Views
-------------

.. note::

    Currently there is no method to create views, so let's do it manually.

::

    $sql = "CREATE VIEW names_only AS SELECT name FROM people";
    $conn->exec($sql);

    $sql = "CREATE VIEW last_ten_events AS SELECT * FROM events ORDER BY id DESC LIMIT 0,10";
    $conn->exec($sql);

Now we can list the views we just created:

::

    $views = $conn->import->listViews();
    print_r($views);

.. _database-abstraction-layer-datadict:

========
DataDict
========

------------
Introduction
------------

Doctrine uses the ``DataDict`` module internally to convert native RDBMS
types to Doctrine types and the reverse. ``DataDict`` module uses two
methods for the conversions:

-  ``getPortableDeclaration()``, which is used for converting native
   RDBMS type declaration to portable Doctrine declaration
-  ``getNativeDeclaration()``, which is used for converting portable
   Doctrine declaration to driver specific type declaration

----------------------------
Getting portable declaration
----------------------------

::

    // test.php

    // ...
    $declaration = $conn->dataDict->getPortableDeclaration('VARCHAR(255)');

    print_r($declaration);

The above example would output the following:

.. code-block:: sh

    $ php test.php
    Array
    (
        [type] => Array
            (
                [0] => string
            )
        [length] => 255
        [unsigned] =>
        [fixed] =>
    )

--------------------------
Getting Native Declaration
--------------------------

::

    // test.php

    // ...
    $portableDeclaration = array(
        'type' => 'string',
        'length' => 20,
        'fixed' => true
    );

    $nativeDeclaration = $conn->dataDict->getNativeDeclaration($portableDeclaration);

    echo $nativeDeclaration;

The above example would output the following:

.. code-block:: sh

    $ php test.php
    CHAR(20)

=======
Drivers
=======

-----
Mysql
-----

^^^^^^^^^^^^^^^^^^
Setting table type
^^^^^^^^^^^^^^^^^^

::

    // test.php

    // ...
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'autoincrement' => true
        ),
        'name' => array(
            'type' => 'string',
            'fixed' => true,
            'length' => 8
        )
    );

.. note::

    The following option is mysql specific and skipped by other
    drivers.

::

    $options = array('type' => 'INNODB');

    $sql = $conn->export->createTableSql('test_table', $fields);
    echo $sql[0];

The above will output the following SQL query:

::

    CREATE TABLE test_table (id INT AUTO_INCREMENT,
    name CHAR(8)) ENGINE = INNODB

==========
Conclusion
==========

This chapter is indeed a nice one. The Doctrine DBAL is a great tool all
by itself. It is probably one of the most fully featured and easy to use
PHP database abstraction layers available today.

Now we are ready to move on and learn about how to use :doc:`transactions`.