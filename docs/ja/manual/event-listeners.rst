========
はじめに
========

Doctrineは柔軟なイベントリスナーアーキテクチャを提供します。このアークテクチャは異なるイベントのリスニングだけでなくリスニングされるメソッドの実行を変更することも可能にします。

様々なDoctrineコンポーネント用の異なるリスナーとフックがあります。リスナーは個別のクラスであるのに対してフックはリスニングされるクラスの範囲内の空のテンプレートメソッドです。

フックはイベントリスナーよりもシンプルですがこれらは異なるアスペクトの分離が欠けています。``Doctrine_Record``フックを使用する例は次の通りです:

 // models/BlogPost.php

class BlogPost extends Doctrine\_Record { // ...

::

    public function preInsert($event)
    {
        $invoker = $event->getInvoker();

        $invoker->created = date('Y-m-d', time());
    }

}

    **NOTE**
    これまでたくさんのモデルを定義してきたので、``BlogModel``に対して独自の``setTableDefinition()``を定義できます。もしくは独自のカスタムモデルを作りましょう！

次のコードで上記のモデルを使うことができます。``title``、``body``と``created``カラムをモデルに追加することを前提とします:

 // test.php

// ... $blog = new BlogPost(); $blog->title = 'New title'; $blog->body =
'Some content'; $blog->save();

echo $blog->created;

上記の例はPHPが理解する現在の日付を出力します。

それぞれのリスナーとフックメソッドは
``Doctrine\_Event``オブジェクトを1つのパラメータとして受け取ります。``Doctrine_Event``オブジェクトは問題のイベントの情報を格納しリスニングされるメソッドの実行を変更できます。

ドキュメントの目的のために多くのメソッドテーブルは``params``という名前のカラムで提供されます。このカラムはパラメータの名前は与えられたイベント上でイベントオブジェクトが保有するパラメータの名前を示します。例えば``preCreateSavepoint``イベントは作成された``savepoint``の名前を持つ1つのパラメータを持ちます。

============
接続リスナー
============

接続リスナーは``Doctrine\_Connection``とそのモジュール(``Doctrine\_Transaction``など)のメソッドをリスニングするために使われます。すべてのリスナーメソッドはリスニングされるイベントの情報を格納する``Doctrine_Event``オブジェクトを1つの引数として受け取ります。

------------------------
新しいリスナーを作成する
------------------------

リスナーを定義する方法は3つあります。最初に``Doctrine_EventListener``を継承するクラスを作成することでリスナーを作成できます:

 class MyListener extends Doctrine\_EventListener { public function
preExec(Doctrine\_Event $event) {

::

    }

}

``Doctrine\_EventListener``を継承するクラスを定義することで``Doctrine\_EventListener\_Interface``の範囲内ですべてのメソッドを定義する必要はありません。これは``Doctrine_EventListener``が既にこれらすべてのメソッド用の空のスケルトンを持つからです。

ときに``Doctrine\_EventListener``を継承するリスナーを定義できないことがあります(他の基底クラスを継承するリスナーを用意できます)。この場合``Doctrine\_EventListener_Interface``を実装させることができます。

 class MyListener implements Doctrine\_EventListener\_Interface { public
function preTransactionCommit(Doctrine\_Event $event) {} public function
postTransactionCommit(Doctrine\_Event $event) {}

::

    public function preTransactionRollback(Doctrine_Event $event) {}
    public function postTransactionRollback(Doctrine_Event $event) {}

    public function preTransactionBegin(Doctrine_Event $event) {}
    public function postTransactionBegin(Doctrine_Event $event) {}

    public function postConnect(Doctrine_Event $event) {}
    public function preConnect(Doctrine_Event $event) {}

    public function preQuery(Doctrine_Event $event) {}
    public function postQuery(Doctrine_Event $event) {}

    public function prePrepare(Doctrine_Event $event) {}
    public function postPrepare(Doctrine_Event $event) {}

    public function preExec(Doctrine_Event $event) {}
    public function postExec(Doctrine_Event $event) {}

    public function preError(Doctrine_Event $event) {}
    public function postError(Doctrine_Event $event) {}

    public function preFetch(Doctrine_Event $event) {}
    public function postFetch(Doctrine_Event $event) {}

    public function preFetchAll(Doctrine_Event $event) {}
    public function postFetchAll(Doctrine_Event $event) {}

    public function preStmtExecute(Doctrine_Event $event) {}
    public function postStmtExecute(Doctrine_Event $event) {}

}

    **CAUTION**
    すべてのリスナーメソッドはここで定義しなければなりません。さもないとPHPは致命的エラーを投げます。

リスナーを作成する3番目の方法はとても優雅です。``Doctrine_Overloadable``を実装するクラスを作成します。インターフェイスは1つのメソッド:
``\__call()``のみを持ちます。このメソッドは*すべての*イベントと補足するために使われます。

 class MyDebugger implements Doctrine\_Overloadable { public function
\_\_call($methodName, $args) { echo $methodName . ' called !'; } }

------------------
リスナーを追加する
------------------

setListener()でリスナーを接続に追加できます。

 $conn->setListener(new MyDebugger());

複数のリスナーが必要な場合はaddListener()を使います。

 $conn->addListener(new MyDebugger()); $conn->addListener(new
MyLogger());

--------------------
プレ接続とポスト接続
--------------------

下記のリスナーのすべては``Doctrine\_Connection``クラスに含まれます。これらすべては``Doctrine_Event``のインスタンスです。

\|\|~ メソッド \|\|~ リスニング \|\|~ パラメータ \|\| \|\|
``preConnect(Doctrine_Event $event)`` \|\|
Doctrine\_Connection::connection() \|\| \|\| \|\|
``postConnect(Doctrine_Event $event)`` \|\|
Doctrine\_Connection::connection() \|\| \|\|

------------------------
トランザクションリスナー
------------------------

下記のリスナーのすべては``Doctrine\_Transaction``クラスに含まれます。これらすべてに``Doctrine_Event``のインスタンスが渡されます。

\|\|~ メソッド \|\|~ リスニング \|\|~ パラメータ \|\| \|\|
``preTransactionBegin()`` \|\| ``beginTransaction()`` \|\| \|\| \|\|
``postTransactionBegin()`` \|\| ``beginTransaction()`` \|\| \|\| \|\|
``preTransactionRollback()`` \|\| ``rollback()`` \|\| \|\| \|\|
``postTransactionRollback()`` \|\| ``rollback()`` \|\| \|\| \|\|
``preTransactionCommit()`` \|\| ``commit()`` \|\| \|\| \|\|
``postTransactionCommit()`` \|\| ``commit()`` \|\| \|\| \|\|
``preCreateSavepoint()`` \|\| ``createSavepoint()`` \|\| ``savepoint``
\|\| \|\| ``postCreateSavepoint()`` \|\| ``createSavepoint()`` \|\|
``savepoint`` \|\| \|\| ``preRollbackSavepoint()`` \|\|
``rollbackSavepoint()`` \|\| ``savepoint`` \|\| \|\|
``postRollbackSavepoint()`` \|\| ``rollbackSavepoint()`` \|\|
``savepoint`` \|\| \|\| ``preReleaseSavepoint()`` \|\|
``releaseSavepoint()`` \|\| ``savepoint`` \|\| \|\|
``postReleaseSavepoint()`` \|\| ``releaseSavepoint()`` \|\|
``savepoint`` \|\|

 class MyTransactionListener extends Doctrine\_EventListener { public
function preTransactionBegin(Doctrine\_Event $event) { echo 'beginning
transaction... '; }

::

    public function preTransactionRollback(Doctrine_Event $event)
    {
        echo 'rolling back transaction... ';
    }

}

------------------
クエリ実行リスナー
------------------

下記のリスナーのすべては``Doctrine\_Connection``と``Doctrine\_Connection\_Statement``クラスに含まれます。そしてこれらすべては``Doctrine_Event``のインスタンスです。

\|\|~ メソッド \|\|~ リスニング \|\|~ パラメータ \|\| \|\|
``prePrepare()`` \|\| ``prepare()`` \|\| ``query`` \|\| \|\|
``postPrepare()`` \|\| ``prepare()`` \|\| ``query`` \|\| \|\|
``preExec()`` \|\| ``exec()`` \|\| ``query`` \|\| \|\| ``postExec()``
\|\| ``exec()`` \|\| ``query, rows`` \|\| \|\| ``preStmtExecute()`` \|\|
``execute()`` \|\| ``query`` \|\| \|\| ``postStmtExecute()`` \|\|
``execute()`` \|\| ``query`` \|\| \|\| ``preExecute()`` \|\|
``execute()`` \* \|\| ``query`` \|\| \|\| ``postExecute()`` \|\|
``execute()`` \* \|\| ``query`` \|\| \|\| ``preFetch()`` \|\|
``fetch()`` \|\| ``query, data`` \|\| \|\| ``postFetch()`` \|\|
``fetch()`` \|\| ``query, data`` \|\| \|\| ``preFetchAll()`` \|\|
``fetchAll()`` \|\| ``query, data`` \|\| \|\| ``postFetchAll()`` \|\|
``fetchAll()`` \|\| ``query, data`` \|\|

    **NOTE**
    ``Doctrine\_Connection::execute()``がプリペアードステートメントパラメータで呼び出されるときにのみ``preExecute()``と``postExecute()``は起動します。そうではない場合``Doctrine_Connection::execute()``は``prePrepare()``、``postPrepare()``、``preStmtExecute()``と``postStmtExecute()``を起動します。

========================
ハイドレーションリスナー
========================

ハイドレーションリスナーは結果セットのハイドレーション処理をリスニングするために使われます。ハイドレーション処理をリスニングするために2つのメソッド:
``preHydrate()``と``postHydrate()``が存在します。

ハイドレーションリスナーを接続レベルで設定する場合、``preHydrate()``と``postHydrate()``ブロックの範囲内のコードは複数のコンポーネントの結果セットの範囲内ですべてのコンポーネントによって実行されます。テーブルレベルで同様のリスナーを追加する場合、テーブルのデータがハイドレイトされているときのみ起動します。

フィールド:
``first\_name``、``last\_name``と``age``を持つ``User``クラスを考えてみましょう。次の例では``first\_name``と``last\_name``フィールドに基づいて``full\__name``と呼ばれる生成フィールドを常にビルドするリスナーを作成します。

 // test.php

// ... class HydrationListener extends Doctrine\_Record\_Listener {
public function preHydrate(Doctrine\_Event $event) { $data =
$event->data;

::

        $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];
        $event->data = $data;
    }

}

行う必要があるのは``User``レコードにこのリスナーを追加して複数のユーザーを取得することです:

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');
$userTable->addRecordListener(new HydrationListener());

$q = Doctrine\_Query::create() ->from('User');

$users = $q->execute();

foreach ($users as $user) { echo $user->full\_name; }

================
レコードリスナー
================

``Doctrine\_Record``は``Doctrine_Connection``とよく似たリスナーを提供します。グローバル、接続、テーブルレベルでリスナーを設定できます。

利用可能なすべてのリスナーメソッドの一覧は次の通りです:
下記のリスナーすべてが``Doctrine\_Record``と``Doctrine\_Validator``クラスに含まれます。そしてこれらすべてに``Doctrine_Event``のインスタンスが渡されます。

\|\|~ メソッド \|\|~ リスニング \|\| \|\| ``preSave()`` \|\| ``save()``
\|\| \|\| ``postSave()`` \|\| ``save()`` \|\| \|\| ``preUpdate()`` \|\|
レコードが``DIRTY``のとき``save()`` \|\| \|\| ``postUpdate()`` \|\|
レコードが``DIRTY``のとき``save()`` \|\| \|\| ``preInsert()`` \|\|
レコードが``DIRTY``のとき``save()`` \|\| \|\| ``postInsert()`` \|\|
レコードが``DIRTY``のとき``save()`` \|\| \|\| ``preDelete()`` \|\|
``delete()`` \|\| \|\| ``postDelete()`` \|\| ``delete()`` \|\| \|\|
``preValidate()`` \|\| ``validate()`` \|\| \|\| ``postValidate()`` \|\|
``validate()`` \|\|

接続リスナーと同じようにレコードリスナーを定義する方法は3つあります:
``Doctrine\_Record\_Listener``を継承する、``Doctrine\_Record\_Listener\_Interface``を実装するもしくは``Doctrine_Overloadable``を実装するです。

次の例では``Doctrine_Overloadable``を実装することでグローバルレベルのリスナーを作成します:

 class Logger implements Doctrine\_Overloadable { public function
\_\_call($m, $a) { echo 'caught event ' . $m;

::

        // do some logging here...
    }

}

マネージャーにリスナーを追加するのは簡単です:

 $manager->addRecordListener(new Logger());

マネージャーレベルのリスナーを追加することでこれらの接続の範囲内ですべてのテーブル/レコードに影響を及ぼします。次の例では接続レベルのリスナーを作成します:

 class Debugger extends Doctrine\_Record\_Listener { public function
preInsert(Doctrine\_Event $event) { echo 'inserting a record ...'; }

::

    public function preUpdate(Doctrine_Event $event)
    {
        echo 'updating a record...';
    }

}

接続にリスナーを追加するのも簡単です:

 $conn->addRecordListener(new Debugger());

リスナーが特定のテーブルのみにアクションを適用するようにリスナーをテーブル固有のものにしたい場合がよくあります。

例は次の通りです:

 class Debugger extends Doctrine\_Record\_Listener { public function
postDelete(Doctrine\_Event $event) { echo 'deleted ' .
$event->getInvoker()->id; } }

このリスナーを任意のテーブルに追加するのは次のようにできます:

 class MyRecord extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $this->addListener(new Debugger());
    }

}

==============
レコードフック
==============

\|\|~ メソッド \|\|~ リスニング \|\| \|\| ``preSave()`` \|\| ``save()``
\|\| \|\| ``postSave()`` \|\| ``save()`` \|\| \|\| ``preUpdate()`` \|\|
レコード状態が``DIRTY``であるとき``save()`` \|\| \|\| ``postUpdate()``
\|\| レコード状態が``DIRTY``であるとき``save()`` \|\| \|\|
``preInsert()`` \|\| レコード状態が``DIRTY``であるとき``save()`` \|\|
\|\| ``postInsert()`` \|\| レコード状態が``DIRTY``であるとき``save()``
\|\| \|\| ``preDelete()`` \|\| ``delete()`` \|\| \|\| ``postDelete()``
\|\| ``delete()`` \|\| \|\| ``preValidate()`` \|\| ``validate()`` \|\|
\|\| ``postValidate()`` \|\| ``validate()`` \|\|

``preInsert()``と``preUpdate()``メソッドを利用するシンプルな例は次の通りです:

 class BlogPost extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 200);
$this->hasColumn('content', 'string'); $this->hasColumn('created',
'date'); $this->hasColumn('updated', 'date'); }

::

    public function preInsert($event)
    {
        $this->created = date('Y-m-d', time());
    }

    public function preUpdate($event)
    {
        $this->updated = date('Y-m-d', time());
    }

}

============
DQLフック
============

レコードリスナーをグローバル、それぞれの接続で、もしくは特定のレコードインスタンスで追加することができます。``Doctrine_Query``は``preDql\*()``フックを実装します。これはクエリが実行されるときに、追加されたレコードリスナーもしくはモデルインスタンス自身でチェックされます。フックを起動したクエリを変更できるフックのためにクエリはクエリの``from``部分に関連するすべてのモデルをチェックします。

DQLで使うことができるフックのリストは次の通りです:

\|\|~ メソッド \|\|~ リスニング \|\| \|\| ``preDqlSelect()`` \|\|
``from()`` \|\| \|\| ``preDqlUpdate()`` \|\| ``update()`` \|\| \|\|
``preDqlDelete()`` \|\| ``delete()`` \|\|

下記のコードは``User``モデル用の``SoftDelete``機能を実装するモデルにレコードリスナーを直接追加する例です。

.. tip::

    SoftDeleteの機能はDoctrineのビヘイビアとして含まれます。このコードは実行されるクエリを修正するためにDQLリスナーをselect、delete、updateする方法を実演しています。Doctrine\_Record::setUp()の定義で$this->actAs('SoftDelete')を指定することでSoftDeleteビヘイビアを使うことができます。

 class UserListener extends Doctrine\_EventListener { /\*\* \* Skip the
normal delete options so we can override it with our own \* \* @param
Doctrine\_Event $event \* @return void \*/ public function
preDelete(Doctrine\_Event $event) { $event->skipOperation(); }

::

    /**
     * Implement postDelete() hook and set the deleted flag to true
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function postDelete(Doctrine_Event $event)
    {
        $name = $this->_options['name'];
        $event->getInvoker()->$name = true;
        $event->getInvoker()->save();
    }

    /**
     * Implement preDqlDelete() hook and modify a dql delete query so it updates the deleted flag
     * instead of deleting the record
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function preDqlDelete(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field = $params['alias'] . '.deleted';
        $q = $event->getQuery();
        if ( ! $q->contains($field)) {
            $q->from('')->update($params['component'] . ' ' . $params['alias']);
            $q->set($field, '?', array(false));
            $q->addWhere($field . ' = ?', array(true));
        }
    }

    /**
     * Implement preDqlDelete() hook and add the deleted flag to all queries for which this model 
     * is being used in.
     *
     * @param Doctrine_Event $event 
     * @return void
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field = $params['alias'] . '.deleted';
        $q = $event->getQuery();
        if ( ! $q->contains($field)) {
            $q->addWhere($field . ' = ?', array(false));
        }
    }

}

オプションとして上記のリスナーのメソッドは下記のユーザークラスに設置できます。Doctrineはそこでフックを同じようにチェックします:

 class User extends Doctrine\_Record { // ...

::

    public function preDqlSelect()
    {
        // ...
    }

    public function preDqlUpdate()
    {
        // ...
    }

    public function preDqlDelete()
    {
        // ...
    }

}

これらのDQLコールバックがチェックされるようにするには、これらを明示的に有効にしなければなりません。これはそれぞれのクエリに対して少量のオーバーヘッドを追加するので、デフォルトでは無効です。以前の章で既にこの属性を有効にしました。

思い出すためにコードを再掲載します:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine::ATTR\_USE\_DQL\_CALLBACKS,
true);

Userモデルとやりとりをするとき削除フラグが考慮されます:

レコードインスタンスを通してユーザーを削除します:

 $user = new User(); $user->username = 'jwage'; $user->password =
'changeme'; $user->save(); $user->delete();

    **NOTE**
    ``$user->delete()``を呼び出しても実際にはレコードは削除されず代わりに削除フラグがtrueに設定されます。

 $q = Doctrine\_Query::create() ->from('User u');

echo $q->getSql();

 SELECT u.id AS u**id, u.username AS u**username, u.password AS
u**password, u.deleted AS u**deleted FROM user u WHERE u.deleted = ?

    **NOTE** ``"u.deleted =
    ?"``が//true//のパラメータの値でwhere条件に自動的に追加されたことに注目してください。

========================
複数のリスナーを連結する
========================

異なるイベントリスナーを連結することができます。このことは同じイベントをリスニングするために複数のリスナーを追加できることを意味します。次の例では与えられた接続用に2つのリスナーを追加します:

この例では``Debugger``と``Logger``は両方とも``Doctrine_EventListener``を継承します:

 $conn->addListener(new Debugger()); $conn->addListener(new Logger());

====================
イベントオブジェクト
====================

----------------------
インボーカーを取得する
----------------------

``getInvoker()``を呼び出すことでイベントを起動したオブジェクトを取得できます:

 class MyListener extends Doctrine\_EventListener { public function
preExec(Doctrine\_Event $event) { $event->getInvoker(); //
Doctrine\_Connection } }

--------------
イベントコード
--------------

``Doctrine_Event``は定数をイベントコードとして使用します。利用可能なイベントの定数の一覧は下記の通りです:

-  ``Doctrine\_Event::CONN_QUERY``
-  ``Doctrine\_Event::CONN_EXEC``
-  ``Doctrine\_Event::CONN_PREPARE``
-  ``Doctrine\_Event::CONN_CONNECT``
-  ``Doctrine\_Event::STMT_EXECUTE``
-  ``Doctrine\_Event::STMT_FETCH``
-  ``Doctrine\_Event::STMT_FETCHALL``
-  ``Doctrine\_Event::TX_BEGIN``
-  ``Doctrine\_Event::TX_COMMIT``
-  ``Doctrine\_Event::TX_ROLLBACK``
-  ``Doctrine\_Event::SAVEPOINT_CREATE``
-  ``Doctrine\_Event::SAVEPOINT_ROLLBACK``
-  ``Doctrine\_Event::SAVEPOINT_COMMIT``
-  ``Doctrine\_Event::RECORD_DELETE``
-  ``Doctrine\_Event::RECORD_SAVE``
-  ``Doctrine\_Event::RECORD_UPDATE``
-  ``Doctrine\_Event::RECORD_INSERT``
-  ``Doctrine\_Event::RECORD_SERIALIZE``
-  ``Doctrine\_Event::RECORD_UNSERIALIZE``
-  ``Doctrine\_Event::RECORD\_DQL_SELECT``
-  ``Doctrine\_Event::RECORD\_DQL_DELETE``
-  ``Doctrine\_Event::RECORD\_DQL_UPDATE``

フックの使い方と返されるコードの例は次の通りです:

 class MyListener extends Doctrine\_EventListener { public function
preExec(Doctrine\_Event $event) { $event->getCode(); //
Doctrine\_Event::CONN\_EXEC } }

class MyRecord extends Doctrine\_Record { public function
preUpdate(Doctrine\_Event $event) { $event->getCode(); //
Doctrine\_Event::RECORD\_UPDATE } }

----------------------
インボーカーを取得する
----------------------

``getInvoker()``メソッドは与えられたイベントを起動したオブジェクトを返します。例えばイベント用の``Doctrine\_Event::CONN\_QUERY``インボーカーは``Doctrine_Connection``オブジェクトです。

``Doctrine_Record``インスタンスが保存されupdateがデータベースに発行されるときに起動する``preUpdate()``という名前のレコードフックの使い方の例は次の通りです:

 class MyRecord extends Doctrine\_Record { public function
preUpdate(Doctrine\_Event $event) { $event->getInvoker(); //
Object(MyRecord) } }

--------------------------------
次のオペレーションをスキップする
--------------------------------

リスナーチェーンのビヘイビアの変更と同様にリスニングされているメソッドの実行の変更のために``Doctrine_Event``は多くのメソッドを提供します。

多くの理由からリスニングされているメソッドの実行をスキップしたいことがあります。これは次のように実現できます(``preExec()``は任意のリスナーメソッドにできることに注意してください):

 class MyListener extends Doctrine\_EventListener { public function
preExec(Doctrine\_Event $event) { // some business logic, then:

::

        $event->skipOperation();
    }

}

--------------------------
次のリスナーをスキップする
--------------------------

リスナーのチェーンを使うとき次のリスナーの実行をスキップしたいことがあります。これは次のように実現できます:

 class MyListener extends Doctrine\_EventListener { public function
preExec(Doctrine\_Event $event) { // some business logic, then:

::

        $event->skipNextListener();
    }

}

======
まとめ
======

イベントリスナーはDoctrineの素晴らしい機能で[doc behaviors
:name]に結びつけられます。これらは最小量のコードで非常に複雑な機能を提供します。

これでパフォーマンスを改善するためのベストな機能である[doc caching
:name]を検討する準備ができました。
