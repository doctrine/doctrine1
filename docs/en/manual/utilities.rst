*********
Utilities
*********

==========
Pagination
==========

------------
Introduction
------------

In real world applications, display content from database tables is a
commom task. Also, imagine that this content is a search result
containing thousands of items. Undoubtely, it will be a huge listing,
memory expensive and hard for users to find the right item. That is
where some organization of this content display is needed and pagination
comes in rescue.

Doctrine implements a highly flexible pager package, allowing you to not
only split listing in pages, but also enabling you to control the layout
of page links. In this chapter, we'll learn how to create pager objects,
control pager styles and at the end, overview the pager layout object -
a powerful page links displayer of Doctrine.

------------------
Working with Pager
------------------

Paginating queries is as simple as effectively do the queries itself.
:php:class:`Doctrine_Pager` is the responsible to process queries and paginate
them. Check out this small piece of code:

::

    // Defining initial variables
    $currentPage    = 1;
    $resultsPerPage = 50;

    // Creating pager object
    $pager = new Doctrine_Pager(
        Doctrine_Query::create()
            ->from( 'User u' )
            ->leftJoin( 'u.Group g' )
            ->orderby( 'u.username ASC' ),
        $currentPage, // Current page of request
        $resultsPerPage, // (Optional) Number of results per page. Default is 25
    );

Until this place, the source you have is the same as the old
:php:class:`Doctrine_Query` object. The only difference is that now you have 2
new arguments. Your old query object plus these 2 arguments are now
encapsulated by the :php:class:`Doctrine_Pager` object. At this stage,
:php:class:`Doctrine_Pager` defines the basic data needed to control pagination.
If you want to know that actual status of the pager, all you have to do
is to check if it's already executed:

::

    $pager->getExecuted();

If you try to access any of the methods provided by :php:class:`Doctrine_Pager`
now, you'll experience :php:class:`Doctrine_Pager_Exception` thrown, reporting
you that Pager was not yet executed. When executed, :php:class:`Doctrine_Pager`
offer you powerful methods to retrieve information. The API usage is
listed at the end of this topic.

To run the query, the process is similar to the current existent
:php:class:`Doctrine_Query` execute call. It even allow arguments the way you
usually do it. Here is the PHP complete syntax, including the syntax of
optional parameters:

::

    $items = $pager->execute( [$args = array() [, $fetchType = null]] );

    foreach ( $items as $item )
    {
        // ...
    }

There are some special cases where the return records query differ of
the counter query. To allow this situation, :php:class:`Doctrine_Pager` has some
methods that enable you to count and then to execute. The first thing
you have to do is to define the count query:

::

    $pager->setCountQuery( $query [, $params = null] );

    // ...
    $rs = $pager->execute();

The first param of ``setCountQuery`` can be either a valid
:php:class:`Doctrine_Query` object or a DQL string. The second argument you can
define the optional parameters that may be sent in the counter query. If
you do not define the params now, you're still able to define it later
by calling the ``setCountQueryParams``:

::

    $pager->setCountQueryParams( [$params = array() [, $append = false]] );

This method accepts 2 parameters. The first one is the params to be sent
in count query and the second parameter is if the
``$params`` should be appended to the list or if it should override the list of count query parameters.
The default behavior is to override the list. One last thing to mention about count query is, if you do not define any parameter for count query,
it will still send the parameters you define in ``$pager->execute()``
call.

Count query is always enabled to be accessed. If you do not define it
and call ``$pager->getCountQuery()``, it will return the "fetcher" query
to you.

If you need access the other functionalities that :php:class:`Doctrine_Pager`
provides, you can access them through the API:

::

    // Returns the check if Pager was already executed
    $pager->getExecuted();

    // Return the total number of itens found on query search
    $pager->getNumResults();

    // Return the first page (always 1)
    $pager->getFirstPage();

    // Return the total number of pages
    $pager->getLastPage();

    // Return the current page
    $pager->getPage();

    // Defines a new current page (need to call execute again to adjust offsets and values)
    $pager->setPage( $page );

    // Return the next page
    $pager->getNextPage();

    // Return the previous page
    $pager->getPreviousPage();

    // Return the first indice of current page
    $pager->getFirstIndice();

    // Return the last indice of current page
    $pager->getLastIndice();

    // Return true if it's necessary to paginate or false if not
    $pager->haveToPaginate();

    // Return the maximum number of records per page
    $pager->getMaxPerPage();

    // Defined a new maximum number of records per page (need to call execute again to adjust offset and values)
    $pager->setMaxPerPage( $maxPerPage );

    // Returns the number of itens in current page
    $pager->getResultsInPage();

    // Returns the Doctrine_Query object that is used to make the count results to pager
    $pager->getCountQuery();

    // Defines the counter query to be used by pager
    $pager->setCountQuery( $query, $params = null );

    // Returns the params to be used by counter Doctrine_Query (return $defaultParams if no param is defined)
    $pager->getCountQueryParams( $defaultParams = array() );

    // Defines the params to be used by counter Doctrine_Query
    $pager->setCountQueryParams( $params = array(), $append = false );

    // Return the Doctrine_Query object
    $pager->getQuery();

    // Return an associated Doctrine_Pager_Range_* instance
    $pager->getRange( $rangeStyle, $options = array() );

------------------------
Controlling Range Styles
------------------------

There are some cases where simple paginations are not enough. One
example situation is when you want to write page links listings. To
enable a more powerful control over pager, there is a small subset of
pager package that allows you to create ranges.

Currently, Doctrine implements two types (or styles) of ranges: Sliding
(:php:class:`Doctrine_Pager_Range_Sliding`) and Jumping
(:php:class:`Doctrine_Pager_Range_Jumping`).

^^^^^^^
Sliding
^^^^^^^

Sliding page range style, the page range moves smoothly with the current
page. The current page is always in the middle, except in the first and
last pages of the range. Check out how does it work with a chunk length
of 5 items:

::

    Listing 1 2 3 4 5 6 7 8 9 10 11 12 13 14
    Page 1: o-------|
    Page 2: |-o-----|
    Page 3: |---o---|
    Page 4:   |---o---|
    Page 5:     |---o---|
    Page 6:       |---o---|
    Page 7:         |---o---|
    Page 8:           |---o---|

^^^^^^^
Jumping
^^^^^^^

In Jumping page range style, the range of page links is always one of a
fixed set of "frames": 1-5, 6-10, 11-15, and so on.

::

    Listing 1 2 3 4 5 6 7 8 9 10 11 12 13 14
    Page 1: o-------|
    Page 2: |-o-----|
    Page 3: |---o---|
    Page 4: |-----o-|
    Page 5: |-------o
    Page 6:           o---------|
    Page 7:           |-o-------|
    Page 8:           |---o-----|

Now that we know how the different of styles of pager range works, it's
time to learn how to use them:

::

    $pagerRange = new Doctrine_Pager_Range_Sliding(
        array(
            'chunk' => 5 // Chunk length
        ),
        $pager // Doctrine_Pager object we learned how to create in previous topic
    );

Alternatively, you can use:

::

    $pagerRange = $pager->getRange(
        'Sliding',
        array(
            'chunk' => 5
        )
    );

What is the advantage to use this object, instead of the
:php:class:`Doctrine_Pager`? Just one; it allows you to retrieve ranges around
the current page.

Look at the example:

::

    // Retrieves the range around the current page
    // In our example, we are using sliding style and we are at page 1
    $pages = $pager_range->rangeAroundPage();

    // Outputs: [1][2][3][4][5]
    echo '['. implode( '][', $pages ) .']';

If you build your :php:class:`Doctrine_Pager` inside the range object, the API
gives you enough power to retrieve information related to
:php:class:`Doctrine_Pager_Range` subclass instance:

::

    // Return the Pager associated to this Pager_Range
    $pager_range->getPager();

    // Defines a new Doctrine_Pager (automatically call _initialize protected method)
    $pager_range->setPager( $pager );

    // Return the options assigned to the current Pager_Range
    $pager_range->getOptions();

    // Returns the custom Doctrine_Pager_Range implementation offset option
    $pager_range->getOption( $option );

    // Check if a given page is in the range
    $pager_range->isInRange( $page );

    // Return the range around the current page (obtained from Doctrine_Pager
    // associated to the $pager_range instance)
    $pager_range->rangeAroundPage();

---------------------------
Advanced layouts with pager
---------------------------

Until now, we learned how to create paginations and how to retrieve
ranges around the current page. To abstract the business logic involving
the page links generation, there is a powerful component called
:php:class:`Doctrine_Pager_Layout`. The main idea of this component is to
abstract php logic and only leave HTML to be defined by Doctrine
developer.

:php:class:`Doctrine_Pager_Layout` accepts 3 obrigatory arguments: a
:php:class:`Doctrine_Pager` instance, a :php:class:`Doctrine_Pager_Range` subclass
instance and a string which is the URL to be assigned as **{%url}** mask in
templates. As you may see, there are two types of "variables" in
:php:class:`Doctrine_Pager_Layout`:

^^^^
Mask
^^^^

A piece of string that is defined inside template as replacements. They
are defined as **{%mask_name}** and are replaced by what you define in
options or what is defined internally by :php:class:`Doctrine_Pager_Layout`
component. Currently, these are the internal masks available:

- **{%page}** Holds the page number, exactly as page_number, but can
  be overwritable by ``addMaskReplacement()`` to behavior like another
  mask or value
- **{%page_number}** Stores the current page number, but cannot be
  overwritable
- **{%url}** Available only in ``setTemplate()`` and
  ``setSelectedTemplate()`` methods. Holds the processed URL, which was
  defined in constructor

^^^^^^^^
Template
^^^^^^^^

As the name explains itself, it is the skeleton of HTML or any other
resource that is applied to each page returned by
``Doctrine_Pager_Range::rangeAroundPage()`` subclasses. There are 3
distinct templates that can be defined:

- ``setTemplate()`` Defines the template that can be used in all pages
  returned by ``Doctrine_Pager_Range::rangeAroundPage()`` subclass
  call
- ``setSelectedTemplate()`` Template that is applied when it is the
  page to be processed is the current page you are. If nothing is
  defined (a blank string or no definition), the template you defined
  in ``setTemplate()`` is used
- ``setSeparatorTemplate()`` Separator template is the string that is
  applied between each processed page. It is not included before the
  first call and after the last one. The defined template of this
  method is not affected by options and also it cannot process masks

Now we know how to create the :php:class:`Doctrine_Pager_Layout` and the types
that are around this component, it is time to view the basic usage:

Creating the pager layout is simple:

::

    $pagerLayout = new Doctrine_Pager_Layout(
        new Doctrine_Pager(
            Doctrine_Query::create()
                ->from( 'User u' )
                ->leftJoin( 'u.Group g' )
                ->orderby( 'u.username ASC' ),
            $currentPage,
            $resultsPerPage
        ),
        new Doctrine_Pager_Range_Sliding(
            array(
                'chunk' => 5
            )
        ),
        'http://wwww.domain.com/app/User/list/page,{%page_number}'
    );

Assigning templates for page links creation:

::

    $pagerLayout->setTemplate( '[{%page}]' );
    $pagerLayout->setSelectedTemplate( '[{%page}]' );

    // Retrieving Doctrine_Pager instance
    $pager = $pagerLayout->getPager();

    // Fetching users
    $users = $pager->execute(); // This is possible too!

    // Displaying page links
    // Displays: [1][2][3][4][5]
    // With links in all pages, except the $currentPage (our example, page 1)
    $pagerLayout->display();

Explaining this source, the first part creates the pager layout
instance. Second, it defines the templates for all pages and for the
current page. The last part, it retrieves the :php:class:`Doctrine_Pager` object
and executes the query, returning in variable ``$users``. The last part
calls the displar without any optional mask, which applies the template
in all pages found by ``Doctrine_Pager_Range::rangeAroundPage()``
subclass call.

As you may see, there is no need to use other masks except the internals
ones. Lets suppose we implement a new functionality to search for Users
in our existent application, and we need to support this feature in
pager layout too. To simplify our case, the search parameter is named
"search" and is received through ``$_GET`` superglobal array. The first
change we need to do is tho adjust the :php:class:`Doctrine_Query` object and
also the URL, to allow it to be sent to other pages.

Creating the pager layout:

::

    $pagerLayout = new Doctrine_Pager_Layout(
        new Doctrine_Pager(
            Doctrine_Query::create()
                ->from( 'User u' )
                ->leftJoin( 'u.Group g' )
                ->where( 'LOWER(u.username) LIKE LOWER(?)', array( '%' . $_GET['search'] . '%' ) )
                ->orderby( 'u.username ASC' ),
            $currentPage,
            $resultsPerPage
        ),
        new Doctrine_Pager_Range_Sliding(
            array(
                'chunk' => 5
            )
        ),
        'http://wwww.domain.com/app/User/list/page,{%page_number}?search={%search}'
    );

Check out the code and notice we added a new mask, called ``{%search}``.
We'll need to send it to the template processing at a later stage. We
then assign the templates, just as defined before, without any change.
And also, we do not need to change execution of query.

Assigning templates for page links creation:

::

    $pagerLayout->setTemplate( '[{%page}]' );
    $pagerLayout->setSelectedTemplate( '[{%page}]' );

    // Fetching users
    $users = $pagerLayout->execute();

    foreach ( $users as $user )
    {
        // ...
    }

The method ``display()`` is the place where we define the custom mask we
created. This method accepts 2 optional arguments: one array of optional
masks and if the output should be returned instead of printed on screen.
In our case, we need to define a new mask, the ``{%search}``, which is
the search offset of ``$_GET`` superglobal array. Also, remember that
since it'll be sent as URL, it needs to be encoded. Custom masks are
defined in key => value pairs. So all needed code is to define an array
with the offset we desire and the value to be replaced:

::

    // Displaying page links
    $pagerLayout->display(
        array(
            'search' => urlencode( $_GET['search'] )
        )
    );

:php:class:`Doctrine_Pager_Layout` component offers accessors to defined
resources. There is not need to define pager and pager range as
variables and send to the pager layout. These instances can be retrieved
by these accessors:

::

    // Return the Pager associated to the Pager_Layout
    $pagerLayout->getPager();

    // Return the Pager_Range associated to the Pager_Layout
    $pagerLayout->getPagerRange();

    // Return the URL mask associated to the Pager_Layout
    $pagerLayout->getUrlMask();

    // Return the template associated to the Pager_Layout
    $pagerLayout->getTemplate();

    // Return the current page template associated to the Pager_Layout
    $pagerLayout->getSelectedTemplate();

    // Defines the Separator template, applied between each page
    $pagerLayout->setSeparatorTemplate( $separatorTemplate );

    // Return the current page template associated to the Pager_Layout
    $pagerLayout->getSeparatorTemplate();

    // Handy method to execute the query without need to retrieve the Pager instance
    $pagerLayout->execute( $params = array(), $hydrationMode = null );

There are a couple of other methods that are available if you want to
extend the :php:class:`Doctrine_Pager_Layout` to create you custom layouter. We
will see these methods in the next section.

------------------------
Customizing pager layout
------------------------

:php:class:`Doctrine_Pager_Layout` does a really good job, but sometimes it is
not enough. Let's suppose a situation where you have to create a layout
of pagination like this one:

<< < 1 2 3 4 5 > >>

Currently, it is impossible with raw :php:class:`Doctrine_Pager_Layout`. But if
you extend it and use the available methods, you can achieve it. The
base Layout class provides you some methods that can be used to create
your own implementation. They are:

::

    // $this refers to an instance of Doctrine_Pager_Layout

    // Defines a mask replacement. When parsing template, it converts replacement
    // masks into new ones (or values), allowing to change masks behavior on the fly
    $this->addMaskReplacement( $oldMask, $newMask, $asValue = false );

    // Remove a mask replacement
    $this->removeMaskReplacement( $oldMask );

    // Remove all mask replacements
    $this->cleanMaskReplacements();

    // Parses the template and returns the string of a processed page
    $this->processPage( $options = array() ); // Needs at least page_number offset in $options array

    // Protected methods, although very useful

    // Parse the template of a given page and return the processed template
    $this->_parseTemplate( $options = array() );

    // Parse the url mask to return the correct template depending of the options sent
    // Already process the mask replacements assigned
    $this->_parseUrlTemplate( $options = array() );

    // Parse the mask replacements of a given page
    $this->_parseReplacementsTemplate( $options = array() );

    // Parse the url mask of a given page and return the processed url
    $this->_parseUrl( $options = array() );

    // Parse the mask replacements, changing from to-be replaced mask with new masks/values
    $this->_parseMaskReplacements( $str );

Now that you have a small tip of useful methods to be used when
extending :php:class:`Doctrine_Pager_Layout`, it's time to see our implemented
class:

::

    class PagerLayoutWithArrows extends Doctrine_Pager_Layout
    {
        public function display( $options = array(), $return = false )
        {
            $pager = $this->getPager();
            $str   = '';

            // First page
            $this->addMaskReplacement( 'page', '&laquo;', true );
            $options['page_number'] = $pager->getFirstPage();
            $str                   .= $this->processPage( $options );

            // Previous page
            $this->addMaskReplacement( 'page', '&lsaquo;', true );
            $options['page_number'] = $pager->getPreviousPage();
            $str                   .= $this->processPage( $options );

            // Pages listing
            $this->removeMaskReplacement( 'page' );
            $str .= parent::display( $options, true );

            // Next page
            $this->addMaskReplacement( 'page', '&rsaquo;', true );
            $options['page_number'] = $pager->getNextPage();
            $str                   .= $this->processPage( $options );

            // Last page
            $this->addMaskReplacement( 'page', '&raquo;', true );
            $options['page_number'] = $pager->getLastPage();
            $str                   .= $this->processPage( $options );

            // Possible wish to return value instead of print it on screen
            if ( $return )
            {
                return $str;
            }

            echo $str;
        }
    }

As you may see, I have to manual process the items <<, <, > and >>. I
override the **{%page}** mask by setting a raw value to it (raw value is
achieved by setting the third parameter as true). Then I define the only
MUST HAVE information to process the page and call it. The return is the
template processed as a string. I do it to any of my custom buttons.

Now supposing a totally different situation. Doctrine is framework
agnostic, but many of our users use it together with Symfony.
:php:class:`Doctrine_Pager` and subclasses are 100% compatible with Symfony, but
:php:class:`Doctrine_Pager_Layout` needs some tweaks to get it working with
Symfony's ``link_to`` helper function. To allow this usage with
:php:class:`Doctrine_Pager_Layout`, you have to extend it and add your custom
processor over it. For example purpose (it works in Symfony), I used
**{link_to}...{/link_to}** as a template processor to do this job.
Here is the extended class and usage in Symfony:

::

    class sfDoctrinePagerLayout extends Doctrine_Pager_Layout
    {
        public function __construct( $pager, $pagerRange, $urlMask )
        {
            sfLoader::loadHelpers( array( 'Url', 'Tag' ) );
            parent::__construct( $pager, $pagerRange, $urlMask );
        }

        protected function _parseTemplate( $options = array() )
        {
            $str = parent::_parseTemplate( $options );

            return preg_replace(
                '/\{link_to\}(.*?)\{\/link_to\}/', link_to( '$1', $this->_parseUrl( $options ) ), $str
            );
    }

}

Usage:

::

    $pagerLayout = new sfDoctrinePagerLayout(
        $pager,
        new Doctrine_Pager_Range_Sliding(
            array(
                'chunk' => 5
            )
        ),
        '@hostHistoryList?page={%page_number}'
    );

    $pagerLayout->setTemplate( '[{link_to}{%page}{/link_to}]' );

======
Facade
======

-----------------------------
Creating & Dropping Databases
-----------------------------

Doctrine offers the ability to create and drop your databases from your
defined Doctrine connections. The only trick to using it is that the
name of your Doctrine connection must be the name of your database. This
is required due to the fact that PDO does not offer a method for
retrieving the name of the database you are connected to. So in order to
create and drop the database Doctrine itself must be aware of the name
of the database.

-------------------
Convenience Methods
-------------------

Doctrine offers static convenience methods available in the main
Doctrine class. These methods perform some of the most used
functionality of Doctrine with one method. Most of these methods are
using in the :php:class:`Doctrine_Task` system. These tasks are also what are
executed from the :php:class:`Doctrine_Cli`.

::

    // Turn debug on/off and check for whether it is on/off
    Doctrine_Core::debug( true );

    if ( Doctrine_Core::debug() )
    {
        echo 'debugging is on';
    }
    else
    {
        echo 'debugging is off';
    }

    // Get the path to your Doctrine libraries
    $path = Doctrine_Core::getPath();

    // Set the path to your Doctrine libraries if it is some non-default location
    Doctrine_Core::setPath( '/path/to/doctrine/libs' );

    // Load your models so that they are present and loaded for Doctrine to work with
    // Returns an array of the Doctrine_Records that were found and loaded
    $models = Doctrine_Core::loadModels( '/path/to/models', Doctrine_Core::MODEL_LOADING_CONSERVATIVE );
    // or Doctrine_Core::MODEL_LOADING_AGGRESSIVE
    print_r( $models );

    // Get array of all the models loaded and present to Doctrine
    $models = Doctrine_Core::getLoadedModels();

    // Pass an array of classes to the above method and it will filter out the ones that are not Doctrine_Records
    $models = Doctrine_Core::filterInvalidModels( array( 'User', 'Formatter', 'Doctrine_Record' ) );
    print_r( $models ); // would return array( 'User' ) because Formatter and Doctrine_Record are not valid

    // Get Doctrine_Connection object for an actual table name
    $conn = Doctrine_Core::getConnectionByTableName( 'user' ); // returns the connection object that the table name is associated with.

    // Generate YAML schema from an existing database
    Doctrine_Core::generateYamlFromDb( '/path/to/dump/schema.yml', array( 'connection_name' ), $options );

    // Generate your models from an existing database
    Doctrine_Core::generateModelsFromDb( '/path/to/generate/models', array( 'connection_name' ), $options );

    // Array of options and the default values
    $options = array(
        'packagesPrefix'       => 'Package',
        'packagesPath'         => '',
        'packagesFolderName'   => 'packages',
        'suffix'               => '.php',
        'generateBaseClasses'  => true,
        'baseClassesPrefix'    => 'Base',
        'baseClassesDirectory' => 'generated',
        'baseClassName'        => 'Doctrine_Record'
    );

    // Generate your models from YAML schema
    Doctrine_Core::generateModelsFromYaml( '/path/to/schema.yml', '/path/to/generate/models', $options );

    // Create the tables supplied in the array
    Doctrine_Core::createTablesFromArray( array( 'User', 'Phoneumber' ) );

    // Create all your tables from an existing set of models
    // Will generate sql for all loaded models if no directory is given
    Doctrine_Core::createTablesFromModels( '/path/to/models' );

    // Generate string of sql commands from an existing set of models
    // Will generate sql for all loaded models if no directory is given
    Doctrine_Core::generateSqlFromModels( '/path/to/models' );

    // Generate array of sql statements to create the array of passed models
    Doctrine_Core::generateSqlFromArray( array( 'User', 'Phonenumber' ) );

    // Generate YAML schema from an existing set of models
    Doctrine_Core::generateYamlFromModels( '/path/to/schema.yml', '/path/to/models' );

    // Create all databases for connections.
    // Array of connection names is optional
    Doctrine_Core::createDatabases( array( 'connection_name' ) );

    // Drop all databases for connections
    // Array of connection names is optional
    Doctrine_Core::dropDatabases( array( 'connection_name' ) );

    // Dump all data for your models to a yaml fixtures file
    // 2nd argument is a bool value for whether or not to generate individual fixture files for each model. If true you need
    // to specify a folder instead of a file.
    Doctrine_Core::dumpData( '/path/to/dump/data.yml', true );

    // Load data from yaml fixtures files
    // 2nd argument is a bool value for whether or not to append the data when loading or delete all data first before loading
    Doctrine_Core::loadData( '/path/to/fixture/files', true );

    // Run a migration process for a set of migration classes
    $num = 5; // migrate to version #5
    Doctrine_Core::migration( '/path/to/migrations', $num );

    // Generate a blank migration class template
    Doctrine_Core::generateMigrationClass( 'ClassName', '/path/to/migrations' );

    // Generate all migration classes for an existing database
    Doctrine_Core::generateMigrationsFromDb( '/path/to/migrations' );

    // Generate all migration classes for an existing set of models
    // 2nd argument is optional if you have already loaded your models using loadModels()
    Doctrine_Core::generateMigrationsFromModels( '/path/to/migrations', '/path/to/models' );

    // Get Doctrine_Table instance for a model
    $userTable = Doctrine_Core::getTable( 'User' );

    // Compile doctrine in to a single php file
    $drivers = array( 'mysql' ); // specify the array of drivers you want to include in this compiled version
    Doctrine_Core::compile( '/path/to/write/compiled/doctrine', $drivers );

    // Dump doctrine objects for debugging
    $conn = Doctrine_Manager::connection();
    Doctrine_Core::dump( $conn );

-----
Tasks
-----

Tasks are classes which bundle some of the core convenience methods in
to tasks that can be easily executed by setting the required arguments.
These tasks are directly used in the Doctrine command line interface.

::

    BuildAll
    BuildAllLoad
    BuildAllReload
    Compile
    CreateDb
    CreateTables
    Dql
    DropDb
    DumpData
    Exception
    GenerateMigration
    GenerateMigrationsDb
    GenerateMigrationsModels
    GenerateModelsDb
    GenerateModelsYaml
    GenerateSql
    GenerateYamlDb
    GenerateYamlModels
    LoadData
    Migrate
    RebuildDb

You can read below about how to execute Doctrine Tasks standalone in
your own scripts.

======================
Command Line Interface
======================

--------------------------------------------------------------------
Introduction The Doctrine Cli is a collection of tasks that help you
--------------------------------------------------------------------
with your day to do development and testing with your Doctrine
implementation. Typically with the examples in this manual, you setup
php scripts to perform whatever tasks you may need. This Cli tool is
aimed at providing an out of the box solution for those tasks.

-----
Tasks
-----

Below is a list of available tasks for managing your Doctrine
implementation.

.. code-block:: sh

    $ ./doctrine
    Doctrine Command Line Interface
    ./doctrine build-all
    ./doctrine build-all-load
    ./doctrine build-all-reload
    ./doctrine compile
    ./doctrine create-db
    ./doctrine create-tables
    ./doctrine dql
    ./doctrine drop-db
    ./doctrine dump-data
    ./doctrine generate-migration
    ./doctrine generate-migrations-db
    ./doctrine generate-migrations-models
    ./doctrine generate-models-db
    ./doctrine generate-models-yaml
    ./doctrine generate-sql
    ./doctrine generate-yaml-db
    ./doctrine generate-yaml-models
    ./doctrine load-data
    ./doctrine migrate
    ./doctrine rebuild-db

The tasks for the CLI are separate from the CLI and can be used
standalone. Below is an example.

::

    $task = new Doctrine_Task_GenerateModelsFromYaml();
    $args = array(
        'yaml_schema_path' => '/path/to/schema',
        'models_path'      => '/path/to/models'
    );

    $task->setArguments( $args );

    try
    {
        if ( $task->validate() )
        {
            $task->execute();
        }
    }
    catch ( Exception $e )
    {
        throw new Doctrine_Exception( $e->getMessage() );
    }

-----
Usage
-----

File named "doctrine" that is set to executable

::

    #!/usr/bin/env php
    [php]

    chdir( dirname( __FILE__ ) );
    include( 'doctrine.php' );

Actual php file named "doctrine.php" that implements the :php:class:`Doctrine_Cli`.

::

    // Include your Doctrine configuration/setup here, your connections, models, etc.

    // Configure Doctrine Cli
    // Normally these are arguments to the cli tasks but if they are set here the arguments will be auto-filled and are not required for you to enter them.

    $config = array(
        'data_fixtures_path' => '/path/to/data/fixtures',
        'models_path'        => '/path/to/models',
        'migrations_path'    => '/path/to/migrations',
        'sql_path'           => '/path/to/data/sql',
        'yaml_schema_path'   => '/path/to/schema'
    );

    $cli = new Doctrine_Cli( $config );
    $cli->run( $_SERVER['argv'] );

Now you can begin executing commands.

::

    ./doctrine generate-models-yaml
    ./doctrine create-tables

=======
Sandbox
=======

------------
Installation
------------

You can install the sandbox by downloading the special sandbox package
from `http://www.doctrine-project.org/download <http://www.doctrine-project.org/download>`_ or you can install it via
svn below.

::

    svn co http://www.doctrine-project.org/svn/branches/0.11 doctrine
    cd doctrine/tools/sandbox
    chmod 0777 doctrine

    ./doctrine

The above steps should give you a functioning sandbox. Execute the
./doctrine command without specifying a task will show you an index of
all the available cli tasks in Doctrine.

==========
Conclusion
==========

I hope some of these utilities discussed in this chapter are of use to
you. Now lets discuss how Doctrine maintains stability and avoids
regressions by using :doc:`unit-testing`.