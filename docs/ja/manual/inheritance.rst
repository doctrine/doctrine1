Doctrineは3種類の継承戦略をサポートします。これらの戦略は混ぜることができます。三種類は単一、具象とカラム集約です。これらの異なる継承の使い方をこの章で学びます。

この章では前の章で利用してきたテスト環境の既存のすべてのスキーマとモデルを削除してください。

 $ rm schema.yml $ touch schema.yml $ rm -rf models/\*

========
単一継承
========

単一継承は最も簡単でシンプルです。単一継承においてすべての子クラスは同じカラムを親として共有しすべての情報は親テーブルに保存されます。

 // models/Entity.php

class Entity extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 30);
$this->hasColumn('username', 'string', 20); $this->hasColumn('password',
'string', 16); $this->hasColumn('created\_at', 'timestamp');
$this->hasColumn('update\_at', 'timestamp'); } }

``Entity``を継承する``User``モデルを作りましょう:

 // models/User.php

class User extends Entity { }

``Group``モデルに対しても同じことを行いましょう:

 // models/Group.php

class Group extends Entity { }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Entity: columns: name: string(30) username: string(20) password:
string(16) created\_at: timestamp updated\_at: timestamp

User: inheritance: extends: Entity type: simple

Group: inheritance: extends: Entity type: simple

上記のコードによって生成されたSQLを確認してみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('Entity',
'User', 'Group')); echo $sql[0];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE entity (id BIGINT AUTO\_INCREMENT, username VARCHAR(20),
password VARCHAR(16), created\_at DATETIME, updated\_at DATETIME, type
VARCHAR(255), name VARCHAR(30), PRIMARY KEY(id)) ENGINE = INNODB

    **NOTE**
    YAMLスキーマファイルを使うとき子クラスでカラムを定義できますがYAMLが解析されるときカラムは自動的に親に移動します。これはカラムを簡単に組織できるようにするためだけです。

========
具象継承
========

具象継承は子クラス用の独立したテーブルを作成します。しかしながら具象継承ではそれぞれのクラスはすべてのカラムを含むテーブルを生成します(継承されたカラムを含む)。具象継承を使うには下記で示されるように子クラスへの明示的な``parent::setTableDefinition()``を追加する必要があります。

 // models/TextItem.php

class TextItem extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('topic', 'string', 100); } }

``TextItem``を継承する``Comment``という名前のモデルを作り``content``という名前のカラムを追加してみましょう:

 // models/Comment.php

class Comment extends TextItem { public function setTableDefinition() {
parent::setTableDefinition();

::

        $this->hasColumn('content', 'string', 300);
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

TextItem: columns: topic: string(100)

Comment: inheritance: extends: TextItem type: concrete columns: content:
string(300)

上記のモデルによって生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine::generateSqlFromArray(array('TextItem',
'Comment')); echo $sql[0] . ""; echo $sql[1];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE text\_item (id BIGINT AUTO\_INCREMENT, topic VARCHAR(100),
PRIMARY KEY(id)) ENGINE = INNODB CREATE TABLE comment (id BIGINT
AUTO\_INCREMENT, topic VARCHAR(100), content TEXT, PRIMARY KEY(id))
ENGINE = INNODB

具象クラスにおいて追加のカラムを定義する必要はありませんが、それぞれのクラス用に個別のテーブルを作るには``setTableDefinition()``の呼び出しを繰り返し書かなければなりません。

次の例では``entity``、``user``と``group``と呼ばれるデータベーステーブルがあります。``Users``と``groups``は両方とも``entities``です。行わなければならないことは3つのクラス(``Entity``、``Group``と``User``)を書き``setTableDefinition()``メソッドの呼び出しを繰り返し記述することです。

 // models/Entity.php

class Entity extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 30);
$this->hasColumn('username', 'string', 20); $this->hasColumn('password',
'string', 16); $this->hasColumn('created', 'integer', 11); } }

// models/User.php

class User extends Entity { public function setTableDefinition() { //
次のメソッド呼び出しは // 具象継承で必要 parent::setTableDefinition(); }
}

// models/Group.php class Group extends Entity { public function
setTableDefinition() { // 次のメソッド呼び出しは // 具象継承で必要
parent::setTableDefinition(); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Entity: columns: name: string(30) username: string(20) password:
string(16) created: integer(11)

User: inheritance: extends: Entity type: concrete

Group: tableName: groups inheritance: extends: Entity type: concrete

上記のモデルによって生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine::generateSqlFromArray(array('Entity', 'User',
'Group')); echo $sql[0] . ""; echo $sql[1] . ""; echo $sql[2] . "";

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE user (id BIGINT AUTO\_INCREMENT, name VARCHAR(30),
username VARCHAR(20), password VARCHAR(16), created BIGINT, PRIMARY
KEY(id)) ENGINE = INNODB CREATE TABLE groups (id BIGINT AUTO\_INCREMENT,
name VARCHAR(30), username VARCHAR(20), password VARCHAR(16), created
BIGINT, PRIMARY KEY(id)) ENGINE = INNODB CREATE TABLE entity (id BIGINT
AUTO\_INCREMENT, name VARCHAR(30), username VARCHAR(20), password
VARCHAR(16), created BIGINT, PRIMARY KEY(id)) ENGINE = INNODB

==========
カラム集約
==========

次の例において``entity``という名前の1つのデータベーステーブルがあります。``Users``と``groups``は両方とも``entities``でこれらは同じデータベーステーブルを共有します。

``entity``テーブルは``type``と呼ばれる1つのカラムを持ちます。このカラムは``group``もしくは``user``であることを伝えます。``users``はタイプ1でグループはタイプ2であると決めます。

行わなければならない唯一の作業は3のレコード(以前と同じ)を作成し親クラスからの``Doctrine_Table::setSubclasses()``メソッド呼び出しを追加することです。

 // models/Entity.php

class Entity extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 30);
$this->hasColumn('username', 'string', 20); $this->hasColumn('password',
'string', 16); $this->hasColumn('created\_at', 'timestamp');
$this->hasColumn('update\_at', 'timestamp');

::

        $this->setSubclasses(array(
                'User'  => array('type' => 1),
                'Group' => array('type' => 2)
            )
        );
    }

}

// models/User.php class User extends Entity { }

// models/Group.php class Group extends Entity { }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Entity: columns: username: string(20) password: string(16) created\_at:
timestamp updated\_at: timestamp

User: inheritance: extends: Entity type: column\_aggregation keyField:
type keyValue: 1

Group: inheritance: extends: Entity type: column\_aggregation keyField:
type keyValue: 2

上記のモデルによって生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine::generateSqlFromArray(array('Entity', 'User',
'Group')); echo $sql[0];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE entity (id BIGINT AUTO\_INCREMENT, username VARCHAR(20),
password VARCHAR(16), created\_at DATETIME, updated\_at DATETIME, type
VARCHAR(255), PRIMARY KEY(id)) ENGINE = INNODB

    **NOTE**
    ``type``カラムが自動的に追加されたことに注目してください。データベースのそれぞれのレコードが所属するモデルを知っているカラム集約継承です。

この機能によって``Entity``テーブルにクエリを行い変えされたオブジェクトが親クラスで設定された制約にマッチする場合``User``もしくは``Group``オブジェクトを戻します。

具体的な内容は下記のコードで見てみましょう。最初に新しい``User``オブジェクトを保存しましょう:

 // test.php

// ... $user = new User(); $user->name = 'Bjarte S. Karlsen';
$user->username = 'meus'; $user->password = 'rat'; $user->save();

新しい``Group``オブジェクトを保存しましょう:

 // test.php

// ... $group = new Group(); $group->name = 'Users'; $group->username =
'users'; $group->password = 'password'; $group->save();

作成した``User``のid用の``Entity``モデルにクエリを行うと、``Doctrine_Query``は``User``のインスタンスを返します。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('Entity e') ->where('e.id =
?');

$user = :code:`q->fetchOne(array(`\ user->id));

echo get\_class($user); // User

``Group``レコードに対して同じようなことを行うと、``Group``のインスタンスが戻されます。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('Entity e') ->where('e.id =
?');

$group = :code:`q->fetchOne(array(`\ group->id));

echo get\_class($group); // Group

    **NOTE**
    上記の内容は``type``カラムであるから可能です。Doctrineはどのクラスによってそれぞれのレコードが作成されたのか知っているので、データは適切なサブクラスにハイドレイトされます。

個別の``User``もしくは``Group``モデルにクエリを行うこともできます:

 $q = Doctrine\_Query::create() ->select('u.id') ->from('User u');

echo $q->getSqlQuery();

上記の``getSql()``の呼び出しは次のSQLクエリを出力します:

 SELECT e.id AS e\_\_id FROM entity e WHERE (e.type = '1')

    **NOTE**
    ``User``型であるレコードのみが返されるように``type``の条件が自動的に追加されたことに注目してください。

======
まとめ
======

モデルでPHPの継承機能を利用する方法を学んだので[doc behaviors
:name]の章に移動します。これは複雑なモデルを小さくて簡単なコードで維持するためのもっとも洗練された便利な機能の1つです。
