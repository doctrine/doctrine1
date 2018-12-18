*************
Data Fixtures
*************

Data fixtures are meant for loading small sets of test data through your
models to populate your database with data to test against. The data
fixtures are often used side by side with some kind of unit/functional
testing suite.

=========
Importing
=========

Importing data fixtures is just as easy as dumping. You can use the
``loadData()`` function:

::

    Doctrine_Core::loadData('/path/to/data.yml');

You can either specify an individual yml file like we have done above,
or you can specify an entire directory:

::

    Doctrine_Core::loadData('/path/to/directory');

If you want to append the imported data to the already existing data
then you need to use the second argument of the ``loadData()`` function.
If you don't specify the second argument as true then the data will be
purged before importing.

Here is how you can append instead of purging:

::

    Doctrine_Core::loadData('/path/to/data.yml', true);

=======
Dumping
=======

You can dump data to fixtures file in many different formats to help you
get started with writing your data fixtures. You can dump your data
fixtures to one big YAML file like the following:

::

    Doctrine_Core::dumpData('/path/to/data.yml');

Or you can optionally dump all data to individual files. One YAML file
per model like the following:

::

    Doctrine_Core::dumpData('/path/to/directory', true);

=========
Implement
=========

Now that we know a little about data fixtures lets implement them in to
our test environment we created and have been using through the previous
chapters so that we can test the example fixtures used in the next
sections.

First create a directory in your ``doctrine_test`` directory named
``fixtures`` and create a file named ``data.yml`` inside:

.. code-block:: sh

    $ mkdir fixtures
    $ touch fixtures/data.yml

Now we need to just modify our ``generate.php`` script to include the
code for loading the data fixtures. Add the following code to the bottom
of ``generate.php``:

::

    // generate.php

    // ...
    Doctrine_Core::loadData('fixtures');

=======
Writing
=======

You can write your fixtures files manually and load them in to your
applications. Below is a sample ``data.yml`` fixtures file. You can also
split your data fixtures file up in to multiple files. Doctrine will
read all fixtures files and parse them, then load all data.

For the next several examples we will use the following models:

::

    // models/Resouce.php
    class Resource extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
            $this->hasColumn('resource_type_id', 'integer');
        }

        public function setUp()
        {
            $this->hasOne('ResourceType as Type', array(
                    'local' => 'resource_type_id',
                    'foreign' => 'id'
                )
            );

            $this->hasMany('Tag as Tags', array(
                    'local' => 'resource_id',
                    'foreign' => 'tag_id',
                    'refClass' => 'ResourceTag'
                )
            );
        }
    }

    // models/ResourceType.php
    class ResourceType extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
        }

        public function setUp()
        {
            $this->hasMany('Resource as Resouces', array(
                    'local' => 'id',
                    'foreign' => 'resource_type_id'
                )
            );
        }
    }

    // models/Tag.php
    class Tag extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
        }

        public function setUp()
        {
            $this->hasMany('Resource as Resources', array(
                    'local' => 'tag_id',
                    'foreign' => 'resource_id',
                    'refClass' => 'ResourceTag'
                )
            );
        }
    }

    // models/ResourceTag.php
    class ResourceTag extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('resource_id', 'integer');
            $this->hasColumn('tag_id', 'integer');
        }
    }

    // models/Category.php
    class BaseCategory extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255, array(
                    'type' => 'string', 'length' => '255'
                )
            );
        }

        public function setUp()
        {
            $this->actAs('NestedSet');
        }
    }

    class BaseArticle extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 255, array(
                    'type' => 'string', 'length' => '255'
                )
            );

            $this->hasColumn('body', 'clob', null, array(
                    'type' => 'clob'
                )
            );
        }

        public function setUp()
        {
            $this->actAs('I18n', array('fields' => array('title', 'body')));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    Resource:
      columns:
        name: string(255)
        resource_type_id: integer
      relations:
        Type:
          class: ResourceType
          foreignAlias: Resources
        Tags:
          class: Tag
          refClass: ResourceTag
          foreignAlias: Resources

    ResourceType:
      columns:
        name: string(255)

    Tag:
      columns:
        name: string(255)

    ResourceTag:
      columns:
        resource_id: integer
        tag_id: integer

    Category:
      actAs: [NestedSet]
      columns:
        name: string(255)

    Article:
      actAs:
        I18n:
          fields: [title, body]
      columns:
        title: string(255)
        body: clob

.. note::

    All row keys across all YAML data fixtures must be unique.
    For example below tutorial, doctrine, help, cheat are all unique.

.. code-block:: yaml

    ---
    # fixtures/data.yml

    Resource:
      Resource_1:
        name: Doctrine Video Tutorial
        Type: Video
        Tags: [tutorial, doctrine, help]
      Resource_2:
        name: Doctrine Cheat Sheet
        Type: Image
        Tags: [tutorial, cheat, help]

    ResourceType:
      Video:
        name: Video
      Image:
        name: Image

    Tag:
      tutorial:
        name: tutorial
      doctrine:
        name: doctrine
      help:
        name: help
      cheat:
        name: cheat

You could optionally specify the Resources each tag is related to
instead of specifying the Tags a Resource has.

.. code-block:: yaml

    ---
    # fixtures/data.yml

    # ...
    Tag:
      tutorial:
        name: tutorial
        Resources: [Resource_1, Resource_2]
      doctrine:
        name: doctrine
        Resources: [Resource_1]
      help:
        name: help
        Resources: [Resource_1, Resource_2]
      cheat:
        name: cheat
        Resources: [Resource_1]

========================
Fixtures For Nested Sets
========================

Writing a fixtures file for a nested set tree is slightly different from
writing regular fixtures files. The structure of the tree is defined
like the following:

.. code-block:: yaml

    ---
    # fixtures/data.yml

    Category:
      Category_1:
        name: Categories # the root node
        children:
          Category_2:
            name: Category 1
          Category_3:
            name: Category 2
            children:
              Category_4:
                name: Subcategory of Category 2

.. tip::

    When writing data fixtures for the NestedSet you must either
    specify at least a ``children`` element of the first data block or
    specify ``NestedSet: true`` under the model which is a NestedSet in
    order for the data fixtures to be imported using the NestedSet api.

.. code-block:: yaml

    ---
    # fixtures/data.yml

    # ...
    Category:
      NestedSet: true
      Category_1:
        name: Categories
    # ...

Or simply specifying the children keyword will make the data fixtures
importing using the NestedSet api.

.. code-block:: yaml

    ---
    # fixtures/data.yml

    # ...
    Category:
      Category_1:
        name: Categories
        children: []
    # ...

If you don't use one of the above methods then it is up to you to
manually specify the lft, rgt and level values for your nested set
records.

=================
Fixtures For I18n
=================

The fixtures for the ``I18n`` aren't anything custom since the ``I18n``
really is just a normal set of relationships that are built on the fly
dynamically:

.. code-block:: yaml

    ---
    # fixtures/data.yml

    # ...
    Article:
      Article_1:
        Translation:
          en:
            title: Title of article
            body: Body of article
          fr:
            title: French title of article
            body: French body of article

==========
Conclusion
==========

By now we should be able to write and load our own data fixtures in our
application. So, now we will move on to learning about the underlying
:doc:`database-abstraction-layer` in Doctrine. This layer is what
makes all the previously discussed functionality possible. You can use
this layer standalone apart from the ORM. In the next chapter we'll
explain how you can use the DBAL by itself.
