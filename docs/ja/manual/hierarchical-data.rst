====
はじめに
====

大抵のユーザーは一度はSQLデータベースで階層データを扱います。階層データの管理はリレーショナルデータベースが意図していることはではないことは疑いの余地はありません。リレーショナルデータベースのテーブルは(XMLのように)階層的ではなく、シンプルでフラットなリストです。階層データは親子のリレーションを持ちリレーショナルデータベーステーブルで自然に表現されません。

我々の目的のために、階層データはデータのコレクションとしてそれぞれのアイテムは単独の親とゼロもしくはそれ以上の子を持ちます(例外はrootアイテムで、これは親を持ちません)。階層データはフォーラムとメーリングリストのスレッド、ビジネス組織のチャート、コンテンツマネジメントのカテゴリ、製品カテゴリを含む様々なデータベースアプリケーションで見つかります。
階層データモデルにおいて、データは木のような構造に編成されます。木構造は親/子のリレーションを使用する情報の反復を可能にします。木構造のデータの説明に関しては、[http://en.wikipedia.org/wiki/Tree\_data\_structure
ここ]を参照してください。

リレーショナルデータベースでツリー構造を管理する方法は主に3つあります:

-  隣接リストモデル
-  入れ子集合モデル(もしくは修正版先行順木走査アルゴリズムとも知られる)
-  経路実体化モデル

**次のリンク先で詳細な説明があります:**

-  [http://www.dbazine.com/oracle/or-articles/tropashko4
   http://www.dbazine.com/oracle/or-articles/tropashko4]
-  [http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
   http://dev.mysql.com/tech-resources/articles/hierarchical-data.html]

=====
入れ子集合
=====

----
はじめに
----

入れ子集合はとても早い読み込みアクセス方法を提供する階層データを保存するための解決方法です。しかしながら、入れ子集合の更新はコストがかかります。それゆえこの解決方法は書き込みよりも読み込みがはるかに多い階層に最適です。ウェブの性質から、この方法は大抵のウェブアプリケーションに当てはまります。

入れ子集合の詳細に関しては、次の記事をご覧ください:

-  [http://www.sitepoint.com/article/hierarchical-data-database/2
   http://www.sitepoint.com/article/hierarchical-data-database/2]
-  [http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
   http://dev.mysql.com/tech-resources/articles/hierarchical-data.html]

--------
セットアップする
--------

モデルを入れ子集合としてセットアップするには、モデルの``setUp()``メソッドにコードを追加しなければなりません。例として下記の``Category``モデルを考えてみましょう:

 // models/Category.php

class Category extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); }

::

    public function setUp()
    {
        $this->actAs('NestedSet');       
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Category: actAs: [NestedSet] columns: name: string(255)

Doctrineのテンプレートモデルの詳細情報は[doc behaviors :index
:name]の章で見つかります。これらのテンプレートはモデルに機能を追加します。入れ子集合の例において、3の追加フィールド:
``lft``、``rgt``と``level``が得られます。``lft``と``rgt``フィールドを気にする必要はありません。これらは内部で木構造を管理するために使われます。しかしながら``level``フィールドは関係があります。整数の値が木の範囲内のノードの深さを表すからです。レベル0はrootノードを意味します。レベル1はrootノードの直接の子であることを意味します。ノードから``level``フィールドを読み込むことで適切なインデントで木を簡単に表示できます。

    **CAUTION**
    ``lft``、``rgt``、``level``に値を割り当ててはなりません。これらは入れ子集合で透過的に管理されているからです。

--------
マルチプルツリー
--------

入れ子集合の実装によってテーブルが複数のrootノードを持つ、すなわち同じテーブルで複数の木を持つことが可能になります。

下記の例は``Category``モデルで複数のrootをセットアップして使う方法を示しています:

 // models/Category.php

class Category extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $options = array(
            'hasManyRoots'     => true,
            'rootColumnName'   => 'root_id'
        );
        $this->actAs('NestedSet', $options);
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Category: actAs: NestedSet: hasManyRoots: true rootColumnName: root\_id
columns: name: string(255)

``rootColumnName``は木を区別するために使われるカラムです。新しいrootノードを作成するとき``root_id``を手動で設定する選択肢があります。さもなければDoctrineが値を割り当てます。

一般的に``root\_id``を直接扱う必要はありません。例えば、新しいノードを既存の木に差し込むもしくはツリーの間でノードを移動させるときDoctrineは関連する``root_id``の変更を透過的に処理します。

------------------
Working with Trees
------------------

モデルを入れ子集合としてセットアップが成功したら作業を始めることができます。Doctrineの入れ子集合を実装する作業は2つのクラス:
``Doctrine\_Tree\_NestedSet``と``Doctrine\_Node\_NestedSet``で行われます。これらのクラスは``Doctrine\_Tree\_Interface``と``Doctrine\_Node_Interface``インターフェイスの実装です。ツリーオブジェクトはテーブルオブジェクトにバインドされノードオブジェクトはレコードオブジェクトにバインドされます。これらの内容は次の通りです:

次のコードを使うことですべてのツリーインターフェイスが利用できます:

 // test.php

// ... $treeObject = Doctrine\_Core::getTable('Category')->getTree();

次の例では``$category``は``Category``のインスタンスです:

 // test.php

// ... $nodeObject = $category->getNode();

上記のコードによって全ノードインターフェイスは``$nodeObject``で利用できます。

次のセクションでノードとツリークラスでもっともよく使われるオペレーションを実演するコードスニペットを見ます。

^^^^^^^^^^^^
rootノードを作成する
^^^^^^^^^^^^

 // test.php

// ... $category = new Category(); $category->name = 'Root Category 1';
$category->save();

$treeObject = Doctrine\_Core::getTable('Category')->getTree();
:code:`treeObject->createRoot(`\ category);

^^^^^^^^
ノードを挿入する
^^^^^^^^

次の例では新しい``Category``インスタンスを``Category``のrootの子として追加しています:

 // test.php

// ... $child1 = new Category(); $child1->name = 'Child Category 1';

$child2 = new Category(); $child2->name = 'Child Category 1';

:code:`child1->getNode()->insertAsLastChildOf(`\ category);
:code:`child2->getNode()->insertAsLastChildOf(`\ category);

^^^^^^^^
ノードを削除する
^^^^^^^^

ツリーからノードを削除するのは簡単でノードオブジェクトで``delete()``メソッドを呼び出します:

 // test.php

// ... $category =
Doctrine\_Core::getTable('Category')->findOneByName('Child Category 1');
$category->getNode()->delete();

    **CAUTION**
    上記のコードは``$category->delete()``を内部で呼び出しています。レコードではなくノードの上で削除を行うことが重要です。さもなければツリーが壊れることがあります。

ノードを削除するとそのノードのすべての子孫も削除されます。ですのでこれらの子孫を削除したくなければノードを削除するまえにどこか別の場所に移動させてください。

^^^^^^^^^
ノードを移動させる
^^^^^^^^^

ノードの移動は簡単です。Doctrineはツリーの間でノードを移動させるためのいくつかのメソッドを提供します:

 // test.php

// ... $category = new Category(); $category->name = 'Root Category 2';
$category->save();

$categoryTable = Doctrine\_Core::getTable('Category');

$treeObject = $categoryTable->getTree(); :code:`treeObject->createRoot(`\ category);

$childCategory = $categoryTable->findOneByName('Child Category 1');
:code:`childCategory->getNode()->moveAsLastChildOf(`\ category); ...

ノードを移動させるために利用可能なメソッドのリストは次の通りです:

-  moveAsLastChildOf($other)
-  moveAsFirstChildOf($other)
-  moveAsPrevSiblingOf($other)
-  moveAsNextSiblingOf($other).

メソッドの名前はその名の通りでなけれればなりません。

^^^^^^^^
ノードを検査する
^^^^^^^^

次のメソッドを使うことでノードとその型を検査することができます:

 // test.php

// ... $isLeaf = $category->getNode()->isLeaf(); $isRoot =
$category->getNode()->isRoot();

    **NOTE**
    上記のメソッドは葉ノードであるかrootノードであるかによってtrue/falseを返します。

^^^^^^^^^^
兄弟の検査と読み込み
^^^^^^^^^^

次のメソッドを使うことでノードが次もしくは前の兄弟を持つのか簡単にチェックできます:

 // test.php

// ... $hasNextSib = $category->getNode()->hasNextSibling(); $hasPrevSib
= $category->getNode()->hasPrevSibling();

次のメソッドで存在する次もしくは前の兄弟を読み取ることができます:

 // test.php

// ... $nextSib = $category->getNode()->getNextSibling(); $prevSib =
$category->getNode()->getPrevSibling();

    **NOTE**
    上記のメソッドは次もしくは前の兄弟が存在しない場合falseを返します。

すべての兄弟の配列を読み取るには``getSiblings()``メソッドを使います:

 // test.php

// ... $siblings = $category->getNode()->getSiblings();

^^^^^^^^^^
子孫の検査と読み取り
^^^^^^^^^^

次のメソッドを使用することでノードが親もしくは子を持つことをチェックできます:

 // test.php

// ... $hasChildren = $category->getNode()->hasChildren(); $hasParent =
$category->getNode()->hasParent();

次のメソッドで最初と最後の子ノードを読み取ることができます:

 // test.php

// ... $firstChild = $category->getNode()->getFirstChild(); $lastChild =
$category->getNode()->getLastChild();

もしくはノードの親を読み取りたい場合:

 // test.php

// ... $parent = $category->getNode()->getParent();

次のメソッドを使用してノードの子を取得できます:

 // test.php

// ... $children = $category->getNode()->getChildren();

    **CAUTION**
    ``getChildren()``メソッドは直接の子孫のみを返します。すべての子孫を取得したい場合、``getDescendants()``メソッドを使います。

次のメソッドでノードの祖先もしくは子孫を取得できます:

 // test.php

// ... $descendants = $category->getNode()->getDescendants(); $ancestors
= $category->getNode()->getAncestors();

ときに子もしくは子孫の数だけ取得したいことがあります。これは次のメソッドで実現できます:

 // test.php

// ... $numChildren = $category->getNode()->getNumberChildren();
$numDescendants = $category->getNode()->getNumberDescendants();

``getDescendants()``と``getAncestors()``は結果ブランチの``depth``を指定するために使用できるパラメータを受けとります。例えば``getDescendants(1)``は直接の子孫のみを読み取ります(1レベル下の子孫で、これは``getChildren()``と同じです)。同じ流儀で
``getAncestors(1)``は直接の祖先(親など)のみを読み取ります。rootノードもしくは特定の祖先までのこのノードのパスを効率的に決定するために``getAncestors()``はとても便利です(すなわちパンくずナビゲーションを構築するため).

^^^^^^^^^^^^
単純木をレンダリングする
^^^^^^^^^^^^

    **NOTE**
    次の例では``hasManyRoots``をfalseに設定することを前提とします。下記の例を適切に動作させるためにこのオプションをfalsenに設定しなければなりません。前のセクションでは値をtrueに設定しました。

 // test.php

// ... $treeObject = Doctrine\_Core::getTable('Category')->getTree();
$tree = $treeObject->fetchTree();

foreach ($tree as $node) { echo str\_repeat('  ', $node['level']) .
$node['name'] . ""; }

------
高度な使い方
------

以前のセクションでは入れ子集合の基本的な使い方を説明しました。このセクションは高度な内容に進みます。

^^^^^^^^^^^^^^^
リレーションでツリーを取得する
^^^^^^^^^^^^^^^

ソフトウェア開発者に要求している場合すでにこの質問が念頭にあるかもしれません:
"関連データを持つツリー/ブランチを取得するには？". Simple example:
カテゴリのツリーを表示したいが、それぞれのカテゴリの関連データの一部も表示したい場合、そのカテゴリのもっとも詳細な製品の商品を考えてみましょう。以前のセクションのようにツリーを取得しツリーをイテレートする合間にリレーションにアクセスするのは可能ですが、必要のないデータベースクエリをたくさん生み出します。幸いにして、``Doctrine\_Query``と入れ子集合の実装の柔軟性が手助けしてくれます。入れ子集合の実装は``Doctrine\_Query``オブジェクトを使用します。入れ子集合実装の基本クエリオブジェクトにアクセスすることで入れ子集合を使いながら``Doctrine_Query``のフルパワーを解き放つことができます。

最初にツリーデータを読み取るために使うクエリを作りましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('c.name, p.name, m.name')
->from('Category c') ->leftJoin('c.HottestProduct p')
->leftJoin('p.Manufacturer m');

ツリー用の基本クエリとして上記のクエリを設定する必要があります:

 $treeObject = Doctrine\_Core::getTable('Category')->getTree();
:code:`treeObject->setBaseQuery(`\ q); $tree = $treeObject->fetchTree();

必要なすべてのデータを持つツリーは1つのクエリで取得できます。

    **NOTE**
    独自の基本クエリを設定しない場合内部で自動的に作成されます。

終えたら基本クエリを通常のものに戻すのは良い考えです:

 // test.php

// ... $treeObject->resetBaseQuery();

さらに踏み込むことができます。[doc improving-performance
:name]の章で述べたように必要なときのみにオブジェクトを取得すべきです。ですので表示(読み込みのみ)目的のみにツリーを表示する場合少し加速するために配列のハイドレーションを使うことができます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('c.name, p.name, m.name')
->from('Category c') ->leftJoin('c.HottestProduct p')
->leftJoin('p.Manufacturer m')
->setHydrationMode(Doctrine\_Core::HYDRATE\_ARRAY);

$treeObject = Doctrine\_Core::getTable('Category')->getTree();
:code:`treeObject->setBaseQuery(`\ q); $tree = $treeObject->fetchTree();
:code:`treeObject->resetBaseQuery(); </code> ```\ tree``で素晴らしく構造化された配列が手に入ります。ともかくレコードにアクセスする配列を使う場合、このような変更はコードの他の部分に影響を与えません。クエリを修正するこのメソッドはすべてのノードとツリーメソッド(``getAncestors()``,
``getDescendants()``、``getChildren()``、``getParent()``)に対して使うことができます。クエリを作り、ツリーオブジェクトの基本クエリとして設定し適切なメソッドとして起動させます。

--------------
インデントでレンダリングする
--------------

下記の例ではすべてのツリーが適切なインデントでレンダリングされます。``fetchRoots()``メソッドを使用してrootを読み取り``fetchTree()``メソッドを使用して個別のツリーを読み取ることができます。

 // test.php

// ... $treeObject = Doctrine\_Core::getTable('Category')->getTree();
$rootColumnName = $treeObject->getAttribute('rootColumnName');

foreach ($treeObject->fetchRoots() as $root) { $options = array(
'root\_id' => :code:`root->`\ rootColumnName );
foreach(:code:`treeObject->fetchTree(`\ options) as $node) { echo
str\_repeat(' ', $node['level']) . $node['name'] . ""; } }

すべての作業を終えた後で上記のコードは次のようにレンダリングされます:

 $ php test.php Root Category 1 Root Category 2 Child Category 1

===
まとめ
===

``NestedSet``ビヘイビアに関するすべての内容と階層データを管理する方法を学んだので[doc
data-fixtures
:name]を学ぶ準備ができています。データフィクスチャはアプリケーションの小さなテストデータをロードするための偉大なツールでユニットテストと機能テストを行うもしくは初期データをアプリケーションに投入するために使われます。
