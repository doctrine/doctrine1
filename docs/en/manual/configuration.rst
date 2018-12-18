..  vim: set ts=4 sw=4 tw=79 :

*************
Configuration
*************

Doctrine controls configuration of features and functionality using
attributes. In this section we will discuss how to set and get
attributes as well as an overview of what attributes exist for you to
use to control Doctrine functionality.

=======================
Levels of Configuration
=======================

Doctrine has a three-level configuration structure. You can set
configuration attributes at a global, connection and table level. If the
same attribute is set on both lower level and upper level, the uppermost
attribute will always be used. So for example if a user first sets
default fetchmode in global level to :php:const:`Doctrine_Core::FETCH_BATCH`
and then sets a table fetchmode to :php:const:`Doctrine_Core::FETCH_LAZY`, the
lazy fetching strategy will be used whenever the records of that table
are being fetched.

*  **Global level**
    The attributes set in global level will affect every connection and every
    table in each connection.
*  **Connection level**
    The attributes set in connection level will take effect on each table in
    that connection.
*  **Table level**
    The attributes set in table level will take effect only on that table.

In the following example we set an attribute at the global level:

::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);

In the next example above we override the global attribute on given
connection:

::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE);

In the last example we override once again the connection level
attribute in the table level:

::

    // bootstrap.php
    $table = Doctrine_Core::getTable('User');
    $table->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);

.. note::

    We haven't introduced the above used :php:meth:`Doctrine_Core::getTable`
    method. You will learn more about the table objects used in Doctrine in the
    :doc:`component-overview` section of the next chapter.

==================
Default Attributes
==================

Doctrine has a few specific attributes available that allow you to
specify the default values of things that in the past were hardcoded
values. Such as default column length, default column type, etc.

----------------------
Default Column Options
----------------------

It is possible to specify an array of default options to be used on
every column in your model.

::

    // bootstrap.php
    $manager->setAttribute(
        Doctrine::ATTR_DEFAULT_COLUMN_OPTIONS,
        array('type' => 'string', 'length' => 255, 'notnull' => true)
    );

---------------------
Default Added Auto Id
---------------------

You can customize the properties of the automatically added primary key
in Doctrine models.

::

    $manager->setAttribute(
        Doctrine::ATTR_DEFAULT_IDENTIFIER_OPTIONS,
        array('name' => '%s_id', 'type' => 'string', 'length' => 16)
    );

.. note::

    The ``%s`` string in the name is replaced with the table name.

===========
Portability
===========

Each database management system (DBMS) has it's own behaviors. For example,
some databases capitalize field names in their output, some lowercase them,
while others leave them alone. These quirks make it difficult to port your
applications over to another database type.  Doctrine strives to overcome these
differences so your applications can switch between DBMS's without any changes.
For example switching from sqlite to mysql.

The portability modes are bitwised, so they can be combined using ``|`` and
removed using ``^``. See the examples section below on how to do this.

.. tip::

    You can read more about the bitwise operators on the
    `PHP website <http://www.php.net/language.operators.bitwise>`_.

---------------------------
Portability Mode Attributes
---------------------------

Below is a list of all the available portability attributes and the
description of what each one does:

=====================================  ===========
Name                                   Description
=====================================  ===========
``PORTABILITY_ALL``                    Turn on all portability features. This is the default setting.
``PORTABILITY_DELETE_COUNT``           Force reporting the number of rows deleted. Some DBMS's don't count the number of rows deleted when performingsimple ``DELETE FROM`` tablename queries. This mode tricks such DBMS's into telling the count by adding ``WHERE 1=1`` to the end of ``DELETE`` queries.
``PORTABILITY_EMPTY_TO_NULL``          Convert empty strings values to null in data in and output. Needed because Oracle considers empty strings to be null, while most other DBMS's know the difference between empty and null.
``PORTABILITY_ERRORS``                 Makes certain error messages in certain drivers compatible with those from other DBMS's
``PORTABILITY_FIX_ASSOC_FIELD_NAMES``  This removes any qualifiers from keys in associative fetches. Some RDBMS, like for example SQLite, will by default use the fully qualified name for a column in assoc fetches if it is qualified in a query.
``PORTABILITY_FIX_CASE``               Convert names of tables and fields to lower or upper case in all methods. The case depends on the field_case option that may be set to either ``CASE_LOWER`` (default) or ``CASE_UPPER``
``PORTABILITY_NONE``                   Turn off all portability features.
``PORTABILITY_NUMROWS``                Enable hack that makes ``numRows`` work in Oracle.
``PORTABILITY_EXPR``                   Makes DQL API throw exceptions when non-portable expressions are being used.
``PORTABILITY_RTRIM``                  Right trim the data output for all data fetches. This does not applied in drivers for RDBMS that automatically right trim values of fixed length character values, even if they do not right trim value of variable length character values.
=====================================  ===========

--------
Examples
--------

Now we can use the ``setAttribute`` method to enable portability for
lowercasing and trimming with the following code::

    // bootstrap.php
    $conn->setAttribute(
        Doctrine_Core::ATTR_PORTABILITY,
        Doctrine_Core::PORTABILITY_FIX_CASE | Doctrine_Core::PORTABILITY_RTRIM
    );

Enable all portability options except trimming::

    // bootstrap.php
    $conn->setAttribute(
        Doctrine_Core::ATTR_PORTABILITY,
        Doctrine_Core::PORTABILITY_ALL ^ Doctrine_Core::PORTABILITY_RTRIM
    );

==================
Identifier quoting
==================

You can quote the db identifiers (table and field names) with
:php:meth:`quoteIdentifier`. The delimiting style depends on which database
driver is being used.

.. note::

    Just because you CAN use delimited identifiers, it doesn't
    mean you SHOULD use them. In general, they end up causing way more
    problems than they solve. Anyway, it may be necessary when you have
    a reserved word as a field name (in this case, we suggest you to
    change it, if you can).

Some of the internal Doctrine methods generate queries. Enabling the
``quote_identifier`` attribute of Doctrine you can tell Doctrine to
quote the identifiers in these generated queries. For all user supplied
queries this option is irrelevant.

Portability is broken by using the following characters inside delimited
identifiers:

============  ==============  ======
Name          Character       Driver
============  ==============  ======
backtick      :literal:`\``   MySQL
double quote  ``"``           Oracle
brackets      ``[`` or ``]``  Access
============  ==============  ======


Delimited identifiers are known to generally work correctly under the
following drivers: Mssql, Mysql, Oracle, Pgsql, Sqlite and Firebird.

When using the :php:const:`Doctrine_Core::ATTR_QUOTE_IDENTIFIER` option, all
of the field identifiers will be automatically quoted in the resulting
SQL statements::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);

Will result in a SQL statement that all the field names are quoted with
the backtick ````` operator (in MySQL):

.. code-block:: sql

    SELECT * FROM sometable WHERE `id` = '123'

As opposed to:

.. code-block:: sql

    SELECT * FROM sometable WHERE id = '123'

=====================
Hydration Overwriting
=====================

By default Doctrine is configured to overwrite any local changes you
have on your objects if you were to query for some objects which have
already been queried for and modified::

    $user = Doctrine_Core::getTable('User')->find(1);
    $user->username = 'newusername';

Now I have modified the above object and if I were to query for the same
data again, my local changes would be overwritten::

    $user = Doctrine_Core::getTable('User')->find(1);
    echo $user->username;

You can disable this behavior by using the :php:const:`Doctrine_Core::ATTR_HYDRATE_OVERWRITE`
attribute::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);

Now if were to run the same test we ran above, the modified username
would not be overwritten.

=====================
Configure Table Class
=====================

If you want to configure the class to be returned when using the
:php:meth:`Doctrine_Core::getTable` method you can set the
:php:const:`Doctrine_Core::ATTR_TABLE_CLASS` attribute. The only requirement is that the class
extends :php:class:`Doctrine_Table`.

::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'MyTableClass');

Now the ``MyTableClass`` would look like the following::

    class MyTableClass extends Doctrine_Table
    {
        public function myMethod()
        {
            // run some query and return the results
        }
    }

Now when you do the following it will return an instance of
``MyTableClass``::

    $user = $conn->getTable('MyModel')->myMethod();

If you want to customize the table class even further you can customize
it for each model. Just create a class named ``MyModelTable`` and make
sure it is able to be autoloaded.

::

    class MyModelTable extends MyTableClass
    {
        public function anotherMethod()
        {
            // run some query and return the results
        }
    }

Now when I execute the following code it will return an instance of
``MyModelTable``::

    echo get_class($conn->getTable('MyModel')); // MyModelTable

=====================
Configure Query Class
=====================

If you would like to configure the base query class returned when you
create new query instances, you can use the :php:const:`Doctrine_Core::ATTR_QUERY_CLASS`
attribute. The only requirement is that it extends the
``Doctrine_Query`` class.

::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'MyQueryClass');

The ``MyQueryClass`` would look like the following::

    class MyQueryClass extends Doctrine_Query
    {
    }

Now when you create a new query it will return an instance of
``MyQueryClass``::

    $q = Doctrine_Core::getTable('User') ->createQuery('u');
    echo get_class($q); // MyQueryClass

==========================
Configure Collection Class
==========================

Since you can configure the base query and table class, it would only make
sense that you can also customize the collection class Doctrine should use. We
just need to set the :php:const:`Doctrine_Core::ATTR_COLLECTION_CLASS`
attribute.

::

    // bootstrap.php
    $conn->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'MyCollectionClass');

The only requirement of the ``MyCollectionClass`` is that it must extend
``Doctrine_Collection``::

    $phonenumbers = $user->Phonenumber;
    echo get_class($phonenumbers); // MyCollectionClass

=========================
Disabling Cascading Saves
=========================

You can optionally disable the cascading save operations which are enabled by
default for convenience with the :php:const:`Doctrine_Core::ATTR_CASCADE_SAVES`
attribute. If you set this attribute to ``false`` it will only cascade and save
if the record is dirty. This means that you can't cascade and save records who
are dirty that are more than one level deep in the hierarchy, but you benefit
with a significant performance improvement.

::

    $conn->setAttribute(Doctrine_Core::ATTR_CASCADE_SAVES, false);

=========
Exporting
=========

The export attribute is used for telling Doctrine what it should export
when exporting classes to your database for creating your tables.

If you don't want to export anything when exporting you can use::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_NONE);

For exporting tables only (but not constraints) you can use on of the
following::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_TABLES);

You can also use the following syntax as it is the same as the above::

    // bootstrap.php
    $manager->setAttribute(
        Doctrine_Core::ATTR_EXPORT,
        Doctrine_Core::EXPORT_ALL ^ Doctrine_Core::EXPORT_CONSTRAINTS
    );

For exporting everything (tables and constraints) you can use::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_ALL);

============================
Naming convention attributes
============================

Naming convention attributes affect the naming of different database
related elements such as tables, indexes and sequences. Basically every
naming convention attribute has affect in both ways. When importing
schemas from the database to classes and when exporting classes into
database tables.

So for example by default Doctrine naming convention for indexes is
``%s_idx``. Not only do the indexes you set get a special suffix, also
the imported classes get their indexes mapped to their non-suffixed
equivalents. This applies to all naming convention attributes.

-----------------
Index name format
-----------------

:php:const:`Doctrine_Core::ATTR_IDXNAME_FORMAT` can be used for changing the
naming convention of indexes. By default Doctrine uses the format
``[name]_idx``. So defining an index called 'ageindex' will actually be
converted into 'ageindex_idx'.

You can change the index naming convention with the following code::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_IDXNAME_FORMAT, '%s_index');

--------------------
Sequence name format
--------------------

Similar to :php:const:`Doctrine_Core::ATTR_IDXNAME_FORMAT`,
:php:const:`Doctrine_Core::ATTR_SEQNAME_FORMAT` can be used for changing the
naming convention of sequences. By default Doctrine uses the format
``[name]_seq``, hence creating a new sequence with the name of
``mysequence`` will lead into creation of sequence called
``mysequence_seq``.

You can change the sequence naming convention with the following code::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_SEQNAME_FORMAT, '%s_sequence');

-----------------
Table name format
-----------------

The table name format can be changed the same as the index and sequence
name format with the following code::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s_table');

--------------------
Database name format
--------------------

The database name format can be changed the same as the index, sequence
and table name format with the following code::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_DBNAME_FORMAT, 'myframework_%s');

---------------------
Validation attributes
---------------------

Doctrine provides complete control over what it validates. The validation
procedure can be controlled with :php:const:`Doctrine_Core::ATTR_VALIDATE`.

The validation modes are bitwised, so they can be combined using ``|`` and
removed using ``^``. See the examples section below on how to do this.

-------------------------
Validation mode constants
-------------------------

========================  ====================================================
Name                      Description
========================  ====================================================
``VALIDATE_NONE``         Turns off the whole validation procedure.
``VALIDATE_LENGTHS``      Makes Doctrine validate all field lengths.
``VALIDATE_TYPES``        Makes Doctrine validate all field types. Doctrine
                          does loose typevalidation. This means that for
                          example string with value '13.3' willnot pass as an
                          integer but '13' will.
``VALIDATE_CONSTRAINTS``  Makes Doctrine validate all fieldconstraints such
                          as ``notnull``, ``email`` etc.
``VALIDATE_ALL``          Turns on all validations.
========================  ====================================================

.. note::

    Validation by default is turned off so if you wish for your
    data to be validated you will need to enable it. Some examples of
    how to change this configuration are provided below.

--------
Examples
--------

You can turn on all validations by using the
:php:const:`Doctrine_Core::VALIDATE_ALL` attribute with the following code::

    // bootstrap.php
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);

You can also configure Doctrine to validate lengths and types, but not
constraints with the following code::

    // bootstrap.php
    $manager->setAttribute(
        Doctrine_Core::ATTR_VALIDATE,
        Doctrine_Core::VALIDATE_LENGTHS | Doctrine_Core::VALIDATE_TYPES
    );

==========
Conclusion
==========

Now we have gone over some of the most common attributes used to
configure Doctrine. Some of these attributes may not apply to you ever
or you may not understand what you could use them for now. As you read
the next chapters you will see which attributes you do and don't need to
use and things will begin to make more sense.

If you saw some attributes you wanted to change the value above, then
you should have added it to your ``bootstrap.php`` file and it should
look something like the following now::

    /* Bootstrap Doctrine.php, register autoloader and specify
       configuration attributes */

    require_once('../doctrine/branches/1.2/lib/Doctrine.php');
    spl_autoload_register(array('Doctrine', 'autoload'));
    $manager = Doctrine_Manager::getInstance();

    $conn = Doctrine_Manager::connection('sqlite::memory:', 'doctrine');

    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);
    $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_ALL);
    $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

Now we are ready to move on to the next chapter where we will learn
everything there is to know about Doctrine :doc:`connections`.
