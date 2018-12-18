**********
Extensions
**********

The Doctrine extensions are a way to create reusable Doctrine extensions
that can be dropped into any project and enabled. An extension is
nothing more than some code that follows the Doctrine standards for code
naming, autoloading, etc.

In order to use the extensions you must first configure Doctrine to know
the path to where your extensions live:

::

    Doctrine_Core::setExtensionsPath( '/path/to/extensions' );

Lets checkout an existing extension from SVN to have a look at it. We'll
have a look at the ``Sortable`` extension which bundles a behavior for
your models which give you up and down sorting capabilities.

.. code-block:: sh

    $ svn co http://svn.doctrine-project.org/extensions/Sortable/branches/1.2-1.0/ /path/to/extensions/Sortable

If you have a look at ``/path/to/extensions/Sortable`` you will see a
directory structure that looks like the following:

::

    Sortable/
      lib/
        Doctrine/
          Template/
            Listener/
              Sortable.php
              Sortable.php
      tests/
        run.php
        Template/
          SortableTestCase.php

To test that the extension will run on your machine you can run the test
suite for the extension. All you need to do is set the ``DOCTRINE_DIR``
environment variable.

.. code-block:: sh

    $ export DOCTRINE_DIR=/path/to/doctrine

.. note::

    The above path to Doctrine must be the path to the main
    folder, not just the lib folder. In order to run the tests it must
    have access to the ``tests`` directory included with Doctrine.

It is possible now to run the tests for the ``Sortable`` extension:

.. code-block:: sh

    $ cd /path/to/extensions/Sortable/tests
    $ php run.php

You should see the tests output the following showing the tests were
successful:

::

    Doctrine Unit Tests
    ===================
    Doctrine_Template_Sortable_TestCase.............................................passed

    Tested: 1 test cases.
    Successes: 26 passes.
    Failures: 0 fails.
    Number of new Failures: 0
    Number of fixed Failures: 0

    Tests ran in 1 seconds and used 13024.9414062 KB of memory

Now if you want to use the extension in your project you will need
register the extension with Doctrine and setup the extension autoloading
mechanism.

First lets setup the extension autoloading.

::

    // bootstrap.php

    // ...
    spl_autoload_register( array( 'Doctrine', 'extensionsAutoload' ) );

Now you can register the extension and the classes inside that extension
will be autoloaded.

::

    $manager->registerExtension( 'Sortable' );

.. note::

    If you need to register an extension from a different
    location you can specify the full path to the extension directory as
    the second argument to the ``registerExtension()`` method.