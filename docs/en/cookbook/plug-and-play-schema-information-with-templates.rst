***********************************************
Plug and Play Schema Information with Templates
***********************************************

Doctrine templates essentially allow you to extract schema information
so that it can be plugged in to multiple Doctrine classes without having
to duplicate any code. Below we will show some examples of what a
template could be used for and how it can make your schema easier to
maintain.

Let's get started. Imagine a project where you have multiple records
which must have address attributes. Their are two basic approaches to
solving this problem. One is to have a single table to store all
addresses and each record will store a foreign key to the address record
it owns. This is the "normalized" way of solving the problem. The
"de-normalized" way would be to store the address attributes with each
record. In this example a template will extract the attributes of an
address and allow you to plug them in to as many Doctrine classes as you
like.

First we must define the template so that we can use it in our Doctrine
classes.

 class Doctrine\_Template\_Address extends Doctrine\_Template { public
function setTableDefinition() { $this->hasColumn('address1', 'string',
255); $this->hasColumn('address2', 'string', 255);
$this->hasColumn('address3', 'string', 255); $this->hasColumn('city',
'string', 255); $this->hasColumn('state', 'string', 2);
$this->hasColumn('zipcode', 'string', 15); } }

Now that we have our template defined, lets define some basic models
that need to have address attributes added to them. Lets start first
with a User.

 class User extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255);
$this->hasColumn('password', 'string', 255); }

::

    public function setUp()
    {
        $this->actAs('Address');
    }

}

Now we also have a Company model which also must contain an address.

 class Company extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255);
$this->hasColumn('description', 'clob'); }

::

    public function setUp()
    {
        $this->actAs('Address');
    }

}

Now lets generate the SQL to create the tables for the User and Company
model. You will see that the attributes from the template are
automatically added to each table.

 CREATE TABLE user (id BIGINT AUTO\_INCREMENT, username VARCHAR(255),
password VARCHAR(255), address1 VARCHAR(255), address2 VARCHAR(255),
address3 VARCHAR(255), city VARCHAR(255), state VARCHAR(2), zipcode
VARCHAR(15), PRIMARY KEY(id)) ENGINE = INNODB

CREATE TABLE company (id BIGINT AUTO\_INCREMENT, name VARCHAR(255),
description LONGTEXT, address1 VARCHAR(255), address2 VARCHAR(255),
address3 VARCHAR(255), city VARCHAR(255), state VARCHAR(2), zipcode
VARCHAR(15), PRIMARY KEY(id)) ENGINE = INNODB

That's it. Now you can maintain your Address schema information from one
place and use the address functionality in as many places as you like.
