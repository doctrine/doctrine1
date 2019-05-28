************
Unit Testing
************

Doctrine is programmatically tested using UnitTests. You can read more
about unit testing `here <http://en.wikipedia.org/wiki/Unit_testing>`_ on
Wikipedia.

=============
Running tests
=============

In order to run the tests that come with doctrine you need to check out
the entire project, not just the lib folder.

.. code-block:: sh

    $ svn co http://svn.doctrine-project.org/branches/1.2 /path/to/co/doctrine

Now change directory to the checked out copy of doctrine.

.. code-block:: sh

    $ cd /path/to/co/doctrine

You should see the following files and directories listed.

::

    CHANGELOG
    COPYRIGHT
    lib/
    LICENSE
    package.xml
    tests/
    tools/
    vendor/

.. tip::

    It is not uncommon for the test suite to have fails that we
    are aware of. Often Doctrine will have test cases for bugs or
    enhancement requests that cannot be committed until later versions.
    Or we simply don't have a fix for the issue yet and the test remains
    failing. You can ask on the mailing list or in IRC for how many
    fails should be expected in each version of Doctrine.

---
CLI
---

To run tests on the command line, you must have php-cli installed.

Navigate to the ``/path/to/co/doctrine/tests`` folder and execute the
``run.php`` script:

.. code-block:: sh

    $ cd /path/to/co/doctrine/tests
    $ php run.php

This will print out a progress bar as it is running all the unit tests.
When it is finished it will report to you what has passed and failed.

The CLI has several options available for running specific tests, groups
of tests or filtering tests against class names of test suites. Run the
following command to check out these options.

.. code-block:: sh

    $ php run.php -help

You can run an individual group of tests like this:

.. code-block:: sh

    $ php run.php --group data_dict

-------
Browser
-------

You can run the unit tests in the browser by navigating to
``doctrine/tests/run.php``. Options can be set through ``_GET``
variables.

For example:

-  `http://localhost/doctrine/tests/run.php <http://localhost/doctrine/tests/run.php>`_
-  `http://localhost/doctrine/tests/run.php?filter=Limit&group[]=query&group[]=record <http://localhost/doctrine/tests/run.php?filter=Limit&group[]=query&group[]=record>`_

.. caution::

    Please note that test results may very depending on your
    environment. For example if ``php.ini`` ``apc.enable_cli`` is set
    to 0 then some additional tests may fail.

=============
Writing Tests
=============

When writing your test case, you can copy ``TemplateTestCase.php`` to
start off. Here is a sample test case:

::

    class Doctrine_Sample_TestCase extends Doctrine_UnitTestCase
    {
        public function prepareTables()
        {
            $this->tables[] = "MyModel1";
            $this->tables[] = "MyModel2";

            parent::prepareTables();
        }

        public function prepareData()
        {
            $this->myModel = new MyModel1();
            //$this->myModel->save();
        }

        public function testInit()
        {

        }

        // This produces a failing test
        public function testTest()
        {
            $this->assertTrue( $this->myModel->exists() );
            $this->assertEqual( 0, 1 );
            $this->assertIdentical( 0, '0' );
            $this->assertNotEqual( 1, 2 );
            $this->assertTrue( ( 5 < 1 ) );
            $this->assertFalse( (1 > 2 ) );
        }
    }

    class Model1 extends Doctrine_Record
    {
    }

    class Model2 extends Doctrine_Record
    {
    }

.. note::

    The model definitions can be included directly in the test
    case file or they can be put in
    ``/path/to/co/doctrine/tests/models`` and they will be autoloaded
    for you.

Once you are finished writing your test be sure to add it to ``run.php``
like the following.

::

    $test->addTestCase( new Doctrine_Sample_TestCase() );

Now when you execute run.php you will see the new failure reported to
you.

------------
Ticket Tests
------------

In Doctrine it is common practice to commit a failing test case for each
individual ticket that is reported in trac. These test cases are
automatically added to run.php by reading all test case files found in
the ``/path/to/co/doctrine/tests/Ticket/`` folder.

You can create a new ticket test case easily from the CLI:

.. code-block:: sh

    $ php run.php --ticket 9999

If the ticket number 9999 doesn't already exist then the blank test case
class will be generated for you at ``/path/to/co/doctrine/tests/Ticket/9999TestCase.php``.

::

    class Doctrine_Ticket_9999_TestCase extends Doctrine_UnitTestCase
    {
    }

-------------------
Methods for testing
-------------------

^^^^^^^^^^^^
Assert Equal
^^^^^^^^^^^^

::

    // ...
    public function test1Equals1()
    {
        $this->assertEqual( 1, 1 );
    }
    // ...

^^^^^^^^^^^^^^^^
Assert Not Equal
^^^^^^^^^^^^^^^^

::

    // ...
    public function test1DoesNotEqual2()
    {
        $this->assertNotEqual( 1, 2 );
    }
    // ...

^^^^^^^^^^^^^^^^
Assert Identical
^^^^^^^^^^^^^^^^

The ``assertIdentical()`` method is the same as the ``assertEqual()``
except that its logic is stricter and uses the ``===`` for comparing the
two values.

::

    // ...
    public function testAssertIdentical()
    {
        $this->assertIdentical( 1, '1' );
    }
    // ...

.. note::

    The above test would fail obviously because the first
    argument is the number 1 casted as PHP type integer and the second
    argument is the number 1 casted as PHP type string.

^^^^^^^^^^^
Assert True
^^^^^^^^^^^

::

    // ...
    public function testAssertTrue()
    {
        $this->assertTrue( 5 > 2 );
    }
    // ...

^^^^^^^^^^^^
Assert False
^^^^^^^^^^^^

::

    // ...
    public function testAssertFalse()
    {
        $this->assertFalse( 5 < 2 );
    }
    // ...

------------
Mock Drivers
------------

Doctrine uses mock drivers for all drivers other than sqlite. The
following code snippet shows you how to use mock drivers:

::

    class Doctrine_Sample_TestCase extends Doctrine_UnitTestCase
    {
        public function testInit()
        {
            $this->dbh  = new Doctrine_Adapter_Mock( 'oracle' );
            $this->conn = Doctrine_Manager::getInstance()->openConnection( $this->dbh );
        }
    }

Now when you execute queries they won't actually be executed against a
real database. Instead they will be collected in an array and you will
be able to analyze the queries that were executed and make test
assertions against them.

::

    class Doctrine_Sample_TestCase extends Doctrine_UnitTestCase
    {
        // ...
        public function testMockDriver()
        {
            $user           = new User();
            $user->username = 'jwage';
            $user->password = 'changeme';
            $user->save();

            $sql = $this->dbh->getAll();

            // print the sql array to find the query you're looking for
            // print_r( $sql );

            $this->assertEqual( $sql[0], 'INSERT INTO user (username, password) VALUES (?, ?)' );
        }
    }

---------------------
Test Class Guidelines
---------------------

Every class should have at least one TestCase equivalent and they should
inherit :php:class:`Doctrine_UnitTestCase`. Test classes should refer to a class
or an aspect of a class, and they should be named accordingly.

Some examples:

-  :php:class:`Doctrine_Record_TestCase` is a good name because it refers to
   the :php:class:`Doctrine_Record` class
-  :php:class:`Doctrine_Record_State_TestCase` is also good, because it refers
   to the state aspect of the :php:class:`Doctrine_Record` class.
-  :php:class:`Doctrine_PrimaryKey_TestCase` is a bad name, because it's too
   generic.

----------------------
Test Method Guidelines
----------------------

Methods should support agile documentation and should be named so that
if it fails, it is obvious what failed. They should also give
information of the system they test

For example the method test name
``Doctrine_Export_Pgsql_TestCase::testCreateTableSupportsAutoincPks()``
is a good name.

Test method names can be long, but the method content should not be. If
you need several assert-calls, divide the method into smaller methods.
There should never be assertions within any loops, and rarely within
functions.

.. note::

    Commonly used testing method naming convention
    ``TestCase::test[methodName]`` is **not** allowed in Doctrine. So in
    this case ``Doctrine_Export_Pgsql_TestCase::testCreateTable()``
    would not be allowed!

==========
Conclusion
==========

Unit testing in a piece of software like Doctrine is so incredible
important. Without it, it would be impossible to know if a change we
make has any kind of negative affect on existing working use cases. With
our collection of unit tests we can be sure that the changes we make
won't break existing functionality.

Now lets move on to learn about how we can :doc:`improving-performance` when using Doctrine.