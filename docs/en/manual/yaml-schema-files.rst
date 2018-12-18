..  vim: set ts=4 sw=4 tw=79 ff=unix :

*****************
YAML Schema Files
*****************

============
Introduction
============

The purpose of schema files is to allow you to manage your model
definitions directly from a YAML file rather then editing php code. The
YAML schema file is parsed and used to generate all your model
definitions/classes. This makes Doctrine model definitions much more
portable.

Schema files support all the normal things you would write with manual
php code. Component to connection binding, relationships, attributes,
templates/behaviors, indexes, etc.

==================
Abbreviated Syntax
==================

Doctrine offers the ability to specify schema in an abbreviated syntax.
A lot of the schema parameters have values they default to, this allows
us to abbreviate the syntax and let Doctrine just use its defaults.
Below is an example of schema taking advantage of all the abbreviations.

.. note::

    The ``detect_relations`` option will attempt to guess
    relationships based on column names. In the example below Doctrine
    knows that ``User`` has one ``Contact`` and will automatically
    define the relationship between the models.

.. code-block:: yaml

    ---
    detect_relations: true

    User:
      columns:
        username: string
        password: string
        contact_id: integer

    Contact:
      columns:
        first_name: string
        last_name: string
        phone: string
        email: string
        address: string

==============
Verbose Syntax
==============

Here is the 100% verbose form of the above schema:

.. code-block:: yaml

    ---
    User:
      columns:
        username:
          type: string(255)
        password:
          type: string(255)
        contact_id:
          type: integer
      relations:
        Contact:
          class: Contact
          local: contact_id
          foreign: id
          foreignAlias: User
          foreignType: one
          type: one

    Contact:
      columns:
        first_name:
          type: string(255)
        last_name:
          type: string(255)
        phone:
          type: string(255)
        email:
          type: string(255)
        address:
          type: string(255)
      relations:
        User:
          class: User
          local: id
          foreign: contact_id
          foreignAlias: Contact
          foreignType: one
          type: one

In the above example we do not define the ``detect_relations`` option,
instead we manually define the relationships so we have complete control
over the configuration of the local/foreign key, type and alias of the
relationship on each side.

=============
Relationships
=============

When specifying relationships it is only necessary to specify the
relationship on the end where the foreign key exists. When the schema
file is parsed, it reflects the relationship and builds the opposite end
automatically. If you specify the other end of the relationship
manually, the auto generation will have no effect.

----------------
Detect Relations
----------------

Doctrine offers the ability to specify a ``detect_relations`` option as
you saw earlier. This feature provides automatic relationship building
based on column names. If you have a ``User`` model with a
``contact_id`` and a class with the name ``Contact`` exists, it will
automatically create the relationships between the two.

-------------------------
Customizing Relationships
-------------------------

Doctrine only requires that you specify the relationship on the end
where the foreign key exists. The opposite end of the relationship will
be reflected and built on the opposite end. The schema syntax offers the
ability to customize the relationship alias and type of the opposite
end. This is good news because it means you can maintain all the
relevant relationship information in one place. Below is an example of
how to customize the alias and type of the opposite end of the
relationship. It demonstrates the relationships ``User`` has one
``Contact`` and ``Contact`` has one ``User`` as ``UserModel``. Normally
it would have automatically generated ``User`` has one ``Contact`` and
``Contact`` has many ``User``. The ``foreignType`` and ``foreignAlias``
options allow you to customize the opposite end of the relationship.

.. code-block:: yaml

    ---
    User:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)
      relations:
        Contact:
          foreignType: one
          foreignAlias: UserModel

    Contact:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        name:
          type: string(255)

You can quickly detect and create the relationships between two models
with the detect_relations option like below.

.. code-block:: yaml

    ---
    detect_relations: true

    User:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        avatar_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)

    Avatar:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        name:
          type: string(255)
        image_file:
          type: string(255)

The resulting relationships would be ``User`` has one ``Avatar`` and
``Avatar`` has many ``User``.

----------
One to One
----------

.. code-block:: yaml

    ---
    User:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)
      relations:
        Contact:
          foreignType: one

    Contact:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        name:
          type: string(255)

-----------
One to Many
-----------

.. code-block:: yaml

    ---
    User:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)

    Phonenumber:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        name:
          type: string(255)
        user_id:
          type: integer(4)
      relations:
        User:
          foreignAlias: Phonenumbers

------------
Many to Many
------------

.. code-block:: yaml

    ---
    User:
      columns:
        id:
          type: integer(4)
          autoincrement: true
          primary: true
        username:
          type: string(255)
        password:
          type: string(255)
      attributes:
        export: all
        validate: true

    Group:
      tableName: group_table
      columns:
        id:
          type: integer(4)
          autoincrement: true
          primary: true
        name:
          type: string(255)
      relations:
        Users:
          foreignAlias: Groups
          class: User
          refClass: GroupUser

    GroupUser:
      columns:
        group_id:
          type: integer(4)
          primary: true
        user_id:
          type: integer(4)
          primary: true
      relations:
        Group:
          foreignAlias: GroupUsers
        User:
          foreignAlias: GroupUsers

This creates a set of models where ``User`` has many ``Groups``,
``Group`` has many ``Users``, ``GroupUser`` has one ``User`` and
``GroupUser`` has one ``Group``.

===================
Features & Examples
===================

------------------
Connection Binding
------------------

If you're not using schema files to manage your models, you will
normally use this code to bind a component to a connection name with the
following code:

Create a connection with code like below:

::

 Doctrine_Manager::connection('mysql://jwage:pass@localhost/connection1', 'connection1');

Now somewhere in your Doctrine bootstrapping of Doctrine you would bind
the model to that connection:

::

 Doctrine_Manager::connection()->bindComponent('User', 'conn1');

Schema files offer the ability to bind it to a specific connection by
specifying the connection parameter. If you do not specify the
connection the model will just use the current connection set on the :php:class:`Doctrine_Manager` instance.

.. code-block:: yaml

    ---
    User:
      connection: connection1
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)

----------
Attributes
----------

Doctrine offers the ability to set attributes for your generated models
directly in your schema files similar to how you would if you were
manually writing your :php:class:`Doctrine_Record` child classes.

.. code-block:: yaml

    ---
    User:
      connection: connection1
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)
      attributes:
        export: none
        validate: false

-----
Enums
-----

To use enum columns in your schema file you must specify the type as
enum and specify an array of values for the possible enum values.

.. code-block:: yaml

    ---
    TvListing:
      tableName: tv_listing
      actAs: [Timestampable]
      columns:
        notes:
          type: string
        taping:
          type: enum
          length: 4
          values: ['live', 'tape']
        region:
          type: enum
          length: 4
          values: ['US', 'CA']

---------------
ActAs Behaviors
---------------

You can attach behaviors to your models with the ``actAs`` option. You
can specify something like the following:

.. code-block:: yaml

    ---
    User:
      connection: connection1
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)
      actAs:
        Timestampable:
        Sluggable:
          fields: [username]
          name: slug # defaults to 'slug'
          type: string # defaults to 'clob'
          length: 255 # defaults to null. clob doesn't require a length

.. note::

    The options specified on the Sluggable behavior above are
    optional as they will use defaults values if you do not specify
    anything. Since they are defaults it is not necessary to type it out
    all the time.

.. code-block:: yaml

    ---
    User:
      connection: connection1
      columns: # ...
      actAs: [Timestampable, Sluggable]

---------
Listeners
---------

If you have a listener you'd like attached to a model, you can specify
them directly in the yml as well.

.. code-block:: yaml

    ---
    User:
      listeners: [ MyCustomListener ]
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)

The above syntax will generated a base class that looks something like
the following:

::

  class BaseUser extends Doctrine_Record
  {
     // ...
     public setUp()
     {
        // ...
        $this->addListener(new MyCustomListener());
     }
  }

-------
Options
-------

Specify options for your tables and when Doctrine creates your tables
from your models the options will be set on the create table statement.

.. code-block:: yaml

    ---
    User:
      connection: connection1
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)
      options:
        type: INNODB
        collate: utf8_unicode_ci
        charset: utf8

-------
Indexes
-------

Please see the :ref:`indexes` section of the
:doc:`defining-models` for more information about indexes and their
options.

.. code-block:: yaml

    ---
    UserProfile:
      columns:
        user_id:
          type: integer
          length: 4
          primary: true
          autoincrement: true
        first_name:
          type: string
          length: 20
        last_name:
          type: string
          length: 20
      indexes:
        name_index:
          fields:
            first_name:
              sorting: ASC
              length: 10
              primary: true
            last_name: []
          type: unique

This is the PHP line of code that is auto-generated inside
``setTableDefinition()`` inside your base model class for the index
definition used above:

::

  $this->index('name_index', array(
          'fields' => array(
              'first_name' => array(
                  'sorting' => 'ASC',
                  'length'  => '10',
                  'primary' => true
              ),
              'last_name' => array()),
          'type' => 'unique'
      )
  );

-----------
Inheritance
-----------

Below we will demonstrate how you can setup the different types of
inheritance using YAML schema files.

^^^^^^^^^^^^^^^^^^
Simple Inheritance
^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    ---
    Entity:
      columns:
        name: string(255)
        username: string(255)
        password: string(255)

    User:
      inheritance:
        extends: Entity
        type: simple

    Group:
      inheritance:
        extends: Entity
        type: simple

.. note::

    Any columns or relationships defined in models that extend
    another in simple inheritance will be moved to the parent when the
    PHP classes are built.

You can read more about this topic in the :doc:`inheritance` chapter.

^^^^^^^^^^^^^^^^^^^^
Concrete Inheritance
^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    ---
    TextItem:
      columns:
        topic: string(255)

    Comment:
      inheritance:
        extends: TextItem
        type: concrete
        columns:
         content: string(300)

You can read more about this topic in the :doc:`inheritance` chapter.

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Column Aggregation Inheritance
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. note::

    Like simple inheritance, any columns or relationships added
    to the children will be automatically removed and moved to the
    parent when the PHP classes are built.

First lets defined a model named ``Entity`` that our other models will
extend from:

.. code-block:: yaml

    ---
    Entity:
      columns:
        name: string(255)
        type: string(255)

.. note::

    The type column above is optional. It will be automatically
    added when it is specified in the child class.

Now lets create a ``User`` model that extends the ``Entity`` model:

.. code-block:: yaml

    ---
    User:
      inheritance:
        extends: Entity
        type: column_aggregation
        keyField: type
        keyValue: User
      columns:
        username: string(255)
        password: string(255)

.. note::

    The ``type`` option under the ``inheritance`` definition is
    optional as it is implied if you specify a ``keyField`` or
    ``keyValue``. If the ``keyField`` is not specified it will default
    to add a column named ``type``. The ``keyValue`` will default to the
    name of the model if you do not specify anything.

Again lets create another model that extends ``Entity`` named ``Group``:

.. code-block:: yaml

    ---
    Group:
      inheritance:
        extends: Entity
        type: column_aggregation
        keyField: type
        keyValue: Group
      columns:
        description: string(255)

.. note::

    The ``User`` ``username`` and ``password`` and the
    ``Group`` ``description`` columns will be automatically moved to the
    parent ``Entity``.

You can read more about this topic in the :doc:`inheritance` chapter.

--------------
Column Aliases
--------------

If you want the ability alias a column name as something other than the
column name in the database this is easy to accomplish with Doctrine. We
simple use the syntax "``column_name as field_name``" in the name of
our column:

.. code-block:: yaml

    ---
    User:
      columns:
        login:
          name: login as username
          type: string(255)
        password:
          type: string(255)

The above example would allow you to access the column named ``login``
from the alias ``username``.

--------
Packages
--------

Doctrine offers the "package" parameter which will generate the models
in to sub folders. With large schema files this will allow you to better
organize your schemas in to folders.

.. code-block:: yaml

    ---
    User:
      package: User
      columns:
        username: string(255)

The model files from this schema file would be put in a folder named
User. You can specify more sub folders by doing "package: User.Models"
and the models would be in User/Models

^^^^^^^^^^^^^^^^^^^
Package Custom Path
^^^^^^^^^^^^^^^^^^^

You can also completely by pass the automatic generation of packages to
the appropriate path by specifying a completely custom path to generate
the package files:

.. code-block:: yaml

    ---
    User:
      package: User
      package_custom_path: /path/to/generate/package
      columns:
        username: string(255)

-------------------------
Global Schema Information
-------------------------

Doctrine schemas allow you to specify certain parameters that will apply
to all of the models defined in the schema file. Below you can find an
example on what global parameters you can set for schema files.

List of global parameters:

====================  ======================================================
Name                  Description
====================  ======================================================
``connection``        Name of connection to bind the models to.
``attributes``        Array of attributes for models.
``actAs``             Array of behaviors for the models to act as.
``options``           Array of tables options for the models.
``package``           Package to put the models in.
``inheritance``       Array of inheritance information for models
``detect_relations``  Whether or not to try and detect foreign key relations
====================  ======================================================

Now here is an example schema where we use some of the above global
parameters:

.. code-block:: yaml

    ---
    connection: conn_name1
    actAs: [Timestampable]
    options:
      type: INNODB
      package: User
      detect_relations: true

    User:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        contact_id:
          type: integer(4)
        username:
          type: string(255)
        password:
          type: string(255)

    Contact:
      columns:
        id:
          type: integer(4)
          primary: true
          autoincrement: true
        name:
          type: string(255)

All of the settings at the top will be applied to every model which is
defined in that YAML file.

==================
Using Schema Files
==================

Once you have defined your schema files you need some code to build the
models from the YAML definition.

::

  $options = array(
      'packagesPrefix' => 'Plugin',
      'baseClassName'  => 'MyDoctrineRecord',
      'suffix' => '.php'
  );

  Doctrine_Core::generateModelsFromYaml('/path/to/yaml', '/path/to/model', $options);

The above code will generate the models for ``schema.yml`` at
``/path/to/generate/models``.

Below is a table containing the different options you can use to
customize the building of models. Notice we use the ``packagesPrefix``,
``baseClassName`` and ``suffix`` options above.

========================  ==========================  ======================================
Name                      Default                     Description
========================  ==========================  ======================================
``packagesPrefix``        ``Package``                 What to prefix the middle package models with.
``packagesPath``          ``#models_path#/packages``  Path to write package files.
``packagesFolderName``    ``packages``                The name of the folder to put packages in, inside of the packages path.
``generateBaseClasses``   ``true``                    Whether or not to generate abstract base models containing the definition and a top level class which is empty extends the base.
``generateTableClasses``  ``true``                    Whether or not to generate a table class for each model.
``baseClassPrefix``       ``Base``                    The prefix to use for generated base class.
``baseClassesDirectory``  ``generated``               Name of the folder to generate the base class definitions in.
``baseTableClassName``    ``Doctrine_Table``          The base table class to extend the other generated table classes from.
``baseClassName``         ``Doctrine_Record``         Name of the base Doctrine_Record class.
``classPrefix``                                       The prefix to use on all generated classes.
``classPrefixFiles``      ``true``                    Whether or not to use the class prefix for the generated file names as well.
``pearStyle``             ``false``                   Whether or not to generated PEAR style class names and file names. This option if set to true will replace underscores(_) with the ``DIRECTORY_SEPARATOR`` in the path to the generated class file.
``suffix``                ``.php``                    Extension for your generated models.
``phpDocSubpackage``                                  The phpDoc subpackage name to generate in the doc blocks.
``phpDocName``                                        The phpDoc author name to generate in the doc blocks.
``phpDocEmail``                                       The phpDoc e-mail to generate in the doc blocks.
========================  ==========================  ======================================

==========
Conclusion
==========

Now that we have learned all about YAML Schema files we are ready to
move on to a great topic regarding :doc:`data-validation`. This is
an important topic because if you are not validating user inputted data
yourself then we want Doctrine to validate data before being persisted
to the database.
