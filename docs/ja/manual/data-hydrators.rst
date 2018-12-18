Doctrineは``Doctrine_Query``インスタンスを
ユーザーが利用できるPHPのデータに変換するためのデータハイドレーターの概念を持ちます。データをハイドレイトするもっとも明らかな方法はこれをオブジェクトグラフに入れるとモデル／クラスインスタンスが返されることです。ときにはデータを配列にハイドレイトしたい場合、ハイドレーションを使わないと単独のスカラーの値が買えされます。この章は異なるハイドレーションのタイプおよび独自のハイドレーションのタイプの書き方もデモンストレートします。

============================
コアハイドレーションメソッド
============================

Doctrineはもっとも共通のハイドレーションのニーズによってあなたを助けてくれる少数のコアのハイドレーションメソッドを提供します。

--------
レコード
--------

最初のタイプは``HYDRATE_RECORD``でこれはデフォルトのハイドレーションのタイプです。これはクエリからデータをとりこれはオブジェクトグラフにハイドレイトします。このタイプによった次のようなことが可能になります。

 $q = Doctrine\_Core::getTable('User') ->createQuery('u')
->leftJoin('u.Email e') ->where('u.username = ?', 'jwage');

$user = $q->fetchOne(array(), Doctrine\_Core::HYDRATE\_RECORD);

echo $user->Email->email;

上記のクエリのデータは1つのクエリによって検索されレコードハイドレーターによってオブジェクトグラフにハイドレイトされました。データベースからの結果セットではなくレコードは扱うのが難しいので、これによってデータを扱うのがはるかに簡単になります。

----
配列
----

配列ハイドレーションタイプは``HYDRATE_ARRAY``定数によって表現されます。PHPオブジェクトを使ってデータをオブジェクトグラフにハイドレイトする代わりにPHP配列を使うこと以外、上記のレコードハイドレーションは理想的です。オブジェクトの代わりに配列を使う利点はこれらがずっと速くハイドレーションは時間がかからないことです。

同じ結果を実行したい場合、同じデータにアクセスできますが、PHP配列経由になります。

 $q = Doctrine\_Core::getTable('User') ->createQuery('u')
->leftJoin('u.Email e') ->where('u.username = ?', 'jwage');

$user = $q->fetchOne(array(), Doctrine\_Core::HYDRATE\_ARRAY);

echo $user['Email']['email'];

--------
スカラー
--------

スカラーハイドレーションタイプは``HYDRATE_SCALAR``定数によって表現されデータをハイドレイトするためにとても速くて効率的な方法です。この方法の欠点はこれはデータをオブジェクトグラフにハイドレイトしないことで、これはたくさんのレコードを扱うときに扱いにくいフラットな長方形の結果セットを返します。

 $q = Doctrine\_Core::getTable('User') ->createQuery('u')
->where('u.username = ?', 'jwage');

$user = $q->fetchOne(array(), Doctrine\_Core::HYDRATE\_SCALAR);

echo $user['u\_username'];

上記のクエリは次のようなデータ構造を生み出します:

 $user = array( 'u\_username' => 'jwage', 'u\_password' => 'changeme',
// ... );

クエリがデータよりもJOINされた多くのリレーションシップを持つ場合
ユーザーが存在するすべてのレコードでユーザーが重複することになります。これはたくさんのレコードを扱うときに扱いが難しいので欠点です。

----------------
シングルスカラー
----------------

単独のスカラー値だけを返したいことがよくあります。これはシングルスカラーハイドレーションの方法で可能で``HYDRATE\_SINGLE_SCALAR``属性によって表現されます。

このハイドレーションタイプによって次のように1人のユーザーが持つ電話番号の数を簡単に数えることができます:

 $q = Doctrine\_Core::getTable('User') ->createQuery('u')
->select('COUNT(p.id)') ->leftJoin('u.Phonenumber p')
->where('u.username = ?', 'jwage');

$numPhonenumbers = $q->fetchOne(array(),
Doctrine\_Core::HYDRATE\_SINGLE\_SCALAR);

echo $numPhonenumbers;

これはより複雑な方法でデータをハイドレイトしてこれらの結果から値を得るよりもはるかによい方法です。これによって本当に欲しいデータをとても速く効率的に得ることができます。

------------
オンデマンド
------------

よりメモリーの使用量が少ないハイドレーションの方法を使いたいのであれば``HYDRATE\_ON_DEMAND``定数によって表現されるオンデマンドハイドレーションを使うことができます。これは一度の1つのレコードグラフのみをハイドレイトするので使われるメモリーがより少なくて済むことを意味します。

 // Doctrine\_Collection\_OnDemandのインスタンスを返す $result =
:code:`q->execute(array(), Doctrine_Core::HYDRATE_ON_DEMAND); foreach (`\ result
as $obj) { // ... }

``Doctrine\_Collection_OnDemand``はイテレートするときに一度にそれぞれのオブジェクトをハイドレイトするのでこの結果はより少ないメモリーで済みます。これは最初にデータベースからすべてのデータをPHPにロードし、返すデータ構造全体を変換する必要がないからです。

------------------------
入れ子集合のレコード階層
------------------------

入れ子集合のビヘイビアを使うモデルのために、入れ子集合のツリーを入れ子オブジェクトの実際の階層にハイドレイトするレコード階層ハイドレーションの方法を使うことができます。

 $categories = Doctrine\_Core::getTable('Category') ->createQuery('c')
->execute(array(), Doctrine\_Core::HYDRATE\_RECORD\_HIERARCHY);

これで``\__children``という名前のマッピングされた値のプロパティにアクセスすることでレコードの子にアクセスできます。名前の衝突を避けるためにこの名前にはプレフィックスとしてアンダースコアがつけられています。

 foreach ($categories->getFirst()->get('\_\_children') as $child) { //
... }

--------------------
入れ子集合の配列階層
--------------------

入れ子集合階層をオブジェクトではなく配列にハイドレイトしたい場合``HYDRATE\_ARRAY\_HIERARCHY``定数を使ってこれを実現できます。これはオブジェクトの代わりにPHP配列を使っている以外は``HYDRATE\_RECORD_HIERARCHY``と同じです。

 $categories = Doctrine\_Core::getTable('Category') ->createQuery('c')
->execute(array(), Doctrine\_Core::HYDRATE\_ARRAY\_HIERARCHY);

次のことができるようになります:

 foreach ($categories[0]['\_\_children'] as $child) { // ... }

==============================
ハイドレーションメソッドを書く
==============================

Doctrineは独自のハイドレーション方法を書きこれらを登録する機能を提供します。必要なのは``Doctrine\_Hydrator\_Abstract``を継承するクラスを書きこれを``Doctrine_Manager``で登録することです。

最初にサンプルのハイドレイターのクラスを書いてみましょう:

 class Doctrine\_Hydrator\_MyHydrator extends
Doctrine\_Hydrator\_Abstract { public function hydrateResultSet($stmt) {
$data = $stmt->fetchAll(PDO::FETCH\_ASSOC); // $dataで何かを行う return
$data; } }

これを使うためには``Doctrine_Manager``で登録します:

 // bootstrap.php

// ... $manager->registerHydrator('my\_hydrator',
'Doctrine\_Hydrator\_MyHydrator');

クエリを実行するとき、``my_hydrator``を渡せばデータをハイドレイトするクラスが使われます。

 $q->execute(array(), 'my\_hydrator');
