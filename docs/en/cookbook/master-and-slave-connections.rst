****************************
Master and Slave Connections
****************************

In this tutorial we explain how you can setup Doctrine connections as
master and slaves for both reading and writing data. This strategy is
common when balancing load across database servers.

So, the first thing we need to do is configure all the available
connections for Doctrine.

 $connections = array( 'master' => 'mysql://root:@master/dbname',
'slave\_1' => 'mysql://root:@slave1/dbname', 'slave\_2' =>
'mysql://root:@slave2/dbname', 'slave\_3' =>
'mysql://root:@slave3/dbname', 'slave\_4' =>
'mysql://root:@slave4/dbname' );

foreach ($connections as $name =>
:code:`dsn) { Doctrine_Manager::connection(`\ dsn, $name); }

Now that we have one master connection and four slaves setup we can
override the :php:class:`Doctrine_Record` and Doctrine\_Query classes to add our
logic for switching between the connections for read and write
functionality. All writes will go to the master connection and all reads
will be randomly distributed across the available slaves.

Lets start by adding our logic to Doctrine\_Query by extending it with
our own MyQuery class and switching the connection in the preQuery()
hook.

 class MyQuery extends Doctrine\_Query { // Since php doesn't support
late static binding in 5.2 we need to override // this method to
instantiate a new MyQuery instead of Doctrine\_Query public static
function create(:code:`conn = null) { return new MyQuery(`\ conn); }

::

    public function preQuery()
    {
        // If this is a select query then set connection to one of the slaves
        if ($this->getType() == Doctrine_Query::SELECT) {
            $this->_conn = Doctrine_Manager::getInstance()->getConnection('slave_' . rand(1, 4));
        // All other queries are writes so they need to go to the master
        } else {
            $this->_conn = Doctrine_Manager::getInstance()->getConnection('master');
        }
    }

}

Now we have queries taken care of, but what about when saving records?
We can force the connection for writes to the master by overriding
Doctrine\_Record and using it as the base for all of our models.

 abstract class MyRecord extends Doctrine\_Record { public function
save(Doctrine\_Connection
:code:`conn = null) { // If specific connection is not provided then lets force the connection // to be the master if (`\ conn
=== null) {
:code:`conn = Doctrine_Manager::getInstance()->getConnection('master'); } parent::save(`\ conn);
} }

All done! Now reads will be distributed to the slaves and writes are
given to the master connection. Below are some examples of what happens
now when querying and saving records.

First we need to setup a model to test with.

 class User extends MyRecord { public function setTableDefinition() {
$this->setTableName('user'); $this->hasColumn('username', 'string', 255,
array('type' => 'string', 'length' => '255'));
$this->hasColumn('password', 'string', 255, array('type' => 'string',
'length' => '255')); } }

 // The save() method will happen on the master connection because it is
a write $user = new User(); $user->username = 'jwage'; $user->password =
'changeme'; $user->save();

// This query goes to one of the slaves because it is a read $q = new
MyQuery(); $q->from('User u'); $users = $q->execute();

print\_r($users->toArray(true));

// This query goes to the master connection because it is a write $q =
new MyQuery(); $q->delete('User') ->from('User u') ->execute();
