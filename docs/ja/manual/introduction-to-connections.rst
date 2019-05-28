========================================
DSN、Data Source Name
========================================

Doctrineを通してデータベースに接続するには、有効なDSN(Data Source
Name)を作らなければなりません。

DoctrineはPDOスタイルと同じようにPEAR
DB/MDB2のDSNをサポートします。次のセクションではPEARのDSN形式を扱います。PDOスタイルのDSNの詳しい情報が必要であれば[http://www.php.net/pdo
PDO]のドキュメントを参照してください。

DSNは次の部分で構成されます:

\|\|~ DSNの部分 \|\|~ 説明 \|\| \|\| ``phptype`` \|\|
PHPで使われるデータベース (すなわちmysql、pgsqlなど) \|\| \|\|
``dbsyntax`` \|\| SQL構文などで使われるデータベース。 \|\| \|\|
``protocol`` \|\|
使用するコミュニケーションプロトコル(すなわち、tcp、unixなど) \|\| \|\|
``hostspec`` \|\| ホストのスペック(hostname[:port]) \|\| \|\|
``database`` \|\| DBMSサーバーで使うデータベース \|\| \|\| ``username``
\|\| ログイン用のユーザー名 \|\| \|\| ``password`` \|\|
ログイン用のパスワード \|\| \|\| ``proto_opts`` \|\|
プロトコルで使われる \|\| \|\| ``option `` \|\|
URIクエリ文字列形式の追加接続オプション。オプションはアンパサンド(&)で分離されます。次のテーブルでオプションの不完全なリストを示します:
\|\|

**オプションのリスト**

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``charset`` \|\|
クライアントの文字集合を設定するバックエンドサポート。\|\| \|\|
``new_link``\|\|
同じホストに複数回接続する場合RDBMSの中には新しい接続を作成しないものがあります。このオプションによって新しい接続の強制が試みられます。\|\|

DSNは連想配列もしくは文字列のどちらかで提供できます。提供されたDSNの文字列フォーマットの完全な表記は次の通りです:


phptype(dbsyntax)://username:password@protocol+hostspec/database?option=value

大抵のバリアントは許容されます:

 phptype://username:password@protocol+hostspec:110//usr/db\_file.db
phptype://username:password@hostspec/database
phptype://username:password@hostspec phptype://username@hostspec
phptype://hostspec/database phptype://hostspec phptype:///database
phptype:///database?option=value&anotheroption=anothervalue
phptype(dbsyntax) phptype

現在サポートされるPDOのデータベースドライバは次の通りです:

\|\|~ ドライバの名前 \|\|~ サポートされるデータベース \|\| \|\|
``fbsql`` \|\| FrontBase \|\| \|\| ``ibase`` \|\| InterBase / Firebird
(PHP 5が必須) \|\| \|\| ``mssql`` \|\| Microsoft SQL Server
(Sybase**ではない**。Compile PHP --with-mssql) \|\| \|\| ``mysql`` \|\|
MySQL \|\| \|\| ``mysqli`` \|\| MySQL (新しい認証プロトコル) (PHP
5が必須) \|\| \|\| ``oci`` \|\| Oracle 7/8/9/10 \|\| \|\| ``pgsql`` \|\|
PostgreSQL \|\| \|\| ``querysim`` \|\| QuerySim \|\| \|\| ``sqlite``
\|\| SQLite 2 \|\|

サポートされる2番目のDSNは次の通りです

 phptype(syntax)://user:pass@protocol(proto\_opts)/database

データベース、オプションの値、ユーザー名もしくはパスワードにDSNを区切るために使われる文字が含まれる場合、URIの16進法のエンコーディングを通してエスケープできます:

\|\|~ 文字 \|\|~ 16進法 \|\| \|\| ``:`` \|\| %3a \|\| \|\| ``/`` \|\|
%2f \|\| \|\| ``@`` \|\| %40 \|\| \|\| ``+`` \|\| %2b \|\| \|\| ``(``
\|\| %28 \|\| \|\| ``)`` \|\| %29 \|\| \|\| `` ?`` \|\| %3f \|\| \|\|
``=`` \|\| %3d \|\| \|\| ``&`` \|\| %26 \|\|

機能の中にはすべてのデータベースでサポートされないものがあることに十分注意してくださるようお願いします。

----------
例
----------

**例 1.** ソケットを通したデータベースへの接続

 mysql://user@unix(/path/to/socket)/pear

**例 2.** 非標準ポートでのデータベースへの接続

 pgsql://user:pass@tcp(localhost:5555)/pear

    **NOTE**
    使うのであれば、IPアドレス``{127.0.0.1}``、ポートパラメータ(デフォルト:
    3306)は無視されます。

**例 3.** オプションを利用するUnixマシン上でのSQLiteへの接続

 sqlite:////full/unix/path/to/file.db?mode=0666

**例 4.** オプションを利用してWindowsマシンでSQLiteに接続する

 sqlite:///c:/full/windows/path/to/file.db?mode=0666

**例 5.** SSLを利用してMySQLiに接続する


mysqli://user:pass@localhost/pear?key=client-key.pem&cert=client-cert.pem

================
新しい接続を開く
================

Doctrineで新しいデータベース接続を開くのはとても簡単です。[http://www.php.net/PDO
PDO]を使う場合、新しいPDOオブジェクトを初期化するだけです。

[doc getting-started
:name]の章で作成した``bootstrap.php``ファイルを覚えていますか？Doctrineのオートローダーが登録されたコードで、新しい接続をインスタンス化します:

 // bootstrap.php

// ... $dsn = 'mysql:dbname=testdb;host=127.0.0.1'; $user = 'dbuser';
$password = 'dbpass';

:code:`dbh = new PDO(`\ dsn, $user, $password);
:code:`conn = Doctrine_Manager::connection(`\ dbh);

.. tip::

    ``Doctrine_Manager::connection()``にPDOインスタンスを直に渡すことでDoctrineは接続用のユーザー名とパスワードを認識できません。既存のPDOインスタンスから接続を読み取る方法がないからです。Doctrineがデータベースの作成と削除ができるようにユーザー名とパスワードが必要です。これを切り抜けるには``$conn``オブジェクトでユーザー名とパスワードオプションを直に設定します。

 // bootstrap.php

// ... $conn->setOption('username', $user); $conn->setOption('password',
$password);

======================
データベースの遅延接続
======================

データベースへの遅延接続は多くのリソースを節約できます。常に遅延接続を使うことが推奨されるので、実際にデータベース接続を必要とする機会はあまりないでしょう(すなわちDoctrineは必要なときだけデータベースに接続する)。

毎回のリクエストでデータベースの接続が必要ないページキャッシュなどでこの機能がとても役に立ちます。データベースへの接続は負荷の大きいオペレーションであることを覚えておいてください。

下記の例では、Doctrineの新しい接続を作成するときに、データベースへの接続は実際に必要になるまで作成されないことを示しています。

 // bootstrap.php

// ...

// この時点でデータベースへの接続は作成されない $conn =
Doctrine\_Manager::connection('mysql://username:password@localhost/test');

// 接続が必要な最初のときに、インスタンス化される //
このクエリによって接続が作成される $conn->execute('SHOW TABLES');

================
接続をテストする
================

この章の前のセクションを読んだ後で、接続を作成する方法を学ぶことにします。接続のインスタンス化をインクルードするためにブートストラップファイルを修正しましょう。この例ではSQLiteのメモリデータベースを使いますが、望むタイプのデータベース接続は何でも使えます。

``bootstrap.php``にデータベース接続を追加すると次のようになります:

 /\*\* \* Bootstrap Doctrine.php、オートローダーを登録して \*
接続属性を指定する \*/

require\_once('../doctrine/branches/1.2/lib/Doctrine.php');
spl\_autoload\_register(array('Doctrine', 'autoload')); $manager =
Doctrine\_Manager::getInstance();

$conn = Doctrine\_Manager::connection('sqlite::memory:', 'doctrine');

接続をテストするために``test.php``スクリプトを修正して小さなテストを実行しましょう。テストスクリプトが変数``$conn``が使えるようになったので接続が動作していることを確認するために小さなテストをセットアップしましょう:

最初に、testテーブルを作りレコードを挿入します:

 // test.php

// ... $conn->export->createTable('test', array('name' => array('type'
=> 'string'))); $conn->execute('INSERT INTO test (name) VALUES (?)',
array('jwage'));

データが挿入されて読み取れることを確認するために作成したばかりの``test``テーブルからシンプルな``SELECT``クエリを実行してみましょう:

 // test.php

// ... $stmt = $conn->prepare('SELECT \* FROM test'); $stmt->execute();
$results = :code:`stmt->fetchAll(); print_r(`\ results);

ターミナルから``test.php``を実行すると結果は次の通りです:

 $ php test.php Array ( [0] => Array ( [name] => jwage [0] => jwage )

)

======
まとめ
======

すばらしい！Doctrine接続の基本的なオペレーションを学びました。新しい接続を用意するためにDoctrineのテスト環境を修正しました。次の章の例では接続を使うのでこの環境は必要です。

[doc configuration
:name]の章に移動してDoctrineの属性システムを利用して機能と設定をコントロールする方法を学びます。
