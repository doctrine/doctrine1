データフィクスチャはテストするデータをデータベースに投入するためにモデルを通してテストデータの小さなセットをロードするための手段です。データフィクスチャはユニット/機能テストスィートでよく使われます。

==============
インポートする
==============

データフィクスチャのインポートはダンプと同じ簡単です。``loadData()``メソッドを使うことができます:

 Doctrine\_Core::loadData('/path/to/data.yml');

上記のように個別のYAMLファイルを指定するか、もしくはディレクトリ全体を指定できます:

 Doctrine\_Core::loadData('/path/to/directory');

インポートしたデータを既存のデータに追加したい場合``loadData()``メソッドの2番目の引数を使う必要があります。2番目の引数をtrueとして指定しない場合データはインポートされる前にパージされます。

パージする代わりに追加する方法は次の通りです:

 Doctrine\_Core::loadData('/path/to/data.yml', true);

==========
ダンプする
==========

データフィクスチャを書き始めるのを手助けするために多くの異なるフォーマットでフィクスチャファイルにデータをダンプできます。次のようんデータフィクスチャを1つの大きなYAMLファイルにダンプできます:

 Doctrine\_Core::dumpData('/path/to/data.yml');

もしくはオプションとしてすべてのデータを個別のファイルにダンプできます。モデルごとに1つのYAMLファイルをダンプするには次のようになります:

 Doctrine\_Core::dumpData('/path/to/directory', true);

====
実装
====

データフィクスチャを知ったので次のセクションで使われるフィクスチャの例をテストできるように、以前の章で利用してきたテスト環境で実装してみましょう。

最初に``doctrine_test``ディレクトリの中で``fixtures``という名前のディレクトリを作成し内部で``data.yml``という名前のファイルを作成します:

 $ mkdir fixtures $ touch fixtures/data.yml

データフィクスチャをロードするコードを含めるために``generate.php``スクリプトを修正する必要があります。``generate.php``の一番下に次のコードを追加します:

 // generate.php

// ... Doctrine\_Core::loadData('fixtures');

====
書く
====

手作業でフィクスチャファイルを書いてアプリケーションでロードできます。下記のコードはサンプルの``data.yml``フィクスチャファイルです。データフィクスチャを複数のファイルに分割することもできます。Doctrineはすべてのフィクスチャファイルを読み込み解析し、すべてのデータをロードします。

次のいくつかの例に対して次のモデルを使用します:

 // models/Resouce.php

class Resource extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255);
$this->hasColumn('resource\_type\_id', 'integer'); }

::

    public function setUp()
    {
        $this->hasOne('ResourceType as Type', array(
                'local' => 'resource_type_id',
                'foreign' => 'id'
            )
        );

        $this->hasMany('Tag as Tags', array(
                'local' => 'resource_id',
                'foreign' => 'tag_id',
                'refClass' => 'ResourceTag'
            )
        );
    }

}

// models/ResourceType.php

class ResourceType extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('Resource as Resouces', array(
                'local' => 'id',
                'foreign' => 'resource_type_id'
            )
        );
    }

}

// models/Tag.php

class Tag extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('Resource as Resources', array(
                'local' => 'tag_id',
                'foreign' => 'resource_id',
                'refClass' => 'ResourceTag'
            )
        );
    }

}

// models/ResourceTag.php

class ResourceTag extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('resource\_id', 'integer');
$this->hasColumn('tag\_id', 'integer'); } }

// models/Category.php

class BaseCategory extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255, array(
'type' => 'string', 'length' => '255' ) ); }

::

    public function setUp()
    {
        $this->actAs('NestedSet');
    }

}

class BaseArticle extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255, array(
'type' => 'string', 'length' => '255' ) );

::

        $this->hasColumn('body', 'clob', null, array(
                'type' => 'clob'
            )
        );
    }

    public function setUp()
    {
        $this->actAs('I18n', array('fields' => array('title', 'body')));
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Resource: columns: name: string(255) resource\_type\_id: integer
relations: Type: class: ResourceType foreignAlias: Resources Tags:
class: Tag refClass: ResourceTag foreignAlias: Resources

ResourceType: columns: name: string(255)

Tag: columns: name: string(255)

ResourceTag: columns: resource\_id: integer tag\_id: integer

Category: actAs: [NestedSet] columns: name: string(255)

Article: actAs: I18n: fields: [title, body] columns: title: string(255)
body: clob

    **NOTE**
    すべての列のキーはすべてのYAMLデータフィクスチャにまたがってユニークでなければなりません。下記のtutorial、doctrine、help、cheatはすべてユニークです。

 # fixtures/data.yml

Resource: Resource\_1: name: Doctrine Video Tutorial Type: Video Tags:
[tutorial, doctrine, help] Resource\_2: name: Doctrine Cheat Sheet Type:
Image Tags: [tutorial, cheat, help]

ResourceType: Video: name: Video Image: name: Image

Tag: tutorial: name: tutorial doctrine: name: doctrine help: name: help
cheat: name: cheat

Resourceが持つTagsを指定する代わりにそれぞれのタグが関係するResourcesを指定できます。

 # fixtures/data.yml

Tag: tutorial: name: tutorial Resources: [Resource\_1, Resource\_2]
doctrine: name: doctrine Resources: [Resource\_1] help: name: help
Resources: [Resource\_1, Resource\_2] cheat: name: cheat Resources:
[Resource\_1]

==========================
入れ子集合用のフィクスチャ
==========================

入れ子集合のツリー用のフィクスチャファイルの書き方は通常のフィクスチャファイルの書き方と少し異なります。ツリーの構造は次のように定義されます:

 # fixtures/data.yml

Category: Category\_1: name: Categories # the root node children:
Category\_2: name: Category 1 Category\_3: name: Category 2 children:
Category\_4: name: Subcategory of Category 2

.. tip::

    NestedSet用のデータフィクスチャを書くとき少なくとも最初のデータブロックの``children``要素を指定するかNestedSetのAPIを使用してデータフィクスチャをインポートするためにNestedSetであるモデルの下で``NestedSet:
    true``を指定しなければなりません。

 # fixtures/data.yml

Category: NestedSet: true Category\_1: name: Categories # ...

もしくはchildrenキーワードを指定することでNestedSetのAPIを使用してデータをインポートします。

 # fixtures/data.yml

Category: Category\_1: name: Categories children: [] # ...

上記の方法を使わない場合入れ子集合レコード用にlft、rgtとレベルの値を手動で指定するのはあなた次第です。

======================
国際化用のフィクスチャ
======================

``I18n``用のフィクスチャはカスタマイズできません。``I18n``は動的に構築されるリレーションの通常の集合にすぎないからです:

 # fixtures/data.yml

Article: Article\_1: Translation: en: title: Title of article body: Body
of article fr: title: French title of article body: French body of
article

======
まとめ
======

データフィクスチャを書いてアプリケーションにロードできるようになりました。内在する[doc
database-abstraction-layer
:name]を学ぶために次の章に移動します。このレイヤーは以前検討したすべての機能に関係します。このレイヤーをORMから独立したものとして使うことができます。次の章ではDBALそのものの使い方を説明します。
