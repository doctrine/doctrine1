.. vim: set ft=rst tw=4 sw=4 et :

========================
CodeIgniter and Doctrine
========================

This tutorial will get you started using Doctrine with Code Igniter

-----------------
Download Doctrine
-----------------

First we must get the source of Doctrine from svn and place it in the
system/database folder.

 $ cd system/database $ svn co
http://svn.doctrine-project.org/branches/0.11/lib doctrine $ cd ..

// If you use svn in your project you can set Doctrine // as an external
so you receive bug fixes automatically from svn $ svn propedit
svn:externals database

// In your favorite editor add the following line // doctrine
http://svn.doctrine-project.org/branches/0.11/lib

--------------
Setup Doctrine
--------------

Now we must setup the configuration for Doctrine and load it in
system/application/config/database.php

 $ vi application/config/database.php

The code below needs to be added under this line of code

 $db['default']['cachedir'] = "";

Add this code // Create dsn from the info above $db['default']['dsn'] =
$db['default']['dbdriver'] . '://' . $db['default']['username'] . ':' .
$db['default']['password']. '@' . $db['default']['hostname'] . '/' .
$db['default']['database'];

// Require Doctrine.php require\_once(realpath(dirname(**FILE**) .
'/../..') . DIRECTORY\_SEPARATOR . 'database/doctrine/Doctrine.php');

// Set the autoloader spl\_autoload\_register(array('Doctrine',
'autoload'));

// Load the Doctrine connection
Doctrine\_Manager::connection($db['default']['dsn'],
$db['default']['database']);

// Set the model loading to conservative/lazy loading
Doctrine\_Manager::getInstance()->setAttribute(Doctrine\_Core::ATTR\_MODEL\_LOADING,
Doctrine\_Core::MODEL\_LOADING\_CONSERVATIVE);

// Load the models for the autoloader
Doctrine\_Core::loadModels(realpath(dirname(**FILE**) . '/..') .
DIRECTORY\_SEPARATOR . 'models');

Now we must make sure system/application/config/database.php is included
in your front controller. Open your front controller in your favorite
text editor.

 $ cd .. $ vi index.php

Change the last 2 lines of code of index.php with the following

 require\_once APPPATH.'config/database.php'; require\_once
BASEPATH.'codeigniter/CodeIgniter'.EXT;

----------------------------
Setup Command Line Interface
----------------------------

Create the following file: system/application/doctrine and chmod the
file so it can be executed. Place the code below in to the doctrine
file.

 $ vi system/application/doctrine

Place this code in system/application/doctrine

 #!/usr/bin/env php define('BASEPATH','.'); // mockup that this app was
executed from ci ;) chdir(dirname(**FILE**)); include('doctrine.php');

Now create the following file: system/application/doctrine.php. Place
the code below in to the doctrine.php file.

 require\_once('config/database.php');

// Configure Doctrine Cli // Normally these are arguments to the cli
tasks but if they are set here the arguments will be auto-filled $config
= array('data\_fixtures\_path' => dirname(**FILE**) .
DIRECTORY\_SEPARATOR . '/fixtures', 'models\_path' => dirname(**FILE**)
. DIRECTORY\_SEPARATOR . '/models', 'migrations\_path' =>
dirname(**FILE**) . DIRECTORY\_SEPARATOR . '/migrations', 'sql\_path' =>
dirname(**FILE**) . DIRECTORY\_SEPARATOR . '/sql', 'yaml\_schema\_path'
=> dirname(**FILE**) . DIRECTORY\_SEPARATOR . '/schema');

:code:`cli = new Doctrine_Cli(`\ config); :code:`cli->run(`\ \_SERVER['argv']);

Now we must create all the directories for Doctrine to use

 // Create directory for your yaml data fixtures files $ mkdir
system/application/fixtures

// Create directory for your migration classes $ mkdir
system/application/migrations

// Create directory for your yaml schema files $ mkdir
system/application/schema

// Create directory to generate your sql to create the database in $
mkdir system/application/sql

Now you have a command line interface ready to go. If you execute the
doctrine shell script with no argument you will get a list of available
commands

 $ cd system/application $ ./doctrine Doctrine Command Line Interface

./doctrine build-all ./doctrine build-all-load ./doctrine
build-all-reload ./doctrine compile ./doctrine create-db ./doctrine
create-tables ./doctrine dql ./doctrine drop-db ./doctrine dump-data
./doctrine generate-migration ./doctrine generate-migrations-db
./doctrine generate-migrations-models ./doctrine generate-models-db
./doctrine generate-models-yaml ./doctrine generate-sql ./doctrine
generate-yaml-db ./doctrine generate-yaml-models ./doctrine load-data
./doctrine migrate ./doctrine rebuild-db $

On Microsoft Windows, call the script via the PHP binary (because the
script won't invoke it automatically:

php.exe doctrine

--------------------
Start Using Doctrine
--------------------

It is simple to start using Doctrine now. First we must create a yaml
schema file. (save it at schema with filename like : user.yml) --- User:
columns: id: primary: true autoincrement: true type: integer(4)
username: string(255) password: string(255) relations: Groups: #
Relation alias or class name class: Group # Class name. Optional if
alias is the class name local: user\_id # Local: User.id =
UserGroup.user\_id. Optional foreign: group\_id # Foreign: Group.id =
UserGroup.group\_id. Optional refClass: UserGroup # xRefClass for
relating Users to Groups foreignAlias: Users # Opposite relationship
alias. Group hasMany Users

Group: tableName: groups columns: id: primary: true autoincrement: true
type: integer(4) name: string(255)

UserGroup: columns: user\_id: type: integer(4) primary: true group\_id:
type: integer(4) primary: true relations: User: local: user\_id # Local
key foreign: id # Foreign key onDelete: CASCADE # Database constraint
Group: local: group\_id foreign: id onDelete: CASCADE

Now if you run the following command it will generate your models in
system/application/models

 $ ./doctrine generate-models-yaml generate-models-yaml - Generated
models successfully from YAML schema

Now check the file system/application/models/generated/BaseUser.php. You
will see a compclass definition like below.

 /\*\* \* This class has been auto-generated by the Doctrine ORM
Framework \*/ abstract class BaseUser extends Doctrine\_Record {

public function setTableDefinition() { $this->setTableName('user');
$this->hasColumn('id', 'integer', 4, array('primary' => true,
'autoincrement' => true)); $this->hasColumn('username', 'string', 255);
$this->hasColumn('password', 'string', 255); }

public function setUp() { $this->hasMany('Group as Groups',
array('refClass' => 'UserGroup', 'local' => 'user\_id', 'foreign' =>
'group\_id'));

::

    $this->hasMany('UserGroup', array('local' => 'id',
                                      'foreign' => 'user_id'));

}

}

// Add custom methods to system/application/models/User.php

/\*\* \* This class has been auto-generated by the Doctrine ORM
Framework \*/ class User extends BaseUser { public function
setPassword($password) { :code:`this->password = md5(`\ password); } }

/\*\* \* This class has been auto-generated by the Doctrine ORM
Framework \*/ class UserTable extends Doctrine\_Table { public function
retrieveAll() { $query = new Doctrine\_Query(); $query->from('User u');
$query->orderby('u.username ASC');

::

    return $query->execute();

} }

Now we can create some sample data to load in to our application(this
step requires you have a valid database configured and ready to go. The
build-all-reload task will drop and recreate the database, create
tables, and load data fixtures

Create a file in system/application/fixtures/users.yml

 $ vi fixtures/users.yml

Add the following yaml to the file

User: jwage: username: jwage password: test

Now run the build-all-reload task to drop db, build models, recreate

 $ ./doctrine build-all-reload build-all-reload - Are you sure you wish
to drop your databases? (y/n) y build-all-reload - Successfully dropped
database named: "jwage\_codeigniter" build-all-reload - Generated models
successfully from YAML schema build-all-reload - Successfully created
database named: "jwage\_codeigniter" build-all-reload - Created tables
successfully build-all-reload - Data was successfully loaded

Now we are ready to use Doctrine in our actual actions. Lets open our
system/application/views/welcome\_message.php and somewhere add the
following code somewhere.

 $user = new User(); $user->username = 'zYne-';
$user->setPassword('password'); $user->save();

$userTable = Doctrine\_Core::getTable('User'); $user =
$userTable->findOneByUsername('zYne-');

echo $user->username; // prints 'zYne-'

$users = $userTable->retrieveAll();

echo :code:`users->count(); // echo '2'' foreach (`\ users as $user) {
echo $user->username; }
