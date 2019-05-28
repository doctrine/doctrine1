------------
Introduction
------------

This is a tutorial & how-to on creating your first project using the
fully featured PHP Doctrine ORM. This tutorial uses the the ready to go
Doctrine sandbox package. It requires a web server, PHP and PDO +
Sqlite.

--------
Download
--------

To get started, first download the latest Doctrine sandbox package:
http://www.doctrine-project.org/download. Second, extract the downloaded
file and you should have a directory named Doctrine-x.x.x-Sandbox.
Inside of that directory is a simple example implementation of a
Doctrine based web application.

````````````````
Package Contents
````````````````
The files/directory structure should look like the
following $ cd Doctrine-0.10.1-Sandbox $ ls config.php doctrine
index.php migrations schema data doctrine.php lib models

The sandbox does not require any configuration, it comes ready to use
with a sqlite database. Below is a description of each of the
files/directories and what its purpose is.

-  doctrine - Shell script for executing the command line interface. Run
   with ./doctrine to see a list of command or ./doctrine help to see a
   detailed list of the commands
-  doctrine.php - Php script which implements the Doctrine command line
   interface which is included in the above doctrine shell script
-  index.php - Front web controller for your web application
-  migrations - Folder for your migration classes
-  schema - Folder for your schema files
-  models - Folder for your model files
-  lib - Folder for the Doctrine core library files

---------------
Running the CLI
---------------

If you execute the doctrine shell script from the command line it will
output the following:

 $ ./doctrine Doctrine Command Line Interface

./doctrine build-all ./doctrine build-all-load ./doctrine
build-all-reload ./doctrine compile ./doctrine create-db ./doctrine
create-tables ./doctrine dql ./doctrine drop-db ./doctrine dump-data
./doctrine generate-migration ./doctrine generate-migrations-db
./doctrine generate-migrations-models ./doctrine generate-models-db
./doctrine generate-models-yaml ./doctrine generate-sql ./doctrine
generate-yaml-db ./doctrine generate-yaml-models ./doctrine load-data
./doctrine migrate ./doctrine rebuild-db

---------------
Defining Schema
---------------

Below is a sample yaml schema file to get started. You can place the
yaml file in schemas/schema.yml. The command line interface looks for
all \*.yml files in the schemas folder.

User: columns: id: primary: true autoincrement: true type: integer(4)
username: string(255) password: string(255) relations: Groups: class:
Group refClass: UserGroup foreignAlias: Users

Group: tableName: groups columns: id: primary: true autoincrement: true
type: integer(4) name: string(255)

UserGroup: columns: user\_id: integer(4) group\_id: integer(4)
relations: User: onDelete: CASCADE Group: onDelete: CASCADE

------------------
Test Data Fixtures
------------------

Below is a sample yaml data fixtures file. You can place this file in
data/fixtures/data.yml. The command line interface looks for all \*.yml
files in the data/fixtures folder.

User: zyne: username: zYne- password: changeme Groups: [founder, lead,
documentation] jwage: username: jwage password: changeme Groups: [lead,
documentation]

Group: founder: name: Founder lead: name: Lead documentation: name:
Documentation

-------------------------------------------------------------------
Building Everything Now that you have written your schema files and
-------------------------------------------------------------------
data fixtures, you can now build everything and begin working with your
models . Run the command below and your models will be generated in the
models folder.

 $ ./doctrine build-all-reload build-all-reload - Are you sure you wish
to drop your databases? (y/n) y build-all-reload - Successfully dropped
database for connection "sandbox" at path
"/Users/jwage/Sites/doctrine/branches/0.10/tools/sandbox/sandbox.db"
build-all-reload - Generated models successfully from YAML schema
build-all-reload - Successfully created database for connection
"sandbox" at path
"/Users/jwage/Sites/doctrine/branches/0.10/tools/sandbox/sandbox.db"
build-all-reload - Created tables successfully build-all-reload - Data
was successfully loaded

Take a peak in the models folder and you will see that the model classes
were generated for you. Now you can begin coding in your index.php to
play with Doctrine itself. Inside index.php place some code like the
following for a simple test.

-------------
Running Tests
-------------

 $query = new Doctrine\_Query(); $query->from('User u, u.Groups g');

$users = $query->execute();

echo '

.. raw:: html

   <pre>

'; print\_r($users->toArray(true));

The print\_r() should output the following data. You will notice that
this is the data that we populated by placing the yaml file in the
data/fixtures files. You can add more data to the fixtures and rerun the
build-all-reload command to reinitialize the database.

 Array ( [0] => Array ( [id] => 1 [username] => zYne- [password] =>
changeme [Groups] => Array ( [0] => Array ( [id] => 1 [name] => Founder
)

::

                    [1] => Array
                        (
                            [id] => 2
                            [name] => Lead
                        )

                    [2] => Array
                        (
                            [id] => 3
                            [name] => Documentation
                        )

                )

        )

    [1] => Array
        (
            [id] => 2
            [username] => jwage
            [password] => changeme
            [Groups] => Array
                (
                    [0] => Array
                        (
                            [id] => 2
                            [name] => Lead
                        )

                    [1] => Array
                        (
                            [id] => 3
                            [name] => Documentation
                        )

                )

        )

)

You can also issue DQL queries directly to your database by using the
dql command line function. It is used like the following.

 jwage:sandbox jwage$ ./doctrine dql "FROM User u, u.Groups g" dql -
executing: "FROM User u, u.Groups g" () dql - - dql - id: 1 dql -
username: zYne- dql - password: changeme dql - Groups: dql - - dql - id:
1 dql - name: Founder dql - - dql - id: 2 dql - name: Lead dql - - dql -
id: 3 dql - name: Documentation dql - - dql - id: 2 dql - username:
jwage dql - password: changeme dql - Groups: dql - - dql - id: 2 dql -
name: Lead dql - - dql - id: 3 dql - name: Documentation

`````````
User CRUD
`````````

Now we can demonstrate how to implement Doctrine in to a
super simple module for managing users and passwords. Place the
following code in your index.php and pull it up in your browser. You
will see the simple application.

 require\_once('config.php');

Doctrine\_Core::loadModels('models');

:code:`module = isset(`\ *REQUEST['module']) ?
$*REQUEST['module']:'users'; :code:`action = isset(`\ *REQUEST['action'])
? $*REQUEST['action']:'list';

if ($module == 'users') { :code:`userId = isset(`\ *REQUEST['id']) &&
$*REQUEST['id'] > 0 ? $\_REQUEST['id']:null; $userTable =
Doctrine\_Core::getTable('User');

::

    if ($userId === null) {
        $user = new User();
    } else {
        $user = $userTable->find($userId);
    }

    switch ($action) {
        case 'edit':
        case 'add':
            echo '<form action="index.php?module=users&action=save" method="POST">
                  <fieldset>
                    <legend>User</legend>
                    <input type="hidden" name="id" value="' . $user->id . '" />
                    <label for="username">Username</label> <input type="text" name="user[username]" value="' . $user->username . '" />
                    <label for="password">Password</label> <input type="text" name="user[password]" value="' . $user->password . '" />
                    <input type="submit" name="save" value="Save" />
                  </fieldset>
                  </form>';
            break;
        case 'save':
            $user->merge($_REQUEST['user']);
            $user->save();

            header('location: index.php?module=users&action=edit&id=' . $user->id);
            break;
        case 'delete':
            $user->delete();

            header('location: index.php?module=users&action=list');
            break;
        default:
            $query = new Doctrine_Query();
            $query->from('User u')
                  ->orderby('u.username');

            $users = $query->execute();

            echo '<ul>';
            foreach ($users as $user) {
                echo '<li><a href="index.php?module=users&action=edit&id=' . $user->id . '">' . $user->username . '</a> &nbsp; <a href="index.php?module=users&action=delete&id=' . $user->id . '">[X]</a></li>';
            }
            echo '</ul>';
    }

    echo '<ul>
            <li><a href="index.php?module=users&action=add">Add</a></li>
            <li><a href="index.php?module=users&action=list">List</a></li>
          </ul>';

} else { throw new Exception('Invalid module'); }
