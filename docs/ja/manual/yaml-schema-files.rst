========
はじめに
========

スキーマファイルの目的はPHPコードの編集よりもYAMLファイルでモデルの定義を直接管理できるようにすることです。すべてのモデルの定義/クラスを生成するためにYAMLスキーマファイルは解析され使われます。これによってDoctrineモデルの定義がはるかにポータブルなものになります。

スキーマファイルはPHPコードで書く通常のすべての内容をサポートします。接続バインディング用のコンポーネント、リレーション、属性、テンプレート/ビヘイビア、インデックスなどがあります。

========
省略構文
========

Doctrineは省略記法でスキーマを指定する機能を提供します。多くのスキーマパラメータはデフォルトの値を持ち、これによって構文をできるのでDoctrineはデフォルトを利用できます。すべての省略記法を利用したスキーマの例は下記の通りです。

    **NOTE**
    ``detect_relations``オプションによってカラムの名前からリレーションの推測が行われます。下記の例ではDoctrine
    は``User``が1つの``Contact``を持つことを知っておりモデルの間のリレーションを自動的に定義します。

 detect\_relations: true

User: columns: username: string password: string contact\_id: integer

Contact: columns: first\_name: string last\_name: string phone: string
email: string address: string

==========
冗長な構文
==========

上記のスキーマを100%冗長にしたものは次の通りです:

 User: columns: username: type: string(255) password: type: string(255)
contact\_id: type: integer relations: Contact: class: Contact local:
contact\_id foreign: id foreignAlias: User foreignType: one type: one

Contact: columns: first\_name: type: string(255) last\_name: type:
string(255) phone: type: string(255) email: type: string(255) address:
type: string(255) relations: User: class: User local: id foreign:
contact\_id foreignAlias: Contact foreignType: one type: one

上記の例では``detect_relations``オプションを定義せず、代わりにローカル/外部キー、型、とそれぞれの側のリレーションのエイリアスの設定を通して完全にコントロールできるように手動でリレーションを定義します。

============
リレーション
============

リレーションを指定するとき外部キーが存在している方のリレーションを指定することだけが必要です。スキーマファイルが解析されるとき、Doctrineはリレーションを反映し反対側を自動的にビルドします。もう一方のリレーションを指定する場合、自動生成は行われません。

----------------------
リレーションを検出する
----------------------

前に見たようにDoctrineは``detect\_relations``オプションを指定する機能を提供します。この機能はカラムの名前に基づいてリレーションを自動的に構築します。``contact_id``を持つ``User``モデルと``Contact``という名前を持つクラスが存在する場合、2つの間のリレーションが自動的に作成されます。

------------------------------
リレーションをカスタマイズする
------------------------------

Doctrineは外部キーが存在している側のリレーションを指定することのみを要求します。リレーションの反対側は反映されもう一方側に基づいてビルドされます。スキーマ構文はリレーションのエイリアスと反対側の型をカスタマイズする機能を提供します。すべての関連するリレーションを一箇所で維持できるのでこれはよいニュースです。下記の内容はエイリアスと反対側のリレーションの型をカスタマイズする方法です。これは``User``が1つの``Contact``を持ち``Contact``は1つの``User``を``UserModel``として持つというリレーションを示しています。通常は自動生成された``User``は1つの``Contact``を持ち``Contact``は複数の``User``を持ちます。``foreignType``と``foreignAlias``オプションによって反対側のリレーションをカスタマイズできます。

 User: columns: id: type: integer(4) primary: true autoincrement: true
contact\_id: type: integer(4) username: type: string(255) password:
type: string(255) relations: Contact: foreignType: one foreignAlias:
UserModel

Contact: columns: id: type: integer(4) primary: true autoincrement: true
name: type: string(255)

次のようにthe
detect\_relationsオプションを持つ2つのモデルの間のリレーションを見つけて作成できます。

 detect\_relations: true

User: columns: id: type: integer(4) primary: true autoincrement: true
avatar\_id: type: integer(4) username: type: string(255) password: type:
string(255)

Avatar: columns: id: type: integer(4) primary: true autoincrement: true
name: type: string(255) image\_file: type: string(255)

結果のリレーションは``User``は1つの``Avatar``を持ち``Avatar``は複数の``User``を持ちます。

------
一対一
------

 User: columns: id: type: integer(4) primary: true autoincrement: true
contact\_id: type: integer(4) username: type: string(255) password:
type: string(255) relations: Contact: foreignType: one

Contact: columns: id: type: integer(4) primary: true autoincrement: true
name: type: string(255)

------
一対多
------

 User: columns: id: type: integer(4) primary: true autoincrement: true
contact\_id: type: integer(4) username: type: string(255) password:
type: string(255)

Phonenumber: columns: id: type: integer(4) primary: true autoincrement:
true name: type: string(255) user\_id: type: integer(4) relations: User:
foreignAlias: Phonenumbers

------
多対多
------

 User: columns: id: type: integer(4) autoincrement: true primary: true
username: type: string(255) password: type: string(255) attributes:
export: all validate: true

Group: tableName: group\_table columns: id: type: integer(4)
autoincrement: true primary: true name: type: string(255) relations:
Users: foreignAlias: Groups class: User refClass: GroupUser

GroupUser: columns: group\_id: type: integer(4) primary: true user\_id:
type: integer(4) primary: true relations: Group: foreignAlias:
GroupUsers User: foreignAlias: GroupUsers

この場合``User``は複数の``Groups``を持ち、``Group``は複数の``Users``を持ち、``GroupUser``は1つの``User``を持ち``GroupUser``は1つの``Group``を持つモデルのセットが作られます。

========
機能と例
========

------------------
接続バインディング
------------------

モデルを管理するためにスキーマファイルを使わないのであれば、通常は次のコードのようにコンポーネントを接続名にバインドするために使います:

下記のように接続を作成します:


Doctrine\_Manager::connection('mysql://jwage:pass@localhost/connection1',
'connection1');

Doctrineのブートストラップスクリプトでモデルをその接続にバインドします:

 Doctrine\_Manager::connection()->bindComponent('User', 'conn1');

スキーマファイルは接続パラメータを指定することでこれを特定の接続にバインドする機能を提供します。接続を指定しなければモデルは``Doctrine_Manager``インスタンスにセットあれた現在の接続を使います。

 User: connection: connection1 columns: id: type: integer(4) primary:
true autoincrement: true contact\_id: type: integer(4) username: type:
string(255) password: type: string(255)

----
属性
----

Doctrine\_Record子クラスを手作業で書いたのと同じようにDoctrineはスキーマファイルで生成モデル用の属性を直接設定する手段を提供します。

 User: connection: connection1 columns: id: type: integer(4) primary:
true autoincrement: true contact\_id: type: integer(4) username: type:
string(255) password: type: string(255) attributes: export: none
validate: false

------
列挙型
------

スキーマファイルでenumカラムを使うために型をenumとして指定し可能なenumの値として値の配列を指定しなければなりません。

 TvListing: tableName: tv\_listing actAs: [Timestampable] columns:
notes: type: string taping: type: enum length: 4 values: ['live',
'tape'] region: type: enum length: 4 values: ['US', 'CA']

--------------------
ActAsビヘイビア
--------------------

``actAs``オプションでモデルにビヘイビアを取り付けることができます:

 User: connection: connection1 columns: id: type: integer(4) primary:
true autoincrement: true contact\_id: type: integer(4) username: type:
string(255) password: type: string(255) actAs: Timestampable: Sluggable:
fields: [username] name: slug # defaults to 'slug' type: string #
defaults to 'clob' length: 255 # defaults to null. clob doesn't require
a length

    **NOTE**
    何も指定しない場合デフォルトの値が使われるのでSluggableビヘイビアで指定されたオプションはオプションです。これらはデフォルトなので毎回入力する必要はありません。

 User: connection: connection1 columns: # ... actAs: [Timestampable,
Sluggable]

--------
リスナー
--------

モデルに取り付けたいリスナーがある場合、同じようにYAMLファイルで直接これらを指定できます。

 User: listeners: [ MyCustomListener ] columns: id: type: integer(4)
primary: true autoincrement: true contact\_id: type: integer(4)
username: type: string(255) password: type: string(255)

上記の構文で次のような基底クラスが生成されます:

 class BaseUser extends Doctrine\_Record { // ...

public setUp() { // ... $this->addListener(new MyCustomListener()); } }

----------
オプション
----------

テーブル用のオプションを指定するとDoctrineがモデルからテーブルを作成するときにオプションはcreate
tableステートメントに設定されます。

 User: connection: connection1 columns: id: type: integer(4) primary:
true autoincrement: true contact\_id: type: integer(4) username: type:
string(255) password: type: string(255) options: type: INNODB collate:
utf8\_unicode\_ci charset: utf8

------------
インデックス
------------

インデックスとオプションの詳細情報は[doc defining-models chapter]の[doc
defining-models:indexes
:name]セクションを参照してくださるようお願いします。

 UserProfile: columns: user\_id: type: integer length: 4 primary: true
autoincrement: true first\_name: type: string length: 20 last\_name:
type: string length: 20 indexes: name\_index: fields: first\_name:
sorting: ASC length: 10 primary: true last\_name: [] type: unique

インデックスの定義用のモデルクラスの``setTableDefinition()``で自動生成されたPHPコードは次の通りです:

 $this->index('name\_index', array( 'fields' => array( 'first\_name' =>
array( 'sorting' => 'ASC',
 'length' => '10', 'primary' => true ), 'last\_name' => array()), 'type'
=> 'unique' ) );

----
継承
----

下記のコードはYAMLスキーマファイルを使用して異なるタイプの継承をセットアップする方法を示しています。

^^^^^^^^
単一継承
^^^^^^^^

 Entity: columns: name: string(255) username: string(255) password:
string(255)

User: inheritance: extends: Entity type: simple

Group: inheritance: extends: Entity type: simple

    **NOTE**
    単一継承するモデルで定義されたカラムもしくはリレーションはPHPクラスが生成されたときに親に移動します。

[doc inheritance:simple
:fullname]の章でこのトピックの詳細を読むことができます。

^^^^^^^^
具象継承
^^^^^^^^

 TextItem: columns: topic: string(255)

Comment: inheritance: extends: TextItem type: concrete columns: content:
string(300)

[doc inheritance:concrete
:fullname]の章でこのトピックの詳細を読むことができます。

^^^^^^^^^^^^^^
カラム集約継承
^^^^^^^^^^^^^^

    **NOTE**
    単一継承のように、PHPクラスが生成されたとき子に追加されるカラムもしくはリレーションは自動的に削除され親に移動します。

他のモデルが継承する``Entity``という名前のモデルを定義しましょう:

 Entity: columns: name: string(255) type: string(255)

    **NOTE**
    typeカラムはオプションです。このカラムは子クラスで指定された場合自動的に追加されます。

``Entity``モデルを継承する``User``モデルを作りましょう:

 User: inheritance: extends: Entity type: column\_aggregation keyField:
type keyValue: User columns: username: string(255) password: string(255)

    **NOTE**
    ``inheritance``定義の下の``type``オプションは``keyField``もしくは``keyValue``を指定する場合暗示されるのでオプションです。``keyField``が指定されない場合デフォルトでは``type``という名前のカラムが追加されます。何も指定しない場合デフォルトで``keyValue``がモデルの名前になります。

再度``Entity``を継承する``Group``という名前の別のモデルを作りましょう:

 Group: inheritance: extends: Entity type: column\_aggregation keyField:
type keyValue: Group columns: description: string(255)

    **NOTE**
    ``User``の``username``と``password``と``Group``の``description``カラムは自動的に親の``Entity``に移動します。

[doc inheritance:column-aggregation
:fullname]で詳細トピックを読むことができます。

--------------------
カスタムのエイリアス
--------------------

データベースのカラム名以外のカラム名のエイリアスを作成したい場合、Doctrineでこれを実現するのは簡単です。カラムの名前で"``column\_name
as field\_name``"の構文を使います:

 User: columns: login: name: login as username type: string(255)
password: type: string(255)

上記の例では``username``エイリアスからカラム名の``login``にアクセスできます。

----------
パッケージ
----------

Doctrineはサブフォルダでモデルを生成する"package"パラメータを提供します。大きなスキーマファイルによってフォルダの内外でスキーマをよりよく編成できます。

 User: package: User columns: username: string(255)

このスキーマファイルからのモデルファイルはUserという名前のフォルダに設置されます。"package:
User.Models"とさらにサブフォルダを指定すればモデルはUser/Modelsになります。

^^^^^^^^^^^^^^^^^^^^^^^^
カスタムパスのパッケージ
^^^^^^^^^^^^^^^^^^^^^^^^

パッケージファイルを生成する完全なカスタムパスを指定することで適切なパスでパッケージを自動生成することもできます:

 User: package: User package\_custom\_path: /path/to/generate/package
columns: username: string(255)

------------------------
グローバルスキーマの情報
------------------------

Doctrineスキーマによってスキーマファイルで定義されたすべてのモデルに適用する特定のパラメータを指定できます。スキーマファイル用に設定できるグローバルパラメータの例が見つかります。

グローバルパラメータのリストは次の通りです:

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``connection`` \|\|
モデルをバインドする接続名。 \|\| \|\| ``attributes`` \|\|
モデル用の属性の配列 \|\| \|\| ``actAs`` \|\| モデル用のビヘイビアの配列
\|\| \|\| ``options`` \|\| モデル用のテーブルオプションの配列 \|\| \|\|
``package`` \|\| モデルを設置するパッケージ \|\| \|\| ``inheritance``
\|\| モデル用の継承情報の配列 \|\| \|\| ``detect_relations`` \|\|
外部キーのリレーションを検出するかどうか \|\|

上記のグローバルパラメータをいつか使ったスキーマの例は次の通りです:

 connection: conn\_name1 actAs: [Timestampable] options: type: INNODB
package: User detect\_relations: true

User: columns: id: type: integer(4) primary: true autoincrement: true
contact\_id: type: integer(4) username: type: string(255) password:
type: string(255)

Contact: columns: id: type: integer(4) primary: true autoincrement: true
name: type: string(255)

トップレベルのすべての設定はYAMLで定義されたすべてのモデルに適用されます。

======================
スキーマファイルを使う
======================

一旦スキーマファイルを定義したらYAMLの定義からモデルをビルドするコードが必要です。

 $options = array( 'packagesPrefix' => 'Plugin', 'baseClassName' =>
'MyDoctrineRecord', 'suffix' => '.php' );

Doctrine\_Core::generateModelsFromYaml('/path/to/yaml',
'/path/to/model', $options);

上記のコードは``/path/to/generate/models``の``schema.yml``用のモデルを生成します。

モデルのビルド方法をカスタマイズするために利用できる異なるオプションの表は次の通りです。``packagesPrefix``、``baseClassName``と``suffix``オプションを使用していることに注目してください。

\|\|~ 名前 \|\|~ デフォルト \|\|~ 説明 \|\| \|\| ``packagesPrefix`` \|\|
``Package`` \|\| ミドルパッケージモデルのプレフィックス \|\| \|\|
``packagesPath`` \|\| ``#models_path#/packages`` \|\|
パッケージファイルを書き込むパス \|\| \|\| ``packagesFolderName`` \|\|
``packages`` \|\| パッケージパス内部で、パッケージを置くフォルダーの名前
\|\| \|\| ``generateBaseClasses`` \|\| ``true`` \|\|
定義と空の基底モデルを継承するトップレベルのクラスを含めて抽象基底モデルを生成するかどうか
\|\| \|\| ``generateTableClasses`` \|\| ``true`` \|\|
モデルごとにテーブルを生成するか \|\| \|\| ``baseClassPrefix`` \|\|
``Base`` \|\| 生成既定モデルに使うプレフィックス \|\| \|\|
``baseClassesDirectory`` \|\| ``generated`` \|\|
基底クラスの定義を生成するフォルダーの名前 \|\| \|\|
``baseTableClassName`` \|\| ``Doctrine_Table`` \|\|
ほかの生成テーブルクラス名が継承する基底テーブルクラス \|\| \|\|
``baseClassName`` \|\| ``Doctrine_Record`` \|\|
Doctrine\_Record既定クラスの名前 \|\| \|\| ``classPrefix`` \|\| \|\|
すべての生成クラスで使うプレフィックス \|\| \|\| ``classPrefixFiles``
\|\| ``true`` \|\|
生成ファイルの名前にもクラスのプレフィックスを使うかどうか \|\| \|\|
``pearStyle`` \|\| ``false`` \|\|
PEARスタイルのクラス名とファイルを生成するか。このオプションがtrueにセットされている場合。生成クラスファイルにおいて``underscores(\_)``は``DIRECTORY_SEPARATOR``に置き換えられます。\|\|
\|\| ``suffix`` \|\| ``.php`` \|\| 生成モデルに使う拡張子 \|\| \|\|
``phpDocSubpackage`` \|\| \|\|
docブロックで生成するphpDocのサブパッケージ名 \|\| \|\| ``phpDocName``
\|\| \|\| docブロックで生成するphpDocの著者名 \|\| \|\| ``phpDocEmail``
\|\| \|\| docブロックで生成するphpDocのメール \|\|

======
まとめ
======

YAMLスキーマファイルのすべてを学んだので[doc data-validation
:name]に関する大きなトピックに移ります。これは重要なトピックです。ユーザーが入力したデータをあなた自身でバリデートしたくない場合データベースに永続的に保存する前にDoctrineにデータをバリデートさせます。
