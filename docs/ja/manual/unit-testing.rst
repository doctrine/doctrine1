Doctrineはユニットテストを使用するプログラムでテストされます。Wikipediaでユニットテストの[http://en.wikipedia.org/wiki/Unit\_testing
記事]を読むことができます。

================
テストを実施する
================

Doctrineに搭載されるテストを実施するにはライブラリフォルダだけでなくプロジェクト全体をチェックアウトする必要があります。

 $ svn co http://svn.doctrine-project.org/branches/1.2
/path/to/co/doctrine

Doctrineのチェックアウトのディレクトリに移動します。

 $ cd /path/to/co/doctrine

次のファイルとディレクトリが見えます。

 CHANGELOG COPYRIGHT lib/ LICENSE package.xml tests/ tools/ vendor/

 .. tip::

    認識できるテストの失敗ケースが用意されるのはめったにありません。もしくはDoctrineには後のバージョンまでコミットできないバグ修正もしくは強化リクエスト用のテストケースが存在します。もしくは単に問題の修正コードが存在しないので失敗のままのテストもあります。Doctrineのそれぞれのバージョンでどれだけの数のテストの失敗ケースがあるのかメーリングリストもしくはIRCでたずねることができます。

------
CLI
------

コマンドラインでテストを実施するには、php-cliをインストールしなければなりません。

``/path/to/co/doctrine/tests``フォルダに移動して``run.php``スクリプトを実行します:

 $ cd /path/to/co/doctrine/tests $ php run.php

これによってすべてのユニットテストを実行しているときに進行バーが出力されます。テストが終了したとき何が成功し何が失敗したのか報告されます。

CLIには特定のテスト、テストのグループを実施するもしくはテストスィートの名前でテストをフィルタリングするためのオプションがありあす。これらのオプションを確認するために次のコマンドを実行してください。

 $ php run.php -help

次のように個別のテストグループを実行できます:

 $ php run.php --group data\_dict

--------
ブラウザ
--------

``doctrine/tests/run.php``に移動すればブラウザでユニットテストを実施できます。オプションは変数``_GET``を通して設定できます。

例:

-  ``http://localhost/doctrine/tests/run.php``
-  ``http://localhost/doctrine/tests/run.php?filter=Limit&group[]=query&group[]=record``

    **CAUTION**
    テストの結果が環境に大きく左右されることがあることにご注意ください。例えば``php.ini``の``apc.enable_cli``ディレクティブが0に設定されている場合追加テストが失敗することがあります。

============
テストを書く
============

テストスィートを書き始めるとき、``TemplateTestCase.php``をコピーすることから始めます。サンプルのテストケースは次の通りです:

 class Doctrine\_Sample\_TestCase extends Doctrine\_UnitTestCase {
public function prepareTables() { $this->tables[] = "MyModel1";
$this->tables[] = "MyModel2"; parent::prepareTables(); }

::

    public function prepareData()
    {
      $this->myModel = new MyModel1();
      //$this->myModel->save();
    }

    public function testInit()
    {

    }

    // This produces a failing test
    public function testTest()
    {
        $this->assertTrue($this->myModel->exists());
        $this->assertEqual(0, 1);
        $this->assertIdentical(0, '0');
        $this->assertNotEqual(1, 2);
        $this->assertTrue((5 < 1));
        $this->assertFalse((1 > 2));
    }

}

class Model1 extends Doctrine\_Record { }

class Model2 extends Doctrine\_Record { }

    **NOTE**
    モデルの定義はテストケースファイルに直接含まれるもしくはこれらは``/path/to/co/doctrine/tests/models``に設置可能です。そうすればこれらはオートロードされます。

テストを書く作業を終えたら必ず``run.php``に次のコードを追加してください。

 $test->addTestCase(new Doctrine\_Sample\_TestCase());

run.phpを実行するとき新しい失敗ケースが報告されます。

--------------
チケットテスト
--------------

Doctrineにおいてtracに報告される個別のチケット用のテストの失敗ケースをコミットするのが慣行になっています。``/path/to/co/doctrine/tests/Ticket/``フォルダで見つかるすべてのテストケースを読むことでこれらの手数とケースは自動的にrun.phpに追加されます。

CLIから新しいテストケースのチケットを作成できます:

 $ php run.php --ticket 9999

チケット番号9999がまだ存在しない場合空白のテストケースクラスが``/path/to/co/doctrine/tests/Ticket/9999TestCase.php``に生成されます。

 class Doctrine\_Ticket\_9999\_TestCase extends Doctrine\_UnitTestCase {
}

------------------
テスト用のメソッド
------------------

^^^^^^^^^^^^^^^^^^^^^^^^
Equalをアサートする
^^^^^^^^^^^^^^^^^^^^^^^^

 // ... public function test1Equals1() { $this->assertEqual(1, 1); } //
...

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Not Equalをアサートする
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

 // ... public function test1DoesNotEqual2() { $this->assertNotEqual(1,
2); } // ...

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Identicalをアサートする
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

ロジックがより厳密で2つの値の比較に``===``を使用すること以外``assertIdentical()``メソッドは``assertEqual()``と同じです。

 // ... public function testAssertIdentical() {
$this->assertIdentical(1, '1'); } // ...

    **NOTE**
    1番目の引数の数字の1がPHPの整数型としてキャストされるのに対して2番目の引数の数字の1はPHPの文字列型としてキャストされるので明らかに失敗します。

^^^^^^^^^^^^^^^^^^^^^^
Trueをアサートする
^^^^^^^^^^^^^^^^^^^^^^

 // ... public function testAssertTrue() { $this->assertTrue(5 > 2); }
// ...

^^^^^^^^^^^^^^^^^^^^^^^^
Falseをアサートする
^^^^^^^^^^^^^^^^^^^^^^^^

 // ... public function testAssertFalse() { $this->assertFalse(5 < 2); }
// ...

--------------
モックドライバ
--------------

Doctrineはsqlite以外のすべてのドライバ用のモックドライバを使用します。次のコードスニペットはモックドライバの使い方を示します:

 class Doctrine\_Sample\_TestCase extends Doctrine\_UnitTestCase {
public function testInit() { $this->dbh = new
Doctrine\_Adapter\_Mock('oracle');
:code:`this->conn = Doctrine_Manager::getInstance()->openConnection(`\ this->dbh);
} }

クエリを実行するときこれらは本当のデータベースに対して実行されません。代わりにこれらは配列に収集され実行されたクエリとそれらに対するテストのアサーションを分析できます。

 class Doctrine\_Sample\_TestCase extends Doctrine\_UnitTestCase { //
...

::

    public function testMockDriver()
    {
        $user = new User();
        $user->username = 'jwage';
        $user->password = 'changeme';
        $user->save();

        $sql = $this->dbh->getAll();

        // 探しているクエリを見つけるためにSQL配列を出力する
        // print_r($sql);

        $this->assertEqual($sql[0], 'INSERT INTO user (username, password) VALUES (?, ?)');
    }

}

--------------------------
テストクラスのガイドライン
--------------------------

すべてのクラスはTestCaseと同等のものを少なくとも1つ持ち``Doctrine_UnitTestCase``を継承します。テストクラスはクラスもしくはクラスのアスペクトを参照し、それに応じて命名されます。

例:

-  ``Doctrine\_Record\_TestCase``は``Doctrine_Record``クラスを指し示すので良い名前です。
-  ``Doctrine\_Record\_State\_TestCase``は``Doctrine_Record``クラスの状態を指し示すのでこれも良い名前です。
-  ``Doctrine\_PrimaryKey_TestCase``は一般的すぎるので悪い名前です。

----------------------------
テストメソッドのガイドライン
----------------------------

メソッドはアジャイルなドキュメントをサポートし何が失敗したのか明確にわかるように名付けられます。これらはテストするシステムの情報も提供します。

例えばメソッドのテスト名として``Doctrine\_Export\_Pgsql_TestCase::testCreateTableSupportsAutoincPks()``は良い名前です。

テストメソッドの名前は長くなる可能性がありますが、メソッドの内容は長くならないようにすべきです。複数のアサート呼び出しが必要なら、メソッドを複数のより小さなメソッドに分割します。ロープもしくは関数の中にアサーションがあってはなりません。

    **NOTE**
    共通の命名規約として使われる``TestCase::test[methodName]``はDoctrineでは**されません**。ですのでこのケースでは``Doctrine\_Export\_Pgsql_TestCase::testCreateTable()``は許可されません！

======
まとめ
======

Doctrineのようなソフトウェアのピースにとってユニットテストは非常に重要です。これ無しでは、変更によって既存の作業ユースケースに悪影響があるのかを知るのは不可能です。ユニットテストのコレクションによって変更が既存の機能を壊さないことを確認できます。

次に[doc improving-performance
パフォーマンスを改善する]方法を学ぶために移動します。
