========
はじめに
========

最初から複数の接続を扱えるようにDoctrineは設計されてきました。個別に指定しない限りDoctrineはクエリの実行には現在の接続を使います。

この章ではDoctrineの接続の作成と扱い方を示します。

==========
接続を開く
==========

``Doctrine\_Manager``はスタティックメソッドの``Doctrine_Manager::connection()``を提供します。このメソッドは新しい接続を開きます。

この例では新しい接続を開く方法を示しています:

 // test.php

// ... $conn =
Doctrine\_Manager::connection('mysql://username:password@localhost/test',
'connection 1');

==============
接続を読み取る
==============

``Doctrine_Manager::connection()``メソッドを使用し引数を渡さない場合現在の接続が返されます:

 // test.php

// ... $conn2 = Doctrine\_Manager::connection();

if ($conn === $conn2) { echo 'Doctrine\_Manager::connection() returns
the current connection'; }

==========
現在の接続
==========

現在の接続は最後に開いた接続です。次の例では``Doctrine_Manager``インスタンスから現在の接続を取得する方法が示されています:

 // test.php

// ... $conn2 =
Doctrine\_Manager::connection('mysql://username2:password2@localhost/test2',
'connection 2');

if ($conn2 === $manager->getCurrentConnection()) { echo 'Current
connection is the connection we just created!'; }

====================
現在の接続を変更する
====================

``Doctrine_Manager::setCurrentConnection()``を呼び出すことで現在の接続を変更できます。

 // test.php

// ... $manager->setCurrentConnection('connection 1');

echo $manager->getCurrentConnection()->getName(); // connection 1

==============
接続を反復する
==============

foreach句にマネージャーオブジェクトを渡すことで開いた接続をイテレートできます。``Doctrine_Manager``が特殊な``IteratorAggregate``インターフェイスを実装するのでこれは可能です。

 // test.php

// ... foreach($manager as $conn) { echo $conn->getName() . ""; }

================
接続名を取得する
================

次のコードで``Doctrine_Connection``インスタンスの名前を簡単に取得できます:

 // test.php

// ... $conn = Doctrine\_Manager::connection();

$name = :code:`manager->getConnectionName(`\ conn);

echo $name; // connection 1

============
接続を閉じる
============

接続を閉じたりDoctrine接続レジストリから削除のは簡単です:

 // test.php

// ... $conn = Doctrine\_Manager::connection();

:code:`manager->closeConnection(`\ conn);

接続を閉じるがDoctrine接続レジストリから削除したくない場合は次のコードが利用できます:

 // test.php

// ... $conn = Doctrine\_Manager::connection(); $conn->close();

======================
すべての接続を取得する
======================

``Doctrine_Manager::getConnections()``メソッドを使用して登録されたすべての接続の配列を読み取ることができます:

 // test.php

// ... $conns = :code:`manager->getConnections(); foreach (`\ conns as
$conn) { echo $conn->getName() . ""; }

上記のコードは初期の頃に``Doctrine_Manager``オブジェクトをイテレートすることと同じです。再度掲載します:

 // test.php

// ... foreach ($manager as $conn) { echo $conn->getName() . ""; }

==================
接続をカウントする
==================

``Countable``インターフェイスを実装するので``Doctrine_Manager``オブジェクトから接続数を取得できます。

 // test.php

// ... :code:`num = count(`\ manager);

echo $num;

上記のコードは次のコードと同じです:

 // test.php

// ... $num = $manager->count();

========================
データベースの作成と削除
========================

Doctrineを使用して接続を作成するとき、これらの接続に関連するデータベースの作成と削除する機能が簡単に手に入ります。

``Doctrine\_Manager``もしくは``Doctrine_Connection``クラスで提供されるメソッドを使うことで簡単にできます。

次のコードではインスタンス化された接続をすべてイテレートして``dropDatabases()``/``createDatabases()``メソッドを呼び出します:

 // test.php

// ... $manager->createDatabases();

$manager->dropDatabases();

**特定の接続に対してデータベースを削除/作成する**

接続インスタンスで``dropDatabase()``/``createDatabase()``メソッドを呼び出すことで特定の``Doctrine_Connection``インスタンス用のデータベースを削除もしくは作成できます:

 // test.php

// ... $conn->createDatabase();

$conn->dropDatabase();

==================
カスタム接続を書く
==================

ときには独自のカスタム接続クラスを作りこれらを活用する機能が必要になることがあります。mysqlを拡張するもしくは独自のデータベース型を独自に書くことが必要になることがあります。これはいくつかのクラスを書き接続型をDoctrineに登録することで可能です。

カスタム接続を作成するにはまず次のクラスを書く必要があります。

 class Doctrine\_Connection\_Test extends Doctrine\_Connection\_Common {
}

class Doctrine\_Adapter\_Test implements Doctrine\_Adapter\_Interface {
// ... all the methods defined in the interface }

ではこれらをDoctrineに登録します:

 // bootstrap.php

// ... $manager->registerConnectionDriver('test',
'Doctrine\_Connection\_Test');

次のような少しの変更でこれが実現されます:

 $conn =
$manager->openConnection('test://username:password@localhost/dbname');

接続にどんなクラスが使われるのか確認すればそれらが上記で定義したクラスであることがわかります。

 echo
get\_class(:code:`conn); // Doctrine_Connection_Test echo get_class(`\ conn->getDbh());
// Doctrine\_Adapter\_Test

======
まとめ
======

Doctrineの接続すべてを学びましたので[doc introduction-to-models
:name]の章でモデルに直に飛び込む準備ができました。Doctrineのモデルも少し学びました。少し遊んで最初のテストモデルを作成しDoctrineが提供するマジックを見ることになります。
