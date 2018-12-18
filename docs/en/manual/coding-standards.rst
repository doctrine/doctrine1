.. vim: set ft=rst tw=4 sw=4 et :

****************
Coding Standards
****************

===================
PHP File Formatting
===================

-------
General
-------

For files that contain only PHP code, the closing tag ("``?>``") is
never permitted. It is not required by PHP. Not including it prevents
trailing whitespace from being accidentally injected into the output.

.. note::

    Inclusion of arbitrary binary data as permitted by
    ``__HALT_COMPILER()`` is prohibited from any Doctrine framework
    PHP file or files derived from them. Use of this feature is only
    permitted for special installation scripts.

-----------
Indentation
-----------

Use an indent of 4 spaces, with no tabs.

-------------------
Maximum Line Length
-------------------

The target line length is 80 characters, i.e. developers should aim keep
code as close to the 80-column boundary as is practical. However, longer
lines are acceptable. The maximum length of any line of PHP code is 120
characters.

----------------
Line Termination
----------------

Line termination is the standard way for Unix text files to represent
the end of a line. Lines must end only with a linefeed (LF). Linefeeds
are represented as ordinal 10, or hexadecimal 0x0A.

You should not use carriage returns (CR) like Macintosh computers (0x0D)
and do not use the carriage return/linefeed combination (CRLF) as
Windows computers (0x0D, 0x0A).

==================
Naming Conventions
==================

-------
Classes
-------

The Doctrine ORM Framework uses the same class naming convention as PEAR
and Zend framework, where the names of the classes directly map to the
directories in which they are stored. The root level directory of the
Doctrine Framework is the "Doctrine/" directory, under which all classes
are stored hierarchially.

Class names may only contain alphanumeric characters. Numbers are
permitted in class names but are discouraged. Underscores are only
permitted in place of the path separator, eg. the filename
"Doctrine/Table/Exception.php" must map to the class name
":php:class:`Doctrine_Table_Exception`".

If a class name is comprised of more than one word, the first letter of
each new word must be capitalized. Successive capitalized letters are
not allowed, e.g. a class "XML\_Reader" is not allowed while
"Xml_Reader" is acceptable.

----------
Interfaces
----------

Interface classes must follow the same conventions as other classes (see
above).

They must also end with the word "Interface" (unless the interface is
approved not to contain it such as :php:class:`Doctrine_Overloadable`). Some
examples:

**Examples**

-  :php:class:`Doctrine_Adapter_Interface`
-  :php:class:`Doctrine_EventListener_Interface`

---------
Filenames
---------

For all other files, only alphanumeric characters, underscores, and the
dash character ("-") are permitted. Spaces are prohibited.

Any file that contains any PHP code must end with the extension ".php".
These examples show the acceptable filenames for containing the class
names from the examples in the section above:

-  ``Doctrine/Adapter/Interface.php``
-  ``Doctrine/EventListener/Interface``

File names must follow the mapping to class names described above.

---------------------
Functions and Methods
---------------------

Function names may only contain alphanumeric characters and underscores
are not permitted. Numbers are permitted in function names but are
highly discouraged. They must always start with a lowercase letter and
when a function name consists of more than one word, the first letter of
each new word must be capitalized. This is commonly called the
"studlyCaps" or "camelCaps" method. Verbosity is encouraged and function
names should be as verbose as is practical to enhance the
understandability of code.

For object-oriented programming, accessors for objects should always be
prefixed with either "get" or "set". This applies to all classes except
for :php:class:`Doctrine_Record` which has some accessor methods prefixed with
'obtain' and 'assign'. The reason for this is that since all user
defined ActiveRecords inherit :php:class:`Doctrine_Record`, it should populate
the get / set namespace as little as possible.

.. note::

    Functions in the global scope ("floating functions") are
    NOT permmitted. All static functions should be wrapped in a static
    class.

---------
Variables
---------

Variable names may only contain alphanumeric characters. Underscores are
not permitted. Numbers are permitted in variable names but are
discouraged. They must always start with a lowercase letter and follow
the "camelCaps" capitalization convention. Verbosity is encouraged.
Variables should always be as verbose as practical. Terse variable names
such as "$i" and "$n" are discouraged for anything other than
the smallest loop contexts. If a loop contains more than 20 lines of
code, the variables for the indices need to have more descriptive names.
Within the framework certain generic object variables should always use
the following names:

=======================  ==================
Object type              Variable name
=======================  ==================
``Doctrine_Connection``  $conn
``Doctrine_Collection``  $coll
``Doctrine_Manager``     $manager
``Doctrine_Query``       $q
=======================  ==================

There are cases when more descriptive names are more appropriate (for
example when multiple objects of the same class are used in same
context), in that case it is allowed to use different names than the
ones mentioned.

---------
Constants
---------

Constants may contain both alphanumeric characters and the underscore.
They must always have all letters capitalized. For readablity reasons,
words in constant names must be separated by underscore characters. For
example, ``ATTR_EXC_LOGGING`` is permitted but ``ATTR_EXCLOGGING`` is
not.Constants must be defined as class members by using the "const"
construct. Defining constants in the global scope with "define" is NOT
permitted.

::

    class Doctrine_SomeClass
    {
        const MY_CONSTANT = 'something';
    }

    echo $Doctrine_SomeClass::MY_CONSTANT;

--------------
Record Columns
--------------

All record columns must be in lowercase and usage of underscores(_) are
encouraged for columns that consist of more than one word.

::

    class User
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'home_address', 'string' );
        }
    }

Foreign key fields must be in format ``[table_name]_[column]``. The
next example is a field that is a foreign key that points to
``user(id)``:

::

    class Phonenumber extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'user_id', 'integer' );
        }
    }

============
Coding Style
============

--------------------
PHP Code Demarcation
--------------------

PHP code must always be delimited by the full-form, standard PHP tags
and short tags are never allowed. For files containing only PHP code,
the closing tag must always be omitted

-------
Strings
-------

When a string is literal (contains no variable substitutions), the
apostrophe or "single quote" must always used to demarcate the string:

Literal String
++++++++++++++

::

    $string = 'something';

When a literal string itself contains apostrophes, it is permitted to
demarcate the string with quotation marks or "double quotes". This is
especially encouraged for SQL statements:

String Containing Apostrophes
+++++++++++++++++++++++++++++

::

    $sql = "SELECT id, name FROM people WHERE name = 'Fred' OR name = 'Susan'";

Variable Substitution
+++++++++++++++++++++

Variable substitution is permitted using the following form:

::

    // variable substitution
    $greeting = "Hello $name, welcome back!";

String Concatenation
++++++++++++++++++++

Strings may be concatenated using the "." operator. A space must always
be added before and after the "." operator to improve readability:

::

    $framework = 'Doctrine' . ' ORM ' . 'Framework';

Concatenation Line Breaking
+++++++++++++++++++++++++++

When concatenating strings with the "." operator, it is permitted to
break the statement into multiple lines to improve readability. In these
cases, each successive line should be padded with whitespace such that
the "."; operator is aligned under the "=" operator:

::

    $sql = "SELECT id, name FROM user "
        . "WHERE name = ? "
        . "ORDER BY name ASC";

------
Arrays
------

Negative numbers are not permitted as indices and a indexed array may be
started with any non-negative number, however this is discouraged and it
is recommended that all arrays have a base index of 0. When declaring
indexed arrays with the array construct, a trailing space must be added
after each comma delimiter to improve readability. It is also permitted
to declare multiline indexed arrays using the "array" construct. In this
case, each successive line must be padded with spaces. When declaring
associative arrays with the array construct, it is encouraged to break
the statement into multiple lines. In this case, each successive line
must be padded with whitespace such that both the keys and the values
are aligned:

::

    $sampleArray = array( 'Doctrine', 'ORM', 1, 2, 3 );

    $sampleArray = array( 1, 2, 3,
                          $a, $b, $c,
                          56.44, $d, 500 );

    $sampleArray = array(
        'first'  => 'firstValue',
        'second' => 'secondValue'
    );

-------
Classes
-------

Classes must be named by following the naming conventions. The brace is
always written next line after the class name (or interface
declaration). Every class must have a documentation block that conforms
to the PHPDocumentor standard. Any code within a class must be indented
four spaces and only one class is permitted per PHP file. Placing
additional code in a class file is NOT permitted.

This is an example of an acceptable class declaration:

::

    /**
     * Documentation here
     */
    class Doctrine_SampleClass
    {
        // entire content of class
        // must be indented four spaces
    }

---------------------
Functions and Methods
---------------------

Methods must be named by following the naming conventions and must
always declare their visibility by using one of the private, protected,
or public constructs. Like classes, the brace is always written next
line after the method name. There is no space between the function name
and the opening parenthesis for the arguments. Functions in the global
scope are strongly discouraged. This is an example of an acceptable
function declaration in a class:

::

    /**
     * Documentation Block Here
     */
    class Foo
    {
        /**
         * Documentation Block Here
         */
        public function bar()
        {
            // entire content of function
            // must be indented four spaces
        }

        public function bar2()
        {

        }
    }

.. note::

    Functions must be separated by only ONE single new line
    like is done above between the ``bar()`` and ``bar2()`` methods.

Passing by-reference is permitted in the function declaration only:

::

    /**
     * Documentation Block Here
     */
    class Foo
    {
        /**
         * Documentation Block Here
         */
        public function bar( &$baz )
        {

        }
    }

Call-time pass by-reference is prohibited. The return value must not be
enclosed in parentheses. This can hinder readability and can also break
code if a method is later changed to return by reference.

::

    /**
     * Documentation Block Here
     */
    class Foo
    {
        /**
         * WRONG
         */
        public function bar()
        {
            return( $this->bar );
        }

        /**
         * RIGHT
         */
        public function bar()
        {
            return $this->bar;
        }
    }

Function arguments are separated by a single trailing space after the
comma delimiter. This is an example of an acceptable function call for a
function that takes three arguments:

::

    threeArguments( 1, 2, 3 );

Call-time pass by-reference is prohibited. See above for the proper way
to pass function arguments by-reference. For functions whose arguments
permitted arrays, the function call may include the array construct and
can be split into multiple lines to improve readability. In these cases,
the standards for writing arrays still apply:

::

    threeArguments( array( 1, 2, 3 ), 2, 3 );

    threeArguments( array( 1, 2, 3, 'Framework',
                           'Doctrine', 56.44, 500 ), 2, 3 );

------------------
Control Statements
------------------

Control statements based on the if and elseif constructs must have a
single space before the opening parenthesis of the conditional, and a
single space after the closing parenthesis. Within the conditional
statements between the parentheses, operators must be separated by
spaces for readability. Inner parentheses are encouraged to improve
logical grouping of larger conditionals. The opening brace is written on
the same line as the conditional statement. The closing brace is always
written on its own line. Any content within the braces must be indented
four spaces.

::

    if ( $foo != 2 )
    {
        $foo = 2;
    }

For if statements that include elseif or else, the formatting must be as
in these examples:

::

    if ( $foo != 1 )
    {
        $foo = 1;
    }
    else
    {
        $foo = 3;
    }

    if ( $foo != 2 )
    {
        $foo = 2;
    }
    elseif ( $foo == 1 )
    {
        $foo = 3;
    }
    else
    {
        $foo = 11;
    }

When ! operand is being used it must use the following formatting:

::

    if ( ! $foo )
    {

    }

Control statements written with the switch construct must have a single
space before the opening parenthesis of the conditional statement, and
also a single space after the closing parenthesis. All content within
the switch statement must be indented four spaces. Content under each
case statement must be indented an additional four spaces but the breaks
must be at the same indentation level as the case statements.

::

    switch ( $case )
    {
        case 1:
        case 2:
        break;
        case 3:
        break;
        default:
        break;
    }

The construct default may never be omitted from a switch statement.

--------------------
Inline Documentation
--------------------

Documentation Format:

All documentation blocks ("docblocks") must be compatible with the
phpDocumentor format. Describing the phpDocumentor format is beyond the
scope of this document. For more information, visit: `http://phpdoc.org/ <http://phpdoc.org/>`

Every method, must have a docblock that contains at a minimum:

-  A description of the function
-  All of the arguments
-  All of the possible return values
-  It is not necessary to use the @access tag because the access level
   is already known from the public, private, or protected construct
   used to declare the function.

If a function/method may throw an exception, use @throws:

::

    /*
     * Test function
     *
     * @throws Doctrine_Exception
     */
    public function test()
    {
        throw new Doctrine_Exception('This function did not work');
    }

==========
Conclusion
==========

This is the last chapter of **Doctrine ORM for PHP - Guide to Doctrine
for PHP**. I really hope that this book was a useful piece of
documentation and that you are now comfortable with using Doctrine and
will be able to come back to easily reference things as needed.

As always, follow the Doctrine :)

Thanks, Jon