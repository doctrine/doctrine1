====================
ユーザー管理システム
====================

ほとんどすべてのアプリケーションではユーザー、ロール、パーミッションなど何らかのセキュリティもしくは認証システムを提供する必要があります。基本的なユーザー管理とセキュリティシステムを提供するいくつかのモデルの例です。

 class User extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255,
array( 'unique' => true ) ); $this->hasColumn('password', 'string',
255); } }

class Role extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); } }

class Permission extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); } }

class RolePermission extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('role\_id', 'integer', null,
array( 'primary' => true ) ); $this->hasColumn('permission\_id',
'integer', null, array( 'primary' => true ) ); }

::

    public function setUp()
    {
        $this->hasOne('Role', array(
                'local' => 'role_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne('Permission', array(
                'local' => 'permission_id',
                'foreign' => 'id'
            )
        );
    }

}

class UserRole extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'primary' => true ) ); $this->hasColumn('role\_id', 'integer',
null, array( 'primary' => true ) ); }

::

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne('Role', array(
                'local' => 'role_id',
                'foreign' => 'id'
            )
        );
    }

}

class UserPermission extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'primary' => true ) ); $this->hasColumn('permission\_id',
'integer', null, array( 'primary' => true ) ); }

::

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne('Permission', array(
                'local' => 'permission_id',
                'foreign' => 'id'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 User: columns: username: string(255) password: string(255) relations:
Roles: class: Role refClass: UserRole foreignAlias: Users Permissions:
class: Permission refClass: UserPermission foreignAlias: Users

Role: columns: name: string(255) relations: Permissions: class:
Permission refClass: RolePermission foreignAlias: Roles

Permission: columns: name: string(255)

RolePermission: columns: role\_id: type: integer primary: true
permission\_id: type: integer primary: true relations: Role: Permission:

UserRole: columns: user\_id: type: integer primary: true role\_id: type:
integer primary: true relations: User: Role:

UserPermission: columns: user\_id: type: integer primary: true
permission\_id: type: integer primary: true relations: User: Permission:

==========================
フォーラムアプリケーション
==========================

カテゴリ、ボードと、スレッドと投稿機能を持つフォーラムアプリケーションの例は次の通りです:

 class Forum\_Category extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('root\_category\_id', 'integer',
10); $this->hasColumn('parent\_category\_id', 'integer', 10);
$this->hasColumn('name', 'string', 50); $this->hasColumn('description',
'string', 99999); }

::

    public function setUp()
    {
        $this->hasMany('Forum_Category as Subcategory', array(
                'local' => 'parent_category_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne('Forum_Category as Rootcategory', array(
                'local' => 'root_category_id',
                'foreign' => 'id'
            )
        );
    }

}

class Forum\_Board extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('category\_id', 'integer', 10);
$this->hasColumn('name', 'string', 100); $this->hasColumn('description',
'string', 5000); }

::

    public function setUp()
    {
        $this->hasOne('Forum_Category as Category', array(
                'local' => 'category_id',
                'foreign' => 'id'
            )
        );
        $this->hasMany('Forum_Thread as Threads',  array(
                'local' => 'id',
                'foreign' => 'board_id'
            )
        );
    } 

}

class Forum\_Entry extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('author', 'string', 50);
$this->hasColumn('topic', 'string', 100); $this->hasColumn('message',
'string', 99999); $this->hasColumn('parent\_entry\_id', 'integer', 10);
$this->hasColumn('thread\_id', 'integer', 10); $this->hasColumn('date',
'integer', 10); }

::

    public function setUp()
    {
        $this->hasOne('Forum_Entry as Parent',  array(
                'local' => 'parent_entry_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne('Forum_Thread as Thread', array(
                'local' => 'thread_id',
                'foreign' => 'id'
            )
        );
    }

}

class Forum\_Thread extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('board\_id', 'integer', 10);
$this->hasColumn('updated', 'integer', 10); $this->hasColumn('closed',
'integer', 1); }

::

    public function setUp()
    {
        $this->hasOne('Forum_Board as Board', array(
                'local' => 'board_id',
                'foreign' => 'id'
            )
        );

        $this->ownsMany('Forum_Entry as Entries', array(
                'local' => 'id',
                'foreign' => thread_id'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Forum\_Category: columns: root\_category\_id: integer(10)
parent\_category\_id: integer(10) name: string(50) description:
string(99999) relations: Subcategory: class: Forum\_Category local:
parent\_category\_id foreign: id Rootcategory: class: Forum\_Category
local: root\_category\_id foreign: id

Forum\_Board: columns: category\_id: integer(10) name: string(100)
description: string(5000) relations: Category: class: Forum\_Category
local: category\_id foreign: id Threads: class: Forum\_Thread local: id
foreign: board\_id

Forum\_Entry: columns: author: string(50) topic: string(100) message:
string(99999) parent\_entry\_id: integer(10) thread\_id: integer(10)
date: integer(10) relations: Parent: class: Forum\_Entry local:
parent\_entry\_id foreign: id Thread: class: Forum\_Thread local:
thread\_id foreign: id

Forum\_Thread: columns: board\_id: integer(10) updated: integer(10)
closed: integer(1) relations: Board: class: Forum\_Board local:
board\_id foreign: id Entries: class: Forum\_Entry local: id foreign:
thread\_id

======
まとめ
======

これらの実際の世界のスキーマの例によってDoctrineの実際のアプリケーションを使うことに役立つことを願っております。この本の最後の章では[doc
coding-standards
コーディング規約]を検討します。コーディング規約はあなたのアプリケーションにもお勧めします。コードの一貫性が大切であることを覚えておいてください！
