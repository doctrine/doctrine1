*******************
Real World Examples
*******************

======================
User Management System
======================

In almost all applications you need to have some kind of security or
authentication system where you have users, roles, permissions, etc.
Below is an example where we setup several models that give you a basic
user management and security system.

::

    class User extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'username', 'string', 255,
                array(
                    'unique' => true,
                )
            );
            $this->hasColumn( 'password', 'string', 255 );
        }
    }

    class Role extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'name', 'string', 255 );
        }
    }

    class Permission extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'name', 'string', 255 );
        }
    }

    class RolePermission extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'role_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
            $this->hasColumn( 'permission_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
        }

        public function setUp()
        {
            $this->hasOne( 'Role',
                array(
                    'local'   => 'role_id',
                    'foreign' => 'id',
                )
            );
            $this->hasOne( 'Permission',
                array(
                    'local'   => 'permission_id',
                    'foreign' => 'id',
                )
            );
        }
    }

    class UserRole extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'user_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
            $this->hasColumn( 'role_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
        }

        public function setUp()
        {
            $this->hasOne( 'User',
                array(
                    'local'   => 'user_id',
                    'foreign' => 'id',
                )
            );
            $this->hasOne( 'Role',
                array(
                    'local'   => 'role_id',
                    'foreign' => 'id',
                )
            );
        }
    }

    class UserPermission extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'user_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
            $this->hasColumn( 'permission_id', 'integer', null,
                array(
                    'primary' => true,
                )
            );
        }

        public function setUp()
        {
            $this->hasOne( 'User',
                array(
                    'local'   => 'user_id',
                    'foreign' => 'id',
                )
            );
            $this->hasOne( 'Permission',
                array(
                    'local'   => 'permission_id',
                    'foreign' => 'id',
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    User:
      columns:
        username: string(255)
        password: string(255)
      relations:
        Roles:
          class: Role
          refClass: UserRole
          foreignAlias: Users
        Permissions:
          class: Permission
          refClass: UserPermission
          foreignAlias: Users

    Role:
      columns:
        name: string(255)
      relations:
        Permissions:
          class: Permission
          refClass: RolePermission
          foreignAlias: Roles

    Permission:
      columns:
        name: string(255)

    RolePermission:
      columns:
        role_id:
          type: integer
          primary: true
        permission_id:
          type: integer
          primary: true
      relations:
        Role:
        Permission:

    UserRole:
      columns:
        user_id:
          type: integer
          primary: true
        role_id:
          type: integer
          primary: true
      relations:
        User:
        Role:

    UserPermission:
      columns:
        user_id:
          type: integer
          primary: true
        permission_id:
          type: integer
          primary: true
      relations:
        User:
        Permission:

=================
Forum Application
=================

Below is an example of a forum application where you have categories,
boards, threads and posts:

::

    class Forum_Category extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'root_category_id', 'integer', 10 );
            $this->hasColumn( 'parent_category_id', 'integer', 10 );
            $this->hasColumn( 'name', 'string', 50 );
            $this->hasColumn( 'description', 'string', 99999 );
        }

        public function setUp()
        {
            $this->hasMany( 'Forum_Category as Subcategory',
                array(
                    'local'   => 'parent_category_id',
                    'foreign' => 'id',
                )
            );
            $this->hasOne( 'Forum_Category as Rootcategory',
                array(
                    'local'   => 'root_category_id',
                    'foreign' => 'id',
                )
            );
        }
    }

    class Forum_Board extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'category_id', 'integer', 10 );
            $this->hasColumn( 'name', 'string', 100 );
            $this->hasColumn( 'description', 'string', 5000 );
        }

        public function setUp()
        {
            $this->hasOne( 'Forum_Category as Category',
                array(
                    'local'   => 'category_id',
                    'foreign' => 'id',
                )
            );
            $this->hasMany( 'Forum_Thread as Threads',
                array(
                    'local'   => 'id',
                    'foreign' => 'board_id'
                )
            );
        }
    }

    class Forum_Entry extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'author', 'string', 50 );
            $this->hasColumn( 'topic', 'string', 100 );
            $this->hasColumn( 'message', 'string', 99999 );
            $this->hasColumn( 'parent_entry_id', 'integer', 10 );
            $this->hasColumn( 'thread_id', 'integer', 10 );
            $this->hasColumn( 'date', 'integer', 10 );
        }

        public function setUp()
        {
            $this->hasOne( 'Forum_Entry as Parent',
                array(
                    'local'   => 'parent_entry_id',
                    'foreign' => 'id',
                )
            );
            $this->hasOne( 'Forum_Thread as Thread',
                array(
                    'local'   => 'thread_id',
                    'foreign' => 'id',
                )
            );
        }
    }

    class Forum_Thread extends Doctrine_Record
    {
        public function setTableDefinition()
        {
            $this->hasColumn( 'board_id', 'integer', 10 );
            $this->hasColumn( 'updated', 'integer', 10 );
            $this->hasColumn( 'closed', 'integer', 1 );
        }

        public function setUp()
        {
            $this->hasOne( 'Forum_Board as Board',
                array(
                    'local'   => 'board_id',
                    'foreign' => 'id',
                )
            );
            $this->hasMany( 'Forum_Entry as Entries',
                array(
                    'local'   => 'id',
                    'foreign' => 'thread_id',
                )
            );
        }
    }

Here is the same example in YAML format. You can read more about YAML in
the :doc:`yaml-schema-files` chapter:

.. code-block:: yaml

    ---
    Forum_Category:
      columns:
        root_category_id: integer(10)
        parent_category_id: integer(10)
        name: string(50)
        description: string(99999)
      relations:
        Subcategory:
          class: Forum_Category
          local: parent_category_id
          foreign: id
        Rootcategory:
          class: Forum_Category
          local: root_category_id
          foreign: id

    Forum_Board:
      columns:
        category_id: integer(10)
        name: string(100)
        description: string(5000)
      relations:
        Category:
          class: Forum_Category
          local: category_id
          foreign: id
        Threads:
          class: Forum_Thread
          local: id
          foreign: board_id

    Forum_Entry:
      columns:
        author: string(50)
        topic: string(100)
        message: string(99999)
        parent_entry_id: integer(10)
        thread_id: integer(10)
        date: integer(10)
      relations:
        Parent:
          class: Forum_Entry
          local: parent_entry_id
          foreign: id
        Thread:
          class: Forum_Thread
          local: thread_id
          foreign: id

    Forum_Thread:
      columns:
        board_id: integer(10)
        updated: integer(10)
        closed: integer(1)
      relations:
        Board:
          class: Forum_Board
          local: board_id
          foreign: id
        Entries:
          class: Forum_Entry
          local: id
          foreign: thread_id

==========
Conclusion
==========

I hope that these real world schema examples will help you with using
Doctrine in the real world in your application. The last chapter of this
book will discuss the :doc:`coding standards <coding-standards>` used in
Doctrine and are recommended for you to use in your application as well.
Remember, consistency in your code is key!