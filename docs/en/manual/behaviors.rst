.. vim: set tw=79 sw=4 ts=4 et :

*********
Behaviors
*********

============
Introduction
============

Many times you may find classes having similar things within your
models. These things may contain anything related to the schema of the
component itself (relations, column definitions, index definitions
etc.). One obvious way of re-factoring the code is having a base class
with some classes extending it.

However inheritance solves only a fraction of things. The following
sections show how using :php:class:`Doctrine_Template` is much more powerful and
flexible than using inheritance.

:php:class:`Doctrine_Template` is a class template system. Templates are
basically ready-to-use little components that your Record classes can
load. When a template is being loaded its ``setTableDefinition()`` and
``setUp()`` methods are being invoked and the method calls inside them
are being directed into the class in question.

This chapter describes the usage of various behaviors available for
Doctrine. You'll also learn how to create your own behaviors. In order
to grasp the concepts of this chapter you should be familiar with the
theory behind :php:class:`Doctrine_Template` and
:php:class:`Doctrine_Record_Generator`. We will explain what these classes are
shortly.

When referring to behaviors we refer to class packages that use
templates, generators and listeners extensively. All the introduced
components in this chapter can be considered ``core`` behaviors, that
means they reside at the Doctrine main repository.

Usually behaviors use generators side-to-side with template classes
(classes that extend :php:class:`Doctrine_Template`). The common workflow is:

*  A new template is being initialized
*  The template creates the generator and calls ``initialize()`` method
*  The template is attached to given class

As you may already know templates are used for adding common definitions
and options to record classes. The purpose of generators is much more
complex. Usually they are being used for creating generic record classes
dynamically. The definitions of these generic classes usually depend on
the owner class. For example the columns of the ``AuditLog`` versioning
class are the columns of the parent class with all the sequence and
autoincrement definitions removed.

================
Simple Templates
================

In the following example we define a template called
``TimestampBehavior``. Basically the purpose of this template is to add
date columns 'created' and 'updated' to the record class that loads this
template. Additionally this template uses a listener called Timestamp
listener which updates these fields based on record actions.

::

    // models/TimestampListener.php
    class TimestampListener extends Doctrine_Record_Listener
    {
        public function preInsert(Doctrine_Event $event)
        {
            $event->getInvoker()->created = date('Y-m-d', time());
            $event->getInvoker()->updated = date('Y-m-d', time());
        }

        public function preUpdate(Doctrine_Event $event)
        {
            $event->getInvoker()->updated = date('Y-m-d', time());
        }
    }

Now lets create a child :php:class:`Doctrine_Template` named
``TimestampTemplate`` so we can attach it to our models with the
``actAs()`` method:

::

    // models/TimestampBehavior.php
	class TimestampTemplate extends Doctrine_Template
	{
	    public function setTableDefinition()
	    {
	        $this->hasColumn('created', 'date');
	        $this->hasColumn('updated', 'date');

	        $this->addListener(new TimestampListener());
	    }
	}

Lets say we have a class called ``BlogPost`` that needs the timestamp
functionality. All we need to do is to add ``actAs()`` call in the class
definition.

::

    class BlogPost extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 200);
            $this->hasColumn('body', 'clob');
        }

        public function setUp()
        {
            $this->actAs('TimestampBehavior');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    BlogPost:
      actAs: [TimestampBehavior]
      columns:
        title: string(200)
        body: clob

Now when we try and utilize the ``BlogPost`` model you will notice that
the ``created`` and ``updated`` columns were added for you and
automatically set when saved:

::

    $blogPost        = new BlogPost();
    $blogPost->title = 'Test';
    $blogPost->body  = 'test';
    $blogPost->save();

    print_r($blogPost->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [title] => Test
        [body] => test
        [created] => 2009-01-22
        [updated] => 2009-01-22
    )

.. note::

    The above described functionality is available via the
    ``Timestampable`` behavior that we have already talked about. You
    can go back and read more about it in the :doc:`behaviors:core-behaviors:timestampable` section of this
    chapter.

========================
Templates with Relations
========================

Many times the situations tend to be much more complex than the
situation in the previous chapter. You may have model classes with
relations to other model classes and you may want to replace given class
with some extended class.

Consider we have two classes, ``User`` and ``Email``, with the following
definitions:

::

    class User extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('username', 'string', 255);
            $this->hasColumn('password', 'string', 255);
        }

        public function setUp()
        {
            $this->hasMany('Email', array(
                    'local' => 'id',
                    'foreign' => 'user_id'
                )
            );
        }
    }

    class Email extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('address', 'string');
            $this->hasColumn('user_id', 'integer');
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                    'local' => 'user_id',
                    'foreign' => 'id'
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    User:
      columns:
        username: string(255)
        password: string(255)

    Email:
      columns:
        address: string
        user_id: integer
      relations:
        User:

Now if we extend the ``User`` and ``Email`` classes and create, for
example, classes ``ExtendedUser`` and ``ExtendedEmail``, the
``ExtendedUser`` will still have a relation to the ``Email`` class and
not the ``ExtendedEmail`` class. We could of course override the
``setUp()`` method of the ``User`` class and define relation to the
``ExtendedEmail`` class, but then we lose the whole point of
inheritance. :php:class:`Doctrine_Template` can solve this problem elegantly
with its dependency injection solution.

In the following example we'll define two templates, ``UserTemplate``
and ``EmailTemplate``, with almost identical definitions as the ``User``
and ``Email`` class had.

::

    // models/UserTemplate.php
    class UserTemplate extends Doctrine_Template
    {
        public function setTableDefinition()
        {
            $this->hasColumn('username', 'string', 255);
            $this->hasColumn('password', 'string', 255);
        }

        public function setUp()
        {
            $this->hasMany('EmailTemplate as Emails', array(
                    'local' => 'id',
                    'foreign' => 'user_id'
                )
            );
        }
    }

Now lets define the ``EmailTemplate``:

::

    // models/EmailTemplate.php
    class EmailTemplate extends Doctrine_Template
    {
        public function setTableDefinition()
        {
            $this->hasColumn('address', 'string');
            $this->hasColumn('user_id', 'integer');
        }

        public function setUp()
        {
            $this->hasOne('UserTemplate as User', array(
                    'local' => 'user_id',
                    'foreign' => 'id'
                )
            );
        }
    }

Notice how we set the relations. We are not pointing to concrete Record
classes, rather we are setting the relations to templates. This tells
Doctrine that it should try to find concrete Record classes for those
templates. If Doctrine can't find these concrete implementations the
relation parser will throw an exception, but before we go ahead of
things, here are the actual record classes:

::

    class User extends Doctrine_Record
    {
        public function setUp()
        {
            $this->actAs('UserTemplate');
        }
    }

    class Email extends Doctrine_Record
    {
        public function setUp()
        {
            $this->actAs('EmailTemplate');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    User:
      actAs: [UserTemplate]

    Email:
      actAs: [EmailTemplate]

Now consider the following code snippet. This does NOT work since we
haven't yet set any concrete implementations for the templates.

::

    // test.php

    // ...
    $user = new User();
    $user->Emails; // throws an exception

The following version works. Notice how we set the concrete
implementations for the templates globally using :php:class:`Doctrine_Manager`:

::

    // bootstrap.php

    // ...
    $manager->setImpl('UserTemplate', 'User')
            ->setImpl('EmailTemplate', 'Email');

Now this code will work and won't throw an exception like it did before:

::

    $user                     = new User();
    $user->Emails[0]->address = 'jonwage@gmail.com';
    $user->save();

    print_r($user->toArray(true));

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [username] =>
        [password] =>
        [Emails] => Array
            (
                [0] => Array
                    (
                        [id] => 1
                        [address] => jonwage@gmail.com
                        [user_id] => 1
                    )
            )
    )

.. tip::

    The implementations for the templates can be set at manager,
    connection and even at the table level.

================
Delegate Methods
================

Besides from acting as a full table definition delegate system,
:php:class:`Doctrine_Template` allows the delegation of method calls. This means
that every method within the loaded templates is available in the record
that loaded the templates. Internally the implementation uses magic
method called ``__call()`` to achieve this functionality.

Lets add to our previous example and add some custom methods to the
``UserTemplate``:

::

    // models/UserTemplate.php
    class UserTemplate extends Doctrine_Template
    {
        // ...
        public function authenticate($username, $password)
        {
            $invoker = $this->getInvoker();
            if ($invoker->username == $username && $invoker->password == $password)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

Now take a look at the following code and how we can use it:

::

    $user           = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';

    if ($user->authenticate('jwage', 'changemte'))
    {
        echo 'Authenticated successfully!';
    }
    else
    {
        echo 'Could not authenticate user!';
    }

You can also delegate methods to :php:class:`Doctrine_Table` classes just as
easily. But, to avoid naming collisions the methods for table classes
must have the string ``TableProxy`` appended to the end of the method
name.

Here is an example where we add a new finder method:

::

    // models/UserTemplate.php
    class UserTemplate extends Doctrine_Template
    {
        // ...
        public function findUsersWithEmailTableProxy()
        {
            return Doctrine_Query::create()
                ->select('u.username')
                ->from('User u')
                ->innerJoin('u.Emails e')
                ->execute();
        }
    }

Now we can access that function from the :php:class:`Doctrine_Table` object for
the ``User`` model:

::

    $userTable = Doctrine_Core::getTable('User');
    $users = $userTable->findUsersWithEmail();

.. tip::

    Each class can consists of multiple templates. If the
    templates contain similar definitions the most recently loaded
    template always overrides the former.

==================
Creating Behaviors
==================

This subchapter provides you the means for creating your own behaviors.
Lets say we have various different Record classes that need to have
one-to-many emails. We achieve this functionality by creating a generic
behavior which creates Email classes on the fly.

We start this task by creating a behavior called ``EmailBehavior`` with
a ``setTableDefinition()`` method. Inside the ``setTableDefinition()``
method various helper methods can be used for easily creating the
dynamic record definition. Commonly the following methods are being
used:

::

    public function initOptions()
    public function buildLocalRelation()
    public function buildForeignKeys(Doctrine_Table $table)
    public function buildForeignRelation($alias = null)
    public function buildRelation() // calls buildForeignRelation() and buildLocalRelation()

::

    class EmailBehavior extends Doctrine_Record_Generator
    {
        public function initOptions()
        {
            $this->setOption('className', '%CLASS%Email');

            // Some other options
            // $this->setOption('appLevelDelete', true);
            // $this->setOption('cascadeDelete', false);
        }

        public function buildRelation()
        {
            $this->buildForeignRelation('Emails');
            $this->buildLocalRelation();
        }

        public function setTableDefinition()
        {
            $this->hasColumn('address', 'string', 255, array(
                    'email'  => true,
                    'primary' => true
                )
            );
        }
    }

==============
Core Behaviors
==============

For the next several examples using the core behaviors lets delete all
our existing schemas and models from our test environment we created and
have been using in the earlier chapters:

.. code-block:: sh

    $ rm schema.yml
    $ touch schema.yml
    $ rm -rf models/*

------------
Introduction
------------

Doctrine comes bundled with some templates that offer out of the box
functionality for your models. You can enable these templates in your
models very easily. You can do it directly in your :php:class:`Doctrine_Record`s
or you can specify them in your YAML schema if you are managing your
models with YAML.

In the next several examples we will demonstrate some of the behaviors
that come bundled with Doctrine.

.. _behaviors-core-behaviors-versionable:

-----------
Versionable
-----------

Lets create a ``BlogPost`` model that we want to have the ability to
have versions:

::

    // models/BlogPost.php
    class BlogPost extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 255);
            $this->hasColumn('body', 'clob');
        }

        public function setUp()
        {
            $this->actAs('Versionable', array(
                    'versionColumn' => 'version',
                    'className' => '%CLASS%Version',
                    'auditLog' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    BlogPost:
      actAs:
        Versionable:
          versionColumn: version
          className: %CLASS%Version
          auditLog: true
      columns:
        title: string(255)
        body: clob

.. note::

    The ``auditLog`` option can be used to turn off the audit
    log history. This is when you want to maintain a version number but
    not maintain the data at each version.

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('BlogPost'));
    echo $sql[0] . "\n";
    echo $sql[1];

The above code would output the following SQL query:

::

    CREATE TABLE blog_post_version (id BIGINT,
    title VARCHAR(255),
    body LONGTEXT,
    version BIGINT,
    PRIMARY KEY(id,
    version)) ENGINE = INNODB
    CREATE TABLE blog_post (id BIGINT AUTO_INCREMENT,
    title VARCHAR(255),
    body LONGTEXT,
    version BIGINT,
    PRIMARY KEY(id)) ENGINE = INNODB
    ALTER TABLE blog_post_version ADD FOREIGN KEY (id) REFERENCES blog_post(id) ON UPDATE CASCADE ON DELETE CASCADE

.. note::

    Notice how we have 2 additional statements we probably
    didn't expect to see. The behavior automatically created a
    ``blog_post_version`` table and related it to ``blog_post``.

Now when we insert or update a ``BlogPost`` the version table will store
all the old versions of the record and allow you to revert back at
anytime. When you instantiate a ``BlogPost`` for the first time this is
what is happening internally:

-  It creates a class called ``BlogPostVersion`` on-the-fly, the table
   this record is pointing at is ``blog_post_version``
-  Everytime a ``BlogPost`` object is deleted / updated the previous
   version is stored into ``blog_post_version``
-  Everytime a ``BlogPost`` object is updated its version number is
   increased.

Now lets play around with the ``BlogPost`` model:

::

    $blogPost        = new BlogPost();
    $blogPost->title = 'Test blog post';
    $blogPost->body  = 'test';
    $blogPost->save();

    $blogPost->title = 'Modified blog post title';
    $blogPost->save();

    print_r($blogPost->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [title] => Modified blog post title
        [body] => test
        [version] => 2
    )

.. note::

    Notice how the value of the ``version`` column is ``2``.
    This is because we have saved 2 versions of the ``BlogPost`` model.
    We can easily revert to another version by using the ``revert()``
    method that the behavior includes.

Lets revert back to the first version:

::

    $blogPost->revert(1);
    print_r($blogPost->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 2
        [title] => Test blog post
        [body] => test
        [version] => 1
    )

.. note::

    Notice how the value of the ``version`` column is set to 1
    and the ``title`` is back to the original value was set it to when
    creating the ``BlogPost``.

.. _behaviors-core-behaviors-timestampable:

-------------
Timestampable
-------------

The Timestampable behavior will automatically add a ``created_at`` and
``updated_at`` column and automatically set the values when a record is
inserted and updated.

Since it is common to want to know the date a post is made lets expand
our ``BlogPost`` model and add the ``Timestampable`` behavior to
automatically set these dates for us.

::

    // models/BlogPost.php
    class BlogPost extends Doctrine_Record
    {
        // ...
        public function setUp()
        {
            $this->actAs('Timestampable');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    BlogPost:
      actAs:
    # ...
        Timestampable:
    # ...

If you are only interested in using only one of the columns, such as a
``created_at`` timestamp, but not a an ``updated_at`` field, set the
``disabled`` to true for either of the fields as in the example below.

.. code-block:: yaml

    ---
    BlogPost:
      actAs:
    # ...
        Timestampable:
          created:
            name: created_at
            type: timestamp
            format: Y-m-d H:i:s
          updated:
            disabled: true
    # ...

Now look what happens when we create a new post:

::

    $blogPost        = new BlogPost();
    $blogPost->title = 'Test blog post';
    $blogPost->body  = 'test';
    $blogPost->save();

    print_r($blogPost->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [title] => Test blog post
        [body] => test
        [version] => 1
        [created_at] => 2009-01-21 17:54:23
        [updated_at] => 2009-01-21 17:54:23
    )

.. note::

    Look how the ``created_at`` and ``updated_at`` values
    were automatically set for you!

Here is a list of all the options you can use with the ``Timestampable``
behavior on the created side of the behavior:

==============  ====================  ===================================================================================================================================================
Name            Default               Description
==============  ====================  ===================================================================================================================================================
``name``        ``created_at``        The name of the column.
``type``        ``timestamp``         The column type.
``options``     ``array()``           Any additional options for the column.
``format``      ``Y-m-d H:i:s``       The format of the timestamp if you don't use the timestamp column type. The date is built using PHP's `date() <http://www.php.net/date>`_ function.
``disabled``    ``false``             Whether or not to disable the created date.
``expression``  ``NOW()``             Expression to use to set the column value.
==============  ====================  ===================================================================================================================================================

Here is a list of all the options you can use with the ``Timestampable``
behavior on the updated side of the behavior that are not possible on
the created side:

============  ========  =========================================================================
Name          Default   Description
============  ========  =========================================================================
``onInsert``  ``true``  Whether or not to set the updated date when the record is first inserted.
============  ========  =========================================================================

.. _behaviors-core-behaviors-sluggable:

---------
Sluggable
---------

The ``Sluggable`` behavior is a nice piece of functionality that will
automatically add a column to your model for storing a unique human
readable identifier that can be created from columns like title,
subject, etc. These values can be used for search engine friendly urls.

Lets expand our ``BlogPost`` model to use the ``Sluggable`` behavior
because we will want to have nice URLs for our posts:

::

    // models/BlogPost.php
    class BlogPost extends Doctrine_Record
    {
        // ...
        public function setUp()
        {
            // ...
            $this->actAs('Sluggable', array(
                    'unique'    => true,
                    'fields'    => array('title'),
                    'canUpdate' => true
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    BlogPost:
      actAs:
    # ...
        Sluggable:
          unique: true
          fields: [title]
          canUpdate: true
    # ...

Now look what happens when we create a new post. The slug column will
automatically be set for us:

::

    $blogPost        = new BlogPost();
    $blogPost->title = 'Test blog post';
    $blogPost->body  = 'test';
    $blogPost->save();

    print_r($blogPost->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [title] => Test blog post
        [body] => test
        [version] => 1
        [created_at] => 2009-01-21 17:57:05
        [updated_at] => 2009-01-21 17:57:05
        [slug] => test-blog-post
    )

.. note::

    Notice how the value of the ``slug`` column was
    automatically set based on the value of the ``title`` column. When a
    slug is created, by default it is ``urlized`` which means all
    non-url-friendly characters are removed and white space is replaced
    with hyphens(-).

The unique flag will enforce that the slug created is unique. If it is
not unique an auto incremented integer will be appended to the slug
before saving to database.

The ``canUpdate`` flag will allow the users to manually set the slug
value to be used when building the url friendly slug.

Here is a list of all the options you can use on the ``Sluggable``
behavior:

===============  =========================================  ===============================================
Name             Default                                    Description
===============  =========================================  ===============================================
``name``         ``slug``                                   The name of the slug column.
``alias``        ``null``                                   The alias of the slug column.
``type``         ``string``                                 The type of the slug column.
``length``       ``255``                                    The length of the slug column.
``unique``       ``true``                                   Whether or not unique slug values are enforced.
``options``      ``array()``                                Any other options for the slug column.
``fields``       ``array()``                                The fields that are used to build slug value.
``uniqueBy``     ``array()``                                The fields that make determine a unique slug.
``uniqueIndex``  ``true``                                   Whether or not to create a unique index.
``canUpdate``    ``false``                                  Whether or not the slug can be updated.
``builder``      ``array('Doctrine_Inflector', 'urlize')``  The ``Class::method()`` used to build the slug.
``indexName``    ``sluggable``                              The name of the index to create.
===============  =========================================  ===============================================

.. _behaviors-core-behaviors-i18n:

----
I18n
----

:php:class:`Doctrine_I18n` package is a behavior for Doctrine that provides
internationalization support for record classes. In the following
example we have a ``NewsItem`` class with two fields ``title`` and
``content``. We want to have the field ``title`` with different
languages support. This can be achieved as follows:

::

    class NewsItem extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 255);
            $this->hasColumn('body', 'blog');
        }

        public function setUp()
        {
            $this->actAs('I18n', array(
                    'fields' => array('title', 'body')
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    NewsItem:
      actAs:
        I18n:
          fields: [title, body]
      columns:
        title: string(255)
        body: clob

Below is a list of all the options you can use with the ``I18n``
behavior:

=============  ======================  ============================================
Name           Default                 Description
=============  ======================  ============================================
``className``  ``%CLASS%Translation``  The name pattern to use for generated class.
``fields``     ``array()``             The fields to internationalize.
``type``       ``string``              The type of ``lang`` column.
``length``     ``2``                   The length of the ``lang`` column.
``options``    ``array()``             Other options for the ``lang`` column.
=============  ======================  ============================================

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('NewsItem'));
    echo $sql[0] . "";
    echo $sql[1];

The above code would output the following SQL query:

::

    CREATE TABLE news_item_translation (id BIGINT,
    title VARCHAR(255),
    body LONGTEXT,
    lang CHAR(2),
    PRIMARY KEY(id,
    lang)) ENGINE = INNODB
    CREATE TABLE news_item (id BIGINT AUTO_INCREMENT,
    PRIMARY KEY(id)) ENGINE = INNODB

.. note::

    Notice how the field ``title`` is not present in the
    ``news_item`` table. Since its present in the translation table it
    would be a waste of resources to have that same field in the main
    table. Basically Doctrine always automatically removes all
    translated fields from the main table.

Now the first time you initialize a new ``NewsItem`` record Doctrine
initializes the behavior that builds the followings things:

1. Record class called ``NewsItemTranslation``
2. Bi-directional relations between ``NewsItemTranslation`` and
   ``NewsItem``

Lets take a look at how we can manipulate the translations of the
``NewsItem``:

::

    // test.php

    // ...
    $newsItem = new NewsItem();
    $newsItem->Translation['en']->title = 'some title';
    $newsItem->Translation['en']->body  = 'test';
    $newsItem->Translation['fi']->title = 'joku otsikko';
    $newsItem->Translation['fi']->body  = 'test'; $newsItem->save();

    print_r($newsItem->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [id] => 1
        [Translation] => Array
            (
                [en] => Array
                    (
                        [id] => 1
                        [title] => some title
                        [body] => test
                        [lang] => en
                    )
                [fi] => Array
                    (
                        [id] => 1
                        [title] => joku otsikko
                        [body] => test
                        [lang] => fi
                    )
            )
    )

How do we retrieve the translated data now? This is easy! Lets find all
items and their Finnish translations:

::

    // test.php

    // ...
    $newsItems = Doctrine_Query::create()
        ->from('NewsItem n')
        ->leftJoin('n.Translation t')
        ->where('t.lang = ?')
        ->execute(array('fi'));

    echo $newsItems[0]->Translation['fi']->title;

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    joku otsikko

.. _behaviors-core-behaviors-nestedset:

---------
NestedSet
---------

The ``NestedSet`` behavior allows you to turn your models in to a nested
set tree structure where the entire tree structure can be retrieved in
one efficient query. It also provided a nice interface for manipulating
the data in your trees.

Lets take a ``Category`` model for example where the categories need to
be organized in a hierarchical tree structure:

::

    // models/Category.php
    class Category extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', 255);
        }

        public function setUp()
        {
            $this->actAs('NestedSet', array(
                    'hasManyRoots' => true,
                    'rootColumnName' => 'root_id'
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    Category:
      actAs:
        NestedSet:
          hasManyRoots: true
          rootColumnName: root_id
      columns:
        name: string(255)

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('Category'));
    echo $sql[0];

The above code would output the following SQL query:

::

    CREATE TABLE category (id BIGINT AUTO_INCREMENT,
    name VARCHAR(255),
    root_id INT,
    lft INT,
    rgt INT,
    level SMALLINT,
    PRIMARY KEY(id)) ENGINE = INNODB

.. note::

    Notice how the ``root_id``, ``lft``, ``rgt`` and ``level``
    columns are automatically added. These columns are used to organize
    the tree structure and are handled automatically for you internally.

We won't discuss the ``NestedSet`` behavior in 100% detail here. It is a
very large behavior so it has its own :doc:`hierarchical-data`.

.. _behaviors-core-behaviors-searchable:

----------
Searchable
----------

The ``Searchable`` behavior is a fulltext indexing and searching tool.
It can be used for indexing and searching both database and files.

Imagine we have a ``Job`` model for job postings and we want it to be
easily searchable:

::

    // models/Job.php
    class Job extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 255);
            $this->hasColumn('description', 'clob');
        }

        public function setUp()
        {
            $this->actAs('Searchable', array(
                    'fields' => array('title', 'content')
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    Job:
      actAs:
        Searchable:
          fields: [title, description]
      columns:
        title: string(255)
        description: clob

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('Job'));
    echo $sql[0] . "";
    echo $sql[1] . "";
    echo $sql[2];

The above code would output the following SQL query:

::

    CREATE TABLE job_index (id BIGINT,
    keyword VARCHAR(200),
    field VARCHAR(50),
    position BIGINT,
    PRIMARY KEY(id,
    keyword,
    field,
    position)) ENGINE = INNODB
    CREATE TABLE job (id BIGINT AUTO_INCREMENT,
    title VARCHAR(255),
    description LONGTEXT,
    PRIMARY KEY(id)) ENGINE = INNODB
    ALTER TABLE job_index ADD FOREIGN KEY (id) REFERENCES job(id) ON UPDATE CASCADE ON DELETE CASCADE

.. note::

    Notice how the ``job_index`` table is automatically
    created for you and a foreign key between ``job`` and ``job_index``
    was automatically created.

Because the ``Searchable`` behavior is such a large topic, we have more
information on this that can be found in the :doc:`searching`
chapter.

.. _behaviors-core-behaviors-geographical:

------------
Geographical
------------

The below is only a demo. The Geographical behavior can be used with any
data record for determining the number of miles or kilometers between 2
records.

::

    // models/Zipcode.php
    class Zipcode extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('zipcode', 'string', 255);
            $this->hasColumn('city', 'string', 255);
            $this->hasColumn('state', 'string', 2);
            $this->hasColumn('county', 'string', 255);
            $this->hasColumn('zip_class', 'string', 255);
        }

        public function setUp()
        {
            $this->actAs('Geographical');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    Zipcode:
      actAs: [Geographical]
      columns:
        zipcode: string(255)
        city: string(255)
        state: string(2)
        county: string(255)
        zip_class: string(255)

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('Zipcode'));
    echo $sql[0];

The above code would output the following SQL query:

::

    CREATE TABLE zipcode (id BIGINT AUTO_INCREMENT,
    zipcode VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(2),
    county VARCHAR(255),
    zip_class VARCHAR(255),
    latitude DOUBLE,
    longitude DOUBLE,
    PRIMARY KEY(id)) ENGINE = INNODB

.. note::

    Notice how the Geographical behavior automatically adds the
    ``latitude`` and ``longitude`` columns to the records used for
    calculating distance between two records. Below you will find some
    example usage.

First lets retrieve two different zipcode records:

::

    // test.php

    // ...
    $zipcode1 = Doctrine_Core::getTable('Zipcode')->findOneByZipcode('37209');
    $zipcode2 = Doctrine_Core::getTable('Zipcode')->findOneByZipcode('37388');

Now we can get the distance between those two records by using the
``getDistance()`` method that the behavior provides:

::

    // test.php

    // ...
    echo $zipcode1->getDistance($zipcode2, $kilometers = false);

.. note::

    The 2nd argument of the ``getDistance()`` method is whether
    or not to return the distance in kilometers. The default is false.

Now lets get the 50 closest zipcodes that are not in the same city:

::

    // test.php

    // ...
    $q = $zipcode1->getDistanceQuery();

    $q->orderby('miles asc')
        ->addWhere($q->getRootAlias() . '.city != ?', $zipcode1->city)
        ->limit(50);

    echo $q->getSqlQuery();

The above call to ``getSql()`` would output the following SQL query:

::

    SELECT
    z.id AS z**id,
    z.zipcode AS z**zipcode,
    z.city AS z**city,
    z.state AS z**state,
    z.county AS z**county,
    z.zip_class AS z**zip_class,
    z.latitude AS z**latitude,
    z.longitude AS z**longitude,
    ((ACOS(SIN(* PI() / 180) * SIN(z.latitude * PI() / 180) + COS(* PI() / 180) * COS(z.latitude * PI() / 180) * COS((- z.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS z**0,
    ((ACOS(SIN(* PI() / 180) * SIN(z.latitude * PI() / 180) + COS(* PI() / 180) * COS(z.latitude * PI() / 180) * COS((- z.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 1.609344) AS z**1
    FROM zipcode z
    WHERE z.city != ?
    ORDER BY z__0 asc
    LIMIT 50

.. note::

    Notice how the above SQL query includes a bunch of SQL that
    we did not write. This was automatically added by the behavior to
    calculate the number of miles between records.

Now we can execute the query and use the calculated number of miles
values:

::

    // test.php

    // ...
    $result = $q->execute();

    foreach ($result as $zipcode) {
        echo $zipcode->city . " - " . $zipcode->miles . "";
        // You could also access $zipcode->kilometers
    }

Get some sample zip code data to test this

`http://www.populardata.com/zip_codes.zip <http://www.populardata.com/zip_codes.zip>`_

Download and import the csv file with the following function:

::

    // test.php

    // ...
    function parseCsvFile($file, $columnheadings = false, $delimiter = ',', $enclosure = "\"")
    {
        $row    = 1;
        $rows   = array();
        $handle = fopen($file, 'r');

        while (($data = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== FALSE) {

            if (!($columnheadings == false) && ($row == 1)) {
                $headingTexts = $data;
            } elseif (!($columnheadings == false)) {
                foreach ($data as $key => $value) {
                    unset($data[$key]);
                    $data[$headingTexts[$key]] = $value;
                }
                $rows[] = $data;
            } else {
                $rows[] = $data;
            }
            $row++;
        }

        fclose($handle);
        return $rows;
    }

    $array = parseCsvFile('zipcodes.csv', false);

    foreach ($array as $key => $value) {
        $zipcode = new Zipcode();
        $zipcode->fromArray($value);
        $zipcode->save();
    }

.. _behaviors-core-behaviors-softdelete:

----------
SoftDelete
----------

The ``SoftDelete`` behavior is a very simple yet highly desired model
behavior which overrides the ``delete()`` functionality and adds a
``deleted_at`` column. When ``delete()`` is called, instead of deleting
the record from the database, a delete_at date is set. Below is an
example of how to create a model with the ``SoftDelete`` behavior being
used.

::

    // models/SoftDeleteTest.php
    class SoftDeleteTest extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('name', 'string', null, array(
                    'primary' => true
                )
            );
        }

        public function setUp()
        {
            $this->actAs('SoftDelete');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    SoftDeleteTest:
      actAs: [SoftDelete]
      columns:
        name:
          type: string(255)
          primary: true

Lets check the SQL that is generated by the above models:

::

    // test.php

    // ...
    $sql = Doctrine_Core::generateSqlFromArray(array('SoftDeleteTest'));
    echo $sql[0];

The above code would output the following SQL query:

::

    CREATE TABLE soft_delete_test (name VARCHAR(255),
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY(name)) ENGINE = INNODB

Now lets put the behavior in action.

.. note::

    You are required to enable DQL callbacks in order for all
    executed queries to have the dql callbacks executed on them. In the
    SoftDelete behavior they are used to filter the select statements to
    exclude all records where the deleted\_at flag is set with an
    additional WHERE condition.

**Enable DQL Callbacks**

::

    // bootstrap.php

    // ...
    $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

Now save a new record so we can test the ``SoftDelete`` functionality:

::

    // test.php

    // ...
    $record       = new SoftDeleteTest();
    $record->name = 'new record';
    $record->save();

Now when we call ``delete()`` the ``deleted_at`` flag will be set to
true:

::

    // test.php

    // ...
    $record->delete();

    print_r($record->toArray());

The above example would produce the following output:

.. code-block:: sh

    $ php test.php
    Array
    (
        [name] => new record
        [deleted_at] => 2009-09-01 00:59:01
    )

Also, when we select some data the query is modified for you:

::

    // test.php

    // ...
    $q = Doctrine_Query::create()
        ->from('SoftDeleteTest t');

    echo $q->getSqlQuery();

The above call to ``getSql()`` would output the following SQL query:

::

    SELECT
    s.name AS s**name,
    s.deleted_at AS s**deleted_at
    FROM soft_delete_test s
    WHERE (s.deleted_at IS NULL)

.. note::

    Notice how the where condition is automatically added to
    only return the records that have not been deleted.

Now if we execute the query:

::

    // test.php

    // ...
    $count = $q->count();
    echo $count;

The above would be echo 0 because it would exclude the record saved
above because the delete flag was set.

=================
Nesting Behaviors
=================

Below is an example of several behaviors to give a complete wiki
database that is versionable, searchable, sluggable, and full I18n.

::

    class Wiki extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('title', 'string', 255);
            $this->hasColumn('content', 'string');
        }

        public function setUp()
        {
            $options  = array('fields' => array('title', 'content'));
            $auditLog = new Doctrine_Template_Versionable($options);
            $search   = new Doctrine_Template_Searchable($options);
            $slug     = new Doctrine_Template_Sluggable(array(
                    'fields' => array('title')
                )
            );
            $i18n = new Doctrine_Template_I18n($options);

            $i18n->addChild($auditLog)
                ->addChild($search)
                ->addChild($slug);

            $this->actAs($i18n);

            $this->actAs('Timestampable');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    WikiTest:
      actAs:
        I18n:
          fields: [title, content]
          actAs:
            Versionable:
              fields: [title, content]
            Searchable:
              fields: [title, content]
            Sluggable:
              fields: [title]
      columns:
        title: string(255)
        content: string

.. note::

    The above example of nesting behaviors is currently broken
    in Doctrine. We are working furiously to come up with a backwards
    compatible fix. We will announce when the fix is ready and update
    the documentation accordingly.

================
Generating Files
================

By default with behaviors the classes which are generated are evaluated
at run-time and no files containing the classes are ever written to
disk. This can be changed with a configuration option. Below is an
example of how to configure the I18n behavior to generate the classes
and write them to files instead of evaluating them at run-time.

::

    class NewsArticle extends Doctrine_Record
    {
        public function setTableDefinition() {
            $this->hasColumn('title', 'string', 255);
            $this->hasColumn('body', 'string', 255); $this->hasColumn('author', 'string', 255);
        }

        public function setUp()
        {
            $this->actAs('I18n', array(
                    'fields'          => array('title', 'body'),
                    'generateFiles'   => true,
                    'generatePath'    => '/path/to/generate'
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    NewsArticle:
      actAs:
        I18n:
          fields: [title, body]
          generateFiles: true
          generatePath: /path/to/generate
      columns:
        title: string(255)
        body: string(255)
        author: string(255)

Now the behavior will generate a file instead of generating the code and
using `eval() <http://www.php.net/eval>`_ to evaluate it at runtime.

==========================
Querying Generated Classes
==========================

If you want to query the auto generated models you will need to make
sure the model with the behavior attached is loaded and initialized. You
can do this by using the static ``Doctrine_Core::initializeModels()``
method.

For example if you want to query the translation table for a
``BlogPost`` model you will need to run the following code:

::

    Doctrine_Core::initializeModels(array('BlogPost'));

    $q = Doctrine_Query::create()
        ->from('BlogPostTranslation t')
        ->where('t.id = ? AND t.lang = ?', array(1, 'en'));

    $translations = $q->execute();

.. note::

    This is required because the behaviors are not instantiated
    until the model is instantiated for the first time. The above
    ``initializeModels()`` method instantiates the passed models and
    makes sure the information is properly loaded in to the array of
    loaded models.

==========
Conclusion
==========

By now we should know a lot about Doctrine behaviors. We should know how
to write our own for our models as well as how to use all the great
behaviors that come bundled with Doctrine.

Now we are ready to move on to discuss the :doc:`searching`
behavior in more detail in the :doc:`searching` chapter. As it is a
large topic we have devoted an entire chapter to it.