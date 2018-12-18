..  vim: set ts=4 sw=4 tw=79 :

***************
Defining Models
***************

As we mentioned before, at the lowest level in Doctrine your schema is
represented by a set of php classes that map the schema meta data for your
database tables.

In this chapter we will explain in detail how you can map your schema
information using php code.

=======
Columns
=======

One problem with database compatibility is that many databases differ in their
behavior of how the result set of a query is returned. MySQL leaves the field
names unchanged, which means if you issue a query of the form ``"SELECT myField
FROM ..."`` then the result set will contain the field ``myField``.

Unfortunately, this is just the way MySQL and some other databases do
it. Postgres for example returns all field names in lowercase whilst
Oracle returns all field names in uppercase. "So what? In what way does
this influence me when using Doctrine?", you may ask. Fortunately, you
don't have to bother about that issue at all.

Doctrine takes care of this problem transparently. That means if you define a
derived Record class and define a field called ``myField`` you will always
access it through ``$record->myField`` (or ``$record['myField']``, whatever
you prefer) no matter whether you're using MySQL or Postgres or Oracle etc.

In short: You can name your fields however you want, using under_scores,
camelCase or whatever you prefer.

.. note::

    In Doctrine columns and column aliases are case sensitive.  So when you are
    using columns in your DQL queries, the column/field names must match the
    case in your model definition.

--------------
Column Lengths
--------------

In Doctrine column length is an integer that specifies the column length. Some
column types depend not only the given portable type but also on the given
length. For example type ``string`` with length 1000 will be translated into native
type ``TEXT`` on mysql.

The length is different depending on the type of column you are using:

*   ``integer``
        Length is the the number of bytes the integer occupies.
*   ``string``
        Number of the characters allowed in the string.
*   ``float/decimal``
        Total number of characters allowed excluding the decimal.
*   ``enum``
        If using native enum length does not apply but if using
        emulated enums then it is just the string length of the
        column value.

--------------
Column Aliases
--------------

Doctrine offers a way of setting column aliases. This can be very useful when
you want to keep the application logic separate from the database logic. For
example if you want to change the name of the database field all you need to
change at your application is the column definition.

::

    // models/Book.php
    class Book extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('bookTitle as title', 'string');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Book:
      columns:
        bookTitle:
          name: bookTitle as title
          type: string

Now the column in the database is named bookTitle but you can access the
property on your objects using title.

.. code-block:: php

    // test.php
    $book = new Book();
    $book->title = 'Some book';
    $book->save();

--------------
Default values
--------------

Doctrine supports default values for all data types. When default value
is attached to a record column this means two things. First this value
is attached to every newly created Record and when Doctrine creates your
database tables it includes the default value in the create table
statement.

::

    // models/generated/BaseUser.php
    class User extends BaseUser
    {
        public function setTableDefinition()
        {
            $this->hasColumn('username', 'string', 255,
                array('default' => 'default username'));
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
      # ...
      columns:
        username:
          type: string(255)
          default: default username
        #...

Now when you print the name on a brand new User record it will print the
default value::

    // test.php
    $user = new User();
    echo $user->username; // default username

----------
Data types
----------

^^^^^^^^^^^^
Introduction
^^^^^^^^^^^^

All DBMS provide multiple choice of data types for the information that can be
stored in their database table fields. However, the set of data types made
available varies from DBMS to DBMS.

To simplify the interface with the DBMS supported by Doctrine, a base set of
data types was defined. Applications may access them independently of the
underlying DBMS.

The Doctrine applications programming interface takes care of mapping data
types when managing database options. It is also able to convert that is sent
to and received from the underlying DBMS using the respective driver.

The following data type examples should be used with Doctrine's
:php:meth:`createTable` method. The example array at the end of the data types
section may be used with :php:meth:`createTable` to create a portable table on
the DBMS of choice (please refer to the main Doctrine documentation to find out
what DBMS back ends are properly supported). It should also be noted that the
following examples do not cover the creation and maintenance of indices, this
chapter is only concerned with data types and the proper usage thereof.

It should be noted that the length of the column affects in database level type
as well as application level validated length (the length that is validated
with Doctrine validators).

#. Example: Column named ``content`` with type ``string`` and length 3000 results in
   database type ``TEXT`` of which has database level length of 4000. However when
   the record is validated it is only allowed to have 'content' -column with
   maximum length of 3000.
#. Example: Column with type ``integer`` and length 1 results in ``TINYINT``
   on many databases.

In general Doctrine is smart enough to know which integer/string type to
use depending on the specified length.

^^^^^^^^^^^^^^
Type modifiers
^^^^^^^^^^^^^^

Within the Doctrine API there are a few modifiers that have been
designed to aid in optimal table design. These are:

*  The notnull modifiers
*  The length modifiers
*  The default modifiers
*  unsigned modifiers for some field definitions, although not all
   DBMS's support this modifier for integer field types.
*  collation modifiers (not supported by all drivers)
*  fixed length modifiers for some field definitions.

Building upon the above, we can say that the modifiers alter the field
definition to create more specific field types for specific usage scenarios.
The notnull modifier will be used in the following way to set the default DBMS
NOT NULL Flag on the field to true or false, depending on the DBMS's definition
of the field value: In PostgreSQL the "NOT NULL" definition will be set to "NOT
NULL", whilst in MySQL (for example) the "NULL" option will be set to "NO". In
order to define a "NOT NULL" field type, we simply add an extra parameter to
our definition array (See the :ref:`examples <data-type-examples>` in the
following section)

.. code-block:: text

    'sometime' = array(
        'type' => 'time',
        'default' => '12:34:05',
        'notnull' => true,
    ),

Using the above example, we can also explore the default field operator.
Default is set in the same way as the notnull operator to set a default value
for the field. This value may be set in any character set that the DBMS
supports for text fields, and any other valid data for the field's data type.
In the above example, we have specified a valid time for the "Time" data type,
'12:34:05'. Remember that when setting default dates and times, as well as
datetimes, you should research and stay within the epoch of your chosen DBMS,
otherwise you will encounter difficult to diagnose errors!

.. code-block:: text

    'sometext' = array(
        'type' => 'string',
        'length' => 12,
    ),

The above example will create a character varying field of length 12 characters
in the database table. If the length definition is left out, Doctrine will
create a length of the maximum allowable length for the data type specified,
which may create a problem with some field types and indexing. Best practice is
to define lengths for all or most of your fields.

^^^^^^^
Boolean
^^^^^^^

The boolean data type represents only two values that can be either 1 or 0. Do
not assume that these data types are stored as integers because some DBMS
drivers may implement this type with single character text fields for a matter
of efficiency. Ternary logic is possible by using null as the third possible
value that may be assigned to fields of this type.

.. note::

    The next several examples are not meant for you to use and
    give them a try. They are simply for demonstrating purposes to show
    you how to use the different Doctrine data types using PHP code or
    YAML schema files.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('booltest', 'boolean');
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        booltest: boolean

^^^^^^^
Integer
^^^^^^^

The integer type is the same as integer type in PHP. It may store integer
values as large as each DBMS may handle.

Fields of this type may be created optionally as unsigned integers but
not all DBMS support it. Therefore, such option may be ignored. Truly
portable applications should not rely on the availability of this
option.

The integer type maps to different database type depending on the column
length.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('integertest', 'integer', 4, array(
                'unsigned' => true
            ));
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        integertest:
          type: integer(4)
          unsigned: true

^^^^^
Float
^^^^^

The float data type may store floating point decimal numbers. This data
type is suitable for representing numbers withina large scale range that
do not require high accuracy. The scale and the precision limits of the
values that may be stored in a database depends on the DBMS that it is
used.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('floattest', 'float');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        floattest: float

^^^^^^^
Decimal
^^^^^^^

The decimal data type may store fixed precision decimal numbers. This
data type is suitable for representing numbers that require high
precision and accuracy.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('decimaltest', 'decimal');
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        decimaltest: decimal

You can specify the length of the decimal just like you would set the
``length`` of any other column and you can specify the ``scale`` as an
option in the third argument::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition() {
            $this->hasColumn('decimaltest', 'decimal', 18,
                array('scale' => 2)
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        decimaltest:
          type: decimal(18)
          scale: 2

^^^^^^
String
^^^^^^

The text data type is available with two options for the length: one
that is explicitly length limited and another of undefined length that
should be as large as the database allows.

The length limited option is the most recommended for efficiency
reasons. The undefined length option allows very large fields but may
prevent the use of indexes, nullability and may not allow sorting on
fields of its type.

The fields of this type should be able to handle 8 bit characters.
Drivers take care of DBMS specific escaping of characters of special
meaning with the values of the strings to be converted to this type.

By default Doctrine will use variable length character types. If fixed
length types should be used can be controlled via the fixed modifier.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('stringtest', 'string', 200, array(
                'fixed' => true
            ));
        }
    }




Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        stringtest:
          type: string(200)
          fixed: true

^^^^^
Array
^^^^^

This is the same as the 'array' type in PHP::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('arraytest', 'array', 10000);
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        arraytest: array(10000)

^^^^^^
Object
^^^^^^

Doctrine supports objects as column types. Basically you can set an
object to a field and Doctrine handles automatically the serialization /
unserialization of that object.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('objecttest', 'object');
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        objecttest: object

.. note::

    The array and object types simply serialize the data when persisting to the
    database and unserialize the data when pulling from the database.

^^^^
Blob
^^^^

Blob (Binary Large OBject) data type is meant to store data of undefined
length that may be too large to store in text fields, like data that is
usually stored in files.

Blob fields are usually not meant to be used as parameters of query
search clause (``WHERE``) unless the underlying DBMS supports a feature
usually known as "full text search".

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('blobtest', 'blob');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        blobtest: blob

^^^^
Clob
^^^^

Clob (Character Large OBject) data type is meant to store data of
undefined length that may be too large to store in text fields, like
data that is usually stored in files.

Clob fields are meant to store only data made of printable ASCII
characters whereas blob fields are meant to store all types of data.

Clob fields are usually not meant to be used as parameters of query
search clause (``WHERE``) unless the underlying DBMS supports a feature
usually known as "full text search".

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('clobtest', 'clob');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        clobtest: clob

^^^^^^^^^
Timestamp
^^^^^^^^^

The timestamp data type is a mere combination of the date and the time
of the day data types. The representation of values of the time stamp
type is accomplished by joining the date and time string values in a
single string joined by a space. Therefore, the format template is
``YYYY-MM-DD HH:MI:SS``. The represented values obey the same rules and
ranges described for the date and time data types.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('timestamptest', 'timestamp');
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        timestamptest: timestamp

^^^^
Time
^^^^

The time data type may represent the time of a given moment of the day.
DBMS independent representation of the time of the day is also
accomplished by using text strings formatted according to the ISO-8601
standard.

The format defined by the ISO-8601 standard for the time of the day is
HH:MI:SS where HH is the number of hour the day from 00 to 23 and MI and
SS are respectively the number of the minute and of the second from 00
to 59. Hours, minutes and seconds numbered below 10 should be padded on
the left with 0.

Some DBMS have native support for time of the day formats, but for
others the DBMS driver may have to represent them as integers or text
values. In any case, it is always possible to make comparisons between
time values as well sort query results by fields of this type.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('timetest', 'time');
        }
    }


Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        timetest: time

^^^^
Date
^^^^

The date data type may represent dates with year, month and day. DBMS
independent representation of dates is accomplished by using text
strings formatted according to the IS0-8601 standard.

The format defined by the ISO-8601 standard for dates is YYYY-MM-DD
where YYYY is the number of the year (Gregorian calendar), MM is the
number of the month from 01 to 12 and DD is the number of the day from
01 to 31. Months or days numbered below 10 should be padded on the left
with 0.

Some DBMS have native support for date formats, but for others the DBMS
driver may have to represent them as integers or text values. In any
case, it is always possible to make comparisons between date values as
well sort query results by fields of this type.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('datetest', 'date');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        datetest: date

^^^^
Enum
^^^^

Doctrine has a unified enum type. The possible values for the column can
be specified on the column definition with
:php:meth:`Doctrine_Record::hasColumn`

.. note::

    If you wish to use native enum types for your DBMS if it
    supports it then you must set the following attribute::

        $conn->setAttribute(Doctrine_Core::ATTR_USE_NATIVE_ENUM, true);

Here is an example of how to specify the enum values::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('enumtest', 'enum', null,
                array('values' => array('php', 'java', 'python'))
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        enumtest:
          type: enum
          values: [php, java, python]

^^^^
Gzip
^^^^

Gzip datatype is the same as string except that its automatically
compressed when persisted and uncompressed when fetched. This datatype
can be useful when storing data with a large compressibility ratio, such
as bitmap images.

::

    class Test extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('gziptest', 'gzip');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Test:
      columns:
        gziptest: gzip

.. note::

    The family of php functions for `compressing
    <http://www.php.net/gzcompress>`_ are used internally for compressing and
    uncompressing the contents of the gzip column type.

.. _data-type-examples:

--------
Examples
--------

Consider the following definition::

    class Example extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('id', 'string', 32, array(
                'type' => 'string',
                'fixed' => 1,
                'primary' => true,
                'length' => '32' )
            );

            $this->hasColumn('someint', 'integer', 10, array(
                'type' => 'integer',
                'unsigned' => true,
                'length' => '10'
            ));

            $this->hasColumn('sometime', 'time', 25, array(
                'type' => 'time',
                'default' => '12:34:05',
                'notnull' => true,
                'length' => '25'
            ));

            $this->hasColumn('sometext', 'string', 12, array(
                'type' => 'string',
                'length' => '12'
            ));

            $this->hasColumn('somedate', 'date', 25, array(
                'type' => 'date',
                'length' => '25'
            ));

            $this->hasColumn('sometimestamp', 'timestamp', 25, array(
                'type' => 'timestamp',
                'length' => '25'
            ));

            $this->hasColumn('someboolean', 'boolean', 25, array(
                'type' => 'boolean',
                'length' => '25'
            ));

            $this->hasColumn('somedecimal', 'decimal', 18, array(
                'type' => 'decimal',
                'length' => '18'
            ));

            $this->hasColumn('somefloat', 'float', 2147483647, array(
                'type' => 'float',
                'length' => '2147483647'
            ));

            $this->hasColumn('someclob', 'clob', 2147483647, array(
                'type' => 'clob',
                'length' => '2147483647'
            ));

            $this->hasColumn('someblob', 'blob', 2147483647, array(
                'type' => 'blob',
                'length' => '2147483647'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    Example:
      tableName: example
      columns:
        id:
          type:    string(32)
          fixed:   true
          primary: true
        someint:
          type:     integer(10)
          unsigned: true
        sometime:
          type:    time(25)
          default: '12:34:05'
          notnull: true
        sometext:      string(12)
        somedate:      date(25)
        sometimestamp: timestamp(25)
        someboolean:   boolean(25)
        somedecimal:   decimal(18)
        somefloat:     float(2147483647)
        someclob:      clob(2147483647)
        someblob:      blob(2147483647)

The above example will create the following database table in Pgsql:

=================  ===================================
Column             Type
=================  ===================================
``id``             ``character(32)``
``someint``        ``integer``
``sometime``       ``time`` without time zone
``sometext``       ``character`` or ``varying(12)``
``somedate``       ``date``
``sometimestamp``  ``timestamp`` without time zone
``someboolean``    ``boolean``
``somedecimal``    ``numeric(18,2)``
``somefloat``      ``double`` precision
``someclob``       ``text``
``someblob``       ``bytea``
=================  ===================================

The schema will create the following database table in Mysql:

=================  ===============
Field              Type
=================  ===============
``id``             ``char(32)``
``someint``        ``integer``
``sometime``       ``time``
``sometext``       ``varchar(12)``
``somedate``       ``date``
``sometimestamp``  ``timestamp``
``someboolean``    ``tinyint(1)``
``somedecimal``    ``decimal(18,2)``
``somefloat``      ``double``
``someclob``       ``longtext``
``someblob``       ``longblob``
=================  ===============

.. _defining-models-relationships:

=============
Relationships
=============

------------
Introduction
------------

In Doctrine all record relations are being set with
:php:meth:`Doctrine_Record::hasMany`, :php:meth:`Doctrine_Record::hasOne` methods.
Doctrine supports almost all kinds of database relations from simple
one-to-one foreign key relations to join table self-referencing
relations.

Unlike the column definitions the :php:meth:`Doctrine_Record::hasMany` and
:php:meth:`Doctrine_Record::hasOne` methods are placed within a method called
setUp(). Both methods take two arguments: the first argument is a string
containing the name of the class and optional alias, the second argument
is an array consisting of relation options. The option array contains
the following keys:

==============  ========  ====================================================
Name            Optional  Description
==============  ========  ====================================================
``local``       No        The local field of the relation. Local field is the
                          linked field inthe defining class.
``foreign``     No        The foreign fieldof the relation. Foreign field is
                          the linked field in the linked class.
``refClass``    Yes       The name of the association class.This is only
                          needed for many-to-many associations.
``owningSide``  Yes       Set to boolean true to indicate the owningside of
                          the relation. The owning side is the side that owns
                          the foreignkey. There can only be one owning side in
                          an association between twoclasses. Note that this
                          option is required if Doctrine can't guess theowning
                          side or it's guess is wrong. An example where this is
                          the case iswhen both 'local' and 'foreign' are part
                          of the identifier (primarykey).  It never hurts to
                          specify the owning side in this way.
``onDelete``    Yes       The ``onDelete`` integrity action that is applied on
                          the foreign key constraint when the tables are
                          created byDoctrine.
``onUpdate``    Yes       The ``onUpdate`` integrity action that is applied on
                          the foreign key constraint when thetables are
                          created by Doctrine.
``cascade``     Yes       Specify application level cascading operations.
                          Currently only delete issupported
==============  ========  ====================================================

So lets take our first example, say we have two classes ``Forum_Board``
and ``Forum_Thread``. Here ``Forum_Board`` has many
``Forum_Threads``, hence their relation is one-to-many. We don't want
to write ``Forum_`` when accessing relations, so we use relation
aliases and use the alias Threads.

First lets take a look at the ``Forum_Board`` class. It has three
columns: name, description and since we didn't specify any primary key,
Doctrine auto-creates an id column for it.

We define the relation to the ``Forum_Thread`` class by using the
:php:meth:`hasMany` method. Here the local field is the primary key of the
board class whereas the foreign field is the ``board_id`` field of the
``Forum_Thread`` class.

::

    // models/Forum_Board.php
    class Forum_Board extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 100);
            $this->hasColumn('description', 'string', 5000);
        }

        public function setUp()
        {
            $this->hasMany('Forum_Thread as Threads', array(
                'local' => 'id',
                'foreign' => 'board_id'
            ));
        }
    }

.. note::

    Notice the as keyword being used above. This means that the ``Forum_Board``
    has a many relationship defined to ``Forum_Thread`` but is aliased as
    ``Threads``.

Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Forum_Board:
      columns:
        name:        string(100)
        description: string(5000)

Then lets have a peek at the ``Forum_Thread`` class. The columns here
are irrelevant, but pay attention to how we define the relation. Since
each Thread can have only one Board we are using the :php:meth:`hasOne`
method. Also notice how we once again use aliases and how the local
column here is ``board_id`` while the foreign column is the ``id``
column.

::

    // models/Forum_Thread.php
    class Forum_Thread extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer');
            $this->hasColumn('board_id', 'integer');
            $this->hasColumn('title', 'string', 200);
            $this->hasColumn('updated', 'integer', 10);
            $this->hasColumn('closed', 'integer', 1);
        }

        public function setUp()
        {
            $this->hasOne('Forum_Board as Board', array(
                'local' => 'board_id',
                'foreign' => 'id'
            ));

            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }


Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

     # schema.yml
    Forum_Thread:
      columns:
        user_id:  integer
        board_id: integer
        title:    string(200)
        updated:  integer(10)
        closed:   integer(1)
      relations:
        User:
          local: user_id
          foreign: id
          foreignAlias: Threads
        Board:
          class: Forum_Board
          local: board_id
          foreign: id
          foreignAlias: Threads

Now we can start using these classes. The same accessors that you've already
used for properties are all available for relations.

First lets create a new board::

    // test.php
    $board = new Forum_Board();
    $board->name = 'Some board';

Now lets create a new thread under the board::

    // ...
    $board->Threads[0]->title = 'new thread 1';
    $board->Threads[1]->title = 'new thread 2';

Each ``Thread`` needs to be associated to a user so lets create a new
``User`` and associate it to each ``Thread``::

    // ...
    $user = new User();
    $user->username = 'jwage';
    $board->Threads[0]->User= $user;
    $board->Threads[1]->User = $user;

Now we can save all the changes with one call. It will save the new
board as well as its threads::

    // ...
    $board->save();

Lets do a little inspecting and see the data structure that is created
when you use the code from above. Add some code to :file:`test.php` to
output an array of the object graph we've just populated::

    print_r($board->toArray(true));

.. tip::

    The :php:meth:`Doctrine_Record::toArray` takes all the data of a
    :php:class:`Doctrine_Record` instance and converts it to an array so you
    can easily inspect the data of a record. It accepts an argument named
    ``$deep`` telling it whether or not to include relationships. In this
    example we have specified ``true`` because we want to include the
    ``Threads`` data.

Now when you execute :file:`test.php` with PHP from your terminal you should see
the following

.. code-block:: text

    $ php test.php
    Array (
        [id] => 2
        [name] => Some board
        [description] =>
        [Threads] => Array
            (
                [0] => Array
                    (
                        [id] => 3
                        [user_id] => 1
                        [board_id] => 2
                        [title] => new thread 1
                        [updated] =>
                        [closed] =>
                        [User] => Array
                            (
                                [id] => 1
                                [is_active] => 1
                                [is_super_admin] => 0
                                [first_name] =>
                                [last_name] =>
                                [username] => jwage
                                [password] =>
                                [type] =>
                                [created_at] => 2009-01-20 16:41:57
                                [updated_at] => 2009-01-20 16:41:57
                            )
                    )
                [1] => Array
                    (
                        [id] => 4
                        [user_id] => 1
                        [board_id] => 2
                        [title] => new thread 2
                        [updated] =>
                        [closed] =>
                        [User] => Array
                            (
                                [id] => 1
                                [is_active] => 1
                                [is_super_admin] => 0
                                [first_name] =>
                                [last_name] =>
                                [username] => jwage
                                [password] =>
                                [type] =>
                                [created_at] => 2009-01-20 16:41:57
                                [updated_at] => 2009-01-20 16:41:57
                            )
                    )
            )
    )

.. note::

    Notice how the auto increment primary key and foreign keys
    are automatically set by Doctrine internally. You don't have to
    worry about the setting of primary keys and foreign keys at all!

------------------------
Foreign Key Associations
------------------------

^^^^^^^^^^
One to One
^^^^^^^^^^

One-to-one relations are probably the most basic relations. In the
following example we have two classes, ``User`` and ``Email`` with their
relation being one-to-one.

First lets take a look at the ``Email`` class. Since we are binding a
one-to-one relationship we are using the :php:meth:`hasOne` method. Notice how
we define the foreign key column (``user_id``) in the ``Email`` class.
This is due to a fact that ``Email`` is owned by the ``User`` class and
not the other way around. In fact you should always follow this
convention - always place the foreign key in the owned class.

The recommended naming convention for foreign key columns is:
``[tableName]_[primaryKey]``. As here the foreign table is 'user' and
its primary key is 'id' we have named the foreign key column as
'user_id'.

::

    // models/Email.php
    class Email extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer');
            $this->hasColumn('address', 'string', 150);
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Email:
      columns:
          user_id: integer
          address: string(150)
      relations:
          User:
          local: user_id
          foreign: id
          foreignType: one

.. tip::

    When using YAML schema files it is not required to specify the relationship
    on the opposite end(``User``) because the relationship is automatically
    flipped and added for you. The relationship will be named the name of the
    class. So in this case the relationship on the ``User`` side will be called
    ``Email`` and will be ``many``. If you wish to customize this you can use
    the ``foreignAlias`` and ``foreignType`` options.

The ``Email`` class is very similar to the ``User`` class. Notice how the local
and foreign columns are switched in the :php:meth:`hasOne` definition compared
to the definition of the ``Email`` class.

::

    // models/User.php
    class User extends BaseUser
    {
        public function setUp()
        {
            parent::setUp();
            $this->hasOne('Email', array(
                'local' => 'id',
                'foreign' => 'user_id'
            ));
        }
    }

.. note::

    Notice how we override the :php:meth:`setUp` method and call
    :php:meth:`parent::setUp`. This is because the ``BaseUser`` class which is
    generated from YAML or from an existing database contains the main
    :php:meth:`setUp` method and we override it in the ``User`` class to add
    an additional relationship.

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
        # ...
        relations:
            # ...
            Email:
                local: id
                foreign: user_id

^^^^^^^^^^^^^^^^^^^^^^^^^^^
One to Many and Many to One
^^^^^^^^^^^^^^^^^^^^^^^^^^^

One-to-Many and Many-to-One relations are very similar to One-to-One
relations. The recommended conventions you came in terms with in the
previous chapter also apply to one-to-many and many-to-one relations.

In the following example we have two classes: ``User`` and
``Phonenumber``. We define their relation as one-to-many (a user can
have many phonenumbers). Here once again the ``Phonenumber`` is clearly
owned by the ``User`` so we place the foreign key in the ``Phonenumber``
class.

::

    // models/User.php
    class User extends BaseUser
    {
        public function setUp()
        {
            parent::setUp();

            // ...

            $this->hasMany('Phonenumber as Phonenumbers', array(
                'local' => 'id',
                'foreign' => 'user_id'
            ));
        }
    }

::

    // models/Phonenumber.php
    class Phonenumber extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer');
            $this->hasColumn('phonenumber', 'string', 50);
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
      # ...
      relations:
        # ...
        Phonenumbers:
          type: many
          class: Phonenumber
          local: id
          foreign: user_id

    Phonenumber:
      columns:
        user_id: integer
        phonenumber: string(50)
      relations:
        User:
          local: user_id
          foreign: id

^^^^^^^^^^^^^^
Tree Structure
^^^^^^^^^^^^^^

A tree structure is a self-referencing foreign key relation. The
following definition is also called Adjacency List implementation in
terms of hierarchical data concepts.

::

    // models/Task.php
    class Task extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 100);
            $this->hasColumn('parent_id', 'integer');
        }

        public function setUp()
        {
            $this->hasOne('Task as Parent', array(
                'local' => 'parent_id',
                'foreign' => 'id'
            ));
            $this->hasMany('Task as Subtasks', array(
                'local' => 'id',
                'foreign' => 'parent_id'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Task:
      columns:
        name: string(100)
        parent_id: integer
      relations:
        Parent:
          class: Task
          local: parent_id
          foreign: id
          foreignAlias: Subtasks

.. note::

    The above implementation is purely an example and is not the most efficient
    way to store and retrieve hierarchical data.  Check the ``NestedSet``
    behavior included in Doctrine for the recommended way to deal with
    hierarchical data.

-----------------------
Join Table Associations
-----------------------

^^^^^^^^^^^^
Many to Many
^^^^^^^^^^^^

If you are coming from relational database background it may be familiar to you
how many-to-many associations are handled: an additional association table is
needed.

In many-to-many relations the relation between the two components is always an
aggregate relation and the association table is owned by both ends. For example
in the case of users and groups: when a user is being deleted, the groups
he/she belongs to are not being deleted. However, the associations between this
user and the groups he/she belongs to are instead being deleted. This removes
the relation between the user and the groups he/she belonged to, but does not
remove the user nor the groups.

Sometimes you may not want that association table rows are being deleted when
user / group is being deleted. You can override this behavior by setting the
relations to association component (in this case ``Groupuser``) explicitly.

In the following example we have Groups and Users of which relation is defined
as many-to-many. In this case we also need to define an additional class called
``Groupuser``.

::

    class User extends BaseUser
    {
        public function setUp()
        {
            parent::setUp();

            // ...

            $this->hasMany('Group as Groups', array(
                'local' => 'user_id',
                'foreign' => 'group_id',
                'refClass' => 'UserGroup'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    User:
        # ...
        relations:
            # ...
            Groups:
                class: Group
                local: user_id
                foreign: group_id
                refClass: UserGroup

.. note::

    The above ``refClass`` option is required when setting up many-to-many
    relationships.

::

    // models/Group.php
    class Group extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->setTableName('groups');
            $this->hasColumn('name', 'string', 30);
        }

        public function setUp()
        {
            $this->hasMany('User as Users', array(
                'local' => 'group_id',
                'foreign' => 'user_id',
                'refClass' => 'UserGroup'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Group:
      tableName: groups
      columns:
        name: string(30)
      relations:
        Users:
          class: User
          local: group_id
          foreign: user_id
          refClass: UserGroup

.. note::

    Please note that ``group`` is a reserved keyword so that is why we renamed
    the table to ``groups`` using the ``setTableName`` method. The other option
    is to turn on identifier quoting using the
    :php:const:`Doctrine_Core::ATTR_QUOTE_IDENTIFIER` attribute so that the
    reserved word is escaped with quotes.

    ::

        $manager->setAttribute(Doctrine_Core::Doctrine_Core::ATTR_QUOTE_IDENTIFIER,
        true);

::

    // models/UserGroup.php
    class UserGroup extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer', null, array(
                'primary' => true
            ));
            $this->hasColumn('group_id', 'integer', null, array(
                'primary' => true
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    UserGroup:
      columns:
        user_id:
          type: integer
          primary: true
        group_id:
          type: integer
          primary: true

Notice how the relationship is bi-directional. Both ``User`` has many ``Group``
and ``Group`` has many ``User``. This is required by Doctrine in order for
many-to-many relationships to fully work.

Now lets play around with the new models and create a user and assign it some
groups. First create a new ``User`` instance::

    // test.php
    $user = new User();

Now add two new groups to the ``User``::

    // ...
    $user->Groups[0]->name = 'First Group';
    $user->Groups[1]->name = 'Second Group';

Now you can save the groups to the database::

    // ...
    $user->save();

Now you can delete the associations between user and groups it belongs
to::

    // ...
    $user->UserGroup->delete();

    $groups = new Doctrine_Collection(Doctrine_Core::getTable('Group'));

    $groups[0]->name = 'Third Group';
    $groups[1]->name = 'Fourth Group';

    $user->Groups[2] = $groups[0]; // $user will now have 3 groups

    $user->Groups = $groups; // $user will now have two groups 'Third Group' and 'Fourth Group'

    $user->save();

Now if we inspect the ``$user`` object data with the
:php:meth:`Doctrine_Record::toArray`::

    // ...
    print_r($user->toArray(true));

The above example would produce the following output

.. code-block:: text

    $ php test.php
    Array
        (
            [id] => 1
            [is_active] => 1
            [is_super_admin] => 0
            [first_name] =>
            [last_name] =>
            [username] => default username
            [password] =>
            [type] =>
            [created_at] => 2009-01-20 16:48:57
            [updated_at] => 2009-01-20 16:48:57
            [Groups] => Array
                (
                    [0] => Array
                        (
                            [id] => 3
                            [name] => Third Group
                        )
                [1] => Array
                    (
                        [id] => 4
                        [name] => Fourth Group
                    )
                )
            [UserGroup] => Array
                (
                )
        )

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Self Referencing (Nest Relations)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

""""""""""""""""""""""""
Non-Equal Nest Relations
""""""""""""""""""""""""

::

    // models/User.php
    class User extends BaseUser
    {
        public function setUp()
        {
            parent::setUp();

            // ...

            $this->hasMany('User as Parents', array(
                'local'    => 'child_id',
                'foreign'  => 'parent_id',
                'refClass' => 'UserReference'
            ));

            $this->hasMany('User as Children', array(
                'local'    => 'parent_id',
                'foreign'  => 'child_id',
                'refClass' => 'UserReference'
            ));
        }
    }

::

    // models/UserReference.php
    class UserReference extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('parent_id', 'integer', null, array(
                'primary' => true
            ));
            $this->hasColumn('child_id', 'integer', null, array(
                'primary' => true
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
      # ...
      relations:
        # ...
        Parents:
          class: User
          local: child_id
          foreign: parent_id
          refClass: UserReference
          foreignAlias: Children

    UserReference:
      columns:
        parent_id:
          type: integer
          primary: true
        child_id:
          type: integer
          primary: true

""""""""""""""""""""
Equal Nest Relations
""""""""""""""""""""

Equal nest relations are perfectly suitable for expressing relations
where a class references to itself and the columns within the reference
class are equal.

This means that when fetching related records it doesn't matter which
column in the reference class has the primary key value of the main
class.

The previous clause may be hard to understand so lets take an example. We
define a class called User which can have many friends. Notice here how
we use the 'equal' option.

::

    // models/User.php
    class User extends BaseUser
    {

        public function setUp()
        {
            parent::setUp();

            // ...

            $this->hasMany('User as Friends', array(
                'local'    => 'user1',
                'foreign'  => 'user2',
                'refClass' => 'FriendReference',
                'equal'    => true,
            ));
        }
    }

::

    // models/FriendReference.php
    class FriendReference extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user1', 'integer', null, array(
                'primary' => true
            ));
            $this->hasColumn('user2', 'integer', null, array(
                'primary' => true
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
      # ...
      relations:
        # ...
        Friends:
          class: User
          local: user1
          foreign: user2
          refClass: FriendReference
          equal: true

    FriendReference:
      columns:
        user1:
          type: integer
          primary: true
        user2:
          type: integer
          primary: true

Now lets define 4 users: Jack Daniels, John Brandy, Mikko Koskenkorva and
Stefan Beer with Jack Daniels and John Brandy being buddies and Mikko
Koskenkorva being the friend of all of them.

::

    // test.php
    $daniels = new User();
    $daniels->username = 'Jack Daniels';

    $brandy = new User();
    $brandy->username = 'John Brandy';

    $koskenkorva = new User();
    $koskenkorva->username = 'Mikko Koskenkorva';

    $beer = new User();
    $beer->username = 'Stefan Beer';

    $daniels->Friends[0] = $brandy;

    $koskenkorva->Friends[0] = $daniels;
    $koskenkorva->Friends[1] = $brandy;
    $koskenkorva->Friends[2] = $beer;

    $conn->flush();

.. note::

    Calling :php:meth:`Doctrine_Connection::flush` will trigger an
    operation that saves all unsaved objects and wraps it in a single
    transaction.

Now if we access for example the friends of Stefan Beer it would return
one user 'Mikko Koskenkorva'::

    // ...
    $beer->free();
    unset($beer);
    $user = Doctrine_Core::getTable('User')->findOneByUsername('Stefan Beer');

    print_r($user->Friends->toArray());

Now when you execute :file:`test.php` you will see the following:

.. code-block:: text

    $ php test.php
    Array
        (
            [0] => Array
                (
                    [id] => 4
                    [is_active] => 1
                    [is_super_admin] => 0
                    [first_name] =>
                    [last_name] =>
                    [username] => Mikko Koskenkorva
                    [password] =>
                    [type] =>
                    [created_at] => 2009-01-20 16:53:13
                    [updated_at] => 2009-01-20 16:53:13
                )
        )

-----------------------
Foreign Key Constraints
-----------------------

^^^^^^^^^^^^
Introduction
^^^^^^^^^^^^

A foreign key constraint specifies that the values in a column (or a
group of columns) must match the values appearing in some row of another
table. In other words foreign key constraints maintain the referential
integrity between two related tables.

Say you have the product table with the following definition::

    // models/Product.php
    class Product extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->hasColumn('price', 'decimal', 18);
            $this->hasColumn('discounted_price', 'decimal', 18);
        }

        public function setUp()
        {
            $this->hasMany('Order as Orders', array(
                'local' => 'id',
                'foreign' => 'product_id'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Product:
      columns:
        name:
          type: string
        price:
          type: decimal(18)
        discounted_price:
          type: decimal(18)
        relations:
          Orders:
            class: Order
            local: id
            foreign: product_id

Let's also assume you have a table storing orders of those products. We
want to ensure that the order table only contains orders of products
that actually exist. So we define a foreign key constraint in the orders
table that references the products table::

    // models/Order.php
    class Order extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->setTableName('orders');
            $this->hasColumn('product_id', 'integer');
            $this->hasColumn('quantity', 'integer');
        }

        public function setUp()
        {
            $this->hasOne('Product', array(
                'local' => 'product_id',
                'foreign' => 'id'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Order:
      tableName: orders
      columns:
        product_id: integer
        quantity: integer
        relations:
          Product:
            local: product_id
            foreign: id

.. note::

    Foreign key columns are automatically indexed by Doctrine
    to ensure optimal performance when issuing queries involving the
    foreign key.

When exported the class ``Order`` would execute the following SQL:

.. code-block:: mysql

    CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTO_INCREMENT,
        product_id INTEGER REFERENCES products (id),
        quantity INTEGER,
        INDEX product_id_idx (product_id)
    )

Now it is impossible to create ``orders`` with a ``product_id`` that
does not appear in the ``product`` table.

We say that in this situation the orders table is the referencing table
and the products table is the referenced table. Similarly, there are
referencing and referenced columns.

^^^^^^^^^^^^^^^^^
Foreign Key Names
^^^^^^^^^^^^^^^^^

When you define a relationship in Doctrine, when the foreign key is
created in the database for you Doctrine will try to create a foreign
key name for you. Sometimes though, this name may not be something you
want so you can customize the name to use with the ``foreignKeyName``
option to your relationship setup.

::

    // models/Order.php
    class Order extends Doctrine_Record
    {
        // ...

        public function setUp()
        {
            $this->hasOne('Product', array(
                'local' => 'product_id',
                'foreign' => 'id',
                'foreignKeyName' => 'product_id_fk'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Order:
        # ...
        relations:
            Product:
                local: product_id
                foreign: id
                foreignKeyName: product_id_fk

^^^^^^^^^^^^^^^^^
Integrity Actions
^^^^^^^^^^^^^^^^^

"""""""
CASCADE
"""""""

Delete or update the row from the parent table and automatically delete
or update the matching rows in the child table. Both ``ON DELETE
CASCADE`` and ``ON UPDATE CASCADE`` are supported. Between two tables,
you should not define several ``ON UPDATE CASCADE`` clauses that act on
the same column in the parent table or in the child table.

""""""""
SET NULL
""""""""

Delete or update the row from the parent table and set the foreign key
column or columns in the child table to ``NULL``. This is valid only if
the foreign key columns do not have the ``NOT NULL`` qualifier
specified. Both ``ON DELETE SET NULL`` and ``ON UPDATE SET NULL``
clauses are supported.

"""""""""
NO ACTION
"""""""""

In standard SQL, ``NO ACTION`` means no action in the sense that an
attempt to delete or update a primary key value is not allowed to
proceed if there is a related foreign key value in the referenced table.

""""""""
RESTRICT
""""""""

Rejects the delete or update operation for the parent table. ``NO
ACTION`` and ``RESTRICT`` are the same as omitting the ``ON DELETE`` or
``ON UPDATE`` clause.

"""""""""""
SET DEFAULT
"""""""""""

In the following example we define two classes, ``User`` and
``Phonenumber`` with their relation being one-to-many. We also add a
foreign key constraint with onDelete cascade action. This means that
every time a ``user`` is being deleted its associated ``phonenumbers``
will also be deleted.

.. note::

    The integrity constraints listed above are case sensitive
    and must be in upper case when being defined in your schema.

Below is an example where the database delete cascading is used.

::

    class Phonenumber extends Doctrine_Record
    {
        // ...

        public function setUp()
        {
            parent::setUp();

            // ...

            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Phonenumber:
        # ...
        relations:
            # ...
            User:
                local: user_id
                foreign: id
                onDelete: CASCADE

.. note::

    Notice how the integrity constraints are placed on the side
    where the foreign key exists. This is required in order for the
    integrity constraints to be exported to your database properly.

.. _indexes:

=======
Indexes
=======

------------
Introduction
------------

Indexes are used to find rows with specific column values quickly.
Without an index, the database must begin with the first row and then
read through the entire table to find the relevant rows.

The larger the table, the more this consumes time. If the table has an
index for the columns in question, the database can quickly determine
the position to seek to in the middle of the data file without having to
look at all the data. If a table has 1,000 rows, this is at least 100
times faster than reading rows one-by-one.

Indexes come with a cost as they slow down the inserts and updates.
However, in general you should **always** use indexes for the fields
that are used in SQL where conditions.

--------------
Adding indexes
--------------

You can add indexes by using ``Doctrine_Record::index``. An example of
adding a simple index to field called name:

.. note::

    The following index examples are not meant for you to
    actually add to your test Doctrine environment. They are only meant
    to demonstrate the API for adding indexes.

::

    class IndexTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->index('myindex', array(
                'fields' => array('name')
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    IndexTest:
      columns:
        name: string
      indexes:
        myindex:
          fields: [name]

An example of adding a multi-column index to field called ``name``::

    class MultiColumnIndexTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->hasColumn('code', 'string');

            $this->index('myindex', array(
                'fields' => array('name', 'code')
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    MultiColumnIndexTest:
      columns:
        name: string
        code: string
      indexes:
        myindex:
          fields: [name, code]

An example of adding multiple indexes on same table::

    class MultipleIndexTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->hasColumn('code', 'string'); $this->hasColumn('age', 'integer');

            $this->index('myindex', array(
                'fields' => array('name', 'code')
            ));

            $this->index('ageindex', array(
                'fields' => array('age')
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    MultipleIndexTest:
      columns:
        name: string
        code: string
        age: integer
      indexes:
        myindex:
          fields: [name, code]
        ageindex:
          fields: [age]

-------------
Index options
-------------

Doctrine offers many index options, some of them being database
specific. Here is a full list of available options:

===========  ===============================================================
Name         Description
===========  ===============================================================
``sorting``  A string valuethat can be either 'ASC' or 'DESC'.
``length``   Index length (only some drivers support this).
``primary``  Whether or not the index is a primary index.
``type``     A string value that can be unique, 'fulltext', 'gist' or 'gin'.
===========  ===============================================================

Here is an example of how to create a unique index on the name column.

::

    class MultipleIndexTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->hasColumn('code', 'string');
            $this->hasColumn('age', 'integer');

            $this->index('myindex', array(
                'fields' => array(
                    'name' => array(
                        'sorting' => 'ASC',
                        'length'  => 10),
                    'code'
                ),
                'type' => 'unique',
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in the
:doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    MultipleIndexTest:
      columns:
        name: string
        code: string
        age: integer
      indexes:
        myindex:
          fields:
            name:
              sorting: ASC
              length: 10
            code:
          type: unique

---------------
Special indexes
---------------

Doctrine supports many special indexes. These include Mysql FULLTEXT and
Pgsql GiST indexes. In the following example we define a Mysql FULLTEXT
index for the field 'content'.

::

    // models/Article.php
    class Article extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
            $this->hasColumn('content', 'string');

            $this->option('type', 'MyISAM');

            $this->index('content', array(
                'fields' => array('content'),
                'type'   => 'fulltext'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Article:
      options:
        type: MyISAM
      columns:
        name: string(255)
        content: string
      indexes:
        content:
          fields: [content]
          type: fulltext

.. note::

    Notice how we set the table type to ``MyISAM``. This is
    because the ``fulltext`` index type is only supported in ``MyISAM``
    so you will receive an error if you use something like ``InnoDB``.

======
Checks
======

You can create any kind of ``CHECK`` constraints by using the :php:meth:`check`
method of the :php:class:`Doctrine_Record`. In the last example we add
constraint to ensure that price is always higher than the discounted price.

::

    // models/Product.php
    class Product extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            // ...
            $this->check('price > discounted_price');
        }

        // ...
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Product:
      # ...
      checks:
        price_check: price > discounted_price

Generates (in pgsql):

.. code-block:: postgres

    CREATE TABLE product (
        id INTEGER,
        price NUMERIC,
        discounted_price NUMERIC,
        PRIMARY KEY(id),
        CHECK (price >= 0),
        CHECK (price <= 1000000),
        CHECK (price > discounted_price)
    )

.. note::

    Some databases don't support ``CHECK`` constraints. When
    this is the case Doctrine simply skips the creation of check
    constraints.

If the Doctrine validators are turned on the given definition would also
ensure that when a record is being saved its price is always greater
than zero.

If some of the prices of the saved products within a transaction is
below zero, Doctrine throws ``Doctrine_Validator_Exception`` and
automatically rolls back the transaction.

=============
Table Options
=============

Doctrine offers various table options. All table options can be set via
the ``Doctrine_Record::option`` function.

For example if you are using MySQL and want to use INNODB tables it can
be done as follows::

    class MyInnoDbRecord extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');
            $this->option('type', 'INNODB');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    MyInnoDbRecord:
      columns:
        name: string
      options:
        type: INNODB

In the following example we set the collate and character set options::

    class MyCustomOptionRecord extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string');

            $this->option('collate', 'utf8_unicode_ci');
            $this->option('charset', 'utf8');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    MyCustomOptionRecord:
      columns:
        name: string
      options:
        collate: utf8_unicode_ci
        charset: utf8

It is worth noting that for certain databases (Firebird, MySql and
PostgreSQL) setting the charset option might not be enough for Doctrine
to return data properly. For those databases, users are advised to also
use the ``setCharset`` function of the database connection::

    $conn = Doctrine_Manager::connection();
    $conn->setCharset('utf8');

==============
Record Filters
==============

Doctrine offers the ability to attach record filters when defining your
models. A record filter is invoked whenever you access a property on a
model that is invalid. So it allows you to essentially add properties
dynamically to a model through the use of one of these filters.

To attach a filter you just need to add it in the :php:meth:`setUp` method of
your model definition::

    class User extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('username', 'string', 255);
            $this->hasColumn('password', 'string', 255);
        }

        public function setUp()
        {
            $this->hasOne('Profile', array(
                'local' => 'id',
                'foreign' => 'user_id'
            ));
            $this->unshiftFilter(new Doctrine_Record_Filter_Compound(array('Profile')));
        }
    }

    class Profile extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer');
            $this->hasColumn('first_name', 'string', 255);
            $this->hasColumn('last_name', 'string', 255);
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }

Now with the above example we can easily access the properties of the
``Profile`` relationship when using an instance of ``User``. Here is an
example::

    $user = Doctrine_Core::getTable('User')
                ->createQuery('u')
                ->innerJoin('u.Profile p')
                ->where('p.username = ?', 'jwage')
                ->fetchOne();

    echo $user->first_name . ' ' . $user->last_name;

When we ask for the ``first_name`` and ``last_name`` properties they
do not exist on the ``$user`` instance so they are forwarded to the
``Profile`` relationship. It is the same as if you were to do the
following::

    echo $user->Profile->first_name . ' ' . $user->Profile->last_name;

You can write your own record filters pretty easily too. All that is
required is you create a class which extends
``Doctrine_Record_Filter`` and implements the :php:meth:`filterSet` and
:php:meth:`filterGet` methods. Here is an example::

    class MyRecordFilter extends Doctrine_Record_Filter
    {
        public function filterSet(Doctrine_Record $record, $name, $value)
        {
            // try and set the property
            throw new Doctrine_Record_UnknownPropertyException(sprintf(
                'Unknown record property / related component "%s" on "%s"',
                $name,
                get_class($record)
            ));
        }

        public function filterGet(Doctrine_Record, $name)
        {
            // try and get the property
            throw new Doctrine_Record_UnknownPropertyException(sprintf(
                'Unknown record property / related component "%s" on "%s"',
                $name,
                get_class($record)
            ));
        }
    }

Now you can add the filter to your models::

    class MyModel extends Doctrine_Record
    {
        // ...

        public function setUp()
        {
            // ...
            $this->unshiftFilter(new MyRecordFilter());
        }
    }

.. note::

    Remember to be sure to throw an instance of the
    ``Doctrine_Record_UnknownPropertyException`` exception class if
    :php:meth:`filterSet` or :php:meth:`filterGet` fail to find the property.

======================
Transitive Persistence
======================

Doctrine offers both database and application level cascading
operations. This section will explain in detail how to setup both
application and database level cascades.

--------------------------
Application-Level Cascades
--------------------------

Since it can be quite cumbersome to save and delete individual objects,
especially if you deal with an object graph, Doctrine provides
application-level cascading of operations.

^^^^^^^^^^^^^
Save Cascades
^^^^^^^^^^^^^

You may already have noticed that :php:meth:`save` operations are already
cascaded to associated objects by default.

^^^^^^^^^^^^^^^
Delete Cascades
^^^^^^^^^^^^^^^

Doctrine provides a second application-level cascade style: delete.
Unlike the :php:meth:`save` cascade, the delete cascade needs to be turned on
explicitly as can be seen in the following code snippet::

    // models/User.php
    class User extends BaseUser
    {
        // ...
        public function setUp()
        {
            parent::setup();

            // ...

            $this->hasMany('Address as Addresses', array(
                'local' => 'id',
                'foreign' => 'user_id',
                'cascade' => array('delete')
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter

.. code-block:: yaml

    # schema.yml
    User:
        # ...
        relations:
            # ...
            Addresses:
                class: Address
                local: id
                foreign: user_id
                cascade: [delete]

The ``cascade`` option is used to specify the operations that are
cascaded to the related objects on the application-level.

.. note::

    Please note that the only currently supported value is ``delete``, more
    options will be added in future releases of Doctrine.

In the example above, Doctrine would cascade the deletion of a ``User``
to it's associated ``Addresses``. The following describes the generic
procedure when you delete a record through :php:meth:`$record->delete`:

#. Doctrine looks at the relations to see if there are any deletion
   cascades it needs to apply. If there are no deletion cascades, go to 3).
#. For each relation that has a delete cascade specified, Doctrine
   verifies that the objects that are the target of the cascade are loaded.
   That usually means that Doctrine fetches the related objects from the
   database if they're not yet loaded.(Exception: many-valued associations
   are always re-fetched from the database, to make sure all objects are
   loaded). For each associated object, proceed with step 1).
#. Doctrine orders all deletions and executes them in the most
   efficient way, maintaining referential integrity.

From this description one thing should be instantly clear:
Application-level cascades happen on the object-level, meaning
operations are cascaded from one object to another and in order to do
that the participating objects need to be available.

This has some important implications:

*  Application-level delete cascades don't perform well on many-valued
   associations when there are a lot of objects in the related
   collection (that is because they need to be fetched from the
   database, the actual deletion is pretty efficient).
*  Application-level delete cascades do not skip the object lifecycle as
   database-level cascades do (see next chapter). Therefore all
   registered event listeners and other callback methods are properly
   executed in an application-level cascade.

-----------------------
Database-Level Cascades
-----------------------

Some cascading operations can be done much more efficiently at the
database level. The best example is the delete cascade.

Database-level delete cascades are generally preferrable over
application-level delete cascades except:

*  Your database does not support database-level cascades (i.e. when
   using MySql with MYISAM tables).
*  You have listeners that listen on the object lifecycle and you want
   them to get invoked.

Database-level delete cascades are applied on the foreign key
constraint. Therefore they're specified on that side of the relation
that owns the foreign key. Picking up the example from above, the
definition of a database-level cascade would look as follows::

    // models/Address.php
    class Address extends Doctrine_Record
    {
        public function setTableDefinition() {
            $this->hasColumn('user_id', 'integer');
            $this->hasColumn('address', 'string', 255);
            $this->hasColumn('country', 'string', 255);
            $this->hasColumn('city', 'string', 255);
            $this->hasColumn('state', 'string', 2);
            $this->hasColumn('postal_code', 'string', 25);
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE'
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    Address:
      columns:
        user_id: integer
        address: string(255)
        country: string(255)
        city: string(255)
        state: string(2)
        postal_code: string(25)
      relations:
        User:
          local: user_id
          foreign: id
          onDelete: CASCADE

The ``onDelete`` option is translated to proper DDL/DML statements when
Doctrine creates your tables.

.. note::

    Note that ``'onDelete' => 'CASCADE'`` is specified on the
    Address class, since the Address owns the foreign key (``user_id``)
    and database-level cascades are applied on the foreign key.

Currently, the only two supported database-level cascade styles are for
``onDelete`` and ``onUpdate``. Both are specified on the side that owns
the foreign key and applied to your database schema when Doctrine
creates your tables.

==========
Conclusion
==========

Now that we know everything about how to define our Doctrine models, I think we
are ready to move on to learning about how to :doc:`work with models
<working-with-models>` in your application.

This is a very large topic as well so take a break, grab a mountain dew
and hurry back for the :doc:`next chapter <working-with-models>`.