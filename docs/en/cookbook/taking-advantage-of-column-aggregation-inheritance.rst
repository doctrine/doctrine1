==================================================
Taking Advantage of Column Aggregation Inheritance
==================================================

First, let me give a brief explanation of what column aggregation
inheritance is and how it works. With column aggregation inheritance all
classes share the same table, and all columns must exist in the parent.
Doctrine is able to know which class each row in the database belongs to
by automatically setting a "type" column so that Doctrine can cast the
correct class when hydrating data from the database. Even if you query
the top level column aggregation class, the collection will return
instances of the class that each row belongs to.

Now that you have a basic understand of column aggregation inheritance
lets put it to use. In this example we will setup some models which will
allow us to use one address table for storing all of our addresses
across the entire application. Any record will be able to have multiple
addresses, and all the information will be stored in one table. First
lets define our Address

 class Address extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('address1', 'string', 255);
$this->hasColumn('address2', 'string', 255);
$this->hasColumn('address3', 'string', 255); $this->hasColumn('city',
'string', 255); $this->hasColumn('state', 'string', 2);
$this->hasColumn('zipcode', 'string', 15); $this->hasColumn('type',
'string', 255); $this->hasColumn('record\_id', 'integer');

::

        $this->option('export', 'tables');

        $this->setSubClasses(array('UserAddress'    => array('type' => 'UserAddress'),
                                   'CompanyAddress' => array('type' => 'CompanyAddress')));
    }

}

Note the option set above to only export tables because we do not want
to export any foreign key constraints since record\_id is going to
relate to many different records.

We are going to setup a User so it can have multiple addresses, so we
will need to setup a UserAddress child class that User can relate to.

 class UserAddress extends Address { public function setUp() {
$this->hasOne('User', array('local' => 'record\_id', 'foreign' =>
'id')); } }

Now lets define our User and link it to the UserAddress model so it can
have multiple addresses. class User extends Doctrine\_Record { public
function setTableDefinition() { $this->hasColumn('username', 'string',
255); $this->hasColumn('password', 'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('UserAddress as Addresses', array('local'    => 'id',
                                                         'foreign'  => 'record_id'));
    }

}

Now say we have a Company record which also needs ot have many
addresses. First we need to setup the CompanyAddress child class

 class CompanyAddress extends Address { public function setUp() {
$this->hasOne('Company', array('local' => 'record\_id', 'foreign' =>
'id')); } }

Now lets define our Company and link it to the CompanyAddress model so
it can have multiple addresses. class Company extends Doctrine\_Record {
public function setTableDefinition() { $this->hasColumn('name',
'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('CompanyAddress as Addresses', array('local'    => 'id',
                                                            'foreign'  => 'record_id'));
    }

}

Now both Users and Companies can have multiple addresses and the data is
all stored in one address table.

Now lets create the tables and insert some records

 Doctrine\_Core::createTablesFromArray(array('User', 'Company',
'Address'));

$user = new User(); $user->username = 'jwage'; $user->password =
'changeme'; $user->Addresses[0]->address1 = '123 Road Dr.';
$user->Addresses[0]->city = 'Nashville'; $user->Addresses[0]->state =
'TN'; $user->save();

$company = new Company(); $company->name = 'centre{source}';
$company->Addresses[0]->address1 = '123 Road Dr.';
$company->Addresses[0]->city = 'Nashville';
$company->Addresses[0]->state = 'TN'; $company->save();

Query for the user and its addresses

 $users = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Addresses a') ->execute();

echo $users[0]->username; // jwage echo
:code:`users[0]->Addresses[0]->address1 = '123 Road Dr.'; echo get_class(`\ users[0]->Addresses[0]);
// UserAddress

Query for the company and its addresses

 $companies = Doctrine\_Query::create() ->from('Company c')
->leftJoin('c.Addresses a') ->execute();

echo $companies[0]->name; // centre{source} echo
:code:`companies[0]->Addresses[0]->address1 = '123 Road Dr.'; echo get_class(`\ companies[0]->Addresses[0]);
// CompanyAddress

Now lets query the Addresses directly and you will notice each child
record returned is hydrated as the appropriate child class that created
the record initially.


:code:`addresses = Doctrine_Query::create() ->from('Address a') ->execute(); echo get_class(`\ addresses[0]);
// UserAddress echo get\_class($addresses[1]); // CompanyAddress
