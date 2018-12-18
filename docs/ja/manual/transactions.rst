========
はじめに
========

データベーストランザクションはデータベースマネジメントシステムもしくは似たようなシステムとのインタラクションのユニットです。トランザクションは完結させるか停止するかどちらかでなければならず他のトランザクションから独立した整合性と信頼性のある方法で扱われます。理想的には、データベースシステムはそれぞれのトランザクションに対してACID(Atomicity、Consistency、Isolation、とDurability)プロパティのすべてを保証します。

-  //[http://en.wikipedia.org/wiki/Atomicity
   原子性(Atomicity)]//はすべてのタスクのトランザクションが実行されるかそれともまったく行われないことを保証するDBMSの機能を示します。資金の転送は完全にやり通すか複数の理由から停止するかどちらかですが、原始性はあるアカウントにお金が入らない場合、他のアカウントが負債を得ることはないことを保証します。
-  //[http://en.wikipedia.org/wiki/Database\_consistency
   Consistency(一貫性)]//はトランザクションの開始時と終了時にデータベースが正しい状態にあることを示します。これはトランザクションはデータベースのルール、もしくは//整合性制約//を破ることができないことを意味します。整合性制約がすべてのアカウントがプラス残高でなければならないことを述べる場合、このルールに違反するトランザクションは停止します。
-  //[http://en.wikipedia.org/wiki/Isolation*%28computer\_science%29
   隔離性(Isolation)]//は他のすべてのオペレーションからトランザクション内のオペレーションを分離させるアプリケーションの機能を示します。これはトランザクション外部のオペレーションは中間状態のデータを見ることができないことを意味します;転送がまだ処理されている間にがクエリが実行された場合でも、銀行のマネージャーはファンドが特定のアカウントもしくは他のアカウントのどちらかに転送されることがわかります。よりフォーマルには、隔離性はトランザクションの履歴(もしくは[http://en.wikipedia.org/wiki/Schedule*%28computer\_science%29
   スケジュール])が[http://en.wikipedia.org/wiki/Serializability
   serializable]であることを意味します。パフォーマンスの理由から、この機能はもっとも緩やかな制約であることが多いです。詳細は[http://en.wikipedia.org/wiki/Isolation\_%28computer\_science%29
   隔離性]の記事を参照してください。
-  //[http://en.wikipedia.org/wiki/Durability\_%28computer\_science%29
   持続性(Durability)]//はユーザーが成功の通知を受けると、トランザクションが永続化され、取り消しされないことが保証されることを示します。このことはシステム障害を乗り越え、[http://en.wikipedia.org/wiki/Database\_system
   データベースシステム]が整合性制約をチェックしトランザクションを停止する必要がないことを意味します。すべてのトランザクションは[http://en.wikipedia.org/wiki/Database\_log
   ログ]に書き込まれトランザクション前の正しい状態にシステムを再現できます。ログで安全になった後でトランザクションはコミットのみできます。

Doctrineにおいてデフォルトではすべてのオペレーションはトランザクションにラップされます。Doctrineが内部で動作する方法について注目すべきことは次の通りです:

-  Doctrineはアプリケーションレベルのトランザクションネスティングを使用する
-  (最も外側のコミットが呼び出されるとき)Doctrineは常に``INSERT`` /
   ``UPDATE`` /
   ``DELETE``クエリを実行する。オペレーションは次の順序で実行されます:
   すべてのinsert、すべてのupdateと最後にすべてのdelete。同じコンポーネントのdeleteオペレーションが1つのクエリに集約されるようにDoctrineはdeleteを最適化する方法を知っています。

最初に新しいトランザクションを始める必要があります:

 $conn->beginTransaction();

次に実行されているクエリになるオペレーションをいくつか実行します:

 $user = new User(); $user->name = 'New user'; $user->save();

$user = Doctrine\_Core::getTable('User')->find(5); $user->name =
'Modified user'; $user->save();

``commit()``メソッドを使用することですべてのクエリをコミットできます:

 $conn->commit();

============
ネスティング
============

Doctrine
DBALでトランザクションを簡単にネストできます。ネストされたトランザクションを実演するシンプルな例を示す下記のコードを確認しましょう。

最初に``saveUserAndGroup()``という名前のPHP関数を作ってみましょう:

 function saveUserAndGroup(Doctrine\_Connection $conn, User $user, Group
$group) { $conn->beginTransaction();

::

    $user->save();

    $group->save();

    $conn->commit();

}

別のトランザクション内部でメソッドを利用します:

 try { $conn->beginTransaction();

::

    saveUserAndGroup($conn,$user,$group);
    saveUserAndGroup($conn,$user2,$group2);
    saveUserAndGroup($conn,$user3,$group3);

    $conn->commit();

} catch(Doctrine\_Exception $e) { $conn->rollback(); }

    **NOTE**
    ``saveUserAndGroup()``への3つの呼び出しがトランザクションでラップされ、それぞれの関数呼び出しは独自のネストされたトランザクションを始めることに注目してください。

====================
Savepoints
====================

Doctrineはトランザクションのセーブポイントをサポートします。このことは名前有りのトランザクションを設定してこれらをネストできることを意味します。

``Doctrine_Transaction::beginTransaction(:code:`savepoint)``は```\ savepoint``の名前で名付けられたトランザクションセーブポイントを設定し、現在のトランザクションが同じ名前のセーブポイントを持つ場合、古いセーブポイントは削除され新しいものが設定されます。

 try { $conn->beginTransaction(); // 何らかのオペレーションをここで行う

::

    // mysavepointと呼ばれる新しいセーブポイントを作成する
    $conn->beginTransaction('mysavepoint');
    try {
        // 何らかのオペレーションをここで行う

        $conn->commit('mysavepoint');
    } catch(Exception $e) {
        $conn->rollback('mysavepoint');
    }
    $conn->commit();

} catch(Exception $e) { $conn->rollback(); }

``Doctrine_Transaction::rollback($savepoint)``はトランザクションを名前付きのセーブポイントにロールバックします。セーブポイントの後で現在のトランザクションが列に行った修正はロールバックで取り消しになります。

    **NOTE**
    例えばMysqlの場合、セーブポイントの後でメモリーに保存された列のロックを開放しません。

名前付きのセーブポイントの後で設定されたセーブポイントは削除されます。

``Doctrine_Transaction::commit($savepoint)``は現在のトランザクションのセーブポイントのセットから名前付きのセーブポイントを削除します。

コミットを実行するもしくはセーブポイントの名前パラメータ無しでロールバックが呼び出されている場合現在のトランザクションのすべてのセーブポイントは削除されます。

 try { $conn->beginTransaction(); // ここで何らかのオペレーションを行う

::

    // mysavepointと呼ばれる新しいセーブポイントを作成する
    $conn->beginTransaction('mysavepoint');

    // ここで何らかのオペレーションを行う

    $conn->commit();   // すべてのセーブポイントを削除する

} catch(Exception $e) { $conn->rollback(); //
すべてのセーブポイントを削除する }

========================
Isolationレベル
========================

トランザクションの独立性レベル(isolation
level)はデフォルトのトランザクションのビヘイビアを設定します。'独立性レベル'という名前が示すように、設定がそれぞれのトランザクションの独立性の程度、もしくはトランザクション内部でどんな種類のロックがクエリに関連付けされているかを決定します。利用できるレベルは4つあります(厳密性の昇順):

: ``READ UNCOMMITTED`` :
トランザクションがまれな場合で、この設定はいわゆる'ダーティリード(dirty
read)'を許可します。1つのトランザクション内部のクエリは別のトランザクションのコミットされていない変更によって影響を受けます。

: ``READ COMMITTED`` :
コミットされた更新は別のトランザクションの範囲内で見えます。トランザクション内の理想的なクエリは異なる結果を返すことができることを意味します。一部のDBMSではこれはデフォルトです。

: ``REPEATABLE READ`` :
トランザクションの範囲内では、すべての読み込みが一貫しています。これはMysql
INNODBエンジンのデフォルトです。

: ``SERIALIZABLE`` :
トランザクションが通常の``SELECT``クエリを持つ場合、他のトランザクションで更新が許可されない。

transactionモジュールを取得するには、次のコードを使います:

 $tx = $conn->transaction;

独立性レベルをREAD COMMITTEDに設定する:

 $tx->setIsolation('READ COMMITTED');

独立性レベルをSERIALIZABLEに設定する:

 $tx->setIsolation('SERIALIZABLE');

.. tip::

    ドライバの中には(Mysqlのように)現在の独立性レベルの取得をサポートするものがあります。次のようにできます:

 $level = $tx->getIsolation();

======
まとめ
======

トランザクションはデータベースの質と一貫性を保証する偉大な機能です。トランザクションを理解したのでイベントサブフレームワークについて学ぶ準備ができています。

イベントサブフレームワークはDoctrineのコアメソッドにフックを入れることを可能にする偉大な機能でコアコードを一行も修正せずに内部機能のオペレーションを変更します。
