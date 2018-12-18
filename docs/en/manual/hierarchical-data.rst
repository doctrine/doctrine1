*****************
Hierarchical Data
*****************

============
Introduction
============

Most users at one time or another have dealt with hierarchical data in a
SQL database and no doubt learned that the management of hierarchical
data is not what a relational database is intended for. The tables of a
relational database are not hierarchical (like XML), but are simply a
flat list. Hierarchical data has a parent-child relationship that is not
naturally represented in a relational database table.

For our purposes, hierarchical data is a collection of data where each
item has a single parent and zero or more children (with the exception
of the root item, which has no parent). Hierarchical data can be found
in a variety of database applications, including forum and mailing list
threads, business organization charts, content management categories,
and product categories.

In a hierarchical data model, data is organized into a tree-like
structure. The tree structure allows repeating information using
parent/child relationships. For an explanation of the tree data
structure, see `here <http://en.wikipedia.org/wiki/Tree_data_structure>`_.

There are three major approaches to managing tree structures in
relational databases, these are:

-  the adjacency list model
-  the nested set model (otherwise known as the modified pre-order tree
   traversal algorithm)
-  materialized path model

**These are explained in more detail at the following links:**

-  `http://www.dbazine.com/oracle/or-articles/tropashko4 <http://www.dbazine.com/oracle/or-articles/tropashko4>`_
-  `http://dev.mysql.com/tech-resources/articles/hierarchical-data.html <http://dev.mysql.com/tech-resources/articles/hierarchical-data.html>`_

==========
Nested Set
==========

------------
Introduction
------------

Nested Set is a solution for storing hierarchical data that provides
very fast read access. However, updating nested set trees is more
costly. Therefore this solution is best suited for hierarchies that are
much more frequently read than written to. And because of the nature of
the web, this is the case for most web applications.

For more detailed information on the Nested Set, read here:

-  `http://www.sitepoint.com/article/hierarchical-data-database/2 <http://www.sitepoint.com/article/hierarchical-data-database/2>`_
-  `http://dev.mysql.com/tech-resources/articles/hierarchical-data.html <http://dev.mysql.com/tech-resources/articles/hierarchical-data.html>`_

----------
Setting Up
----------

To set up your model as Nested Set, you must add some code to the
``setUp()`` method of your model. Take this ``Category`` model below for
example:

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
            $this->actAs('NestedSet');
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    # schema.yml

    # ...
    Category:
      actAs: [NestedSet]
      columns:
        name: string(255)

Detailed information on Doctrine's templating model can be found in
chapter :doc:`behaviors`. These templates add some
functionality to your model. In the example of the nested set, your
model gets 3 additional fields: ``lft``, ``rgt`` and ``level``. You
never need to care about the ``lft`` and ``rgt`` fields. These are used
internally to manage the tree structure. The ``level`` field however, is
of interest for you because it's an integer value that represents the
depth of a node within it's tree. A level of 0 means it's a root node. 1
means it's a direct child of a root node and so on. By reading the
``level`` field from your nodes you can easily display your tree with
proper indention.

.. caution::

    You must never assign values to ``lft``, ``rgt``,
    ``level``. These are managed transparently by the nested set
    implementation.

--------------
Multiple Trees
--------------

The nested set implementation can be configured to allow your table to
have multiple root nodes, and therefore multiple trees within the same
table.

The example below shows how to setup and use multiple roots with the
``Category`` model:

::

    // models/Category.php
    class Category extends Doctrine_Record
    {
        // ...
        public function setUp()
        {
            $options = array(
                'hasManyRoots'   => true,
                'rootColumnName' => 'root_id'
            );
            $this->actAs('NestedSet', $options);
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

The ``rootColumnName`` is the column used to differentiate between
trees. When you create a new root node you have the option to set the
``root_id`` manually, otherwise Doctrine will assign a value for you.

In general use you do not need to deal with the ``root_id`` directly.
For example, when you insert a new node into an existing tree or move a
node between trees Doctrine transparently handles the associated
``root_id`` changes for you.

------------------
Working with Trees
------------------

After you successfully set up your model as a nested set you can start
working with it. Working with Doctrine's nested set implementation is
all about two classes: :php:class:`Doctrine_Tree_NestedSet` and
:php:class:`Doctrine_Node_NestedSet`. These are nested set implementations of
the interfaces :php:class:`Doctrine_Tree_Interface` and
:php:class:`Doctrine_Node_Interface`. Tree objects are bound to your table
objects and node objects are bound to your record objects. This looks as
follows:

The full tree interface is available by using the following code:

::

    // test.php

    // ...
    $treeObject = Doctrine_Core::getTable('Category')->getTree();

In the next example ``$category`` is an instance of ``Category``:

::

    // test.php

    // ...
    $nodeObject = $category->getNode();

With the above code the full node interface is available on
``$nodeObject``.

In the following sub-chapters you'll see code snippets that demonstrate
the most frequently used operations with the node and tree classes.

^^^^^^^^^^^^^^^^^^^^
Creating a Root Node
^^^^^^^^^^^^^^^^^^^^

::

    // test.php

    // ...
    $category       = new Category();
    $category->name = 'Root Category 1';
    $category->save();

    $treeObject = Doctrine_Core::getTable('Category')->getTree();
    $treeObject->createRoot($category);

^^^^^^^^^^^^^^^^
Inserting a Node
^^^^^^^^^^^^^^^^

In the next example we're going to add a new ``Category`` instance as a
child of the root ``Category`` we created above:

::

    // test.php

    // ...
    $child1       = new Category();
    $child1->name = 'Child Category 1';

    $child2       = new Category();
    $child2->name = 'Child Category 1';

    $child1->getNode()->insertAsLastChildOf($category);
    $child2->getNode()->insertAsLastChildOf($category);

^^^^^^^^^^^^^^^
Deleting a Node
^^^^^^^^^^^^^^^

Deleting a node from a tree is as simple as calling the ``delete()``
method on the node object:

::

    // test.php

    // ...
    $category = Doctrine_Core::getTable('Category')->findOneByName('Child Category 1');
    $category->getNode()->delete();

.. caution::

    The above code calls ``$category->delete()`` internally.
    It's important to delete on the node and not on the record.
    Otherwise you may corrupt the tree.

Deleting a node will also delete all descendants of that node. So make
sure you move them elsewhere before you delete the node if you don't
want to delete them.

^^^^^^^^^^^^^
Moving a Node
^^^^^^^^^^^^^

Moving a node is simple. Doctrine offers several methods for moving
nodes around between trees:

::

    // test.php

    // ...
    $category       = new Category();
    $category->name = 'Root Category 2';
    $category->save();

    $categoryTable = Doctrine_Core::getTable('Category');
    $treeObject    = $categoryTable->getTree();
    $treeObject->createRoot($category);

    $childCategory = $categoryTable->findOneByName('Child Category 1');
    $childCategory->getNode()->moveAsLastChildOf($category);
    ...

Below is a list of the methods available for moving nodes around:

-  ``moveAsLastChildOf($other)``
-  ``moveAsFirstChildOf($other)``
-  ``moveAsPrevSiblingOf($other)``
-  ``moveAsNextSiblingOf($other)``

The method names should be self-explanatory to you.

^^^^^^^^^^^^^^^^
Examining a Node
^^^^^^^^^^^^^^^^

You can examine the nodes and what type of node they are by using some
of the following functions:

::

    // test.php

    // ...
    $isLeaf = $category->getNode()->isLeaf();
    $isRoot = $category->getNode()->isRoot();

.. note::

    The above used functions return true/false depending on
    whether or not they are a leaf or root node.

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Examining and Retrieving Siblings
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can easily check if a node has any next or previous siblings by
using the following methods:

::

    // test.php

    // ...
    $hasNextSib = $category->getNode()->hasNextSibling();
    $hasPrevSib = $category->getNode()->hasPrevSibling();

You can also retrieve the next or previous siblings if they exist with
the following methods:

::

    // test.php

    // ...
    $nextSib = $category->getNode()->getNextSibling();
    $prevSib = $category->getNode()->getPrevSibling();

.. note::

    The above methods return false if no next or previous
    sibling exists.

If you want to retrieve an array of all the siblings you can simply use
the ``getSiblings()`` method:

::

    // test.php

    // ...
    $siblings = $category->getNode()->getSiblings();

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Examining and Retrieving Descendants
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can check if a node has a parent or children by using the following
methods:

::

    // test.php

    // ...
    $hasChildren = $category->getNode()->hasChildren();
    $hasParent   = $category->getNode()->hasParent();

You can retrieve a nodes first and last child by using the following
methods:

::

    // test.php

    // ...
    $firstChild = $category->getNode()->getFirstChild();
    $lastChild  = $category->getNode()->getLastChild();

Or if you want to retrieve the parent of a node:

::

    // test.php

    // ...
    $parent = $category->getNode()->getParent();

You can get the children of a node by using the following method:

::

    // test.php

    // ...
    $children = $category->getNode()->getChildren();

.. caution::

    The ``getChildren()`` method returns only the direct
    descendants. If you want all descendants, use the
    ``getDescendants()`` method.

You can get the descendants or ancestors of a node by using the
following methods:

::

    // test.php

    // ...
    $descendants = $category->getNode()->getDescendants();
    $ancestors   = $category->getNode()->getAncestors();

Sometimes you may just want to get the number of children or
descendants. You can use the following methods to accomplish this:

::

    // test.php

    // ...
    $numChildren    = $category->getNode()->getNumberChildren();
    $numDescendants = $category->getNode()->getNumberDescendants();

The ``getDescendants()`` and ``getAncestors()`` both accept a parameter
that you can use to specify the ``depth`` of the resulting branch. For
example ``getDescendants(1)`` retrieves only the direct descendants (the
descendants that are 1 level below, that's the same as
``getChildren()``). In the same fashion ``getAncestors(1)`` would only
retrieve the direct ancestor (the parent), etc.`` getAncestors()`` can
be very useful to efficiently determine the path of this node up to the
root node or up to some specific ancestor (i.e. to construct a
breadcrumb navigation).

^^^^^^^^^^^^^^^^^^^^^^^
Rendering a Simple Tree
^^^^^^^^^^^^^^^^^^^^^^^

.. note::

    The next example assumes you have ``hasManyRoots`` set to
    false so in order for the below example to work properly you will
    have to set that option to false. We set the value to true in a
    earlier section.

::

    // test.php

    // ...
    $treeObject = Doctrine_Core::getTable('Category')->getTree();
    $tree       = $treeObject->fetchTree();

    foreach ($tree as $node) {
        echo str_repeat('&nbsp;&nbsp;', $node['level']) . $node['name'] . "\n";
    }

--------------
Advanced Usage
--------------

The previous sections have explained the basic usage of Doctrine's
nested set implementation. This section will go one step further.

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Fetching a Tree with Relations
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you're a demanding software developer this question may already have
come into your mind: "How do I fetch a tree/branch with related data?".
Simple example: You want to display a tree of categories, but you also
want to display some related data of each category, let's say some
details of the hottest product in that category. Fetching the tree as
seen in the previous sections and simply accessing the relations while
iterating over the tree is possible but produces a lot of unnecessary
database queries. Luckily, :php:class:`Doctrine_Query` and some flexibility in
the nested set implementation have come to your rescue. The nested set
implementation uses :php:class:`Doctrine_Query` objects for all it's database
work. By giving you access to the base query object of the nested set
implementation you can unleash the full power of :php:class:`Doctrine_Query`
while using your nested set.

First lets create the query we want to use to retrieve our tree data
with:

::

    // test.php

    // ...
    $q = Doctrine_Query::create()
        ->select('c.name, p.name, m.name')
        ->from('Category c')
        ->leftJoin('c.HottestProduct p')
        ->leftJoin('p.Manufacturer m');

Now we need to set the above query as the base query for the tree:

::

    $treeObject = Doctrine_Core::getTable('Category')->getTree();
    $treeObject->setBaseQuery($q);
    $tree       = $treeObject->fetchTree();

There it is, the tree with all the related data you need, all in one
query.

.. note::

    If you don't set your own base query then one will be
    automatically created for you internally.

When you are done it is a good idea to reset the base query back to
normal:

::

    // test.php

    // ...
    $treeObject->resetBaseQuery();

You can take it even further. As mentioned in the chapter :doc:`improving-performance` you should only fetch objects when you need
them. So, if we need the tree only for display purposes (read-only) we
can use the array hydration to speed things up a bit:

::

    // test.php

    // ...
    $q = Doctrine_Query::create()
        ->select('c.name, p.name, m.name')
        ->from('Category c')
        ->leftJoin('c.HottestProduct p')
        ->leftJoin('p.Manufacturer m')
        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

    $treeObject = Doctrine_Core::getTable('Category')->getTree();
    $treeObject->setBaseQuery($q);
    $tree       = $treeObject->fetchTree();
    $treeObject->resetBaseQuery();

Now you got a nicely structured array in ``$tree`` and if you use array
access on your records anyway, such a change will not even effect any
other part of your code. This method of modifying the query can be used
for all node and tree methods (``getAncestors()``, ``getDescendants()``,
``getChildren()``, ``getParent()``, ...). Simply create your query, set
it as the base query on the tree object and then invoke the appropriate
method.

------------------------
Rendering with Indention
------------------------

Below you will find an example where all trees are rendered with proper
indention. You can retrieve the roots using the ``fetchRoots()`` method
and retrieve each individual tree by using the ``fetchTree()`` method.

::

    // test.php

    // ...
    $treeObject     = Doctrine_Core::getTable('Category')->getTree();
    $rootColumnName = $treeObject->getAttribute('rootColumnName');

    foreach ($treeObject->fetchRoots() as $root) {
        $options = array(
            'root_id' => $root->$rootColumnName
        );
        foreach($treeObject->fetchTree($options) as $node) {
            echo str_repeat(' ', $node['level']) . $node['name'] . "\n";
        }
    }

After doing all the examples above the code above should render as
follows:

.. code-block:: sh

    $ php test.php
    Root Category 1
    Root Category 2
    Child Category 1

==========
Conclusion
==========

Now that we have learned all about the ``NestedSet`` behavior and how to
manage our hierarchical data using Doctrine we are ready to learn about
:doc:`data-fixtures`. Data fixtures are a great tool for loading
small sets of test data in to your applications to be used for unit and
functional tests or to populate your application with its initial data.