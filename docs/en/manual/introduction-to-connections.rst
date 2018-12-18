..  vim: set ts=4 sw=4 tw=79 :

***************************
Introduction to Connections
***************************

=========================
DSN, the Data Source Name
=========================

In order to connect to a database through Doctrine, you have to create a
valid DSN (Data Source Name).

Doctrine supports both PEAR DB/MDB2 like data source names as well as
PDO style data source names. The following section deals with PEAR like
data source names. If you need more info about the PDO-style data source
names see the documentation on PDO_.

----------
Main Parts
----------

The DSN consists in the following parts:

==============  =====================================================
DSN part        Description
==============  =====================================================
``phptype``     Database backend used in PHP (i.e. mysql, pgsql etc.)
``dbsyntax``    Database used with regards to SQL syntax etc
``protocol``    Communication protocol to use ( i.e. tcp, unix etc.)
``hostspec``    Host specification (hostname[:port]) \|\|
``database``    Database to use on the DBMS server
``username``    User name for login
``password``    Password for login
``proto_opts``  Maybe used with protocol
``option``      Additional connection options in URI query string
                format. Options are separated by ampersand (``&``).
                The following section shows a non-complete list of
                options.
==============  =====================================================

^^^^^^^
Options
^^^^^^^

============  =================================================================
Name          Description
============  =================================================================
``charset``   Some backends support setting the client charset.
``new_link``  Some RDBMS do not create new connections when connecting to the
              same host multiple times. This option will attempt to force a new
              connection.
============  =================================================================

-----------------
Providing the DSN
-----------------

The DSN can either be provided as an associative array or as a string.
The string format of the supplied DSN is in its fullest form:

.. code-block:: text

    phptype(dbsyntax)://username:password@protocol+hostspec/database?option=value

Most variations are allowed:

.. code-block:: text

    phptype://username:password@protocol+hostspec:110//usr/db_file.db
    phptype://username:password@hostspec/database
    phptype://username:password@hostspec
    phptype://username@hostspec
    phptype://hostspec/database phptype://hostspec phptype:///database
    phptype:///database?option=value&anotheroption=anothervalue
    phptype(dbsyntax) phptype

The currently supported PDO database drivers are:

============  ===============================================================
Driver name   Supported databases
============  ===============================================================
``fbsql``     FrontBase
``ibase``     InterBase / Firebird (requires PHP 5)
``mssql``     Microsoft SQL Server (NOT for Sybase. Compile PHP --with-mssql)
``mysql``     MySQL
``mysqli``    MySQL (supports new authentication protocol) (requires PHP 5)
``oci``       Oracle 7/8/9/10
``pgsql``     PostgreSQL
``querysim``  QuerySim
``sqlite``    SQLite 2
============  ===============================================================

A second DSN format supported is

.. code-block:: text

    phptype(syntax)://user:pass@protocol(proto_opts)/database

If your database, option values, username or password contain characters
used to delineate DSN parts, you can escape them via URI hex encodings:

=========  ========
Character  Hex Code
=========  ========
``:``      ``%3a``
``/``      ``%2f``
``@``      ``%40``
``+``      ``%2b``
``(``      ``%28``
``)``      ``%29``
``?``      ``%3f``
``=``      ``%3d``
``&``      ``%26``
=========  ========

Please note, that some features may be not supported by all database drivers.

--------
Examples
--------

#. Connect to database through a socket

    .. code-block:: text

        mysql://user@unix(/path/to/socket)/pear

#. Connect to database on a non standard port

    .. code-block:: text

        pgsql://user:pass@tcp(localhost:5555)/pear

    .. note::

        If you use, the IP address ``127.0.0.1``, the port parameter is ignored
        (default: 3306).

#. Connect to SQLite on a Unix machine using options

    .. code-block:: text

        sqlite:////full/unix/path/to/file.db?mode=0666

#. Connect to SQLite on a Windows machine using options

    .. code-block:: text

        sqlite:///c:/full/windows/path/to/file.db?mode=0666

#. Connect to MySQLi using SSL

    .. code-block:: text

        mysqli://user:pass@localhost/pear?key=client-key.pem&cert=client-cert.pem

=======================
Opening New Connections
=======================

Opening a new database connection in Doctrine is very easy. If you wish
to use PDO_ you can just initialize a new :php:class:`PDO`
object.

Remember our ``bootstrap.php`` file we created in the :doc:`getting-started` chapter? Under the code where we registered the
Doctrine autoloader we are going to instantiate our new connection:

::

    // bootstrap.php
    $dsn = 'mysql:dbname=testdb;host=127.0.0.1';
    $user = 'dbuser';
    $password = 'dbpass';

    $dbh = new PDO($dsn, $user, $password);
    $conn = Doctrine_Manager::connection($dbh);

.. tip::

    Directly passing a PDO instance to ``Doctrine_Manager::connection`` will not
    allow Doctrine to be aware of the username and password for the connection,
    since their is no way to retrieve it from an existing PDO instance. The
    username and password is required in order for Doctrine to be able to create
    and drop databases. To get around this you can manually set the username and
    password option directly on the ``$conn`` object.

    ::

        // bootstrap.php
        $conn->setOption('username', $user);
        $conn->setOption('password', $password);

========================
Lazy Database Connecting
========================

Lazy-connecting to database can save a lot of resources. There might be
many times where you don't need an actual database connection, hence its
always recommended to use lazy-connecting (that means Doctrine will only
connect to database when needed).

This feature can be very useful when using for example page caching,
hence not actually needing a database connection on every request.
Remember connecting to database is an expensive operation.

In the example below we will show you when you create a new Doctrine
connection, the connection to the database isn't created until it is
actually needed.

::

    // bootstrap.php
    // At this point no actual connection to the database is created
    $conn = Doctrine_Manager::connection('mysql://username:password@localhost/test');

    // The first time the connection is needed, it is instantiated
    // This query triggers the connection to be created
    $conn->execute('SHOW TABLES');

=======================
Testing your Connection
=======================

After reading the previous sections of this chapter, you should now know
how to create a connection. So, lets modify our bootstrap file to
include the initialization of a connection. For this example we will
just be using a sqlite memory database but you can use whatever type of
database connection you prefer.

Add your database connection to ``bootstrap.php`` and it should look
something like the following::

    /* Bootstrap Doctrine.php, register autoloader and specify
       configuration attributes */

    require_once('../doctrine/branches/1.2/lib/Doctrine.php');
    spl_autoload_register(array('Doctrine', 'autoload'));
    $manager = Doctrine_Manager::getInstance();

    $conn = Doctrine_Manager::connection('sqlite::memory:', 'doctrine');

To test the connection lets modify our ``test.php`` script and perform a
small test. Since we create a variable name ``$conn``, that variable is
available to the test script so lets setup a small test to make sure our
connection is working:

First lets create a test table and insert a record::

    // test.php
    $conn->export->createTable('test', array('name' => array('type' => 'string')));
    $conn->execute('INSERT INTO test (name) VALUES (?)', array('jwage'));

Now lets execute a simple ``SELECT`` query from the ``test`` table we
just created to make sure the data was inserted and that we can retrieve
it::

    // test.php
    $stmt = $conn->prepare('SELECT \* FROM test');
    $stmt->execute();
    $results = $stmt->fetchAll();
    print_r($results);

Execute ``test.php`` from your terminal and you should see:

.. code-block:: text

    php test.php
    Array(
      [0] => Array(
        [name] => jwage,
        [0] => jwage
      )
    )

==========
Conclusion
==========

Great! Now we learned some basic operations of Doctrine connections. We
have modified our Doctrine test environment to have a new connection.
This is required because the examples in the coming chapters will
require a connection.

Lets move on to the :doc:`configuration` chapter and learn how you
can control functionality and configurations using the Doctrine
attribute system.

.. _PDO: http://www.php.net/PDO
