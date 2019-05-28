Doctrine Database Abstraction
Layerは内臓されるフレームワークで、使用しているデータベースとコミュニケーションを行いデータベースタイプに応じて適切なSQLを送信するためにORMが使用しています。このフレームワークはデータベースが持つテーブルもしくはテーブルが持つフィールドのような情報をデータベースに問い合わせる機能も持ち、ORMが既存のデータベースからモデルを簡単に生成できる手段です。

このレイヤーはORMから独立して使うことができます。例えば既存のアプリケーションがPDOを直接使用しこれをDoctrine
ConnectionsとDBALを使うためにこれを移植したい場合に役立ちます。後のフェーズで新しいことのためにORMを使い始めたりORMで使えるように古いピースを書き直したりします。

DBALは少数の異なるモジュールで構成されます。この章では異なるモジュールとそれらが担っている仕事を検討します

============
エクスポート
============

Exportモジュールはデータベース構造を管理するためのメソッドを提供します。メソッドはそれぞれの責務に基づいて分類できます。例えばデータベースの要素をcreate、edit(alterもしくはupdate)、listもしくはdelete
(drop)するなどです。以下のドキュメントでは利用可能なメソッドの一覧と使い方の例を示します。

--------
はじめに
--------

Exportモジュールでメソッドを変更するすべてのスキーマはalterオペレーションのために使われるSQLを返す同等物を持ちます。例えば``createTable()``は``createTableSql()``によって返されるクエリを実行します。

この章では``events_db``という名前のデータベースで、次のテーブルが作成され、変更され最後には削除されます:

**events**

\|\|~ 名前 \|\|~ 型 \|\|~ Primary \|\|~ Auto Increment \|\| \|\| ``id``
\|\| ``integer`` \|\| ``true`` \|\| ``true`` \|\| \|\| ``name`` \|\|
``string(255)`` \|\| ``false`` \|\| ``false`` \|\| \|\| ``datetime``
\|\| ``timestamp`` \|\| ``false`` \|\| ``false`` \|\|

**people**

\|\|~ 名前 \|\|~ 型 \|\|~ Primary \|\|~ Auto Increment \|\| \|\| ``id``
\|\| ``integer`` \|\| ``true`` \|\| ``true`` \|\| \|\| ``name`` \|\|
``string(255)`` \|\| ``false``\|\| ``false`` \|\|

**event\_participants**

\|\|~ 名前 \|\|~ 型 \|\|~ Primary \|\|~ Auto Increment \|\| \|\|
``event_id`` \|\| ``integer`` \|\| ``true`` \|\| ``false`` \|\| \|\|
``person_id`` \|\| ``string(255)`` \|\| ``true`` \|\| ``false`` \|\|

----------------------
データベースを作成する
----------------------

Doctrineで新しいデータベースを作成するのはシンプルです。作成するデータベースの名前を引数にして``createDatabase()``メソッドを呼び出すだけです。

 // test.php

// ... $conn->export->createDatabase('events\_db');

新しい``events_db``に接続するために``bootstrap.php``ファイルの接続を変更してみましょう:

 // bootstrap.php

/\*\* \* Bootstrap Doctrine.php, register autoloader and specify \*
configuration attributes \*/

// ...

$conn =
Doctrine\_Manager::connection('mysql://root:@localhost/events\_db',
'doctrine');

// ...

------------------
テーブルを作成する
------------------

データベースは作成され接続を再設定しました。テーブルに追加に移ります。``createTable()``メソッドは3つのパラメータ:
テーブルの名前、フィールド定義の配列、と追加オプション(オプションでRDBMS固有)を受けとります。

``events``テーブルを作成しましょう:

 // test.php

// $definition = array( 'id' => array( 'type' => 'integer', 'primary' =>
true, 'autoincrement' => true ), 'name' => array( 'type' => 'string',
'length' => 255 ), 'datetime' => array( 'type' => 'timestamp' ) );

$conn->export->createTable('events', $definition);

定義配列のキーはテーブルのフィールド名です。値は他のキーと同じように必須キーの``type``を格納する配列で、``type``の値によって、``type``キーの値はDoctrineのデータ型と同じものが可能です。データ型によって、他のオプションが変わることがあります。

\|\|~ データ型 \|\|~ 長さ \|\|~ デフォルト \|\|~ not null \|\|~ unsigned
\|\|~ autoincrement \|\| \|\| ``string`` \|\| x \|\| x \|\| x \|\| \|\|
\|\| \|\| ``boolean`` \|\| \|\| x \|\| x \|\| \|\| \|\| \|\| ``integer``
\|\| x \|\| x \|\| x \|\| x \|\| x \|\| \|\| ``decimal`` \|\| \|\| x
\|\| x \|\| \|\| \|\| \|\| ``float`` \|\| \|\| x \|\| x \|\| \|\| \|\|
\|\| ``timestamp`` \|\| \|\| x \|\| x \|\| \|\| \|\| \|\| ``time`` \|\|
\|\| x \|\| x \|\| \|\| \|\| \|\| ``date`` \|\| \|\| x \|\| x \|\| \|\|
\|\| \|\| ``clob`` \|\| x \|\| \|\| x \|\| \|\| \|\| \|\| ``blob`` \|\|
x \|\| \|\| x \|\| \|\| \|\|

``people``テーブルを作ることができます:

 // test.php

// ... $definition = array( 'id' => array( 'type' => 'integer',
'primary' => true, 'autoincrement' => true ), 'name' => array( 'type' =>
'string', 'length' => 255 ) );

$conn->export->createTable('people', $definition);

``createTable()``メソッドの3番目の引数としてオプションの配列を指定することもできます:

 // test.php

// ... $options = array( 'comment' => 'Repository of people',
'character\_set' => 'utf8', 'collate' => 'utf8\_unicode\_ci', 'type' =>
'innodb', );

// ...

$conn->export->createTable('people', $definition, $options);

------------------
外部キーを作成する
------------------

外部キーで``event_participants``テーブルを作成します:

 // test.php

// ... $options = array( 'foreignKeys' => array( 'events\_id\_fk' =>
array( 'local' => 'event\_id', 'foreign' => 'id', 'foreignTable' =>
'events', 'onDelete' => 'CASCADE', ) ), 'primary' => array('event\_id',
'person\_id'), );

$definition = array( 'event\_id' => array( 'type' => 'integer',
'primary' => true ), 'person\_id' => array( 'type' => 'integer',
'primary' => true ), );

$conn->export->createTable('event\_participants', $definition,
$options);

    **TIP**
    上記の例で``person_id``に対して外部キーを省略していることに注目してください。この例では次の例で個別の外部キーをテーブルに追加する方法を示すために省略しました。通常は``foreignKeys``で定義された両方の外部キーがあることがベストです。

``person\_id``カラムの``event_participants``テーブルに見つからない外部キーを追加してみましょう:

 // test.php

// ... $definition = array('local' => 'person\_id', 'foreign' => 'id',
'foreignTable' => 'people', 'onDelete' => 'CASCADE');

$conn->export->createForeignKey('event\_participants', $definition);

------------------
テーブルを変更する
------------------

``Doctrine_Export``ドライバはデータベースがポータブルでありながら既存のデータベーステーブルを簡単に変更する方法を提供します。

 // test.php

// ... $alter = array( 'add' => array( 'new\_column' => array( 'type' =>
'string', 'length' => 255 ), 'new\_column2' => array( 'type' =>
'string', 'length' => 255 ) ) );

echo $conn->export->alterTableSql('events', $alter);

``alterTableSql()``への呼び出しは次のSQLクエリを出力します:

 ALTER TABLE events ADD new\_column VARCHAR(255), ADD new\_column2
VARCHAR(255)

    **NOTE**
    生成SQLのみを実行しこれを返したくない場合、``alterTable()``メソッドを使います。

 // test.php

// ...

$conn->export->alterTable('events', $alter);

``alterTable()``メソッドは2つのパラメータを必須とし3番目のパラメータはオプションです:

\|\|~ 名前 \|\|~ 型 \|\|~ 説明 \|\| \|\|
//:code:`name// || ``string`` || 変更が想定されるテーブルの名前。 || || //`\ changes//
\|\| ``array`` \|\|
実行を前提とされる変更のそれぞれのタイプの詳細を含む連想配列。\|\|

オプションの3番目のパラメータ(デフォルト: false):

\|\|~ 名前 \|\|~ 型 \|\|~ 説明 \|\| \|\| //$check// \|\| ``boolean``
\|\| 実行前にDBMSが実際にオペレーションを実行できるかチェックする \|\|

現在サポートされる変更のタイプは次のように定義されます:

\|\|~ 変更 \|\|~ 説明 \|\| \|\| //name// \|\| テーブル用の新しい名前
\|\| \|\| //add// \|\|
配列のインデックスとして追加されるフィールドの名前を格納する連想配列。配列のそれぞれのエントリの値は追加されるフィールドのプロパティを格納する別の連想配列に設定されます。フィールドのプロパティはDoctrineパーサーによって定義されたものと同じです。\|\|
\|\| // remove// \|\|
配列のインデックスとして削除されるフィールドの名前を格納する連想配列。現在それぞれのエントリに割り当てられた値は無視されます。空の配列は将来の互換性のために使われます。\|\|
\|\| //rename// \|\|
配列のインデックスとしてリネームされるフィールドの名前を格納する連想配列。配列のそれぞれのエントリの値は別の連想配列に設定されます。この別の連想配列は新しいフィールド名と``CREATE
TABLE``文として使われるDBM固有のSQLコードで既にあるフィールドの宣言の一部を格納するものとして設定されるDeclarationという名前のエントリを持ちます。\|\|
\|\| //change// \|\|
配列のインデックスとして変更されるフィールドの名前を格納する連想配列。フィールドと他のプロパティを変更するか、change配列エントリは配列インデックスとしてフィールドの新しい名前を格納するかを念頭においてください。\|\|

配列のそれぞれのエントリの値はフィールドのプロパティを格納する別の連想配列に設定されます。これは配列エントリとして変更されることを意味します。これらのエントリはそれぞれのプロパティの新しい値に割り当てられます。フィールドのプロパティはDoctrineパーサーが定義するものと同じです。

 // test.php

// ... $alter = array('name' => 'event', 'add' => array( 'quota' =>
array( 'type' => 'integer', 'unsigned' => 1 ) ), 'remove' => array(
'new\_column2' => array() ), 'change' => array( 'name' => array(
'length' => '20', 'definition' => array( 'type' => 'string', 'length' =>
20 ) ) ), 'rename' => array( 'new\_column' => array( 'name' => 'gender',
'definition' => array( 'type' => 'string', 'length' => 1, 'default' =>
'M' ) ) )

::

                );

$conn->export->alterTable('events', $alter);

    **NOTE**
    テーブルを``event``にリネームしたことに注目してください。テーブルを``events``にリネームし直しましょう。機能を示すためだけにテーブルをリネームしたので次の例のためにテーブルを``events``と名づける必要があります。

 // test.php

// ... $alter = array( 'name' => 'events' );

$conn->export->alterTable('event', $alter);

----------------------
インデックスを作成する
----------------------

インデックスを作成するために、``createIndex()``メソッドが使われます。このメソッドは``createConstraint()``と似たシグニチャを持ち、テーブルの名前、インデックスの名前と定義配列を受け取ります。定義配列は``fields``という名前の1つのキーを持ち、その値はインデックスの一部であるフィールドを格納する別の連想配列です。フィールドは次のキーを持つ配列として定義されます:
ソート、昇順と降順の長さを持つ値、整数値

すべてのRDBMSはインデックスソートもしくは長さをサポートしないので、これらの場合ドライバはこれらを無視します。テストのeventデータベースでは、アプリケーションが固有のtimeframeで起きるイベントを表示することを前提とすることができます。selectは``WHERE``条件でdatatimeフィールドを使います。このフィールドにインデックスが存在する場合に手助けになります。

 // test.php

// ... $definition = array( 'fields' => array( 'datetime' => array() )
);

$conn->export->createIndex('events', 'datetime', $definition);

----------------------------
データベースの要素を削除する
----------------------------

上記で示されたそれぞれの``create*()``メソッドに対して、データベース、テーブル、フィールド、インデックスもしくは制約を削除するために対応する``drop*()``メソッドが存在します。``drop\*()``メソッドは削除されるアイテムの存在をチェックしません。try-catchブロックを使用して例外をチェックするのは開発者の責務です:

 // test.php

// ... try { $conn->export->dropSequence('nonexisting'); }
catch(Doctrine\_Exception $e) {

}

次のコードで制約を簡単に削除できます:

 // test.php

// ... $conn->export->dropConstraint('events', 'PRIMARY', true);

    **NOTE** 3番目の引数はこれが主キーであることのヒントを与えます。

 // test.php

// ... $conn->export->dropConstraint('event\_participants',
'event\_id');

次のコードでインデックスを簡単に削除できます:

 $conn->export->dropIndex('events', 'event\_timestamp');

    **TIP**
    次の2つの例を実際に実行するのは推奨されません。次のセクションで我々の例が無傷で動作できるように``events_db``が必要です。

次のコードでデータベースからテーブルを削除します:

 // test.php

// ... $conn->export->dropTable('events');

次のコードでデータベースを削除できます:

 // test.php

// ... $conn->export->dropDatabase('events\_db');

============
Import
============

importモジュールによってデータベース接続の内容を検証できます。それぞれのデータベースとそれぞれのデータベースのスキーマを学びます。

----
紹介
----

データベースに何があるのか見るために、Importモジュールの``list\*()``ファミリーのメソッドを使うことができます。

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``listDatabases()`` \|\|
データベースの一覧を表示する。\|\| \|\| ``listFunctions()`` \|\|
利用可能なメソッドの一覧を表示する。\|\| \|\|
``listSequences(:code:`dbName)`` || 利用可能なシーケンスの一覧を表示する。オプションパラメータとしてデータベースの名前を受け取る。帝京されない場合、選択されたデータベースが想定されます。|| || ``listTableConstraints(`\ tableName)``
\|\| 利用可能なテーブルの一覧を表示する。テーブルの名前を受け取る。\|\|
\|\|
``listTableColumns(:code:`tableName)`` || テーブルで利用可能なカラムの一覧を表示する。|| || ``listTableIndexes(`\ tableName)``
\|\| テーブルで定義されているインデックスの一覧を表示する。\|\| \|\|
``listTables(:code:`dbName)`` || データベースのテーブルの一覧を表示する。 || || ``listTableTriggers(`\ tableName)``
\|\| テーブルのトリッガーの一覧を表示する。\|\| \|\|
``listTableViews(:code:`tableName)`` || テーブルで利用可能なビューの一覧を表示する。|| || ``listUsers()`` || データベース用のユーザーの一覧を表示する。|| || ``listViews(`\ dbName)``
\|\| データベース用のビューの一覧を表示する。\|\|

下記において上記のメソッドの使い方の例が見つかります:

----------------------------
データベースの一覧を表示する
----------------------------

 // test.php

// ... $databases = :code:`conn->import->listDatabases(); print_r(`\ databases);

--------------------------
シーケンスの一覧を表示する
--------------------------

 // test.php

// ... $sequences =
:code:`conn->import->listSequences('events_db'); print_r(`\ sequences);

--------------------
制約の一覧を表示する
--------------------

 // test.php

// ... $constraints =
:code:`conn->import->listTableConstraints('event_participants'); print_r(`\ constraints);

------------------------------
テーブルカラムの一覧を表示する
------------------------------

 // test.php

// ... $columns =
:code:`conn->import->listTableColumns('events'); print_r(`\ columns);

------------------------------------
テーブルインデックスの一覧を表示する
------------------------------------

 // test.php

// ... $indexes =
:code:`conn->import->listTableIndexes('events'); print_r(`\ indexes);

------------------------
テーブルの一覧を表示する
------------------------

 $tables = :code:`conn->import->listTables(); print_r(`\ tables);

----------------------
ビューの一覧を表示する
----------------------

    **NOTE**
    現在、ビューを作成するメソッドは存在しないので、手動で作成してください。

 $sql = "CREATE VIEW names\_only AS SELECT name FROM people";
:code:`conn->exec(`\ sql);

$sql = "CREATE VIEW last\_ten\_events AS SELECT \* FROM events ORDER BY
id DESC LIMIT 0,10"; :code:`conn->exec(`\ sql);

先ほど作成したビューの一覧を表示できます:

 $views = :code:`conn->import->listViews(); print_r(`\ views);

================
DataDict
================

--------
はじめに
--------

ネイティブのRDBMの型をDoctrineの型に変換するもしくはその逆を行うためにDoctrineは内部で``DataDict``モジュールを使用します。``DataDict``モジュールは変換のために2つのメソッドを使用します:

-  ``getPortableDeclaration()``はネイティブなRDBMSの型宣言をポータブルなDoctrine宣言に変換するために使われる
-  ``getNativeDeclaration()``はDoctrine宣言をドライバ固有の型宣言に変換するために使われる

--------------------------
ポータブルな宣言を取得する
--------------------------

 // test.php

// ... $declaration =
$conn->dataDict->getPortableDeclaration('VARCHAR(255)');

print\_r($declaration);

上記の例は次の内容を出力します:

 $ php test.php Array ( [type] => Array ( [0] => string )

::

    [length] => 255
    [unsigned] => 
    [fixed] => 

)

--------------------------
ネイティブな宣言を取得する
--------------------------

 // test.php

// ... $portableDeclaration = array( 'type' => 'string', 'length' => 20,
'fixed' => true );

$nativeDeclaration = :code:`conn->dataDict->getNativeDeclaration(`\ portableDeclaration);

echo $nativeDeclaration;

上記の例は次の内容を出力します:

 $ php test.php CHAR(20)

========
ドライバ
========

----------
Mysql
----------

^^^^^^^^^^^^^^^^^^^^
テーブル型を設定する
^^^^^^^^^^^^^^^^^^^^

 // test.php

// ... $fields = array( 'id' => array( 'type' => 'integer',
'autoincrement' => true ), 'name' => array( 'type' => 'string', 'fixed'
=> true, 'length' => 8 ) );

    **NOTE** 次のオプションはMySQL固有で他のドライバはスキップします。

 $options = array('type' => 'INNODB');

$sql = $conn->export->createTableSql('test\_table', $fields); echo
$sql[0];

上記の例は次のSQLクエリを出力します:

 CREATE TABLE test\_table (id INT AUTO\_INCREMENT, name CHAR(8)) ENGINE
= INNODB

======
まとめ
======

この章は本当に素晴らしいものです。Doctrine
DBALはそれ自身が偉大なツールです。おそらく最も機能を持つものの1つでPHPデータベース抽象化レイヤーを簡単に利用できます。

[doc transactions :name]の使い方を学ぶ準備が整いました。
