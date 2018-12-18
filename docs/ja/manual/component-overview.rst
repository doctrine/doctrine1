この章はDoctrineを構成するすべてのメインコンポーネントとそれらの連携方法を鳥の目から見ることを目的としています。前の章で既に大半のコンポーネントを検討しましたがこの章ではすべてのコンポーネントとそれらのジョブの理解が進みます。

============
マネージャー
============

``Doctrine_Manager``クラスはSingletonで構成階層のrootでありDoctrineのいくつかの面をコントロールするFacadeです。次のコードでSingletonインスタンスを読み取ることができます。

 // test.php

// ... $manager = Doctrine\_Manager::getInstance();

--------------
接続を読み取る
--------------

 // test.php

// ... $connections = :code:`manager->getConnections(); foreach (`\ connections
as $connection) { echo $connection->getName() . ""; }

``Doctrine_Manager``はイテレータを実装するので接続をループするために変数$managerをループできます。

 // test.php

// ... foreach ($manager as $connection) { echo $connection->getName() .
""; }

====
接続
====

``Doctrine_Connection``はデータベース用のラッパーです。接続は典型的なPDOのインスタンスですが、Doctrineの設計のおおかげで、PDOが提供する機能を模倣する独自アダプタを設計することが可能です。

``Doctrine_Connection``クラスは次のことを対処します:

-  PDOから見つからないデータベースのポータビリティ機能(例えばLIMIT/OFFSETのエミュレーション)を処理する
-  ``Doctrine_Table``オブジェクトの経過を追跡する
-  レコードの経過を追跡する
-  update/insert/deleteする必要のあるレコードの経過を追跡する
-  トランザクションと入れ子構造のトランザクションを処理する
-  INSERT / UPDATE /
   DELETEオペレーションの場合の実際のデータベースクエリを処理する
-  DQLを使用データベースクエリを行う。DQLは[doc
   dql-doctrine-query-language :name]の章で学ぶことができる。
-  オプションとしてDoctrine\_Validatorを使用してトランザクションをバリデートしてあり得るエラーの全情報を示す

------------------
利用できるドライバ
------------------

DoctrineはPDOがサポートするデータベース用のすべてのドライバを持ちます。サポートされるデータベースは次の通りです:

-  FreeTDS / Microsoft SQL Server / Sybase
-  Firebird/Interbase 6
-  Informix
-  Mysql
-  Oracle
-  Odbc
-  PostgreSQL
-  Sqlite

--------------
接続を作成する
--------------

 // bootstrap.php

// ... $conn =
Doctrine\_Manager::connection('mysql://username:password@localhost/test',
'connection 1');

    **NOTE**
    前の章で既に新しい接続を作成しました。上記のステップをスキップして既に作成した接続を使うことができます。``Doctrine_Manager::connection()``メソッドを使用して読み取ることができます。

--------------------
接続をflushする
--------------------

新しい``User``レコードを作成するときレコードは接続をflushしてその接続に対して保存されていないすべてのオブジェクトを保存します。下記は例です:

 // test.php

// ... $conn = Doctrine\_Manager::connection();

$user1 = new User(); $user1->username = 'Jack';

$user2 = new User(); $user2->username = 'jwage';

$conn->flush();

``Doctrine_Connection::flush()``を呼び出せばその接続に対する未保存のレコードインスタンスが保存されます。もちろんオプションとしてそれぞれのレコードごとに``save()``を呼び出して同じことができます。

 // test.php

// ... $user1->save(); $user2->save();

========
テーブル
========

``Doctrine\_Table``はコンポーネント(レコード)によって指定されるスキーマ情報を保有します。例えば``Doctrine_Record``を継承する``User``クラスがある場合、それぞれのスキーマ定義の呼び出しは後で使う情報を保有するユニークなテーブルオブジェクトにデリゲートされます。

それぞれの``Doctrine\_Table``は``Doctrine_Connection``によって登録されます。下記に示されるそれぞれのコンポーネント用のテーブルオブジェクトを簡単に取得できます。

例えば、Userクラス用のテーブルオブジェクトを読み取りたい場合を考えます。これは``User``を``Doctrine_Core::getTable()``メソッドの第一引数として渡すことで可能です。

------------------------------
テーブルオブジェクトを取得する
------------------------------

指定するレコードのテーブルオブジェクトを取得するには、``Doctrine_Record::getTable()``を呼び出すだけです。

 // test.php

// ... $accountTable = Doctrine\_Core::getTable('Account');

--------------------
カラム情報を取得する
--------------------

適切な``Doctrine\_Table``メソッドを使用することで``Doctrine_Record``のカラム定義セットを読み取ることができます。すべてのカラムのすべての情報が必要な場合は次のように行います:

 // test.php

// ... $columns = $accountTable->getColumns();

$columns = :code:`accountTable->getColumns(); foreach (`\ columns as
:code:`column) { print_r(`\ column); }

上記の例が実行されるときに次の内容が出力されます:

 $ php test.php Array ( [type] => integer [length] => 20 [autoincrement]
=> 1 [primary] => 1 ) Array ( [type] => string [length] => 255 ) Array (
[type] => decimal [length] => 18 )

ときにこれがやりすぎであることがあります。次の例はカラムの名前を配列として読み取る方法を示しています:

 // test.php

// ... $names = :code:`accountTable->getColumnNames(); print_r(`\ names);

上記の例が実行されるとき次の内容が出力されます:

 $ php test.php Array ( [0] => id [1] => name [2] => amount )

----------------------------
リレーションの情報を取得する
----------------------------

次のように``Doctrine\_Table::getRelations()``を呼び出すことですべての``Doctrine_Relation``オブジェクトの配列を取得できます:

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');

$relations = $userTable->getRelations();

foreach ($relations as $name => $relation) { echo $name . ":"; echo
"Local - " . $relation->getLocal() . ""; echo "Foreign - " .
$relation->getForeign() . ""; }

上記の例が実行されるとき次の内容が出力されます:

 $ php test.php Email: Local - id Foreign - user\_id

Phonenumbers: Local - id Foreign - user\_id

Groups: Local - user\_id Foreign - group\_id

Friends: Local - user1 Foreign - user2

Addresses: Local - id Foreign - user\_id

Threads: Local - id Foreign - user\_id

``Doctrine\_Table::getRelation()``メソッドを使用することで個別のリレーション用の``Doctrine_Relation``オブジェクトを取得できます。

 // test.php

// ... $relation = $userTable->getRelation('Phonenumbers');

echo 'Name: ' . $relation['alias'] . ""; echo 'Local - ' .
$relation['local'] . ""; echo 'Foreign - ' .
:code:`relation['foreign'] . "\n"; echo 'Relation Class - ' . get_class(`\ relation);

上記の例が実行されるとき次の内容が出力されます:

 $ php test.php Name: Phonenumbers Local - id Foreign - user\_id
Relation Class - Doctrine\_Relation\_ForeignKey

    **NOTE**
    上記の例において変数``$relation}は}配列としてアクセスできる``Doctrine\_Relation_ForeignKey``のインスタンスを格納していることに注目してください。多くのDoctrineのクラスのように、これが``ArrayAccess``を実装するからです。

``toArray()``メソッドと``print_r()``を使用することでリレーションのすべての情報を検査してデバッグすることができます。

 // test.php

// ... $array = :code:`relation->toArray(); print_r(`\ array);

--------------------
ファインダーメソッド
--------------------

``Doctrine_Table``は基本的なファインダーメソッドを提供します。これらのファインダーメソッドはとても速く書けるので1つのデータベーステーブルからデータを取得する場合に使われます。いくつかのコンポーネント(データベーステーブル)を使用するクエリが必要な場合
``Doctrine_Connection::query()``を使います。

主キーで個別のユーザーを簡単に見つけるには``find()``メソッドを使用します:

 // test.php

// ... $user = :code:`userTable->find(2); print_r(`\ user->toArray());

上記の例が実行されるとき次の内容が出力されます:

 $ php test.php Array ( [id] => 2 [is\_active] => 1 [is\_super\_admin]
=> 0 [first\_name] => [last\_name] => [username] => jwage [password] =>
[type] => [created\_at] => 2009-01-21 13:29:12 [updated\_at] =>
2009-01-21 13:29:12 )

データベースのすべての``User``レコードのコレクションを読み取るために``findAll()``メソッドを使うこともできます:

 // test.php

// ... foreach ($userTable->findAll() as $user) { echo $user->username .
""; }

上記の例が実行されるとき次の内容が出力されます:

 $ php test.php Jack jwage

    **CAUTION**
    ``findAll()``メソッドは推奨されません。このメソッドがデータベースのすべてのレコードを返しリレーションから情報を読み取る場合高いクエリカウントを引き起こしながらそのデータを遅延ロードするからです。[doc
    dql-doctrine-query-language
    :name]の章を読めばレコードと関連レコードを効率的に読み取る方法を学べます。

``findByDql()``メソッドを使用して
DQLでレコードのセットを読み取ることもできます:

 // test.php

// ... $users = $userTable->findByDql('username LIKE ?', '%jw%');

foreach($users as $user) { echo $user->username . ""; }

上記の例が実行されるときに次の内容が出力されます:

 $ php test.php jwage

Doctrineは追加のマジックファインダーメソッドも提供します。この内容はDQLの章の[doc
dql-doctrine-query-language:magic-finders
:name]セクションで読むことができます。

    **NOTE**
    ``Doctrine\_Table``によって提供される下記のすべてのファインダーメソッドはクエリを実行するために``Doctrine_Query``のインスタンスを使用します。オブジェクトは内部で動的に構築され実行されます。

    リレーションを通して複数のオブジェクトにアクセスするときは``Doctrine_Query``インスタンスを使用することが多いに推奨されます。そうでなければデータが遅延ロードされるので高いクエリカウントを得ることになります。[doc
    dql-doctrine-query-language :name]の章で詳細を学ぶことができます。

^^^^^^^^^^^^^^^^^^^^^^^^
カスタムのテーブルクラス
^^^^^^^^^^^^^^^^^^^^^^^^

カスタムのテーブルクラスを追加するのはとても楽です。行う必要のあるのはクラスを[componentName]Tableとして名付けこれらに``Doctrine_Table``を継承させます。``User``モデルに関して次のようなクラスを作ることになります:

 // models/UserTable.php

class UserTable extends Doctrine\_Table { }

----------------------
カスタムのファインダー
----------------------

カスタムのテーブルオブジェクトにカスタムのファインダーメソッドを追加できます。これらのファインダーメソッドは速い``Doctrine_Table``ファインダーメソッドもしくは[doc
dql-doctrine-query-language DQL API]
(``Doctrine_Query::create()``)を使用できます。

 // models/UserTable.php

class UserTable extends Doctrine\_Table { public function
findByName(:code:`name) { return Doctrine_Query::create() ->from('User u') ->where('u.name LIKE ?', "%`\ name%")
->execute(); } }

Doctrineは``getTable()``を呼び出すときに``Doctrine\_Table``の子クラスである``UserTable``が存在するかチェックしそうである場合、デフォルトの``Doctrine_Table``の代わりにそのクラスのインスタンスを返します。

    **NOTE**
    カスタムの``Doctrine\_Table``クラスをロードするには、下記のように``bootstrap.php``ファイルで``autoload\_table_classes``属性を有効にしなければなりません。

 // boostrap.php

// ...
$manager->setAttribute(Doctrine\_Core::ATTR\_AUTOLOAD\_TABLE\_CLASSES,
true);

これで``User``テーブルオブジェクトに問い合わせるとき次の内容が得られます:

 $userTable = Doctrine\_Core::getTable('User');

echo get\_class($userTable); // UserTable

$users = $userTable->findByName("Jack");

    **NOTE**
    ``findByName()``メソッドを追加する上記の例はマジックファインダーメソッドによって自動的に利用可能になります。DQLの章の[doc
    dql-doctrine-query-language:magic-finders
    :name]セクションで読むことができます。

========
レコード
========

Doctrineは``Doctrine_Record``子クラスを用いてRDBMSのテーブルを表します。これらのクラスはスキーマ情報、お婦四、属性などを定義する場所です。これらの子クラスのインスタンスはデータベースのレコードを表しこれらのオブジェクトでプロパティの取得と設定ができます。

----------
プロパティ
----------

``Doctrine_Record``のそれぞれ割り当てられたカラムプロパティはデータベースのテーブルカラムを表します。[doc
defining-models :name]の章でモデルの定義方法の詳細を学ぶことになります。

カラムへのアクセスは簡単です:

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');

$user = $userTable->find(1);

**オーバーロードを通してプロパティにアクセスする**

 // test.php

// ... echo $user->username;

**get()でプロパティにアクセスする**

 // test.php

// ... echo $user->get('username);

**ArrayAccessでプロパティにアクセスする**

 // test.php

// ... echo $user['username'];

.. tip::

   
    カラムの値にアクセスする推奨方法はArrayAccessを使うことです。これによって必要に応じてレコードと配列取得を切り替えるのが簡単になるからです。

レコードのプロパティのイテレーションは配列のやり方と似ています。``foreach``コンストラクトを使用します。``Doctrine_Record``は``IteratorAggregate``インターフェイスを実装するのでこれは実現可能です。

 // test.php

// ... foreach ($user as $field => $value) { echo $field . ': ' . $value
. ""; }

配列に関してプロパティの存在のチェックには``isset()``を、プロパティをnullに設定するには``unset()``が利用できます。

if文で'name'という名前のプロパティが存在するか簡単にチェックできます:

 // test.php

// ... if (isset($user['username'])) {

}

nameプロパティの割り当てを解除したい場合PHPの``unset()``関数を使うことができます:

 // test.php

// ... unset($user['username']);

レコードプロパティ用に値を設定するとき``Doctrine_Record::getModified()``を使用して修正されたフィールドと値の配列を取得できます。

 // test.php

// ... $user['username'] = 'Jack Daniels';

print\_r($user->getModified());

上記のコードが実行されるとき次の内容が出力されます:

 $ php test.php Array ( [username] => Jack Daniels )

``Doctrine_Record::isModified()``メソッドを使用してレコードが修正されることをチェックすることもできます:

 // test.php

// ... echo $user->isModified() ? 'Modified':'Not Modified';

ときどき任意のレコードのカラムカウントを読み取りたいことがあります。これを行うには``count()``関数にレコードを引数として渡します。``Doctrine_Record``が``Countable``インターフェイスを実装するのでこれは可能です。他には``count()``メソッドを呼び出す方法があります。

 // test.php

// ... echo :code:`record->count(); echo count(`\ record);

``Doctrine_Record``は任意のレコードの識別子にアクセスするための特別なメソッドを提供します。このメソッドは``identifier()``と呼ばれキーが識別子のフィールド名であり、値が、関連プロパティの値である配列を返します。

 // test.php

// ... $user['username'] = 'Jack Daniels'; $user->save();

print\_r($user->identifier()); // array('id' => 1)

よくあるのは配列の値を任意のレコードに割り当てることです。これらの値を個別に設定するのはやりずらいと思うかもしれません。しかし悩む必要はありません。``Doctrine_Record``は任意の配列もしくはレコードを別のものにマージする方法を提供します。

``merge()``メソッドはレコードもしくは配列のプロパティをイテレートしてオブジェクトに値を割り当てます。

 // test.php

// ... $values = array( 'username' => 'someone', 'age' => 11, );

:code:`user->merge(`\ values);

echo $user->username; // someone echo $user->age; // 11

次のように1つのレコードの値を別のものにマージすることもできます:

 // test.php

// ... $user1 = new User(); $user1->username = 'jwage';

$user2 = new User(); :code:`user2->merge(`\ user1);

echo $user2->username; // jwage

    **NOTE**
    ``Doctrine_Record``は``fromArray()``メソッドを持ちます。このメソッドは``merge()``に理想的なもので``toArray()``メソッドとの一貫性を保つためだけに存在します。

------------------
レコードを更新する
------------------

オブジェクトの更新は非常に簡単で、``Doctrine\_Record::save()``メソッドを呼び出すだけです。他の方法は``Doctrine_Connection::flush()``を呼び出す方法でこの場合すべてのオブジェクトが保存されます。flushはsaveメソッドを呼び出すだけよりも重たいオペレーションであることに注意してください。

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');

$user = $userTable->find(2);

if ($user !== false) { $user->username = 'Jack Daniels';

::

    $user->save();

}

ときどき直接更新を行いたいことがあります。直接の更新においてオブジェクトはデータベースからロードされません。むしろデータベースの状態が直接更新されます。次の例においてすべてのユーザーを更新するためにDQL
UPDATE文を使います。

すべてのユーザー名を小文字にするクエリを実行します:

 // test.php

// ... $q = Doctrine\_Query::create() ->update('User u')
->set('u.username', 'LOWER(u.name)');

$q->execute();

レコードの識別子が既知であればオブジェクトを利用して更新を実行することもできます。``Doctrine\_Record::assignIdentifier()``メソッドを使うときこれはレコード識別子を設定し状態を変更するので``Doctrine_Record::save()``の呼び出しはinsertの代わりにupdateを実行します。

 // test.php

// ... $user = new User(); $user->assignIdentifer(1); $user->username =
'jwage'; $user->save();

--------------------
レコードを置き換える
--------------------

レコードを置き換えるのはシンプルです。まずは新しいオブジェクトをインスタンス化して保存します。次にデータベースに既に存在する同じ主キーもしくはユニークキーの値で新しいオブジェクトをインスタンス化すればデータベースで新しい列をinsertする代わりに列を置き換え/更新が行われます。下記は例です。

最初に、ユーザー名がユニークインデックスである``User``モデルを想像してみましょう。

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->password =
'changeme'; $user->save();

次のクエリを発行します。

 INSERT INTO user (username, password) VALUES (?,?) ('jwage',
'changeme')

別の新しいオブジェクトを作り同じユーザー名と異なるパスワードを設定します。

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->password =
'newpassword'; $user->replace();

次のクエリが発行されます

 REPLACE INTO user (id,username,password) VALUES (?,?,?) (null, 'jwage',
'newpassword')

新しいレコードがinsertされる代わりにレコードが置き換え/更新されます。

--------------------------
レコードをリフレッシュする
--------------------------

ときにデータベースからのデータでレコードをリフレッシュしたいことがあります。``Doctrine_Record::refresh()``を使います。

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(2);
$user->username = 'New name';

``Doctrine_Record::refresh()``メソッドを使う場合データベースからデータが再度選択されインスタンスのプロパティが更新されます。

 // test.php

// ... $user->refresh();

------------------------------
リレーションをリフレッシュする
------------------------------

``Doctrine_Record::refresh()``メソッドは既にロードされたレコードのリレーションをリフレッシュすることもできますが、オリジナルのクエリでこれらを指定する必要があります。

最初に関連``Groups``で``User``を読み取りましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Groups') ->where('id = ?');

$user = $q->fetchOne(array(1));

関連``Users``で``Group``を読み取りましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('Group g')
->leftJoin('g.Users') ->where('id = ?');

$group = $q->fetchOne(array(1));

``UserGroup``インスタンスで読み取られた``User``と``Group``をリンクしましょう:

 // test.php

// ... $userGroup = new UserGroup(); $userGroup->user\_id = $user->id;
$userGroup->group\_id = $group->id; $userGroup->save();

``Group``を``User``に追加するだけで``User``を``Group``にリンクすることもできます。Doctrineは``UserGroup``インスタンスの作成を自動的に引き受けます:

 // test.php

// ... $user->Groups[] = $group; $user->save()

``Doctrine_Record::refresh(true)``を呼び出す場合新しく作成された参照をロードするレコードとリレーションがリフレッシュされます:

 // test.php

// ... $user->refresh(true); $group->refresh(true);

``Doctrine_Record::refreshRelated()``を使用してモデルの定義されたすべてのリレーションを遅延リフレッシュすることもできます:

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->findOneByName('jon');
$user->refreshRelated();

リレーションを個別に指定してリフレッシュしたい場合リレーションの名前を``refreshRelated()``メソッドに渡せばリレーションは遅延ロードされます:

 // test.php

// ... $user->refreshRelated('Phonenumber');

------------------
レコードを削除する
------------------

Doctrineでのレコード削除は``Doctrine\_Record::delete()``、``Doctrine\_Collection::delete()``と``Doctrine_Connection::delete()``メソッドによって処理されます。

 // test.php

// ... $userTable = Doctrine\_Core::getTable("User");

$user = $userTable->find(2);

// ユーザーと関連コンポジットオブジェクトすべてを削除する if($user !==
false) { $user->delete(); }

``User``レコードの``Doctrine\_Collection``がある場合``delete()``を呼び出すと``Doctrine_Record::delete()``が呼び出されてすべてのレコードがループされます。

 // test.php

// ... $users = $userTable->findAll();

``Doctrine_Collection::delete()``を呼び出すことですべてのユーザーと関連コンポジットオブジェクトを削除できます。deleteを1つずつ呼び出すことでコレクションのすべての``Users``がループされます:

 // test.php

// ... $users->delete();

------------
式の値を使う
------------

SQLの式をカラムの値として使う必要のある状況があります。これはポータブルなDQL式をネイティブなSQL式に変換する``Doctrine_Expression``を使用することで実現できます。

``timepoint(datetime)``と``name(string)``のカラムを持つeventという名前のクラスがある場合を考えてみましょう。現在のタイムスタンプによるレコードの保存は次のように実現されます:

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->updated\_at
= new Doctrine\_Expression('NOW()'); $user->save();

上記のコードは次のSQLクエリを発行します:

 INSERT INTO user (username, updated\_at\_) VALUES ('jwage', NOW())

.. tip::

    更新された値を取得するためにオブジェクトで``Doctrine_Expression``を使うとき``refresh()``を呼び出さなければなりません。

 // test.php

// ... $user->refresh();

------------------------
レコードの状態を取得する
------------------------

それぞれの``Doctrine\_Record``は状態を持ちます。最初のすべてレコードは一時的もしくは永続的になります。データベースから読み取られたすべてのレコードは永続的に新しく作成されたすべてのレコードは一時的なものと見なされます。``Doctrine_Record``がデータベースから読み取られるが唯一ロードされたプロパティが主キーである場合、このレコードはプロキシと呼ばれる状態を持ちます。

一時的もしくは永続的なすべての``Doctrine\_Record``はcleanもしくはdirtyのどちらかです。``Doctrine_Record``はプロパティが変更されていないときはcleanで少なくともプロパティの1つが変更されたときはdirtyです。

レコードはlockedと呼ばれる状態を持つこともできます。まれに起きる循環参照の場合に無限反復を避けるためにDoctrineは現在レコードで操作オペレーションが行われていることを示すこの状態を内部で使用します。

レコードがなり得るすべての異なる状態と手短な説明を含むテーブルは下記の通りです:

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``Doctrine\_Record::STATE_PROXY`` \|\|
レコードがproxyの状態にある一方で、永続性とすべてではないプロパティがデータベースからロードされる。
\|\| \|\| ``Doctrine\_Record::STATE_TCLEAN`` \|\|
レコードが一時的にcleanである一方で、一時性が変更されプロパティは変更されない。\|\|
\|\| ``Doctrine\_Record::STATE_TDIRTY`` \|\|
レコードが一時的にdirtyである一方で、一時性とプロパティの一部が変更される。\|\|
\|\| ``Doctrine\_Record::STATE_DIRTY`` \|\|
レコードがdirtyである一方で永続性とプロパティの一部が変更される。\|\|
\|\| ``Doctrine\_Record::STATE_CLEAN`` \|\|
レコードがcleanである一方で、永続性は変更されプロパティは変更されない。\|\|
\|\| ``Doctrine\_Record::STATE_LOCKED`` \|\|
レコードがロックされる。\|\|

``Doctrine_Record::state()``メソッドを使用してレコードの状態を簡単に取得できます:

 // test.php

// ... $user = new User();

if ($user->state() == Doctrine\_Record::STATE\_TDIRTY) { echo 'Record is
transient dirty'; }

    **NOTE**
    上記のオブジェクトは``TDIRTY``です。これがスキーマで指定されたデフォルトの値をいくつか持つからです。デフォルトの値を持たないオブジェクトを使い新しいインスタンスを作成すると``TCLEAN``が返されます。

 // test.php

// ... $account = new Account();

if ($account->state() == Doctrine\_Record::STATE\_TCLEAN) { echo 'Record
is transient clean'; }

------------------------------
オブジェクトのコピーを取得する
------------------------------

ときにオブジェクトのコピーを手に入れたいことがあります(コピーされたすべてのプロパティを持つオブジェクト)。Doctrineはこのためのシンプルなメソッド:
``Doctrine_Record::copy()``を提供します。

 // test.php

// ... $copy = $user->copy();

``copy()``でレコードをコピーすると古いレコードの値を持つ新しいレコード(``TDIRTY``の状態)が返され、そのレコードのリレーションがコピーされることに注意してください。リレーションもコピーしたくなければ、``copy(false)``を使う必要があります。

**リレーション無しのユーザーのコピーを入手する**

 // test.php

// ... $copy = $user->copy(false);

PHPの``clone``キーワードを使えばこの``copy()``メソッドが内部で使用されます:

 // test.php

// ... $copy = clone $user;

------------------------
空白のレコードを保存する
------------------------

デフォルトでは未修整のレコードで``save()``メソッドが呼び出されているときDoctrineは実行しません。レコードが修正されていなくてもレコードを強制的にINSERTしたい状況があります。これはレコードの状態を``Doctrine\_Record::STATE_TDIRTY``を割り当てることで実現できます。

 // test.php

// ... $user = new User(); $user->state('TDIRTY'); $user->save();

----------------------------
カスタムの値をマッピングする
----------------------------

カスタムの値をレコードにマッピングしたい状況があります。例えば値が外部のリソースに依存しておりこれらの値をデータベースにシリアライズして保存せずに実行時に利用可能にすることだけを行いたい場合があります。これは次のように実現できます:

 // test.php

// ... $user->mapValue('isRegistered', true);

$user->isRegistered; // true

------------
シリアライズ
------------

ときにレコードオブジェクトをシリアライズしたいことがあります(例えばキャッシュを保存するため):

 // test.php

// ... :code:`string = serialize(`\ user);

:code:`user = unserialize(`\ string);

------------------
存在をチェックする
------------------

レコードがデータベースに存在するか知りたいことがとてもよくあります。任意のレコードがデータベースの列の同等の内容を持つかを確認するために``exists()``メソッドを使うことができます:

 // test.php

// ... $record = new User();

echo $record->exists() ? 'Exists':'Does Not Exist'; // Does Not Exist

$record->username = 'someone'; $record->save();

echo $record->exists() ? 'Exists':'Does Not Exist'; // Exists

--------------------------
カラム用のコールバック関数
--------------------------

``Doctrine_Record``はカラムを呼び出すコールバックを添付する方法を提供します。例えば特定のカラムをトリムしたい場合、次のメソッドを使うことができます:

 // test.php

// ... $record->call('trim', 'username');

============
コレクション
============

``Doctrine\_Collection``はレコードのコレクションです(Doctrine\_Recordを参照)。レコードに関してコレクションは``Doctrine\_Collection::delete()``と``Doctrine_Collection::save()``をそれぞれ使用して削除と保存ができます。

DQL API(``Doctrine_Query``を参照)もしくはrawSql
API(``Doctrine\_RawSql``を参照)のどちらかでデータベースからデータを取得するとき、デフォルトではメソッドは``Doctrine_Collection``のインスタンスを返します。

次の例では新しいコレクションを初期化する方法を示しています:

 // test.php

// ... $users = new Doctrine\_Collection('User');

コレクションにデータを追加します:

 // test.php

// ... $users[0]->username = 'Arnold'; $users[1]->username = 'Somebody';

コレクションの削除と同じように保存もできます:

 $users->save();

------------------
要素にアクセスする
------------------

``set()``と``get()``メソッドもしくはArrayAccessインターフェイスで``Doctrine_Collection``の要素にアクセスできます。

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User'); $users =
$userTable->findAll();

**ArrayAccessインターフェイスで要素にアクセスする**

 // test.php

// ... $users[0]->username = "Jack Daniels"; $users[1]->username = "John
Locke";

**get()で要素にアクセスする**

 echo $users->get(1)->username;

--------------------
新しい要素を追加する
--------------------

存在しないコレクションの単独の要素とこれらの要素(レコード)にアクセスするときDoctrineはこれらを自動的に追加します。

次の例ではデータベースからすべてのユーザー(5人)を取得しコレクションにユーザーの組を追加します。

PHP配列に関してインデックスはゼロから始まります。

 // test.php

// ... $users = $userTable->findAll();

echo count($users); // 5

$users[5]->username = "new user 1"; $users[6]->username = "new user 2";

オプションとして配列インデックスから5と6を省略可能でその場合通常のPHP配列と同じように自動的にインクリメントされます:

 // test.php

// ... $users[]->username = 'new user 3'; // キーは7 $users[]->username
= 'new user 4'; // キーは8

--------------------------------
コレクションのカウントを取得する
--------------------------------

``Doctrine_Collection::count()``メソッドはコレクションの現在の要素の数を返します。

 // test.php

// ... $users = $userTable->findAll();

echo $users->count();

``Doctrine_Collection``はCountableインターフェイスを実装するの以前の例に対する妥当な代替方法はcount()メソッドにコレクションを引数として渡すことです。

 // test.php

// ... echo count($users);

----------------------
コレクションを保存する
----------------------

``Doctrine_Record``と同じようにコレクションは``save()``メソッドを呼び出すことで保存できます。``save()``が呼び出されるときDoctrineはすべてのレコードに対して``save()``オペレーションを実行しトランザクション全体のプロシージャをラップします。

 // test.php

// ... $users = $userTable->findAll();

$users[0]->username = 'Jack Daniels';

$users[1]->username = 'John Locke';

$users->save();

----------------------
コレクションを削除する
----------------------

Doctrine
Recordsとまったく同じように``delete()``メソッドを呼び出すだけでDoctrine
Collectionsは削除できます。すべてのコレクションに関してDoctrineはsingle-shot-deleteを実行する方法を知っています。これはそれぞれのコレクションに対して1つのデータベースクエリのみが実行されることを意味します。

例えば複数のコレクションがある場合を考えます。ユーザーのコレクションを削除するときDoctrineはトランザクション全体に対して1つのクエリのみを実行します。クエリは次のようになります:

 DELETE FROM user WHERE id IN (1,2,3, ... ,N)

----------------
キーのマッピング
----------------

ときにコレクションの要素用の通常のインデックス作成をしたくないことがあります。その場合例えば主キーをコレクションとしてマッピングすることが役に立つことがあります。次の例はこれを実現する方法を実演しています。

``id``カラムをマッピングします。

 // test.php

// .... $userTable = Doctrine\_Core::getTable('User');

$userTable->setAttribute(Doctrine\_Core::ATTR\_COLL\_KEY, 'id');

これで``user``コレクションは``id``カラムの値を要素インデックスとして使用します:

 // test.php

// ... $users = $userTable->findAll();

foreach($users as $id => $user) { echo $id . $user->username; }

``name``カラムをマッピングするとよいでしょう:

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');

$userTable->setAttribute(Doctrine\_Core::ATTR\_COLL\_KEY, 'username');

これでユーザーコレクションは``name``カラムの値を要素インデックスとして使用します:

 // test.php

// ... $users = $userTable->findAll();

foreach($users as $username => $user) { echo $username . ' - ' .
$user->created\_at . ""; }

    **CAUTION**
    スキーマで``username``カラムがuniqueとして指定された場合のみこれは利用可能であることに注意してください。そうでなければ重複するコレクションのキーのためにデータは適切にハイドレイトされない事態に遭遇することになります。

------------------------
関連レコードをロードする
------------------------

Doctrineはすべてのレコード要素用のすべての関連レコードを効率的い読み取る方法を提供します。これは例えばユーザーのコレクションがある場合``loadRelated()``メソッドを呼び出すだけですべてのユーザーのすべての電話番号をロードできることを意味します。

しかしながら、大抵の場合関連要素を明示的にロードする必要はなく、むしろ行うべきはDQL
APIとJOINを使用して一度にすべてをロードすることを試みることです。

次の例ではユーザー、電話番号とユーザーが所属するグループを読み取るために3つのクエリを使用します。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u');

$users = $q->execute();

すべてのユーザーの電話番号をロードしてみましょう:

 // test.php

// ... $users->loadRelated('Phonenumbers');

foreach($users as $user) { echo $user->Phonenumbers[0]->phonenumber; //
ここでは追加のDBクエリは不要 }

``loadRelated()``はリレーション、アソシエーションに対しても動作します:

 // test.php

// ... $users->loadRelated('Groups');

foreach($users as $user) { echo $user->Groups[0]->name; }

下記の例はDQL APIを使用してより効率的にこれを行う方法を示します。

1つのクエリですべてをロードする``Doctrine_Query``を書きます:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumbers p') ->leftJoin('u.Groups g');

$users = $q->execute();

``Phonenumbers``と``Groups``を使うとき追加のデータベースクエリは必要ありません:

 // test.php

// ... foreach($users as $user) { echo
$user->Phonenumbers[0]->phonenumber; echo $user->Groups[0]->name; }

==========
バリデータ
==========

DoctrineのバリデーションはMVCアーキテクチャのモデル部分でビジネスルールを強制する方法です。このバリデーションを永続的なデータ保存が行われる直前に渡される必要のあるゲートウェイとみなすことができます。これらのビジネスルールの定義はレコードレベル、すなわちactive
recordモデルクラスにおいて行われます(``Doctrine\_Record``を継承するクラス)。この種のバリデーションを使うために最初に行う必要のあることはこれをグローバルに設定することです。これは``Doctrine_Manager``を通して行われます。

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine::VALIDATE\_ALL);

バリデーションを有効にすると、一連のバリデーションが自動的に使えるようになります:

-  データ型のバリデーション:
   カラムに割り当てられるすべての値は正しい型であるかチェックされます。すなわち次のよに指定した場合

レコードのカラムが'integer'型である場合、Doctrineはそのカラムに割り当てられた値がその型であるかをバリデートします。PHPはゆるい型の言語なのでこの種の型バリデーションはできる限りスマートであるように試みます。例えば2は"7"と同じように有効な整数型である一方で"3f"はそうではありません。型バリデーションはすべてのカラムで行われます(すべてのカラム定義は型を必要とするからです)。

-  長さのバリデーション:
   名前がほのめかす通り、カラムに割り当てられたすべての値が最大長を越えないことを確認するためにバリデートされます。

次の定数:
``VALIDATE\_ALL``、``VALIDATE\_TYPES``、``VALIDATE\_LENGTHS``、``VALIDATE\_CONSTRAINTS``、``VALIDATE_NONE``をビット演算子で結びつけることができます。

例えば長さバリデーション以外のすべてのバリデーションを有効にするには次のように行います:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
VALIDATE\_ALL & ~VALIDATE\_LENGTHS);

[doc data-validation
:name]の章でこのトピックの詳細を読むことができます。

--------------------
さらにバリデーション
--------------------

型と長さバリデーションは手軽ですが大抵の場合これらだけでは十分ではありません。それゆえDoctrineはデータをより詳しくバリデートするために利用できるメカニズムを提供します。

バリデータはさらにバリデーションを指定するための簡単な手段です。Doctrineは``email``、``country``、``ip``、``range``と``regexp``バリデータなど頻繁に必要とされるたくさんのバリデータを事前に定義しています。[doc
data-validation
:name]の章で利用可能なバリデータの全リストが見つかります。``hasColumn()``メソッドの4番目の引数を通してどのバリデータをどのカラムに適用するのかを指定できます。これが十分ではなく事前に定義されたバリデータとして利用できない特別なバリデータが必要な場合、3つの選択肢があります:

-  独自のバリデータを書けます。
-  Doctrineの開発者に新しいバリデータのニーズを提案できます。
-  バリデータフックが使えます。

最初の2つのオプションが推奨されます。バリデーションが一般的に利用可能で多くの状況に適用できるからです。このケースにおいて新しいバリデータを実装するのは良い考えです。しかしながら、バリデーションが特別なものでなければDoctrineが提供するフックを使う方がベターです:

-  ``validate()`` (レコードがバリデートされるたびに実行される)
-  ``validateOnInsert()``
   (レコードが新しくバリデートされるときに実行される)
-  ``validateOnUpdate()``
   (レコードが新しくなくバリデートされるときに実行される)

active recordで特殊なバリデーションが必要な場合active
recordクラス(``Doctrine_Record``の子孫)でこれらのメソッドの1つをオーバーライドできます。フィールドをバリデートするためにこれらのメソッドの範囲内でPHPのすべての力を使うことができます。フィールドがバリデーションを渡さないときエラーをレコードのエラーに追加できます。次のコードスニペットはカスタムバリデーションと一緒にバリデータを定義する例を示しています:

 // models/User.php

class User extends BaseUser { protected function validate() { if
($this->username == 'God') { // Blasphemy! Stop that! ;-) // syntax:
add(, ) $errorStack = $this->getErrorStack(); $errorStack->add('name',
'You cannot use this username!'); } } }

// models/Email.php

class Email extends BaseEmail { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        // 使われる'email'と'unique'バリデータ
        $this->hasColumn('address','string', 150, array('email', 'unique'));
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Email: columns: address: type: string(150) email: true unique: true

------------------------------------
ValidもしくはNot Valid
------------------------------------

モデルでビジネスルールを指定する方法を理解したので、アプリケーションの残りの部分でこれらのルールを扱う方法を見てみましょう。

^^^^^^^^^^^^^^^^^^^^
暗黙のバリデーション
^^^^^^^^^^^^^^^^^^^^

(``$record->save()``の呼び出しを通して)レコードが永続的データとして保存されているときバリデーションの全手続きが実行されます。そのプロセスの間にエラーが起きると``Doctrine\_Validator\_Exception``型のエラーが投げられます。例外を補足して``Doctrine\_Validator\_Exception::getInvalidRecords()``インスタンスメソッドを使用してエラーを解析できます。このメソッドはバリデーションをパスしなかったすべてのレコードへの参照を持つ通常の配列を返します。それぞれのレコードのエラースタックを解析することでそれぞれのレコードのエラーを詳しく調査することができます。レコードのエラースタックは``Doctrine\_Record::getErrorStack()``インスタンスメソッドで取得できます。それぞれのエラースタックは``Doctrine\_Validator_ErrorStack``クラスのインスタンスです。エラースタックはエラーを検査するためのインターフェイスを簡単に使う方法を提供します。

^^^^^^^^^^^^^^^^^^^^^^
明示的なバリデーション
^^^^^^^^^^^^^^^^^^^^^^

任意のときに任意のレコードに対してバリデーションを明示的に実行できます。この目的のために``Doctrine\_Record``は``Doctrine\_Record::isValid()``インスタンスメソッドを提供します。このメソッドはバリデーションの結果を示す論理型を返します。このメソッドがfalseを返す場合、例外が投げられないこと以外は上記と同じ方法でエラースタックを検査できるので、``Doctrine_Record::getErrorStack()``を通したバリデーションがパスしなかったレコードのエラースタックを得られます。

次のコードスニペットは``Doctrine\_Validator_Exception``によって引き起こされる明示的なバリデーションの処理方法の例です。

 // test.php

// ... $user = new User();

try { $user->username = str\_repeat('t', 256); $user->Email->address =
"drink@@notvalid.."; $user->save(); }
catch(Doctrine\_Validator\_Exception $e) { $userErrors =
$user->getErrorStack(); $emailErrors = $user->Email->getErrorStack();

::

    foreach($userErrors as $fieldName => $errorCodes) {
        echo $fieldName . " - " . implode(', ', $errorCodes) . "\n";
    }

    foreach($emailErrors as $fieldName => $errorCodes) {
        echo $fieldName . " - " . implode(', ', $errorCodes) . "\n";
    }

}

.. tip::

   
    ``$e->getInvalidRecords()``を使うことができます。扱っているレコードを知っているときは上記の内容を直接使う方がシンプルです。

アプリケーションで簡単に使えるように読みやすく整形されたエラースタックを読み取ることもできます:

 // test.php

// ... echo $user->getErrorStackAsString();

次のようにエラー文字列が出力されます:

 Validation failed in class User

1 field had validation error:

::

    * 1 validator failed on username (length)

==============
プロファイラー
==============

``Doctrine\_Connection\_Profiler``は``Doctrine_Connection``用のイベントリスナーです。これは柔軟なクエリプロファイリングを提供します。SQL文字列に加えクエリプロファイルはクエリを実行するための経過時間を含みます。これによってモデルクラスにデバッグコードを追加せずにクエリのインスペクションの実行が可能になります。

``Doctrine\_Connection_Profiler``はDoctrine\_Connection用のイベントリスナーとして追加されることで有効になります。

 // test.php

// ... $profiler = new Doctrine\_Connection\_Profiler();

$conn = Doctrine\_Manager::connection(); :code:`conn->setListener(`\ profiler);

--------------
基本的な使い方
--------------

ページの中にはロードが遅いものがあるでしょう。次のコードは接続から完全なプロファイラーレポートを構築する方法を示しています:

 // test.php

// ... :code:`time = 0; foreach (`\ profiler as $event) { $time +=
$event->getElapsedSecs(); echo $event->getName() . " " . sprintf("%f",
$event->getElapsedSecs()) . ""; echo $event->getQuery() . ""; $params =
:code:`event->getParams(); if( ! empty(`\ params)) { print\_r($params);
} } echo "Total time: " . $time . "";

.. tip::

    [http://www.symfony-project.com
    symfony]、[http://framework.zend.com
    Zend]などのフレームワークはウェブデバッグツールバーを提供します。Doctrineはそれぞれのクエリにかかる時間と同様にすべてのページで実行されるクエリの回数をレポートする機能を提供します。

========================
マネージャーをロックする
========================

    **NOTE**
    'トランザクション(Transaction)'という用語はデータベースのトランザクションではなく一般的な意味を示します。

ロックは並行処理をコントロールするメカニズムです。最もよく知られるロック戦略は楽観的と悲観的ロックです。次のセクションでこれら2つの戦略の手短な説明を行います。現在Doctrineがサポートしているのは悲観的ロックです。

------------
楽観的ロック
------------

トランザクションが開始するときオブジェクトの状態/バージョンに注目されます。トランザクションが終了するとき注目された状態/バージョンの参与しているオブジェクトが現在の状態/バージョンと比較されます。状態/バージョンが異なる場合オブジェクトは他のトランザクションによって修正され現在のトランザクションは失敗します。このアプローチは'楽観的'(optimistic)と呼ばれます。複数のユーザーが同時に同じオブジェクト上のトランザクションに参加しないことを前提としているからです。

------------
悲観的ロック
------------

トランザクションに参加する必要のあるオブジェクトはユーザーがトランザクションを開始した瞬間にロックされます。ロックが有効な間、他のユーザーがこれらのオブジェクトで作動するトランザクションを始めることはありません。これによってトランザクションを始めるユーザー以外のユーザーが同じオブジェクトを修正しないことが保証されます。

Doctrineの悲観的オフラインロック機能はHTTPリクエストとレスポンスサイクルと/もしくは完了させるためにたくさんの時間がかかるアクションもしくはプロシージャの並行処理をコントロールするために使うことができます。

----------
例
----------

次のコードスニペットはDoctrineの悲観的オフラインロック機能の使い方を実演しています。

ロックがリクエストされたページでロックマネージャーインスタンスを取得します:

 // test.php

// ... $lockingManager = new Doctrine\_Locking\_Manager\_Pessimistic();

.. tip::

    300秒 =
    5分のタイムアウトをロックしようとする前に、タイムアウトした古いロックを必ず解放してください。これは``releaseAgedLocks()``メソッドを使用することで可能です。

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(1);

try { $lockingManager->releaseAgedLocks(300);

::

    $gotLock = $lockingManager->getLock($user, 'jwage');

    if ($gotLock)
    {
        echo "Got lock!";
    }
    else
    {
        echo "Sorry, someone else is currently working on this record";
    }

} catch(Doctrine\_Locking\_Exception $dle) { echo $dle->getMessage(); //
handle the error }

トランザクションが終了するページでロックマネジャーのインスタンスを取得します:

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(1);

$lockingManager = new Doctrine\_Locking\_Manager\_Pessimistic();

try { if (:code:`lockingManager->releaseLock(`\ user, 'jwage')) { echo
"Lock released"; } else { echo "Record was not locked. No locks
released."; } } catch(Doctrine\_Locking\_Exception $dle) { echo
$dle->getMessage(); // handle the error }

------------
技術的な詳細
------------

悲観的オフラインロックマネージャーはロックをデータベースで保存します(それゆえ'オフライン'です)。マネージャーをインスタンス化して``ATTR\_CREATE_TABLES``がTRUEに設定されているときに必要なロックテーブルは自動的に作成されます。インストール用の集中化と一貫したテーブル作成のプロシージャを提供するために将来この振る舞いが変更される可能性があります。

======
ビュー
======

データベースビューは複雑なクエリのパフォーマンスを多いに増大できます。これらをキャッシュされたクエリとして見なすことができます。``Doctrine_View``はデータベースビューとDQLクエリの統合を提供します。

------------
ビューを使う
------------

データベースでビューを使うのは簡単です。``Doctrine_View``クラスは既存のビューの作成と削除をする機能を提供します。

``Doctrine\_Query``によって実行されるSQLを保存することで``Doctrine\_View``クラスは``Doctrine_Query``クラスを統合します。

最初に新しい``Doctrine_Query``インスタンスを作成しましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumber p') ->limit(20);

データベースビューを指定するための``name``と同じように``Doctrine\_View``インスタンスを作成し``Doctrine_Query``インスタンスにこれを渡しましょう:

 // test.php

// ... :code:`view = new Doctrine_View(`\ q,
'RetrieveUsersAndPhonenumbers');

``Doctrine_View::create()``メソッドを使用してビューを簡単に作成できます:

 // test.php

// ... try { $view->create(); } catch (Exception $e) {}

代わりにデータベースビューを削除したい場合``Doctrine_View::drop()``メソッドを使います:

 // test.php

// ... try { $view->drop(); } catch (Exception $e) {}

ビューの使用はとても簡単です。``Doctrine\_Query``オブジェクトと同じようにビューの実行と結果の取得には``Doctrine_View::execute()``を使います:

 // test.php

// ... $users = $view->execute();

foreach ($users as :code:`user) { print_r(`\ us->toArray()); }

======
まとめ
======

Doctrineが提供するコア機能の大部分を見てきました。この本の次の章では日常生活を楽にするオプション機能の一部をカバーします。

[doc native-sql 次の章]ではDoctrine Query
Languageの代わりに配列とオブジェクトの間でデータをハイドレイトするネイティブなSQLの使い方を学びます。
