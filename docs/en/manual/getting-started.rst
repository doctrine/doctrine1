..  vim: set ts=4 sw=4 tw=79 :

***************
Getting Started
***************

=====================
Checking Requirements
=====================

First we need to make sure that you can run Doctrine on your server. We
can do this one of two ways:

First create a small PHP script named :php:meth:`phpinfo.php`` and upload it
somewhere on your web server that is accessible to the web:

::

    phpinfo();

Now execute it from your browser by going to
``http://localhost/phpinfo.php``. You will see a list of information
detailing your PHP configuration. Check that your PHP version is >=
**5.2.3** and that you have PDO and the desired drivers installed.

.. note::

    You can also check your PHP installation has the necessary
    requirements by running some commands from the terminal. We will
    demonstrate in the next example.

Check that your PHP version is >= 5.2.3 with the following command:

.. code-block:: sh

    php -v

Now check that you have PDO and the desired drivers installed with the
following command:

.. code-block:: sh

    php -i

You could also execute the ``phpinfo.php`` from the command line and get
the same result as the above example:

.. code-block:: sh

    php phpinfo.php

.. note::

    Checking the requirements are required in order to run the
    examples used throughout this documentation.

==========
Installing
==========

Currently it is possible to install Doctrine four different ways that
are listed below:

*  SVN (subversion)
*  SVN externals
*  PEAR Installer
*  Download PEAR Package

It is recommended to download Doctrine via SVN (subversion), because in
this case updating is easy. If your project is already under version
control with SVN, you should choose SVN externals.

.. tip::

    If you wish to just try out Doctrine in under 5 minutes, the
    sandbox package is recommended. We will discuss the sandbox package
    in the next section.

-------
Sandbox
-------

Doctrine also provides a special package which is a zero configuration
Doctrine implementation for you to test Doctrine without writing one
line of code. You can download it from the
`download page <http://www.doctrine-project.org/download>`_.

.. note::

    The sandbox implementation is not a recommend
    implementation for a production application. It's only purpose is
    for exploring Doctrine and running small tests.

---
SVN
---

It is highly recommended that you use Doctrine via SVN and the externals
option. This option is the best as you will receive the latest bug fixes
from SVN to ensure the best experience using Doctrine.

^^^^^^^^^^
Installing
^^^^^^^^^^

To install Doctrine via SVN is very easy. You can download any version
of Doctrine from the SVN server: ``http://svn.doctrine-project.org``

To check out a specific version you can use the following command from
your terminal:

.. code-block:: sh

    svn co http://svn.doctrine-project.org/branches/1.2 .

If you do not have a SVN client, chose one from the list below. Find the
Checkout option and enter http://svn.doctrine-project.org/branches/1.2
in the path or repository url parameter. There is no need for a username
or password to check out Doctrine.

*  `TortoiseSVN <http://tortoisesvn.tigris.org/>`_ a Windows application
   that integrates into Windows Explorer
*  `svnx <http://www.apple.com/downloads/macosx/development_tools/svnx.html>`_ a
   Mac OS X GUI svn application
*  `Eclipse <http://www.eclipse.org/>`_ has SVN integration through the
   `subeclipse plugin <http://subclipse.tigris.org/>`_
*  `Versions <http://versionsapp.com/>`_ a subversion client for the mac

^^^^^^^^
Updating
^^^^^^^^

Updating Doctrine with SVN is just as easy as installing. Simply execute
the following command from your terminal:

.. code-block:: sh

    svn update

-------------
SVN Externals
-------------

If your project is already under version control with SVN, then it is
recommended that you use SVN externals to install Doctrine.

You can start by navigating to your checked out project in your
terminal:

.. code-block:: sh

    cd /var/www/my_project

Now that you are under your checked out project, you can execute the
following command from your terminal and setup Doctrine as an SVN
external:

.. code-block:: sh

    svn propedit svn:externals lib/vendor

The above command will open your editor and you need to place the
following text inside and save:

.. code-block:: text

    doctrine http://svn.doctrine-project.org/branches/1.2/lib

Now you can install Doctrine by doing an svn update:

.. code-block:: sh

    svn update

It will download and install Doctrine at the following path:
``/var/www/my_project/lib/vendor/doctrine``

.. tip::

    Don't forget to commit your change to the SVN externals:

    .. code-block:: sh

        svn commit

--------------
PEAR Installer
--------------

Doctrine also provides a PEAR server for installing and updating
Doctrine on your servers. You can easily install Doctrine with the
following command:

.. code-block:: sh

    pear install pear.doctrine-project.org/Doctrine-1.2.x

.. note::

    Replace the above 1.2.x with the version you wish to
    install. For example "1.2.1".

---------------------
Download Pear Package
---------------------

If you do not wish to install via PEAR or do not have PEAR installed,
you can always just manually download the package from the
`website <http://www.doctrine-project.org/download>`_. Once you download
the package to your server you can extract it using the following
command under linux.

.. code-block:: sh

    tar xzf Doctrine-1.2.1.tgz

============
Implementing
============

Now that you have Doctrine in your hands, we are ready to implement
Doctrine in to our application. This is the first step towards getting
started with Doctrine.

First create a directory named ``doctrine_test``. This is where we will
place all our test code:

.. code-block:: sh

    mkdir doctrine_test
    cd doctrine_test

----------------------------
Including Doctrine Libraries
----------------------------

The first thing we must do is find the ``Doctrine.php`` file containing
the core class so that we can require it in to our application. The
``Doctrine.php`` file is in the lib folder from when you downloaded
Doctrine in the previous section.

We need to move the Doctrine libraries in to the ``doctrine_test``
directory into a folder in ``doctrine_test/lib/vendor/doctrine``:

.. code-block:: sh

    mkdir lib
    mkdir lib/vendor
    mkdir lib/vendor/doctrine
    mv /path/to/doctrine/lib doctrine

Or if you are using SVN, you can use externals:

.. code-block:: sh

    svn co http://svn.doctrine-project.org/branches/1.2/lib lib/vendor/doctrine

Now add it to your svn externals:

.. code-block:: sh

    svn propedit svn:externals lib/vendor

It will open up your editor and place the following inside and save:

.. code-block:: text

    doctrine http://svn.doctrine-project.org/branches/1.2/lib

Now when you do SVN update you will get the Doctrine libraries updated:

.. code-block:: sh

    svn update lib/vendor

---------------------------
Require Doctrine Base Class
---------------------------

We need to create a php script for bootstrapping Doctrine and all the
configuration for it. Create a file named ``bootstrap.php`` and place
the following code in the file::

    // bootstrap.php
    /* Bootstrap Doctrine.php, register autoloader specify
       configuration attributes and load models. */

    require_once(dirname(**FILE**) . '/lib/vendor/doctrine/Doctrine.php');

-------------------
Register Autoloader
-------------------

Now that we have the :php:class:`Doctrine` class present, we need to register the
class autoloader function in the bootstrap file::

    // bootstrap.php
    spl_autoload_register(array('Doctrine', 'autoload'));

Lets also create the singleton :php:class:`Doctrine_Manager` instance and assign
it to a variable named ``$manager``::

    // bootstrap.php
    $manager = Doctrine_Manager::getInstance();

^^^^^^^^^^^^^^^^^^^^^
Autoloading Explained
^^^^^^^^^^^^^^^^^^^^^

.. note::

    You can read about the PHP autoloading on the
    `php website <http://www.php.net/spl_autoload_register>`_. Using the
    autoloader allows us to lazily load classes as they are requested
    instead of pre-loading all classes. This is a huge benefit to
    performance.

The way the Doctrine autoloader works is simple. Because our class names
and paths are related, we can determine the path to a Doctrine class
based on its name.

Imagine we have a class named ``Doctrine_Some_Class`` and we
instantiate an instance of it::

    $class = new Doctrine_Some_Class();

The above code will trigger a call to the :php:meth:`Doctrine_Core::autoload`
function and pass it the name of the class instantiated. The class name
string is manipulated and transformed in to a path and required. Below
is some pseudo code that shows how the class is found and required::

    class Doctrine
    {
        public function autoload($className)
        {
            $classPath = str_replace('_', '/', $className) . '.php';
            $path = '/path/to/doctrine/' . $classPath;
            require_once($path);
            return true;
        }
    }

In the above example the :php:meth:`Doctrine\Some_Class` can be found at
``/path/to/doctrine/Doctrine/Some/Class.php``.

.. note::

    Obviously the real :php:meth:`Doctrine_Core::autoload` function
    is a bit more complex and has some error checking to ensure the file
    exists but the above code demonstrates how it works.

--------------
Bootstrap File
--------------

.. tip::

    We will use this bootstrap class in later chapters and sections so be sure
    to create it!

The bootstrap file we have created should now look like the following::

    // bootstrap.php
    /* Bootstrap Doctrine.php, register autoloader specify
       configuration attributes and load models. */

    require_once(dirname(**FILE**) . '/lib/vendor/doctrine/Doctrine.php');
    spl_autoload_register(array('Doctrine', 'autoload'));
    $manager = Doctrine_Manager::getInstance();

This new bootstrapping file will be referenced several times in this book as it
is where we will make changes to our implementation as we learn how to use
Doctrine step by step.

.. note::

    The configuration attributes mentioned above are a feature in Doctrine used
    for configuring and controlling functionality. You will learn more about
    attributes and how to get/set them in the [doc configuration :name]
    chapter.

-----------
Test Script
-----------

Now lets create a simple test script that we can use to run various tests as we
learn about the features of Doctrine.

Create a new file in the ``doctrine_test`` directory named ``test.php`` and
place the following code inside:

::

    // test.php
    require_once('bootstrap.php');
    echo Doctrine_Core::getPath();

Now you can execute the test script from your command line. This is how we will
perform tests with Doctrine throughout the chapters so make sure it is working
for you! It should output the path to your Doctrine installation.

.. code-block:: sh

    php test.php /path/to/doctrine/lib

==========
Conclusion
==========

Phew! This was our first chapter where we actually got into some code.  As you
saw, first we were able to check that our server can actually run Doctrine.
Then we learned all the different ways we can download and install Doctrine.
Lastly we learned how to implement Doctrine by setting up a small test
environment that we will use to perform some exercises in the remaining
chapters of the book.

Now lets move on and get our first taste of Doctrine connections in the
:doc:`introduction-to-connections` chapter.
