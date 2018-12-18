..  vim: set ts=4 sw=4 tw=79 :

*******************
Working with Models
*******************

==================
Define Test Schema
==================

.. note::

    Remember to delete any existing schema information and
    models from previous chapters.

    .. code-block:: sh

        rm schema.yml
        touch schema.yml
        rm -rf models/*

For the next several examples we will use the following schema::

    // models/User.php
    class User extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('username', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            ));
            $this->hasColumn('password', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            ));
        }

        public function setUp()
        {
            $this->hasMany('Group as Groups', array(
                'refClass' => 'UserGroup',
                'local' => 'user_id',
                'foreign' => 'group_id'
            ));
            $this->hasOne('Email', array(
                'local' => 'id',
                'foreign' => 'user_id'
            ));
            $this->hasMany('Phonenumber as Phonenumbers', array(
                'local' => 'id',
                'foreign' => 'user_id'
            ));
        }
    }


::

    // models/Email.php
    class Email extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer', null, array(
                'type' => 'integer'
            ));
            $this->hasColumn('address', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            ));
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }

::

    // models/Phonenumber.php
    class Phonenumber extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer', null, array(
                'type' => 'integer'
            ));
            $this->hasColumn('phonenumber', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            ));
            $this->hasColumn('primary_num', 'boolean');
        }

        public function setUp()
        {
            $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            ));
        }
    }

::

    // models/Group.php
    class Group extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->setTableName('groups');
            $this->hasColumn('name', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            ));
        }

        public function setUp()
        {
            $this->hasMany('User as Users', array(
                'refClass' => 'UserGroup',
                'local' => 'group_id',
                'foreign' => 'user_id'
            ));
        }
    }

::

    // models/UserGroup.php
    class UserGroup extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn('user_id', 'integer', null, array(
                'type' => 'integer',
                'primary' => true
            ));
            $this->hasColumn('group_id', 'integer', null, array(
                'type' => 'integer',
                'primary' => true
            ));
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    # schema.yml
    User:
      columns:
        username: string(255)
        password: string(255)
      relations:
        Groups:
          class: Group
          local: user_id
          foreign: group_id
          refClass: UserGroup
          foreignAlias: Users

    Email:
      columns:
        user_id: integer
        address: string(255)
      relations:
        User:
          foreignType: one

    Phonenumber:
      columns:
        user_id: integer
        phonenumber: string(255)
        primary_num: boolean
      relations:
        User:
          foreignAlias:
            Phonenumbers

    Group:
      tableName: groups
      columns:
        name: string(255)

    UserGroup:
      columns:
        user_id:
          type: integer
          primary: true
        group_id:
          type: integer
          primary: true

Now that you have your schema defined you can instantiate the database
by simply running the :file:`generate.php` script we so conveniently created
in the previous chapter.

.. code-block:: sh

    php generate.php

======================
Dealing with Relations
======================

------------------------
Creating Related Records
------------------------

Accessing related records in Doctrine is easy: you can use exactly the
same getters and setters as for the record properties.

You can use any of the three ways above, however the last one is the
recommended one for array portability purposes.

::

    // test.php
    $user = new User();
    $user['username'] = 'jwage';
    $user['password'] = 'changeme';

    $email = $user->Email;

    $email = $user->get('Email');

    $email = $user['Email'];

When accessing a one-to-one related record that doesn't exist, Doctrine
automatically creates the object. That is why the above code is
possible.

::

    $user->Email->address = 'jonwage@gmail.com';
    $user->save();

When accessing one-to-many related records, Doctrine creates a
:php:class:`Doctrine_Collection` for the related component. Lets say we have
``users`` and ``phonenumbers`` and their relation is one-to-many. You
can add ``phonenumbers`` easily as shown above::

    $user->Phonenumbers[]->phonenumber = '123 123';
    $user->Phonenumbers[]->phonenumber = '456 123';
    $user->Phonenumbers[]->phonenumber = '123 777';

Now we can easily save the user and the associated phonenumbers::

    $user->save();

Another way to easily create a link between two related components is by
using :php:meth:`Doctrine_Record::link`. It often happens that you have two
existing records that you would like to relate (or link) to one another.
In this case, if there is a relation defined between the involved record
classes, you only need the identifiers of the related record(s):

Lets create a few new ``Phonenumber`` objects and keep track of the new
phone number identifiers::

    // test.php
    $phoneIds = array();

    $phone1 = new Phonenumber(); $phone1['phonenumber'] = '555 202 7890';
    $phone1->save();

    $phoneIds[] = $phone1['id'];

    $phone2 = new Phonenumber(); $phone2['phonenumber'] = '555 100 7890';
    $phone2->save();

    $phoneIds[] = $phone2['id'];

Let's link the phone numbers to the user, since the relation to
``Phonenumbers`` exists for the ``User`` record::

    $user = new User();
    $user['username'] = 'jwage';
    $user['password'] = 'changeme';
    $user->save();

    $user->link('Phonenumbers', $phoneIds);

If a relation to the ``User`` record class is defined for the
``Phonenumber`` record class, you may even do this:

First create a user to work with::

    $user = new User();
    $user['username'] = 'jwage';
    $user['password'] = 'changeme';
    $user->save();

Now create a new ``Phonenumber`` instance::

    $phone1 = new Phonenumber();
    $phone1['phonenumber'] = '555 202 7890';
    $phone1->save();

Now we can link the ``User`` to our ``Phonenumber``::

    $phone1->link('User', array($user['id']));

We can create another phone number::

    $phone2 = new Phonenumber();
    $phone2['phonenumber'] = '555 100 7890';
    $phone2->save();

Let's link this ``Phonenumber`` to our ``User`` too::

    $phone2->link('User', array($user['id']));

--------------------------
Retrieving Related Records
--------------------------

You can retrieve related records by the very same :php:class:`Doctrine_Record`
methods as in the previous subchapter. Please note that whenever you
access a related component that isn't already loaded Doctrine uses one
``SQL SELECT`` statement for the fetching, hence the following example
executes three ``SQL SELECTs``.

dakmdaklsd alksdlkamsd kldlksmdklm

::

    // test.php
    $user = Doctrine_Core::getTable('User')->find(1);

    echo $user->Email['address'];

    echo $user->Phonenumbers[0]->phonenumber;

Much more efficient way of doing this is using DQL. The following
example uses only one SQL query for the retrieval of related components.

::

    // test.php
    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Email e')
            ->leftJoin('u.Phonenumbers p')
            ->where('u.id = ?', 1);

    $user = $q->fetchOne();

    echo $user->Email['address'];

    echo $user->Phonenumbers[0]['phonenumber'];

------------------------
Updating Related Records
------------------------

You can update the related records by calling save for each related
object / collection individually or by calling save on the object that
owns the other objects. You can also call
``Doctrine_Connection::flush`` which saves all pending objects.

::

    // test.php
    $user->Email['address'] = 'koskenkorva@drinkmore.info';

    $user->Phonenumbers[0]['phonenumber'] = '123123';

    $user->save();

.. note::

    In the above example calling :php:meth:`$user->save` saves the
    ``email`` and ``phonenumber``.

------------------------
Clearing Related Records
------------------------

You can clear a related records references from an object. This does not
change the fact that these objects are related and won't change it in
the database if you save. It just simply clears the reference in PHP of
one object to another.

You can clear all references by doing the following::

    // test.php
    $user->clearRelated();

Or you can clear a specific relationship::

    $user->clearRelated('Email');

This is useful if you were to do something like the following::

    if ($user->Email->exists()) {
        // User has e-mail
    } else {
        // User does not have a e-mail
    }

    $user->clearRelated('Email');

Because Doctrine will automatically create a new ``Email`` object if the
user does not have one, we need to clear that reference so that if we
were to call :php:meth:`$user->save` it wouldn't save a blank ``Email`` record
for the ``User``.

We can simplify the above scenario even further by using the
:php:meth:`relatedExists` method. This is so that you can do the above check
with less code and not have to worry about clearing the unnecessary
reference afterwards.

::

    if ($user->relatedExists('Email')) {
        // User has e-mail
    } else {
        //  User does not have a e-mail
    }

------------------------
Deleting Related Records
------------------------

You can delete related records individually be calling :php:meth:`delete` on a
record or on a collection.

Here you can delete an individual related record::

    // test.php
    $user->Email->delete();

You can delete an individual record from within a collection of records::

    $user->Phonenumbers[3]->delete();

You could delete the entire collection if you wanted::

    $user->Phonenumbers->delete();

Or can just delete the entire user and all related objects::

    $user->delete();

Usually in a typical web application the primary keys of the related
objects that are to be deleted come from a form. In this case the most
efficient way of deleting the related records is using DQL DELETE
statement. Lets say we have once again ``Users`` and ``Phonenumbers``
with their relation being one-to-many. Deleting the given
``Phonenumbers`` for given user id can be achieved as follows::

    $q = Doctrine_Query::create()
            ->delete('Phonenumber')
            ->addWhere('user_id = ?', 5)
            ->whereIn('id', array(1, 2, 3));

    $numDeleted = $q->execute();

Sometimes you may not want to delete the ``Phonenumber`` records but to
simply unlink the relations by setting the foreign key fields to null.
This can of course be achieved with DQL but perhaps to most elegant way
of doing this is by using :php:meth:`Doctrine_Record::unlink`.

.. note::

    Please note that the :php:meth:`unlink` method is very smart. It
    not only sets the foreign fields for related ``Phonenumbers`` to
    null but it also removes all given ``Phonenumber`` references from
    the ``User`` object.

Lets say we have a ``User`` who has three ``Phonenumbers`` (with
identifiers 1, 2 and 3). Now unlinking the ``Phonenumbers`` 1 and 3 can
be achieved as easily as::

    $user->unlink('Phonenumbers', array(1, 3));

    echo $user->Phonenumbers->count(); // 1

----------------------------
Working with Related Records
----------------------------

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Testing the Existence of a Relation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The below example would return false because the relationship has not
been instantiated yet::

    // test.php
    $user = new User();
    if (isset($user->Email)) {
        // ...
    }

Now the next example will return true because we instantiated the
``Email`` relationship::

    // test.php
    $obj->Email = new Email();
    if(isset($obj->Email)) {
        // ...
    }

======================
Many-to-Many Relations
======================

.. caution::

    Doctrine requires that Many-to-Many relationships be
    bi-directional. For example: both ``User`` must have many ``Groups``
    and ``Group`` must have many ``User``.

-------------------
Creating a New Link
-------------------

Lets say we have two classes ``User`` and ``Group`` which are linked
through a ``GroupUser`` association class. When working with transient
(new) records the fastest way for adding a ``User`` and couple of
``Group`` objects for it is::

    // test.php
    $user = new User();
    $user->username = 'Some User';
    $user->Groups[0]->username = 'Some Group';
    $user->Groups[1]->username = 'Some Other Group';
    $user->save();

However in real world scenarios you often already have existing groups,
where you want to add a given user. The most efficient way of doing this
is::

    $groupUser = new GroupUser();
    $groupUser->user_id = $userId;
    $groupUser->group_id = $groupId;
    $groupUser->save();

---------------
Deleting a Link
---------------

The right way to delete links between many-to-many associated records is
by using the DQL DELETE statement. Convenient and recommended way of
using DQL DELETE is through the Query API.

::

    // test.php
    $q = Doctrine_Query::create()
            ->delete('UserGroup')
            ->addWhere('user_id = ?', 5)
            ->whereIn('group_id', array(1, 2));

    $deleted = $q->execute();

Another way to ``unlink`` the relationships between related objects is
through the ``Doctrine_Record::unlink`` method. However, you should
avoid using this method unless you already have the parent model, since
it involves querying the database first.

::

    $user = Doctrine_Core::getTable('User')->find(5);
    $user->unlink('Group', array(1, 2));
    $user->save();

You can also unlink ALL relationships to ``Group`` by omitting the
second argument::

    $user->unlink('Group');

While the obvious and convenient way of deleting a link between ``User``
and ``Group`` would be the following, you still should NOT do this::

    $user = Doctrine_Core::getTable('User')->find(5);
    $user->GroupUser->remove(0)->remove(1);
    $user->save();

This is due to a fact that the call to ``$user->GroupUser`` loads all
``Group`` links for given ``User``. This can be time-consuming task if
the ``User`` belongs to many ``Groups``. Even if the user belongs to few
``groups`` this will still execute an unnecessary SELECT statement.

================
Fetching Objects
================

Normally when you fetch data from database the following phases are
executed:

* Sending the query to database
* Retrieve the returned data from the database

In terms of object fetching we call these two phases the 'fetching'
phase. Doctrine also has another phase called hydration phase. The
hydration phase takes place whenever you are fetching structured arrays
/ objects. Unless explicitly specified everything in Doctrine gets
hydrated.

Lets consider we have ``Users`` and ``Phonenumbers`` with their relation
being one-to-many. Now consider the following plain sql query::

    // test.php
    $sql = 'SELECT
                u.id, u.username, p.phonenumber
            FROM user u
               LEFT JOIN phonenumber p
                   ON u.id = p.user_id';

    $results = $conn->getDbh()->fetchAll($sql);

If you are familiar with these kind of one-to-many joins it may be familiar to
you how the basic result set is constructed. Whenever the user has more than
one phonenumbers there will be duplicated data in the result set. The result
set might look something like:

=========  ========  ==============  =================
``index``  ``u.id``  ``u.username``  ``p.phonenumber``
=========  ========  ==============  =================
0          1         Jack Daniels    123 123
1          1         Jack Daniels    456 456
2          2         John Beer       111 111
3          3         John Smith      222 222
4          3         John Smith      333 333
5          3         John Smith      444 444
=========  ========  ==============  =================

Here Jack Daniels has two ``Phonenumbers``, John Beer has one whereas
John Smith has three. You may notice how clumsy this result set is. Its
hard to iterate over it as you would need some duplicate data checking
logic here and there.

Doctrine hydration removes all duplicated data. It also performs many
other things such as:

* Custom indexing of result set elements
* Value casting and preparation
* Value assignment listening
* Makes multi-dimensional array out of the two-dimensional result set array, the number of dimensions is equal to the number of nested joins

Now consider the DQL equivalent of the SQL query we used::

    $q = Doctrine_Query::create()
          ->select('u.id, u.username, p.phonenumber')
          ->from('User u')
          ->leftJoin('u.Phonenumbers p');

    $results = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

    print_r($results);

The structure of this hydrated array would look like

.. code-block:: text

    $ php test.php
    Array (
        [0] => Array
            (
                [id] => 1
                [username] =>
                [Phonenumbers] => Array
                    (
                        [0] => Array
                            (
                                [id] => 1
                                [phonenumber] => 123 123
                            )
                        [1] => Array
                            (
                                [id] => 2
                                [phonenumber] => 456 123
                            )
                        [2] => Array
                            (
                                [id] => 3
                                [phonenumber] => 123 777
                            )
                    )
            )
    )

This structure also applies to the hydration of objects(records) which is the
default hydration mode of Doctrine. The only differences are that the
individual elements are represented as :php:class:`Doctrine_Record` objects and
the arrays converted into :php:class:`Doctrine_Collection` objects. Whether
dealing with arrays or objects you can:

* Iterate over the results using ``foreach``
* Access individual elements using array access brackets
* Get the number of elements using ``count()`` function
* Check if given element exists using ``isset()``
* Unset given element using ``unset()``

You should always use array hydration when you only need to data for
access-only purposes, whereas you should use the record hydration when
you need to change the fetched data.

The constant O(n) performance of the hydration algorithm is ensured by a
smart identifier caching solution.

.. tip::

    Doctrine uses an identity map internally to make sure that multiple objects
    for one record in a database don't ever exist. If you fetch an object and
    modify some of its properties, then re-fetch that same object later, the
    modified properties will be overwritten by default. You can change this
    behavior by changing the ``ATTR_HYDRATE_OVERWRITE`` attribute to ``false``.

--------------
Sample Queries
--------------

* Count number of records for a relationship::

    // test.php
    $q = Doctrine_Query::create()
            ->select('u.*, COUNT(DISTINCT p.id) AS num_phonenumbers')
            ->from('User u')
            ->leftJoin('u.Phonenumbers p')
            ->groupBy('u.id');

    $users = $q->fetchArray();

    echo $users[0]['Phonenumbers'][0]['num_phonenumbers'];

* Retrieve Users and the Groups they belong to::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Groups g');

    $users = $q->fetchArray();

    foreach ($users[0]['Groups'] as $group) {
        echo $group['name'];
    }

* Simple WHERE with one parameter value::

    $q = Doctrine_Query::create()
        ->from('User u')
        ->where('u.username = ?', 'jwage');

    $users = $q->fetchArray();

* Multiple WHERE with multiple parameters values::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Phonenumbers p')
            ->where('u.username = ? AND p.id = ?', array(1, 1));

    $users = $q->fetchArray();

.. tip::

    You can also optionally use the :php:meth:`andWhere` method to add
    to the existing where parts.

    ::

        $q = Doctrine_Query::create()
                ->from('User u')
                ->leftJoin('u.Phonenumbers p')
                ->where('u.username = ?', 1)
                ->andWhere('p.id = ?', 1);

        $users = $q->fetchArray();

* Using :php:meth:`whereIn` convenience method::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->whereIn('u.id', array(1, 2, 3));

    $users = $q->fetchArray();

* The following is the same as above example::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where('u.id IN (1, 2, 3)');

    $users = $q->fetchArray();

* Using DBMS function in your WHERE::

    $userEncryptedKey = 'a157a558ac00449c92294c7fab684ae0';

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where("MD5(CONCAT(u.username, 'secret_key')) = ?", $userEncryptedKey);

    $user = $q->fetchOne();

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where('LOWER(u.username) = LOWER(?)', 'jwage');

    $user = $q->fetchOne();

* Limiting result sets using aggregate functions. Limit to users with more than one phonenumber::

    $q = Doctrine_Query::create()
            ->select('u.*, COUNT(DISTINCT p.id) AS num_phonenumbers')
            ->from('User u')
            ->leftJoin('u.Phonenumbers p')
            ->having('num_phonenumbers > 1')
            ->groupBy('u.id');

    $users = $q->fetchArray();

* Join only primary phonenumbers using WITH::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->leftJoin('u.Phonenumbers p WITH p.primary_num = ?', true);

    $users = $q->fetchArray();

* Selecting certain columns for optimization::

    $q = Doctrine_Query::create()
            ->select('u.username, p.phone')
            ->from('User u')
            ->leftJoin('u.Phonenumbers p');

    $users = $q->fetchArray();


* Using a wildcard to select all ``User`` columns but only one ``Phonenumber`` column::

    $q = Doctrine_Query::create()
            ->select('u.*, p.phonenumber')
            ->from('User u')
            ->leftJoin('u.Phonenumbers p');

    $users = $q->fetchArray();

* Perform DQL delete with simple WHERE::

    $q = Doctrine_Query::create()
            ->delete('Phonenumber')
            ->addWhere('user_id = 5');

    $deleted = $q->execute();

* Perform simple DQL update for a column::

    $q = Doctrine_Query::create()
            ->update('User u')
            ->set('u.is_active', '?', true)
            ->where('u.id = ?', 1);

    $updated = $q->execute();

* Perform DQL update with DBMS function. Make all usernames lowercase::

    $q = Doctrine_Query::create()
            ->update('User u')
            ->set('u.username', 'LOWER(u.username)');

    $updated = $q->execute();

* Using mysql LIKE to search for records::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where('u.username LIKE ?', '%jwage%');

    $users = $q->fetchArray();

* Use the INDEXBY keyword to hydrate the data where the key of record
  entry is the name of the column you assign::

    $q = Doctrine_Query::create()
            ->from('User u INDEXBY u.username');

    $users = $q->fetchArray();

  Now we can print the user with the username of jwage::

    print_r($users['jwage']);

* Using positional parameters::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where('u.username = ?', array('Arnold'));

    $users = $q->fetchArray();

* Using named parameters::

    $q = Doctrine_Query::create()
            ->from('User u')
            ->where('u.username = :username', array(':username' => 'Arnold'));

    $users = $q->fetchArray();

* Using subqueries in your WHERE. Find users not in group named Group
  2::

    $q = Doctrine_Query::create()
        ->from('User u')
        ->where('u.id NOT IN (
                    SELECT u.id FROM User u2 INNER JOIN u2.Groups g WHERE g.name = ?
                )', 'Group 2');

    $users = $q->fetchArray();

.. tip::

    You can accomplish this without using subqueries. The two
    examples below would have the same result as the example above.

    * Use INNER JOIN to retrieve users who have groups, excluding the group
      named Group 2::

        $q = Doctrine_Query::create()
                ->from('User u')
                ->innerJoin('u.Groups g WITH g.name != ?', 'Group 2');

        $users = $q->fetchArray();

    * Use WHERE condition to retrieve users who have groups, excluding the
      group named Group 2::

        $q = Doctrine_Query::create()
                ->from('User u')
                ->leftJoin('u.Groups g')
                ->where('g.name != ?', 'Group 2');

        $users = $q->fetchArray();

Doctrine has many different ways you can execute queries and retrieve
the data. Below are examples of all the different ways you can execute a
query:

* First lets create a sample query to test with::

    $q = Doctrine_Query::create() ->from('User u');

* You can use array hydration with the :php:meth:`fetchArray` method::

    $users = $q->fetchArray();

* You can also use array hydration by specifying the hydration method to
  the second argument of the :php:meth:`execute` method::

    $users = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY)

* You can also specify the hydration method by using the
  :php:meth:`setHydrationMethod` method::

    $users = $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute(); // So is this

.. note::

    Custom accessors and mutators will not work when hydrating
    data as anything except records. When you hydrate as an array it is
    only a static array of data and is not object oriented. If you need
    to add custom values to your hydrated arrays you can use the some of
    the events such as ``preHydrate`` and ``postHydrate``

* Sometimes you may want to totally bypass hydration and return the raw
  data that PDO returns::

    $users = $q->execute(array(), Doctrine_Core::HYDRATE_NONE);

.. tip::

    More can be read about skipping hydration in the :doc:`improving performance <improving-performance>` chapter.

* If you want to just fetch one record from the query::

    $user = $q->fetchOne();

    // Fetch all and get the first from collection
    $user = $q->execute()->getFirst();

------------------
Field Lazy Loading
------------------

Whenever you fetch an object that has not all of its fields loaded from
database then the state of this object is called proxy. Proxy objects
can load the unloaded fields lazily.

In the following example we fetch all the Users with the ``username``
field loaded directly. Then we lazy load the password field::

    // test.php
    $q = Doctrine_Query::create()
            ->select('u.username')
            ->from('User u')
            ->where('u.id = ?', 1);
    $user = $q->fetchOne();

The following lazy-loads the ``password`` field and executes one
additional database query to retrieve the value::

    $user->password;

Doctrine does the proxy evaluation based on loaded field count. It does not
evaluate which fields are loaded on field-by-field basis. The reason for this
is simple: performance. Field lazy-loading is very rarely needed in PHP world,
hence introducing some kind of variable to check which fields are loaded would
introduce unnecessary overhead to basic fetching.

==================
Arrays and Objects
==================

:php:class:`Doctrine_Record` and :php:class:`Doctrine_Collection` provide methods to
facilitate working with arrays: :php:meth:`toArray`, :php:meth:`fromArray` and
:php:meth:`synchronizeWithArray`.

--------
To Array
--------

The :php:meth:`toArray` method returns an array representation of your records
or collections. It also accesses the relationships the objects may have.
If you need to print a record for debugging purposes you can get an
array representation of the object and print that.

::

    print_r($user->toArray());

If you do not want to include the relationships in the array then you
need to pass the ``$deep`` argument with a value of ``false``::

    print_r($user->toArray(false));

----------
From Array
----------

If you have an array of values you want to use to fill a record or even
a collection, the :php:meth:`fromArray` method simplifies this common task.

::

    $data = array(
        'name'   => 'John',
        'age'    => '25',
        'Emails' => array(
            array('address' => 'john@mail.com'),
            array('address' => 'john@work.com')
        )
    );

    $user = new User();
    $user->fromArray($data);
    $user->save();

----------------------
Synchronize With Array
----------------------

:php:meth:`synchronizeWithArray` allows you to... well, synchronize a record
with an array. So if have an array representation of your model and modify a
field, modify a relationship field or even delete or create a relationship,
this changes will be applied to the record.

::

    $q = Doctrine_Query::create()
            ->select('u.*, g.*')
            ->from('User u')
            ->leftJoin('u.Groups g')
            ->where('id = ?', 1);

    $user = $q->fetchOne();

Now convert it to an array and modify some of the properties::

    $arrayUser = $user->toArray(true);

    $arrayUser['username'] = 'New name';
    $arrayUser['Group'][0]['name'] = 'Renamed Group';
    $arrayUser['Group'][] = array('name' => 'New Group');

Now use the same query to retrieve the record and synchronize the record
with the ``$arrayUser`` variable::

    $user = Doctrine_Query::create()
                ->select('u.*, g.*')
                ->from('User u')
                ->leftJoin('u.Groups g')
                ->where('id = ?', 1)
                ->fetchOne();

    $user->synchronizeWithArray($arrayUser);
    $user->save();

==========================
Overriding the Constructor
==========================

Sometimes you want to do some operations at the creation time of your objects.
Doctrine doesn't allow you to override the
:php:meth:`Doctrine_Record::__construct` method but provides an alternative::

    class User extends Doctrine_Record
    {
        public function construct()
        {
            $this->username = 'Test Name';
            $this->doSomething();
        }

        public function doSomething()
        {
            // ...
        }

        // ...
    }

The only drawback is that it doesn't provide a way to pass parameters to
the constructor.

==========
Conclusion
==========

By now we should know absolutely everything there is to know about
models. We know how to create them, load them and most importantly we
know how to use them and work with columns and relationships. Now we are
ready to move on to learn about how to use the :doc:`dql-doctrine-query-language`.
