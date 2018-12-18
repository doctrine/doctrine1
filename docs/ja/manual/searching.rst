========
はじめに
========

検索は巨大なトピックなので、この章全体では``Searchable``と呼ばれるビヘイビアを専門に扱います。これは全文インデックス作成と検索ツールでデータベースとファイルの両方で使うことができます。

次の定義を持つ``NewsItem``クラスを考えてみましょう:

 // models/NewsItem.php

class NewsItem extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('body', 'clob'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

NewsItem: columns: title: string(255) body: clob

ユーザーが異なるニュースの項目を検索できるアプリケーションを考えてみましょう。これを実装する明らかな方法はフォームを構築し投稿された値に基づいて次のようなDQLクエリを構築することです:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('NewsItem i')
->where('n.title LIKE ? OR n.content LIKE ?');

アプリケーションが成長するにつれてこの種のクエリはとても遅くなります。例えば``%framework%``パラメータで以前のクエリを使うとき、(``framework``という単語を含むタイトルもしくは内容を持つすべてのニュースの項目を見つけることと同等です)データベースはテーブルのそれぞれの列をトラバースしなければなりません。当然ながらこれは非常に遅くなります。

Doctrineはこの問題を検索コンポーネントとインバースインデックスで解決します。最初に定義を少し変えてみましょう:

 // models/NewsItem.php

class NewsItem extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $this->actAs('Searchable', array(
                'fields' => array('title', 'content')
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

NewsItem: actAs: Searchable: fields: [title, content] # ...

上記のモデルで生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('NewsItem'));
echo $sql[0] . ""; echo $sql[1] . ""; echo $sql[2];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE news\_item\_index (id BIGINT, keyword VARCHAR(200), field
VARCHAR(50), position BIGINT, PRIMARY KEY(id, keyword, field, position))
ENGINE = INNODB CREATE TABLE news\_item (id BIGINT AUTO\_INCREMENT,
title VARCHAR(255), body LONGTEXT, PRIMARY KEY(id)) ENGINE = INNODB
ALTER TABLE news\_item\_index ADD FOREIGN KEY (id) REFERENCES
news\_item(id) ON UPDATE CASCADE ON DELETE CASCADE

Here we tell Doctrine that
``NewsItem``クラスがsearchable(内部ではDoctrineが``Doctrine\_Template_Searchable``をロードする)として振る舞い``title``と``content``フィールドは全文検索用のインデックス付きフィールドとしてマークされます。これは``NewsItem``が追加もしくは更新されるたびにDoctrineは次のことを行うことを意味します:

1. インバース検索インデックスを更新するもしくは
2. インバース検索インデックスに新しいエントリを追加する(バッチでインバース検索インデックスを更新するのが効率的であることがあります)

================
インデックス構造
================

Doctrineが使用するインバースインデックスの構造は次の通りです:

[ (string) keyword] [ (string) field ] [ (integer) position ] [ (mixed)
[foreign\_keys] ]

\|\|~ カラム \|\|~ 説明 \|\| \|\| ``keyword`` \|\|
検索できるテキストのキーワード \|\| \|\| ``field`` \|\|
キーワードが見つかるフィールド \|\| \|\| ``position`` \|\|
キーワードが見つかる位置 \|\| \|\| ``[foreign_keys]`` \|\|
インデックスが作成されるレコードの外部キー \|\|

``NewsItem``の例において``[foreign_keys]``は``NewsItem(id)``への外部キー参照と``onDelete
=> CASCADE``制約を持つ1つの``id``フィールドを格納します。

このテーブルの列のようになりますの例は次のようになります:

\|\|~ キーワード \|\|~ フィールド \|\|~ 位置 \|\|~ id \|\| \|\|
``database`` \|\| ``title`` \|\| ``3`` \|\| ``1``\|\|

この例において単語の``database``は``1``の``id``を持つ``NewsItem``の``title``フィールドの3番目の単語です。

====================
インデックスのビルド
====================

検索可能なレコードがデータベースにinsertされるときDoctrineはインデックスビルドのプロシージャを実行します。プロシージャが検索リスナーによって起動されているときこれはバックグラウンドで行われます。このプロシージャのフェーズは次の通りです:

1. ``Doctrine\_Search_Analyzer``基底クラスを使用してテキストを分析する
2. 分析されたすべてのキーワード用に新しい列をインデックステーブルに挿入する

新しい検索可能なエントリが追加されるときインデックステーブルを更新したくなく、むしろ特定の間隔でインデックステーブルをバッチ更新したい場合があります。直接の更新機能を無効にするにはビヘイビアを添付する際にbatchUpdatesオプションをtrueに設定する必要があります:

 // models/NewsItem.php

class NewsItem extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $this->actAs('Searchable', array(
                'fields' => array('title', 'content')
                'batchUpdates' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

NewsItem: actAs: Searchable: fields: [title, content] batchUpdates: true
# ...

更新プロシージャの実際のバッチは``batchUpdateIndex()``メソッドによって起動します。これは2つのオプション引数:
``limit``と``offset``を受けとります。バッチでインデックス化されるエントリ数を制限するためにlimitが使用できoffsetはインデックス作成を始める最初のエントリを設定するために使用できます。

最初に新しい``NewsItem``レコードを挿入してみましょう:

 // test.php

// ... $newsItem = new NewsItem(); $newsItem->title = 'Test';
$newsItem->body = 'test'; $newsItem->save();

    **NOTE**
    バッチ更新を有効にしない場合``NewsItem``レコードを挿入もしくは更新するときにインデックスは自動的に更新されます。バッチ更新を有功にする場合次のコードでバッチ更新を実行できます:

 // test.php

// ... $newsItemTable = Doctrine\_Core::getTable('NewsItem');
$newsItemTable->batchUpdateIndex();

====================
テキストアナライザー
====================

デフォルトではDoctrineはテキスト分析のために``Doctrine\_Search\_Analyzer_Standard``を使用します。このクラスは次のことを実行します:

-  'and'、'if'などのストップワードをはぎとる。よく使われ検索には関係ないのと、インデックスのサイズを適切なものにするため。
-  すべてのキーワードを小文字にする。標準アナライザーはすべてのキーワードを小文字にするので単語を検索するとき'database'と'DataBase'は等しいものとしてみなされる。
-  アルファベットと数字ではないすべての文字はホワイトスペースに置き換える。通常のテキストでは例えば'database.'などアルファベットと数字ではない文字がキーワードに含まれるからである。標準のアナライザーはこれらをはぎとるので'database'は'database.'にマッチします
-  すべてのクォテーション記号を空の文字列に置き換えるので"O'Connor"は"oconnor"にマッチします

``Doctrine\_Search\_Analyzer_Interface``を実装することで独自のアナライザークラスを書くことができます。``MyAnalyzer``という名前のアナライザーを作成する例は次の通りです:

 // models/MyAnalyzer.php

class MyAnalyzer implements Doctrine\_Search\_Analyzer\_Interface {
public function analyze($text) { :code:`text = trim(`\ text); return
$text; } }

    **NOTE**
    検索アナライザーは``analyze()``という名前の1つのメソッドを持たなければなりません。このメソッドはインデックス化される入力テキストの修正版を返します。

このアナライザーは検索オブジェクトに次のように適用されます:

 // test.php

// ... $newsItemTable = Doctrine\_Core::getTable('NewsItem'); $search =
$newsItemTable ->getTemplate('Doctrine\_Template\_Searchable')
->getPlugin();

$search->setOption('analyzer', new MyAnalyzer());

==========
クエリ言語
==========

``Doctrine_Search``はApache
Luceneに似たクエリ言語を提供します。``Doctrine\_Search_Query``は人間が読解でき、構築が簡単なクエリ言語を同等の複雑なDQLに変換します。そしてこのDQLは通常のSQLに変換されます。

==============
検索を実行する
==============

次のコードはレコードのidと関連データを読み取るシンプルな例です。

 // test.php

// ... $newsItemTable = Doctrine\_Core::getTable('NewsItem');

$results = :code:`newsItemTable->search('test'); print_r(`\ results);

上記のコードは次のクエリを実行します:

 SELECT COUNT(keyword) AS relevance, id FROM article\_index WHERE id IN
(SELECT id FROM article\_index WHERE keyword = ?) AND id IN (SELECT id
FROM article\_index WHERE keyword = ?) GROUP BY id ORDER BY relevance
DESC

コードの出力は次の通りです:

 $ php test.php Array ( [0] => Array ( [relevance] => 1 [id] => 1 )

)

実際の``NewsItem``オブジェクトを読み取るために別のクエリでこれらの結果を使うことができます:

 // test.php

// ... :code:`ids = array(); foreach (`\ results as $result) { $ids[] =
$result['id']; }

$q = Doctrine\_Query::create() ->from('NewsItem i') ->whereIn('i.id',
$ids);

$newsItems = $q->execute();

print\_r($newsItems->toArray());

上記の例は次の出力を生み出します:

 $ php test.php Array ( [0] => Array ( [id] => 1 [title] => Test [body]
=> test )

)

オプションとして検索インデックスを使用して結果を制限するwhere条件サブクエリで修正するために``search()``メソッドにクエリオブジェクトを渡すことができます。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('NewsItem i');

$q = Doctrine\_Core::getTable('Article') ->search('test', $q);

echo $q->getSqlQuery();

上記の``getSql()``の呼び出しは次のSQLクエリを出力します:

 SELECT n.id AS n**id, n.title AS n**title, n.body AS n\_\_body FROM
news\_item n WHERE n.id IN (SELECT id FROM news\_item\_index WHERE
keyword = ? GROUP BY id)

クエリを実行して``NewsItem``オブジェクトを取得できます:

 // test.php

// ... $newsItems = $q->execute();

print\_r($newsItems->toArray());

上記の例は次の出力を生み出します:

 $ php test.php Array ( [0] => Array ( [id] => 1 [title] => Test [body]
=> test )

)

============
ファイル検索
============

前に述べたように``Doctrine\_Search``はファイル検索にも使うことができます。検索可能なディレクトリを用意したい場合を考えてみましょう。最初に``Doctrine\_Search\_File``のインスタンスを作る必要があります。これは``Doctrine_Search``の子クラスでファイル検索に必要な機能を提供します。

 // test.php

// ... $search = new Doctrine\_Search\_File();

2番目に行うことはインデックステーブルを生成することです。デフォルトではDoctrineはデータベースのインデックスクラスを``FileIndex``
と名づけます。

上記のモデルによって生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('FileIndex'));

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE file\_index (url VARCHAR(255), keyword VARCHAR(200), field
VARCHAR(50), position BIGINT, PRIMARY KEY(url, keyword, field,
position)) ENGINE = INNODB

``Doctrine_Core::createTablesFromArray()``メソッドを使用することでデータベースで実際のテーブルを作ることができます:

 // test.php

// ... Doctrine\_Core::createTablesFromArray(array('FileIndex'));

ファイルサーチャーを使い始めることができます。この例では``models``ディレクトリのインデックスを作りましょう:

 // test.php

// ... $search->indexDirectory('models');

``indexDirectory()``はディレクトリを再帰的にイテレートしインデックステーブルを更新しながらその範囲のすべてのファイルを分析します。

最後にインデックス化されたファイルの範囲内でテキストのピースの検索を始めることができます:

 // test.php

// ... $results = :code:`search->search('hasColumn'); print_r(`\ results);

上記の例は次の出力を生み出します:

 $ php test.php Array ( [0] => Array ( [relevance] => 2 [url] =>
models/generated/BaseNewsItem.php )

)

======
まとめ
======

``Searchable``ビヘイビアのすべてを学んだので[doc hierarchical-data
:name]の章で``NestedSet``ビヘイビアの詳細を学ぶ準備ができています。``NestedSet``は``Searchable``ビヘイビアのように大きなトピックなので1つの章全体で扱います。
