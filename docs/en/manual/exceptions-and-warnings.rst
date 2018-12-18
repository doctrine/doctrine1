***********************
Exceptions and Warnings
***********************

==================
Manager exceptions
==================

:php:class:`Doctrine_Manager_Exception` is thrown if something failed at the
connection management

::

    try
    {
        $manager->getConnection( 'unknown' );
    }
    catch ( Doctrine_Manager_Exception )
    {
        // catch errors
    }

===================
Relation exceptions
===================

Relation exceptions are being thrown if something failed during the
relation parsing.

=====================
Connection exceptions
=====================

Connection exceptions are being thrown if something failed at the
database level. Doctrine offers fully portable database error handling.
This means that whether you are using sqlite or some other database you
can always get portable error code and message for the occurred error.

::

    try
    {
        $conn->execute( 'SELECT * FROM unknowntable' );
    }
    catch ( Doctrine_Connection_Exception $e )
    {
        echo 'Code : ' . $e->getPortableCode();
        echo 'Message : ' . $e->getPortableMessage();
    }

================
Query exceptions
================

An exception will be thrown when a query is executed if the DQL query is
invalid in some way.

==========
Conclusion
==========

Now that you know how to deal with Doctrine throwing exceptions lets
move on and show you some :doc:`real world schemas <real-world-examples>`
that would be used in common web applications found today on the web.