**********
Technology
**********

============
Introduction
============

Doctrine is a product of the work of many people. Not just the people
who have coded and documented this software are the only ones
responsible for this great framework. Other ORMs in other languages are
a major resource for us as we can learn from what they have already
done.

.. note::

    Doctrine has also borrowed pieces of code from other open
    source projects instead of re-inventing the wheel. Two of the
    projects borrowed from are `symfony <http://www.symfony-project.com>`_
    and the `Zend Framework <http://framework.zend.com>`_. The relevant
    license information can be found in the root of Doctrine when you
    `download <http://www.doctrine-project.org>`_ it in a file named
    ``LICENSE``.

============
Architecture
============

Doctrine is divided into three main packages: CORE, ORM and DBAL. Below
is a list of some of the main classes that make up each of the packages.

-------------
Doctrine CORE
-------------

-  Doctrine
-  :ref:`Doctrine_Manager <component-overview-manager>`
-  :ref:`Doctrine_Connection <component-overview-connection>`
-  :ref:`Doctrine_Compiler <improving-performance-compile>`
-  :doc:`Doctrine_Exception <exceptions-and-warnings>`
-  Doctrine_Formatter
-  Doctrine_Object
-  Doctrine_Null
-  :doc:`Doctrine_Event <event-listeners>`
-  Doctrine_Overloadable
-  Doctrine_Configurable
-  :doc:`Doctrine_EventListener <event-listeners>`

-------------
Doctrine DBAL
-------------

-  :ref:`Doctrine_Expression_Driver <component-overview-using-expression-values>`
-  :ref:`Doctrine_Export <database-abstraction-layer-export>`
-  :ref:`Doctrine_Import <database-abstraction-layer-import>`
-  Doctrine_Sequence
-  :doc:`Doctrine_Transaction <transactions>`
-  :ref:`Doctrine_DataDict <database-abstraction-layer-datadict>`

Doctrine DBAL is also divided into driver packages.

------------
Doctrine ORM
------------

-  :ref:`Doctrine_Record <component-overview-record>`
-  :ref:`Doctrine_Table <component-overview-table>`
-  :ref:`Doctrine_Relation <defining-models-relationships>`
-  :ref:`Doctrine_Expression <component-overview-using-expression-values>`
-  :doc:`Doctrine_Query <dql-doctrine-query-language>`
-  :doc:`Doctrine_RawSql <native-sql>`
-  :ref:`Doctrine_Collection <component-overview-collection>`
-  Doctrine_Tokenizer

Other miscellaneous packages.

-  :doc:`Doctrine_Validator <data-validation>`
-  Doctrine_Hook
-  :ref:`Doctrine_View <component-overview-views>`

There are also behaviors for Doctrine:

-  :ref:`Geographical <behaviors-core-behaviors-geographical>`
-  :ref:`I18n <behaviors-core-behaviors-i18n>`
-  :ref:`NestedSet <behaviors-core-behaviors-nestedset>`
-  :ref:`Searchable <behaviors-core-behaviors-searchable>`
-  :ref:`Sluggable <behaviors-core-behaviors-sluggable>`
-  :ref:`SoftDelete <behaviors-core-behaviors-softdelete>`
-  :ref:`Timestampable <behaviors-core-behaviors-timestampable>`
-  :ref:`Versionable <behaviors-core-behaviors-versionable>`

====================
Design Patterns Used
====================

``GoF (Gang of Four)`` design patterns used:

-  `Singleton <http://www.dofactory.com/Patterns/PatternSingleton.aspx>`_, for forcing only one instance of :php:class:`Doctrine_Manager`
-  `Composite <http://www.dofactory.com/Patterns/PatternComposite.aspx>`_, for leveled configuration
-  `Factory <http://www.dofactory.com/Patterns/PatternFactory.aspx>`_, for connection driver loading and many other things
-  `Observer <http://www.dofactory.com/Patterns/PatternObserver.aspx>`_, for event listening
-  `Flyweight <http://www.dofactory.com/Patterns/PatternFlyweight.aspx>`_, for efficient usage of validators
-  `Iterator <http://www.dofactory.com/Patterns/PatternFlyweight.aspx>`_, for iterating through components (Tables, Connections, Records etc.)
-  `State <http://www.dofactory.com/Patterns/PatternState.aspx>`_, for state-wise connections
-  `Strategy <http://www.dofactory.com/Patterns/PatternStrategy.aspx>`_, for algorithm strategies

Enterprise application design patterns used:

-  `Active Record <http://www.martinfowler.com/eaaCatalog/activeRecord.html>`_, Doctrine is an implementation of this pattern
-  `UnitOfWork <http://www.martinfowler.com/eaaCatalog/unitOfWork.html>`_, for maintaining a list of objects affected in a transaction
-  `Identity Field <http://www.martinfowler.com/eaaCatalog/identityField.html>`_, for maintaining the identity between record and database row
-  `Metadata Mapping <http://www.martinfowler.com/eaaCatalog/metadataMapping.html>`_, for Doctrine DataDict
-  `Dependent Mapping <http://www.martinfowler.com/eaaCatalog/dependentMapping.html>`_, for mapping in general, since all records extend :php:class:`Doctrine_Record` which performs all mappings
-  `Foreign Key Mapping <http://www.martinfowler.com/eaaCatalog/foreignKeyMapping.html>`_, for one-to-one, one-to-many and many-to-one relationships
-  `Association Table Mapping <http://www.martinfowler.com/eaaCatalog/associationTableMapping.html>`_, for association table mapping (most commonly many-to-many relationships)
-  `Lazy Load <http://www.martinfowler.com/eaaCatalog/lazyLoad.html>`_, for lazy loading of objects and object properties
-  `Query Object <http://www.martinfowler.com/eaaCatalog/queryObject.html>`_, DQL API is actually an extension to the basic idea of Query Object pattern

=====
Speed
=====

-  **Lazy initialization** - For collection elements
-  **Subselect fetching** - Doctrine knows how to fetch collections
   efficiently using a subselect.
-  **Executing SQL statements later, when needed** : The connection
   never issues an INSERT or UPDATE until it is actually needed. So if
   an exception occurs and you need to abort the transaction, some
   statements will never actually be issued. Furthermore, this keeps
   lock times in the database as short as possible (from the late UPDATE
   to the transaction end).
-  **Join fetching** - Doctrine knows how to fetch complex object graphs
   using joins and subselects
-  **Multiple collection fetching strategies** - Doctrine has multiple
   collection fetching strategies for performance tuning.
-  **Dynamic mixing of fetching strategies** - Fetching strategies can
   be mixed and for example users can be fetched in a batch collection
   while users' phonenumbers are loaded in offset collection using only
   one query.
-  **Driver specific optimizations** - Doctrine knows things like
   bulk-insert on mysql.
-  **Transactional single-shot delete** - Doctrine knows how to gather
   all the primary keys of the pending objects in delete list and
   performs only one sql delete statement per table.
-  **Updating only the modified columns.** - Doctrine always knows which
   columns have been changed.
-  **Never inserting/updating unmodified objects.** - Doctrine knows if
   the the state of the record has changed.
-  **PDO for database abstraction** - PDO is by far the fastest
   availible database abstraction layer for php.

==========
Conclusion
==========

This chapter should have given you a complete birds eye view of all the
components of Doctrine and how they are organized. Up until now you have
seen them all used a part from each other but the separate lists of the
three main packages should have made things very clear for you if it was
not already.

Now we are ready to move on and learn about how to deal with Doctrine
throwing exceptions in the :doc:`exceptions-and-warnings` chapter.
