***************
Event Listeners
***************

============
Introduction
============

Doctrine provides flexible event listener architecture that not only
allows listening for different events but also for altering the
execution of the listened methods.

There are several different listeners and hooks for various Doctrine
components. Listeners are separate classes whereas hooks are empty
template methods within the listened class.

Hooks are simpler than event listeners but they lack the separation of
different aspects. An example of using :php:class:`Doctrine_Record` hooks:

::

    // models/BlogPost.php

    class BlogPost extends Doctrine_Record
    {
        // ...

        public function preInsert($event)
        {
            $invoker = $event->getInvoker();

            $invoker->created = date('Y-m-d', time());
        }
    }

.. note::

    By now we have defined lots of models so you should be able
    to define your own ``setTableDefinition()`` for the ``BlogPost``
    model or even create your own custom model!

Now you can use the above model with the following code assuming we
added a ``title``, ``body`` and ``created`` column to the model:

::

    // test.php

    // ...
    $blog        = new BlogPost();
    $blog->title = 'New title';
    $blog->body  = 'Some content';
    $blog->save();

    echo $blog->created;

The above example will output the current date as PHP knows it.

Each listener and hook method takes one parameter :php:class:`Doctrine_Event`
object. :php:class:`Doctrine_Event` object holds information about the event in
question and can alter the execution of the listened method.

For the purposes of this documentation many method tables are provided
with column named ``params`` indicating names of the parameters that an
event object holds on given event. For example the
``preCreateSavepoint`` event has one parameter with the name of the
created ``savepoint``, which is quite intuitively named as
``savepoint``.

====================
Connection Listeners
====================

Connection listeners are used for listening the methods of
:php:class:`Doctrine_Connection` and its modules (such as
:php:class:`Doctrine_Transaction`). All listener methods take one argument
:php:class:`Doctrine_Event` which holds information about the listened event.

-----------------------
Creating a New Listener
-----------------------

There are three different ways of defining a listener. First you can
create a listener by making a class that inherits :php:class:`Doctrine_EventListener`:

::

    class MyListener extends Doctrine_EventListener
    {
        public function preExec( Doctrine_Event $event )
        {

        }
    }

Note that by declaring a class that extends :php:class:`Doctrine_EventListener`
you don't have to define all the methods within the
:php:class:`Doctrine_EventListener_Interface`. This is due to a fact that
:php:class:`Doctrine_EventListener` already has empty skeletons for all these
methods.

Sometimes it may not be possible to define a listener that extends
:php:class:`Doctrine_EventListener` (you might have a listener that inherits
some other base class). In this case you can make it implement
:php:class:`Doctrine_EventListener_Interface`.

::

    class MyListener implements Doctrine_EventListener_Interface
    {
        public function preTransactionCommit( Doctrine_Event $event ) {}
        public function postTransactionCommit( Doctrine_Event $event ) {}

        public function preTransactionRollback( Doctrine_Event $event ) {}
        public function postTransactionRollback( Doctrine_Event $event ) {}

        public function preTransactionBegin( Doctrine_Event $event ) {}
        public function postTransactionBegin( Doctrine_Event $event ) {}

        public function postConnect( Doctrine_Event $event ) {}
        public function preConnect( Doctrine_Event $event ) {}

        public function preQuery( Doctrine_Event $event ) {}
        public function postQuery( Doctrine_Event $event ) {}

        public function prePrepare( Doctrine_Event $event ) {}
        public function postPrepare( Doctrine_Event $event ) {}

        public function preExec( Doctrine_Event $event ) {}
        public function postExec( Doctrine_Event $event ) {}

        public function preError( Doctrine_Event $event ) {}
        public function postError( Doctrine_Event $event ) {}

        public function preFetch( Doctrine_Event $event ) {}
        public function postFetch( Doctrine_Event $event ) {}

        public function preFetchAll( Doctrine_Event $event ) {}
        public function postFetchAll( Doctrine_Event $event ) {}

        public function preStmtExecute( Doctrine_Event $event ) {}
        public function postStmtExecute( Doctrine_Event $event ) {}
    }

.. caution::

    All listener methods must be defined here otherwise PHP throws fatal error.

The third way of creating a listener is a very elegant one. You can make
a class that implements :php:class:`Doctrine_Overloadable`. This interface has
only one method: ``__call()``, which can be used for catching *all*
the events.

::

    class MyDebugger implements Doctrine_Overloadable
    {
        public function __call( $methodName, $args )
        {
            echo $methodName . ' called !';
        }
    }

-------------------
Attaching listeners
-------------------

You can attach the listeners to a connection with ``setListener()``.

::

    $conn->setListener( new MyDebugger() );

If you need to use multiple listeners you can use ``addListener()``.

::

    $conn->addListener( new MyDebugger() );
    $conn->addListener( new MyLogger() );

--------------------
Pre and Post Connect
--------------------

All of the below listeners are invoked in the :php:class:`Doctrine_Connection`
class. And they are all passed an instance of :php:class:`Doctrine_Event`.

=================  ================  ==============
Methods            Listens           Params
=================  ================  ==============
``preConnect()``   ``connection()``
``postConnect()``  ``connection()``
=================  ================  ==============

---------------------
Transaction Listeners
---------------------

All of the below listeners are invoked in the :php:class:`Doctrine_Transaction`
class. And they are all passed an instance of :php:class:`Doctrine_Event`.

=============================  =======================  =============
Methods                        Listens                  Params
=============================  =======================  =============
``preTransactionBegin()``      ``beginTransaction()``
``postTransactionBegin()``     ``beginTransaction()``
``preTransactionRollback()``   ``rollback()``
``postTransactionRollback()``  ``rollback()``
``preTransactionCommit()``     ``commit()``
``postTransactionCommit()``    ``commit()``
``preCreateSavepoint()``       ``createSavepoint()``    ``savepoint``
``postCreateSavepoint()``      ``createSavepoint()``    ``savepoint``
``preRollbackSavepoint()``     ``rollbackSavepoint()``  ``savepoint``
``postRollbackSavepoint()``    ``rollbackSavepoint()``  ``savepoint``
``preReleaseSavepoint()``      ``releaseSavepoint()``   ``savepoint``
``postReleaseSavepoint()``     ``releaseSavepoint()``   ``savepoint``
=============================  =======================  =============

::

    class MyTransactionListener extends Doctrine_EventListener
    {
        public function preTransactionBegin( Doctrine_Event $event )
        {
            echo 'beginning transaction... ';
        }

        public function preTransactionRollback( Doctrine_Event $event )
        {
            echo 'rolling back transaction... ';
        }
    }

-------------------------
Query Execution Listeners
-------------------------

All of the below listeners are invoked in the :php:class:`Doctrine_Connection`
and :php:class:`Doctrine_Connection_Statement` classes. And they are all passed
an instance of :php:class:`Doctrine_Event`.

=====================  ===================  ===============
Methods                Listens              Params
=====================  ===================  ===============
``prePrepare()``       ``prepare()``        ``query``
``postPrepare()``      ``prepare()``        ``query``
``preExec()``          ``exec()``           ``query``
``postExec()``         ``exec()``           ``query,rows``
``preStmtExecute()``   ``execute()``        ``query``
``postStmtExecute()``  ``execute()``        ``query``
``preExecute()``       ``execute()`` *****  ``query``
``postExecute()``      ``execute()`` *****  ``query``
``preFetch()``         ``fetch()``          ``query, data``
``postFetch()``        ``fetch()``          ``query, data``
``preFetchAll()``      ``fetchAll()``       ``query, data``
``postFetchAll()``     ``fetchAll()``       ``query, data``
=====================  ===================  ===============

.. note::

    ``preExecute()`` and ``postExecute()`` only get invoked
    when ``Doctrine_Connection::execute()`` is being called without
    prepared statement parameters. Otherwise
    ``Doctrine_Connection::execute()`` invokes ``prePrepare()``,
    ``postPrepare()``, ``preStmtExecute()`` and ``postStmtExecute()``.

===================
Hydration Listeners
===================

The hydration listeners can be used for listening to resultset hydration
procedures. Two methods exist for listening to the hydration procedure:
``preHydrate()`` and ``postHydrate()``.

If you set the hydration listener on connection level the code within
the ``preHydrate()`` and ``postHydrate()`` blocks will be invoked by all
components within a multi-component resultset. However if you add a
similar listener on table level it only gets invoked when the data of
that table is being hydrated.

Consider we have a class called ``User`` with the following fields:
``first_name``, ``last_name`` and ``age``. In the following example we
create a listener that always builds a generated field called
``full_name`` based on ``first_name`` and ``last_name`` fields.

::

    // test.php

    // ...
    class HydrationListener extends Doctrine_Record_Listener
    {
        public function preHydrate( Doctrine_Event $event )
        {
            $data              = $event->data;
            $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $event->data       = $data;
        }
    }

Now all we need to do is attach this listener to the ``User`` record and
fetch some users:

::

    // test.php

    // ...
    $userTable = Doctrine_Core::getTable('User');
    $userTable->addRecordListener( new HydrationListener() );

    $q = Doctrine_Query::create()
        ->from('User');

    $users = $q->execute();

    foreach ( $users as $user )
    {
        echo $user->full_name;
    }

================
Record Listeners
================

:php:class:`Doctrine_Record` provides listeners very similar to
:php:class:`Doctrine_Connection`. You can set the listeners at global,
connection and table level.

Here is a list of all available listener methods:

All of the below listeners are invoked in the :php:class:`Doctrine_Record` and
:php:class:`Doctrine_Validator` classes. And they are all passed an instance of
:php:class:`Doctrine_Event`.

==================  ===============================================
Methods             Listens
==================  ===============================================
``preSave()``       ``save()``
``postSave()``      ``save()``
``preUpdate()``     ``save()`` **when the record state is** ``DIRTY``
``postUpdate()``    ``save()`` **when the record state is** ``DIRTY``
``preInsert()``     ``save()`` **when the record state is** ``TDIRTY``
``postInsert()``    ``save()`` **when the record state is** ``TDIRTY``
``preDelete()``     ``delete()``
``postDelete()``    ``delete()``
``preValidate()``   ``validate()``
``postValidate()``  ``validate()``
==================  ===============================================


Just like with connection listeners there are three ways of defining a
record listener: by extending :php:class:`Doctrine_Record_Listener`, by
implementing :php:class:`Doctrine_Record_Listener_Interface` or by
implementing :php:class:`Doctrine_Overloadable`.

In the following we'll create a global level listener by implementing
:php:class:`Doctrine_Overloadable`:

::

    class Logger implements Doctrine_Overloadable
    {
        public function __call( $m, $a )
        {
            echo 'caught event ' . $m;

            // do some logging here...
        }
    }

Attaching the listener to manager is easy:

::

    $manager->addRecordListener( new Logger() );

Note that by adding a manager level listener it affects on all
connections and all tables / records within these connections. In the
following we create a connection level listener:

::

    class Debugger extends Doctrine_Record_Listener
    {
        public function preInsert( Doctrine_Event $event )
        {
            echo 'inserting a record ...';
        }

        public function preUpdate( Doctrine_Event $event )
        {
            echo 'updating a record...';
        }
    }

Attaching the listener to a connection is as easy as:

::

    $conn->addRecordListener( new Debugger() );

Many times you want the listeners to be table specific so that they only
apply on the actions on that given table.

Here is an example:

::

    class Debugger extends Doctrine_Record_Listener
    {
        public function postDelete( Doctrine_Event $event )
        {
            echo 'deleted ' . $event->getInvoker()->id;
        }
    }

Attaching this listener to given table can be done as follows:

::

    class MyRecord extends Doctrine_Record
    {
        // ...
        public function setUp()
        {
            $this->addListener( new Debugger() );
        }
    }

============
Record Hooks
============

All of the below listeners are invoked in the :php:class:`Doctrine_Record` and
:php:class:`Doctrine_Validator` classes. And they are all passed an instance of
:php:class:`Doctrine_Event`.

==================  ==================================================
Methods             Listens
==================  ==================================================
``preSave()``       ``save()``
``postSave()``      ``save()``
``preUpdate()``     ``save()`` **when the record state is** ``DIRTY``
``postUpdate()``    ``save()`` **when the record state is** ``DIRTY``
``preInsert()``     ``save()`` **when the record state is** ``TDIRTY``
``postInsert()``    ``save()`` **when the record state is** ``TDIRTY``
``preDelete()``     ``delete()``
``postDelete()``    ``delete()``
``preValidate()``   ``validate()``
``postValidate()``  ``validate()``
==================  ==================================================

Here is a simple example where we make use of the ``preInsert()`` and
``preUpdate()`` methods:

::

    class BlogPost extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'title', 'string', 200 );
            $this->hasColumn( 'content', 'string' );
            $this->hasColumn( 'created', 'date' );
            $this->hasColumn( 'updated', 'date' );
        }

        public function preInsert( $event )
        {
            $this->created = date( 'Y-m-d', time() );
        }

        public function preUpdate( $event )
        {
            $this->updated = date( 'Y-m-d', time() );
        }
    }

=========
DQL Hooks
=========

Doctrine allows you to attach record listeners globally, on each
connection, or on specific record instances. :php:class:`Doctrine_Query`
implements ``preDql*()`` hooks which are checked for on any attached
record listeners and checked for on the model instance itself whenever a
query is executed. The query will check all models involved in the
``from`` part of the query for any hooks which can alter the query that
invoked the hook.

Here is a list of the hooks you can use with DQL:

==================  ================
Methods             Listens
==================  ================
``preDqlSelect()``  ``from()``
``preDqlUpdate()``  ``update()``
``preDqlDelete()``  ``delete()``
==================  ================

Below is an example record listener attached directly to the model which
will implement the ``SoftDelete`` functionality for the ``User`` model.

.. tip::

    The SoftDelete functionality is included in Doctrine as a
    behavior. This code is used to demonstrate how to use the select,
    delete, and update DQL listeners to modify executed queries. You can
    use the SoftDelete behavior by specifying
    ``$this->actAs('SoftDelete')`` in your ``Doctrine_Record::setUp()``
    definition.

::

    class UserListener extends Doctrine_EventListener
    {
        /**
         * Skip the normal delete options so we can override it with our own
         *
         * @param Doctrine_Event $event
         * @return void
         */
        public function preDelete( Doctrine_Event $event )
        {
            $event->skipOperation();
        }

        /**
         * Implement postDelete() hook and set the deleted flag to true
         *
         * @param Doctrine_Event $event
         * @return void
         */
        public function postDelete( Doctrine_Event $event )
        {
            $name                       = $this->_options['name'];
            $event->getInvoker()->$name = true;
            $event->getInvoker()->save();
        }

        /**
         * Implement preDqlDelete() hook and modify a dql delete query so it updates the deleted flag
         * instead of deleting the record
         *
         * @param Doctrine_Event $event
         * @return void
         */
        public function preDqlDelete( Doctrine_Event $event )
        {
            $params = $event->getParams();
            $field  = $params['alias'] . '.deleted';
            $q      = $event->getQuery();

            if ( ! $q->contains( $field ) )
            {
                $q->from('')->update( $params['component'] . ' ' . $params['alias'] );
                $q->set( $field, '?', array(false) );
                $q->addWhere( $field . ' = ?', array(true) );
            }
        }

        /**
         * Implement preDqlDelete() hook and add the deleted flag to all queries for which this model
         * is being used in.
         *
         * @param Doctrine_Event $event
         * @return void
         */
        public function preDqlSelect( Doctrine_Event $event )
        {
            $params = $event->getParams();
            $field  = $params['alias'] . '.deleted';
            $q      = $event->getQuery();

            if ( ! $q->contains( $field ) )
            {
                $q->addWhere( $field . ' = ?', array(false) );
            }
        }
    }

All of the above methods in the listener could optionally be placed in
the user class below. Doctrine will check there for the hooks as well:

::

    class User extends Doctrine_Record
    {
        // ...
        public function preDqlSelect()
        {
            // ...
        }

        public function preDqlUpdate()
        {
            // ...
        }

        public function preDqlDelete()
        {
            // ...
        }
    }

In order for these dql callbacks to be checked, you must explicitly turn
them on. Because this adds a small amount of overhead for each query, we
have it off by default. We already enabled this attribute in an earlier
chapter.

Here it is again to refresh your memory:

::

    // bootstrap.php

    // ...
    $manager->setAttribute( Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true );

Now when you interact with the User model it will take in to account the
deleted flag:

Delete user through record instance:

::

    $user           = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();
    $user->delete();

.. note::

    The above call to ``$user->delete()`` does not actually
    delete the record instead it sets the deleted flag to true.

::

    $q = Doctrine_Query::create()
        ->from('User u');

    echo $q->getSqlQuery();

::

    SELECT
    u.id AS u**id,
    u.username AS u**username,
    u.password AS u**password,
    u.deleted AS u**deleted
    FROM user u
    WHERE u.deleted = ?

.. note::

    Notice how the ``"u.deleted = ?"`` was automatically added
    to the where condition with a parameter value of **true**.

==================
Chaining Listeners
==================

Doctrine allows chaining of different event listeners. This means that
more than one listener can be attached for listening the same events.
The following example attaches two listeners for given connection:

In this example ``Debugger`` and ``Logger`` both inherit
:php:class:`Doctrine_EventListener`:

::

    $conn->addListener( new Debugger() );
    $conn->addListener( new Logger() );

================
The Event object
================

-------------------
Getting the Invoker
-------------------

You can get the object that invoked the event by calling
``getInvoker()``:

::

    class MyListener extends Doctrine_EventListener
    {
        public function preExec( Doctrine_Event $event )
        {
            $event->getInvoker(); // Doctrine_Connection
        }
    }

-----------
Event Codes
-----------

:php:class:`Doctrine_Event` uses constants as event codes. Below is the list of
all available event constants:

-  ``Doctrine_Event::CONN_QUERY``
-  ``Doctrine_Event::CONN_EXEC``
-  ``Doctrine_Event::CONN_PREPARE``
-  ``Doctrine_Event::CONN_CONNECT``
-  ``Doctrine_Event::STMT_EXECUTE``
-  ``Doctrine_Event::STMT_FETCH``
-  ``Doctrine_Event::STMT_FETCHALL``
-  ``Doctrine_Event::TX_BEGIN``
-  ``Doctrine_Event::TX_COMMIT``
-  ``Doctrine_Event::TX_ROLLBACK``
-  ``Doctrine_Event::SAVEPOINT_CREATE``
-  ``Doctrine_Event::SAVEPOINT_ROLLBACK``
-  ``Doctrine_Event::SAVEPOINT_COMMIT``
-  ``Doctrine_Event::RECORD_DELETE``
-  ``Doctrine_Event::RECORD_SAVE``
-  ``Doctrine_Event::RECORD_UPDATE``
-  ``Doctrine_Event::RECORD_INSERT``
-  ``Doctrine_Event::RECORD_SERIALIZE``
-  ``Doctrine_Event::RECORD_UNSERIALIZE``
-  ``Doctrine_Event::RECORD_DQL_SELECT``
-  ``Doctrine_Event::RECORD_DQL_DELETE``
-  ``Doctrine_Event::RECORD_DQL_UPDATE``

Here are some examples of hooks being used and the code that is
returned:

::

    class MyListener extends Doctrine_EventListener
    {
        public function preExec( Doctrine_Event $event )
        {
            $event->getCode(); // Doctrine_Event::CONN_EXEC
        }
    }

    class MyRecord extends Doctrine_Record
    {
        public function preUpdate( Doctrine_Event $event )
        {
            $event->getCode(); // Doctrine_Event::RECORD_UPDATE
        }
    }

-------------------
Getting the Invoker
-------------------

The method ``getInvoker()`` returns the object that invoked the given
event. For example for event ``Doctrine_Event::CONN_QUERY`` the
invoker is a :php:class:`Doctrine_Connection` object.

Here is an example of using the record hook named ``preUpdate()`` that
is invoked when a :php:class:`Doctrine_Record` instance is saved and an update
is issued to the database:

::

    class MyRecord extends Doctrine_Record
    {
        public function preUpdate( Doctrine_Event $event )
        {
            $event->getInvoker(); // Object(MyRecord)
        }
    }

-------------------
Skip Next Operation
-------------------

:php:class:`Doctrine_Event` provides many methods for altering the execution of
the listened method as well as for altering the behavior of the listener
chain.

For some reason you may want to skip the execution of the listened
method. It can be done as follows (note that ``preExec()`` could be any
listener method):

::

    class MyListener extends Doctrine_EventListener
    {
        public function preExec( Doctrine_Event $event )
        {
            // some business logic, then:
            $event->skipOperation();
        }
    }

-----------------
Skip Next Listener
------------------

When using a chain of listeners you might want to skip the execution of
the next listener. It can be achieved as follows:

::

    class MyListener extends Doctrine_EventListener
    {
        public function preExec( Doctrine_Event $event )
        {
            // some business logic, then:
            $event->skipNextListener();
        }
    }

==========
Conclusion
==========

Event listeners are a great feature in Doctrine and combined with :doc:`behaviors` they can provide some very complex functionality with a
minimal amount of code.

Now we are ready to move on to discuss the best feature in Doctrine for
improving performance, :doc:`caching`.