Doctrineのマイグレーションパッケージのプログラミングインターフェイスを通して本番のデータベースを簡単に更新できます。データベースがバージョン管理されバージョンを通して差し戻しできるように変更が行われます。

==========================
マイグレーションを実行する
==========================

マイグレーションクラスの作り方を学んだので次のセクションでDoctrinenのテスト環境でマイグレーションを実装できるようにマイグレーションの実行の仕方を見てみましょう。

最初に``Doctrine_Migration``の新しいインスタンスを作りこれにマイグレーションクラスへのパスを渡しましょう:

 $migration = new Doctrine\_Migration('/path/to/migration\_classes',
$conn);

    **NOTE**
    ``Doctrine\_Migration``コンストラクタの2番目の引数に注目してください。オプションの``Doctrine_Connection``インスタンスを渡すことができます。使うマイグレーションクラスの接続を渡さなければ、現在の接続が取り込まれます。

``migrate()``メソッドを呼び出すことで最新バージョンに移行できます:

 $migration->migrate();

特定のバージョンにマイグレートするには``migrate()``に引数を渡します。例えばバージョン0から3にマイグレートできます:

 $migration->migrate(3);

バージョン3から0に戻すことができます:

 $migration->migrate(0);

データベースの現在のバージョンを取得するには``getCurrentVersion()``メソッドを使います:

 echo $migration->getCurrentVersion();

 .. tip::


    ``migrate()``メソッドのバージョン番号の引数を省略するとDoctrineは内部でディレクトリで見つかるクラスの最新バージョン番号にマイグレートしようとします。

    **NOTE** **マイグレーションのトランザクション**
    内部ではDoctrineはトランザクションのマイグレーションバージョンをラップしません。マイグレーションクラスで例外とトランザクションを処理するのは開発者しだいです。トランザクションDDLをサポートするデータベースはごくわずかであることを覚えておいてください。大抵のデータベースでは、トランザクションでマイグレーションをラップする場合でも、create、alter、drop、renameなどのステートメントは効果があります。

====
実装
====

マイグレーションの実施方法を理解したので``migrate.php``という名前でテスト環境のスクリプトを実装してみましょう。

最初にマイグレーションクラスを保存する場所が必要なので``migrations``という名前のディレクトリを作りましょう:

 $ mkdir migrations

``migrate.php``スクリプトを作り次のコードを記入します:

 // migrate.php

require\_once('bootstrap.php');

$migration = new Doctrine\_Migration('migrations');
$migration->migrate();

============================
マイグレーションクラスを書く
============================

マイグレーションクラスは``Doctrine_Migration``を継承するシンプルなクラスで構成されます。``up()``と``down()``メソッドを定義します。これらのメソッドはそれぞれ指定されたマイグレーションバージョンでのデータベースの変更とその取り消しを意味します。

    **NOTE**
    クラスの名前がなんであれ、正しい順序でマイグレーションをロードするために使われる数字が含まれる接頭辞をクラスが含まれるファイルの名前につけなければなりません。

バージョン1から始まるデータベースをビルドするために使うマイグレーションクラスの例です。

最初のバージョンとして``migration_test``という名前の新しいテーブルを作りましょう:

 // migrations/1\_add\_table.php

class AddTable extends Doctrine\_Migration\_Base { public function up()
{ $this->createTable('migration\_test', array('field1' => array('type'
=> 'string'))); }

::

    public function down()
    {
        $this->dropTable('migration_test');
    }

}

前のバージョンで追加したテーブルに新しいカラムを追加した2番目のバージョンを作りましょう:

 // migrations/2\_add\_column.php

class AddColumn extends Doctrine\_Migration\_Base { public function up()
{ $this->addColumn('migration\_test', 'field2', 'string'); }

::

    public function down()
    {
        $this->removeColumn('migration_test', 'field2');
    }

}

最後に、``field1``カラムの型を変更してみましょう:

 // migrations/3\_change\_column.php

class ChangeColumn extends Doctrine\_Migration\_Base { public function
up() { $this->changeColumn('migration\_test', 'field2', 'integer'); }

::

    public function down()
    {
        $this->changeColumn('migration_test', 'field2', 'string');
    }

}

3つのマイグレーションクラスを作成したので以前実装した``migrate.php``スクリプトを実行できます:

 $ php migrate.php

データベースを見ると``migrate\_test``という名前のテーブルが存在し``migration_version``のバージョン番号が3に設定されることが確認できます。

最初の状態に差し戻したい場合``migrate.php``スクリプトで``migrate()``メソッドにバージョン番号を渡します:

 // migrate.php

// ... $migration = new Doctrine\_Migration('migrations');
$migration->migrate(0);

そして``migrate.php``スクリプトを実行します:

 $ php migrate.php

データベースを見ると、``up()``メソッドで行ったすべての内容が``down()``メソッドの内容によって差し戻されます。

------------------------
利用可能なオペレーション
------------------------

マイグレーションクラスでデータベースを変えるために利用できるメソッドの一覧は次の通りです。

^^^^^^^^^^^^^^^^^^
テーブルを作成する
^^^^^^^^^^^^^^^^^^

 // ... public function up() { $columns = array( 'id' => array( 'type'
=> 'integer', 'unsigned' => 1, 'notnull' => 1, 'default' => 0 ), 'name'
=> array( 'type' => 'string', 'length' => 12 ), 'password' => array(
'type' => 'string', 'length' => 12 ) );

::

        $options = array(
            'type'     => 'INNODB',
            'charset'  => 'utf8'
        );

        $this->createTable('table_name', $columns, $options);
    }

// ...

    **NOTE**
    スキーマを操作するために使われるデータ構造とデータベース抽象化レイヤーで使われるデータ構造が同じであることにお気づきかもしれません。これはマイグレーションクラスで指定されているオペレーションを実行するために内部でマイグレーションパッケージがデータベース抽象化レイヤーを使用しているからです。

^^^^^^^^^^^^^^^^^^
テーブルを削除する
^^^^^^^^^^^^^^^^^^

 // ... public function down() { $this->dropTable('table\_name'); } //
...

^^^^^^^^^^^^^^^^^^^^^^
テーブルをリネームする
^^^^^^^^^^^^^^^^^^^^^^

 // ... public function up() { $this->renameTable('old\_table\_name',
'new\_table\_name'); } // ...

^^^^^^^^^^^^^^
制約を作成する
^^^^^^^^^^^^^^

 // ... public function up() { $definition = array( 'fields' => array(
'username' => array() ), 'unique' => true );

::

        $this->createConstraint('table_name', 'constraint_name', $definition);
    }

// ...

^^^^^^^^^^^^^^
制約を削除する
^^^^^^^^^^^^^^

**Now the opposite ``down()`` would look like the following:**

 // ... public function down() { $this->dropConstraint('table\_name',
'constraint\_name'); } // ...

^^^^^^^^^^^^^^^^^^
外部キーを削除する
^^^^^^^^^^^^^^^^^^

 // ... public function up() { $definition = array( 'local' =>
'email\_id', 'foreign' => 'id', 'foreignTable' => 'email', 'onDelete' =>
'CASCADE' );

::

        $this->createForeignKey('table_name', 'email_foreign_key', $definition);
    }

// ...

``$definition``用の有効なオプションは次の通りです:

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``name`` \|\| オプションの制約名 \|\|
\|\| ``local`` \|\| ローカルフィールド \|\| \|\| ``foreign`` \|\|
外部参照フィールド \|\| \|\| ``foreignTable`` \|\| 外部テーブルの名前
\|\| \|\| ``onDelete`` \|\| 参照の削除アクション \|\| \|\| ``onUpdate``
\|\| 参照の更新アクション \|\| \|\| ``deferred`` \|\|
延期された制約チェック \|\|

^^^^^^^^^^^^^^^^^^
外部キーを削除する
^^^^^^^^^^^^^^^^^^

 // ... public function down() { $this->dropForeignKey('table\_name',
'email\_foreign\_key'); } // ...

^^^^^^^^^^^^^^^^
カラムを追加する
^^^^^^^^^^^^^^^^

 // ... public function up() { $this->addColumn('table\_name',
'column\_name', 'string', $length, $options); } // ...

^^^^^^^^^^^^^^^^^^^^
カラムをリネームする
^^^^^^^^^^^^^^^^^^^^

    **NOTE**
    sqliteのような一部のDBMSはカラムのリネームオペレーションを実装していません。sqlite接続を使用している場合カラムをリネームしようとすると例外が投げられます。

 // ... public function up() { $this->renameColumn('table\_name',
'old\_column\_name', 'new\_column\_name'); } // ...

^^^^^^^^^^^^^^^^
カラムを変更する
^^^^^^^^^^^^^^^^

**既存のカラムのアスペクトを変更する:**

 // ... public function up() { $options = array('length' => 1);
$this->changeColumn('table\_name', 'column\_name', 'tinyint', $options);
} // ...

^^^^^^^^^^^^^^^^
カラムを削除する
^^^^^^^^^^^^^^^^

 // ... public function up() { $this->removeColumn('table\_name',
'column\_name'); } // ...

^^^^^^^^^^^^^^^^^^^^^^^^
不可逆なマイグレーション
^^^^^^^^^^^^^^^^^^^^^^^^

.. tip::

    リバースできない``up()``メソッドでオペレーションを実行することがあります。例えばテーブルからカラムを削除する場合です。この場合新しい``Doctrine\_Migration_IrreversibleMigrationException``例外を投げる必要があります。

 // ... public function down() { throw new
Doctrine\_Migration\_IrreversibleMigrationException( 'The remove column
operation cannot be undone!' ); } // ...

^^^^^^^^^^^^^^^^^^^^^^
インデックスを追加する
^^^^^^^^^^^^^^^^^^^^^^

 // ... public function up() { $options = array('fields' => array(
'username' => array( 'sorting' => 'ascending' ), 'last\_login' =>
array()));

::

        $this->addIndex('table_name', 'index_name', $options)
    }

// ...

^^^^^^^^^^^^^^^^^^^^^^
インデックスを削除する
^^^^^^^^^^^^^^^^^^^^^^

 // ... public function down() { $this->removeIndex('table\_name',
'index\_name'); } // ...

------------------------
プレフックとポストフック
------------------------

モデルでデータベースのデータを変えることが必要な場合があります。テーブルを作成もしくは変更するので``up()``もしくは``down()``メソッドが処理された後でデータを変更しなければなりません。``preUp()``、``postUp()``、``preDown()``、と``postDown()``という名前でフックを用意します。定義すればこれらのメソッドは実行されます:

 // migrations/1\_add\_table.php

class AddTable extends Doctrine\_Migration\_Base { public function up()
{ $this->createTable('migration\_test', array('field1' => array('type'
=> 'string'))); }

::

    public function postUp()
    {
        $migrationTest = new MigrationTest();
        $migrationTest->field1 = 'Initial record created by migrations';
        $migrationTest->save();
    }

    public function down()
    {
        $this->dropTable('migration_test');
    }

}

    **NOTE**
    上記の例は``MigrationTest``モデルを作成し利用可能にしたことを前提とします。``up()``メソッドが実行されると``migration_test``テーブルが作成されるので``MigrationTest``モデルが使われます。このモデルの定義は下記の通りです。

 // models/MigrationTest.php

class MigrationTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('field1', 'string'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を学びます:

 # schema.yml

MigrationTest: columns: field1: string

----------------------
Up/Downの自動化
----------------------

Doctrineのマイグレーション機能では大抵の場合マイグレーションメソッドの反対側を自動化することが可能です。例えばマイグレーションのupで新しいカラムを作成する場合、downを簡単に自動化するのは可能で必要なのは作成されたカラムを削除することです。これは``up``と``down``の両方に対して``migrate()``メソッドを使用して実現可能です。

``migrate()``メソッドは$directionの引数を受け取り``up``もしくは``down``の値を持つようになります。この値は``column``、``table``、のようなメソッドの最初の引数に渡されます。

カラムの追加と削除を自動化する例は次の通りです。

 class MigrationTest extends Doctrine\_Migration\_Base { public function
migrate($direction) { :code:`this->column(`\ direction, 'table\_name',
'column\_name', 'string', '255'); } }

上記のマイグレーションでupを実行するときカラムが追加され、downが実行されるときカラムが削除されます。

自動化できるマイグレーションメソッドのリストは次の通りです:

\|\|~ 自動メソッド名 \|\|~ 自動化 \|\| \|\| ``table()`` \|\|
createTable()/dropTable() \|\| \|\| ``constraint()`` \|\|
createConstraint()/dropConstraint() \|\| \|\| ``foreignKey()`` \|\|
createForeignKey()/dropForeignKey() \|\| \|\| ``column()`` \|\|
addColumn()/removeColumn() \|\| \|\| ``index()`` \|\|
addIndex()/removeIndex() \|\|

--------------------------
マイグレーションを生成する
--------------------------

Doctrineはいくつかの異なる方法でマイグレーションクラスを生成する機能を提供します。既存のデータベースを再現するマイグレーションのセットを生成する、もしくは既存のモデルのセット用にデータベースを作成するマイグレーションクラスを生成します。2つのスキーマ情報の2つのセットの間の違いからマイグレーションを生成することもできます。

^^^^^^^^^^^^^^^^
データベースから
^^^^^^^^^^^^^^^^

既存のデータベース接続からマイグレーションのセットを生成するには、``Doctrine_Core::generateMigrationsFromDb()``を使います。

 Doctrine\_Core::generateMigrationsFromDb('/path/to/migration/classes');

^^^^^^^^^^^^^^^^
既存のモデルから
^^^^^^^^^^^^^^^^

既存のモデルのセットからマイグレーションのセットを生成するには、``Doctrine_Core::generateMigrationsFromModels()``を使うだけです。


Doctrine\_Core::generateMigrationsFromModels('/path/to/migration/classes',
'/path/to/models');

^^^^^^^^^^
差分ツール
^^^^^^^^^^

ときにはモデルを変更して変更に対するマイグレーション処理を自動化できるようにしたいことがあります。以前は変更に対してマイグレーションクラスを書かなければなりませんでした。しかし差分ツールによって変更を行い変更用のマイグレーションクラスを生成できます。

差分ツールはシンプルで使いやすいです。これは"from"と"to"を受け取り、これらは次のうちのどれかになります:

-  YAMLスキーマファイルへのパス
-  既存のデータベース接続の名前
-  モデルの既存のセットへのパス

2つのYAMLスキーマファイルを作るシンプルな例を考えます。1つは``schema1.yml``でもう1つは``schema2.yml``という名前です。

``schema1.yml``はシンプルな``User``モデルを含みます:

 # schema1.yml

User: columns: username: string(255) password: string(255)

スキーマを修正して``email_address``カラムを追加する場合を考えてみましょう:

 # schema1.yml

User: columns: username: string(255) password: string(255)
email\_address: string(255)

これでデータベースに新しいカラムを追加できるマイグレーションクラスを簡単に作ることができます:


Doctrine\_Core::generateMigrationsFromDiff('/path/to/migration/classes',
'/path/to/schema1.yml', '/path/to/schema2.yml');

これによって``/path/to/migration/classes/1236199329_version1.php``のパスでファイルが生み出されます。

 class Version1 extends Doctrine\_Migration\_Base { public function up()
{ $this->addColumn('user', 'email\_address', 'string', '255', array ());
}

::

    public function down()
    {
        $this->removeColumn('user', 'email_address');
    }

}

データベースを簡単にマイグレートして新しいカラムを追加できます！

======
まとめ
======

安全かつ簡単にスキーマを変更できるので本番のデータベーススキーマを変更するためにマイグレーション機能は大いに推奨されます。

マイグレーションはこの本で検討する最後の機能です。最後の章では日常業務で手助けになる他のトピックを検討します。最初に他の[doc
utilities :name]を検討しましょう。
