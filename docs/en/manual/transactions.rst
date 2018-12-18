************
Transactions
************

============
Introduction
============

A database transaction is a unit of interaction with a database
management system or similar system that is treated in a coherent and
reliable way independent of other transactions that must be either
entirely completed or aborted. Ideally, a database system will guarantee
all of the ACID (Atomicity, Consistency, Isolation, and Durability)
properties for each transaction.

-  `Atomicity <http://en.wikipedia.org/wiki/Atomicity>`_ refers to the
   ability of the DBMS to guarantee that either all of the tasks of a
   transaction are performed or none of them are. The transfer of funds
   can be completed or it can fail for a multitude of reasons, but
   atomicity guarantees that one account won't be debited if the other
   is not credited as well.
-  `Consistency <http://en.wikipedia.org/wiki/Database_consistency>`_
   refers to the database being in a legal state when the transaction
   begins and when it ends. This means that a transaction can't break
   the rules, or //integrity constraints//, of the database. If an
   integrity constraint states that all accounts must have a positive
   balance, then any transaction violating this rule will be aborted.
-  `Isolation <http://en.wikipedia.org/wiki/Schedule_(computer_science)>`_ refers to the ability of the application to make
   operations in a transaction appear isolated from all other
   operations. This means that no operation outside the transaction can
   ever see the data in an intermediate state; a bank manager can see
   the transferred funds on one account or the other, but never on both
   - even if she ran her query while the transfer was still being
   processed. More formally, isolation means the transaction history (or
   `schedule <http://en.wikipedia.org/wiki/Schedule_(computer_science)>`_) is `serializable <http://en.wikipedia.org/wiki/Serializability>`_. For performance reasons, this ability is the most
   often relaxed constraint. See the
   `isolation <http://en.wikipedia.org/wiki/Isolation_(computer_science)>`_ article for more details.
-  `Durability <http://en.wikipedia.org/wiki/Durability_(computer_science)>`_ refers to the guarantee that once the user has been
   notified of success, the transaction will persist, and not be undone.
   This means it will survive system failure, and that the
   `database system <http://en.wikipedia.org/wiki/Database_system>`_ has
   checked the integrity constraints and won't need to abort the
   transaction. Typically, all transactions are written into a
   `log <http://en.wikipedia.org/wiki/Database_log>`_ that can be played
   back to recreate the system to its state right before the failure. A
   transaction can only be deemed committed after it is safely in the
   log.

In Doctrine all operations are wrapped in transactions by default. There
are some things that should be noticed about how Doctrine works
internally:

-  Doctrine uses application level transaction nesting.
-  Doctrine always executes ``INSERT`` / ``UPDATE`` / ``DELETE`` queries
   at the end of transaction (when the outermost commit is called). The
   operations are performed in the following order: all inserts, all
   updates and last all deletes. Doctrine knows how to optimize the
   deletes so that delete operations of the same component are gathered
   in one query.

First we need to begin a new transation:

::

    $conn->beginTransaction();

Next perform some operations which result in queries being executed:

::

    $user       = new User();
    $user->name = 'New user';
    $user->save();

    $user       = Doctrine_Core::getTable('User')->find(5);
    $user->name = 'Modified user';
    $user->save();

Now we can commit all the queries by using the ``commit()`` method:

::

    $conn->commit();

=======
Nesting
=======

You can easily nest transactions with the Doctrine DBAL. Check the code
below for a simple example demonstrating nested transactions.

First lets create a standard PHP function named ``saveUserAndGroup()``:

::

    function saveUserAndGroup(Doctrin_Connection $conn, User $user, Group $group)
    {
        $conn->beginTransaction();

        $user->save();

        $group->save();

        $conn->commit();
    }

Now we make use of the function inside another transaction:

::

    try {
        $conn->beginTransaction();

        saveUserAndGroup($conn,$user,$group);
        saveUserAndGroup($conn,$user2,$group2);
        saveUserAndGroup($conn,$user3,$group3);

        $conn->commit();

    } catch(Doctrine_Exception $e) {
        $conn->rollback();
    }

.. note::

    Notice how the three calls to ``saveUserAndGroup()`` are
    wrapped in a transaction, and each call to the function starts its
    own nested transaction.

==========
Savepoints
==========

Doctrine supports transaction savepoints. This means you can set named
transactions and have them nested.

The
``Doctrine_Transaction::beginTransaction($savepoint)`` sets a named transaction savepoint with a name of ``$savepoint``.
If the current transaction has a savepoint with the same name, the old
savepoint is deleted and a new one is set.

::

    try {
        $conn->beginTransaction();
        // do some operations here

        // creates a new savepoint called mysavepoint
        $conn->beginTransaction('mysavepoint');
        try {
            // do some operations here

            $conn->commit('mysavepoint');
        } catch(Exception $e) {
            $conn->rollback('mysavepoint');
        }
        $conn->commit();
    } catch(Exception $e) {
        $conn->rollback();
    }

The ``Doctrine_Transaction::rollback($savepoint)`` rolls back a
transaction to the named savepoint. Modifications that the current
transaction made to rows after the savepoint was set are undone in the
rollback.

.. note::

    Mysql, for example, does not release the row locks that
    were stored in memory after the savepoint.

Savepoints that were set at a later time than the named savepoint are
deleted.

The ``Doctrine_Transaction::commit($savepoint)`` removes the named
savepoint from the set of savepoints of the current transaction.

All savepoints of the current transaction are deleted if you execute a
commit or if a rollback is being called without savepoint name
parameter.

::

    try {
        $conn->beginTransaction();
        // do some operations here

        // creates a new savepoint called mysavepoint
        $conn->beginTransaction('mysavepoint');

        // do some operations here

        $conn->commit();   // deletes all savepoints
    } catch(Exception $e) {
        $conn->rollback(); // deletes all savepoints
    }

================
Isolation Levels
================

A transaction isolation level sets the default transactional behavior.
As the name 'isolation level' suggests, the setting determines how
isolated each transation is, or what kind of locks are associated with
queries inside a transaction. The four available levels are (in
ascending order of strictness):

``READ UNCOMMITTED``
 Barely transactional, this setting allows for so-called 'dirty reads', where queries inside one transaction are affected by uncommitted changes in another transaction.

``READ COMMITTED``
 Committed updates are visible within another transaction. This means identical queries within a transaction can return differing results. This is the default in some DBMS's.

``REPEATABLE READ``
 Within a transaction, all reads are consistent. This is the default of Mysql INNODB engine.

``SERIALIZABLE``
 Updates are not permitted in other transactions if a transaction has run an ordinary ``SELECT`` query.

To get the transaction module use the following code:

::

    $tx = $conn->transaction;

Set the isolation level to READ COMMITTED:

::

    $tx->setIsolation('READ COMMITTED');

Set the isolation level to SERIALIZABLE:

::

    $tx->setIsolation('SERIALIZABLE');

.. tip::

    Some drivers (like Mysql) support the fetching of current
    transaction isolation level. It can be done as follows:

::

    $level = $tx->getIsolation();

==========
Conclusion
==========

Transactions are a great feature for ensuring the quality and
consistency of your database. Now that you know about transactions we
are ready to move on and learn about the events sub-framework.

The events sub-framework is a great feature that allows you to hook in
to core methods of Doctrine and alter the operations of internal
functionality without modifying one line of core code.