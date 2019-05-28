========
はじめに
========

Doctrineは多くの人の作業の成果物です。他の言語のORMは開発者の学習のための主要なリソースです。

    **NOTE**
    Doctrineは車輪の再発明をする代わりに他のオープンソースのプロジェクトからコードのピースも借りました。コードを借りた2つのプロジェクトは[http://www.symfony-project.com
    symfony]と[http://framework.zend.com Zend
    Framework]です。Doctrineのライセンス情報は``LICENSE``という名前のファイルで見つかります。

==============
アーキテクチャ
==============

Doctrineは3つのパッケージ:
CORE、ORMとDBALに分割されます。下記のリストはそれぞれのパッケージを構成するメインクラスの一部のリストは下記の通りです。

--------------------------
Doctrine CORE
--------------------------

-  Doctrine
-  [doc component-overview:manager Doctrine\_Manager]
-  [doc component-overview:connection Doctrine\_Connection]
-  [doc improving-performance:compile Doctrine\_Compiler]
-  [doc exceptions-and-warnings Doctrine\_Exception]
-  Doctrine\_Formatter
-  Doctrine\_Object
-  Doctrine\_Null
-  [doc event-listeners Doctrine\_Event]
-  Doctrine\_Overloadable
-  Doctrine\_Configurable
-  [doc event-listeners Doctrine\_EventListener]

--------------------------
Doctrine DBAL
--------------------------

-  [doc component-overview:record:using-expression-values
   Doctrine\_Expression\_Driver]
-  [doc database-abstraction-layer:export Doctrine\_Export]
-  [doc database-abstraction-layer:import Doctrine\_Import]
-  Doctrine\_Sequence
-  [doc transactions Doctrine\_Transaction]
-  [doc database-abstraction-layer:datadict Doctrine\_DataDict]

Doctrine DBALはドライバパッケージにも分割されます。

------------------------
Doctrine ORM
------------------------

-  [doc component-overview:record Doctrine\_Record]
-  [doc component-overview:table Doctrine\_Table]
-  [doc defining-models:relationships Doctrine\_Relation]
-  [doc component-overview:record:using-expression-values
   Doctrine\_Expression]
-  [doc dql-doctrine-query-language Doctrine\_Query]
-  [doc native-sql Doctrine\_RawSql]
-  [doc component-overview:collection Doctrine\_Collection]
-  Doctrine\_Tokenizer

その他のパッケージ。

-  [doc data-validation Doctrine\_Validator]
-  Doctrine\_Hook
-  [doc component-overview:views Doctrine\_View]

Doctrine用のビヘイビアもあります:

-  [doc behaviors:core-behaviors:geographical :name]
-  [doc behaviors:core-behaviors:i18n :name]
-  [doc behaviors:core-behaviors:nestedset :name]
-  [doc behaviors:core-behaviors:searchable :name]
-  [doc behaviors:core-behaviors:sluggable :name]
-  [doc behaviors:core-behaviors:softdelete :name]
-  [doc behaviors:core-behaviors:timestampable :name]
-  [doc behaviors:core-behaviors:versionable :name]

================
デザインパターン
================

使用されている``GoF (Gang of Four)``デザインパターン:

-  [http://www.dofactory.com/Patterns/PatternSingleton.aspx
   Singleton]、``Doctrine_Manager``のインスタンスを1つに強制するために
-  [http://www.dofactory.com/Patterns/PatternComposite.aspx
   Composite]、レベル付きの設定に
-  [http://www.dofactory.com/Patterns/PatternFactory.aspx
   Factory]、接続ドライバのロードとその他に
-  [http://www.dofactory.com/Patterns/PatternObserver.aspx
   Observer]、イベントのリスティング
-  [http://www.dofactory.com/Patterns/PatternFlyweight.aspx
   Flyweight]、バリデータの効率的な使い方
-  [http://www.dofactory.com/Patterns/PatternFlyweight.aspx
   Iterator]、コンポーネント(Tables、
   Connections、Recordsなど)のイテレート用に
-  [http://www.dofactory.com/Patterns/PatternState.aspx
   State]、状態を認識する接続に
-  [http://www.dofactory.com/Patterns/PatternStrategy.aspx
   Strategy]、アルゴリズム戦略に

使用されているエンタープライズアプリケーションデザインパターン:

-  [http://www.martinfowler.com/eaaCatalog/activeRecord.html Active
   Record], Doctrineはこのパターンを実装します
-  [http://www.martinfowler.com/eaaCatalog/unitOfWork.html
   UnitOfWork]、トランザクションに影響するオブジェクトのリストの維持に
-  [http://www.martinfowler.com/eaaCatalog/identityField.html Identity
   Field]、レコードとデータベースの列のアイデンティティの維持に
-  [http://www.martinfowler.com/eaaCatalog/metadataMapping.html Metadata
   Mapping]、Doctrine DataDictに
-  [http://www.martinfowler.com/eaaCatalog/dependentMapping.html
   Dependent Mapping],
   一般的なマッピングに、``Doctrine_Record``を継承するすべてのレコードがすべてのマッピングを実行するので
-  [http://www.martinfowler.com/eaaCatalog/foreignKeyMapping.html
   Foreign Key Mapping]、一対一、一対多、多対多、多対一のリレーションに
-  [http://www.martinfowler.com/eaaCatalog/associationTableMapping.html
   Association Table
   Mapping]、アソシエーションテーブルマッピング(多対多のリレーションに最も使われる)に
-  [http://www.martinfowler.com/eaaCatalog/lazyLoad.html Lazy
   Load]、オブジェクトとオブジェクトプロパティの遅延ロードに
-  [http://www.martinfowler.com/eaaCatalog/queryObject.html Query
   Object]、DQL APIはQuery Objectパターンの基本アイディアの拡張

========
動作速度
========

-  **遅延初期化** - コレクション要素
-  **Subselectの取得** -
   Doctrineはsubselectを使用してコレクションを効率的に取得する方法を知っている。
-  **必要なときに、SQLステートメントの遅延実行** :
   実際に必要になるまで接続はINSERTもしくはUPDATEを発行しません。ですので例外が起きてトランザクションを停止させる必要がある場合、一部のステートメントは実際に発行されることはありません。さらに、これによってデータベースのロック時間をできるかぎり短く保ちます(遅延UPDATEからトランザクションの終了まで)。
-  **Joinの取得** -
   Doctrineはjoinとsubselectを使用して複雑なオブジェクトグラフを取得する方法を知っている
-  **複数のコレクション取得戦略** -
   Doctrineはパフォーマンスチューニングのための複数のコレクション取得戦略を持ちます。
-  **取得戦略の動的なミックス** -
   取得戦略は組み合わせ可能で例えばユーザーがバッチコレクションで取得可能である一方でユーザーの電話番号が1つのクエリのみを使用してオフセットコレクションでロードできます。
-  **ドライバ固有の最適化** -
   Doctrineはmysqlのbulk-insertを知っています。
-  **トランザクションの単発削除** -
   Doctrineは削除リストの追加オブジェクトのすべての主キーを集めテーブルごとに1つのdelete文のみを実行する方法を知っています。
-  **修正されたカラムのみを更新する** -
   Doctrineはどのカラムが変更されたのか常に知っています。
-  **未修正オブジェクトを挿入/更新しない** -
   Doctrineはレコードの状態が変更されたか知っています。
-  **データベース抽象化のためのPDO** -
   PDOはPHPの最速のデータベース抽象化レイヤーです。

======
まとめ
======

この章ではDoctrineのコンポーネントの完全な鳥瞰図と編成の情報を提供します。これまでこれらを個別の部分として見てきましたが3つのメインパッケージの個別のリストによってこれまでわからなかったことが明らかになります。

次に例外の扱い方を学ぶために[doc exceptions-and-warnings
:name]の章に移動します。
