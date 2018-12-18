**********
Migrations
**********

The Doctrine migration package allows you to easily update your
production databases through a nice programmatic interface. The changes
are done in a way so that your database is versioned and you can walk
backwards and forwards through the database versions.

=====================
Performing Migrations
=====================

Before we learn how to create the migration classes lets take a look at
how we can run migrations so that we can implement them in our Doctrine
test environment in the next section.

First lets create a new instance of :php:class:`Doctrine_Migration` and pass it
the path to our migration classes:

::

    $migration = new Doctrine_Migration( '/path/to/migration_classes', $conn );

.. note::

    Notice the second argument to the :php:class:`Doctrine_Migration`
    constructor. You can pass an optional :php:class:`Doctrine_Connection`
    instance. If you do not pass the connection for the migration class
    to use, it will simply grab the current connection.

Now we can migrate to the latest version by calling the ``migrate()``
method:

::

    $migration->migrate();

If you want to migrate to a specific version you can pass an argument to
``migrate()``. For example we can migrate to version 3 from 0:

::

    $migration->migrate( 3 );

Now you can migrate back to version 0 from 3:

::

    $migration->migrate( 0 );

If you want to get the current version of the database you can use the
``getCurrentVersion()`` method:

::

    echo $migration->getCurrentVersion();

.. tip::

    Omitting the version number argument for the ``migrate()``
    method means that internally Doctrine will try and migrate to the
    latest class version number that it could find in the directory you
    passed.

.. note::
    **Transactions in Migrations** Internally Doctrine does not
    wrap migration versions in transactions. It is up to you as the
    developer to handle exceptions and transactions in your migration
    classes. Remember though, that very few databases support
    transactional DDL. So on most databases, even if you wrap the
    migrations in a transaction, any DDL statements like create, alter,
    drop, rename, etc., will take effect anyway.

=========
Implement
=========

Now that we know how to perform migrations lets implement a little
script in our Doctrine test environment named ``migrate.php``.

First we need to create a place for our migration classes to be stored
so lets create a directory named ``migrations``:

.. code-block:: sh

    $ mkdir migrations

Now create the ``migrate.php`` script in your favorite editor and place
the following code inside:

::

    // migrate.php
    require_once('bootstrap.php');

    $migration = new Doctrine_Migration( 'migrations' );
    $migration->migrate();

=========================
Writing Migration Classes
=========================

Migration classes consist of a simple class that extends from
:php:class:`Doctrine_Migration`. You can define a ``up()`` and ``down()`` method
that is meant for doing and undoing changes to a database for that
migration version.

.. note::

    The name of the class can be whatever you want, but the
    name of the file which contains the class must have a prefix
    containing a number that is used for loading the migrations in the
    correct order.

Below are a few examples of some migration classes you can use to build
your database starting from version 1.

For the first version lets create a new table named ``migration_test``:

::

    // migrations/1_add_table.php
    class AddTable extends Doctrine_Migration_Base
    {
        public function up()
        {
            $this->createTable( 'migration_test', array( 'field1' => array( 'type' => 'string' ) ) );
        }

        public function down()
        {
            $this->dropTable( 'migration_test' );
        }
    }

Now lets create a second version where we add a new column to the table
we added in the previous version:

::

    // migrations/2_add_column.php
    class AddColumn extends Doctrine_Migration_Base
    {
        public function up()
        {
            $this->addColumn( 'migration_test', 'field2', 'string' );
        }

        public function down()
        {
            $this->removeColumn( 'migration_test', 'field2' );
        }
    }

Finally, lets change the type of the ``field1`` column in the table we
created previously:

::

    // migrations/3_change_column.php
    class ChangeColumn extends Doctrine_Migration_Base
    {
        public function up()
        {
            $this->changeColumn( 'migration_test', 'field2', 'integer' );
        }

        public function down()
        {
            $this->changeColumn( 'migration_test', 'field2', 'string' );
        }
    }

Now that we have created the three migration classes above we can run
our ``migrate.php`` script we implemented earlier:

.. code-block:: sh

    $ php migrate.php

If you look in the database you will see that we have the table named
``migrate_test`` created and the version number in the
``migration_version`` is set to three.

If you want to migrate back to where we started you can pass a version
number to the ``migrate()`` method in the ``migrate.php`` script:

::

    // migrate.php

    // ...
    $migration = new Doctrine_Migration( 'migrations' );
    $migration->migrate( 0 );

Now run the ``migrate.php`` script:

.. code-block:: sh

    $ php migrate.php

If you look in the database now, everything we did in the ``up()``
methods has been reversed by the contents of the ``down()`` method.

---------------------
 Available Operations
---------------------

Here is a list of the available methods you can use to alter your
database in your migration classes.

^^^^^^^^^^^^
Create Table
^^^^^^^^^^^^

::

    // ...
    public function up()
    {
        $columns = array(
            'id' => array(
                'type'     => 'integer',
                'unsigned' => 1,
                'notnull'  => 1,
                'default'  => 0
            ),
            'name' => array(
                'type'   => 'string',
                'length' => 12
            ),
            'password' => array(
                'type'   => 'string',
                'length' => 12
            )
        );

        $options = array(
            'type'    => 'INNODB',
            'charset' => 'utf8'
        );

        $this->createTable( 'table_name', $columns, $options );
    }
    // ...

.. note::

    You might notice already that the data structures used to
    manipulate the your schema are the same as the data structures used
    with the database abstraction layer. This is because internally the
    migration package uses the database abstraction layer to perform the
    operations specified in the migration classes.

^^^^^^^^^^
Drop Table
^^^^^^^^^^

::

    // ...
    public function down()
    {
        $this->dropTable( 'table_name' );
    }
    // ...

^^^^^^^^^^^^
Rename Table
^^^^^^^^^^^^

::

    // ...
    public function up()
    {
        $this->renameTable( 'old_table_name', 'new_table_name' );
    }
    // ...

^^^^^^^^^^^^^^^^^
Create Constraint
^^^^^^^^^^^^^^^^^

::

    // ...
    public function up()
    {
        $definition = array(
            'fields' => array(
                'username' => array()
            ),
            'unique' => true
        );

        $this->createConstraint( 'table_name', 'constraint_name', $definition );
    }
    // ...

^^^^^^^^^^^^^^^
Drop Constraint
^^^^^^^^^^^^^^^

**Now the opposite** ``down()`` **would look like the following:**

::

    // ...
    public function down()
    {
        $this->dropConstraint( 'table_name', 'constraint_name' );
    }
    // ...

^^^^^^^^^^^^^^^^^^
Create Foreign Key
^^^^^^^^^^^^^^^^^^

::

    // ...
    public function up()
    {
        $definition = array(
            'local'        => 'email_id',
            'foreign'      => 'id',
            'foreignTable' => 'email',
            'onDelete'     => 'CASCADE',
        );

        $this->createForeignKey( 'table_name', 'email_foreign_key', $definition );
    }
    // ...

The valid options for the ``$definition`` are:

============  ==============================
Name          Description
============  ==============================
name          Optional constraint name
local         The local field(s)
foreign       The foreign reference field(s)
foreignTable  The name of the foreign table
onDelete      Referential delete action
onUpdate      Referential update action
deferred      Deferred constraint checking
============  ==============================

^^^^^^^^^^^^^^^^
Drop Foreign Key
^^^^^^^^^^^^^^^^

::

    // ...
    public function down()
    {
        $this->dropForeignKey( 'table_name', 'email_foreign_key' );
    }
    // ...

^^^^^^^^^^
Add Column
^^^^^^^^^^

::

    // ...
    public function up()
    {
        $this->addColumn( 'table_name', 'column_name', 'string', $length, $options );
    }
    // ...

^^^^^^^^^^^^^
Rename Column
^^^^^^^^^^^^^

.. note::

    Some DBMS like sqlite do not implement the rename column
    operation. An exception is thrown if you try and rename a column
    when using a sqlite connection.

::

    // ...
    public function up()
    {
        $this->renameColumn( 'table_name', 'old_column_name', 'new_column_name' );
    }
    // ...

^^^^^^^^^^^^^
Change Column
^^^^^^^^^^^^^

**Change any aspect of an existing column:**

::

    // ...
    public function up()
    {
        $options = array( 'length' => 1 );
        $this->changeColumn( 'table_name', 'column_name', 'tinyint', $options );
    }
    // ...

^^^^^^^^^^^^^
Remove Column
^^^^^^^^^^^^^

::

    // ...
    public function up()
    {
        $this->removeColumn( 'table_name', 'column_name' );
    }
    // ...

^^^^^^^^^^^^^^^^^^^^^^
Irreversible Migration
^^^^^^^^^^^^^^^^^^^^^^

.. tip::

    Sometimes you may perform some operations in the ``up()``
    method that cannot be reversed. For example if you remove a column
    from a table. In this case you need to throw a new
    :php:class:`Doctrine_Migration_IrreversibleMigrationException` exception.

::

    // ...
    public function down()
    {
        throw new Doctrine_Migration_IrreversibleMigrationException( 'The remove column operation cannot be undone!' );
    }
    // ...

^^^^^^^^^
Add Index
^^^^^^^^^

::

    // ...
    public function up()
    {
        $options = array(
            'fields' => array(
                'username' => array(
                    'sorting' => 'ascending'
                ),
                'last_login' => array()
            )
        );

        $this->addIndex( 'table_name', 'index_name', $options );
    }
    // ...

^^^^^^^^^^^^
Remove Index
^^^^^^^^^^^^

::

    // ...
    public function down()
    {
        $this->removeIndex( 'table_name', 'index_name' );
    }
    // ...

-------------------
 Pre and Post Hooks
-------------------

Sometimes you may need to alter the data in the database with your
models. Since you may create a table or make a change, you have to do
the data altering after the ``up()`` or ``down()`` method is processed.
We have hooks in place for this named ``preUp()``, ``postUp()``,
``preDown()``, and ``postDown()``. Define these methods and they will be
triggered:

::

    // migrations/1_add_table.php
    class AddTable extends Doctrine_Migration_Base
    {
        public function up()
        {
            $this->createTable( 'migration_test', array(
                'field1' => array(
                    'type' => 'string'
                )
            );
        }

        public function postUp()
        {
            $migrationTest         = new MigrationTest();
            $migrationTest->field1 = 'Initial record created by migrations';
            $migrationTest->save();
        }

        public function down()
        {
            $this->dropTable( 'migration_test' );
        }
    }

.. note::

    The above example assumes you have created and made
    available the ``MigrationTest`` model. Once the ``up()`` method is
    executed the ``migration_test`` table is created so the
    ``MigrationTest`` model can be used. We have provided the definition
    of this model below.

::

    // models/MigrationTest.php
    class MigrationTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'field1', 'string' );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    MigrationTest:
      columns:
        field1: string

-------------------
 Up/Down Automation
-------------------

In Doctrine migrations it is possible most of the time to automate the
opposite of a migration method. For example if you create a new column
in the up of a migration, we should be able to easily automate the down
since all we need to do is remove the column that was created. This is
possible by using the ``migrate()`` function for both the ``up`` and
``down``.

The ``migrate()`` method accepts an argument of $direction and it will
either have a value of ``up`` or ``down``. This value is passed to the
first argument of functions like ``column``, ``table``, etc.

Here is an example where we automate the adding and removing of a column

::

    class MigrationTest extends Doctrine_Migration_Base
    {
        public function migrate( $direction )
        {
            $this->column( $direction, 'table_name', 'column_name', 'string', '255' );
        }
    }

Now when we run up with the above migration, the column will be added
and when we run down the column will be removed.

Here is a list of the following migration methods that can be automated:

====================  =======================================
Automate Method Name  Automates
====================  =======================================
**table()**           **createTable()/dropTable()**
**constraint()**      **createConstraint()/dropConstraint()**
**foreignKey()**      **createForeignKey()/dropForeignKey()**
**column()**          **addColumn()/removeColumn()**
**index()**           **addIndex()/removeIndex()**
====================  =======================================

----------------------
 Generating Migrations
----------------------

Doctrine offers the ability to generate migration classes a few
different ways. You can generate a set of migrations to re-create an
existing database, or generate migration classes to create a database
for an existing set of models. You can even generate migrations from
differences between two sets of schema information.

^^^^^^^^^^^^^
From Database
^^^^^^^^^^^^^

To generate a set of migrations from the existing database connections
it is simple, just use ``Doctrine_Core::generateMigrationsFromDb()``.

::

    Doctrine_Core::generateMigrationsFromDb( '/path/to/migration/classes' );

^^^^^^^^^^^^^^^^^^^^
From Existing Models
^^^^^^^^^^^^^^^^^^^^

To generate a set of migrations from an existing set of models it is
just as simple as from a database, just use
``Doctrine_Core::generateMigrationsFromModels()``.

::

    Doctrine_Core::generateMigrationsFromModels( '/path/to/migration/classes', '/path/to/models' );

^^^^^^^^^
Diff Tool
^^^^^^^^^

Sometimes you may want to alter your models and be able to automate the
migration process for your changes. In the past you would have to write
the migration classes manually for your changes. Now with the diff tool
you can make your changes then generate the migration classes for the
changes.

The diff tool is simple to use. It accepts a "from" and a "to" and they
can be one of the following:

 -  Path to yaml schema files
 -  Name of an existing database connection
 -  Path to an existing set of models

A simple example would be to create two YAML schema files, one named
``schema1.yml`` and another named ``schema2.yml``.

The ``schema1.yml`` contains a simple ``User`` model:

.. code-block:: yaml

    ---
    # schema1.yml

    User:
      columns:
        username: string(255)
        password: string(255)

Now imagine we modify the above schema and want to add a
``email_address`` column:

.. code-block:: yaml

    ---
    # schema1.yml

    User:
      columns:
        username: string(255)
        password: string(255)
        email_address: string(255)

Now we can easily generate a migration class which will add the new
column to our database:

::

    Doctrine_Core::generateMigrationsFromDiff( '/path/to/migration/classes', '/path/to/schema1.yml', '/path/to/schema2.yml' );

This will produce a file at the path ``/path/to/migration/classes/1236199329_version1.php``

::

    class Version1 extends Doctrine_Migration_Base
    {
        public function up()
        {
            $this->addColumn( 'user', 'email_address', 'string', '255', array() );
        }

        public function down()
        {
            $this->removeColumn( 'user', 'email_address' );
        }
    }

Now you can easily migrate your database and add the new column!

==========
Conclusion
==========

Using migrations is highly recommended for altering your production
database schemas as it is a safe and easy way to make changes to your
schema.

Migrations are the last feature of Doctrine that we will discuss in this
book. The final chapters will discuss some other topics that will help
you be a better Doctrine developers on a day-to-day basis. First lets
discuss some of the other :doc:`utilities` that Doctrine provides.
