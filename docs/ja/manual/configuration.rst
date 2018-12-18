Doctrineは属性を利用して機能と機能性を制御します。このセクションではDoctrineの機能性を使うために、属性の設定と取得する方法および、存在する属性をオーバーライドする方法を検討します。

============
設定のレベル
============

Doctrineは3レベルの設定構造を持ちます。グローバル、接続とテーブルレベルで設定属性を設定できます。同じ属性が下側と上側の両方のレベルで設定される場合、一番上の属性が常に使われます。例えばユーザーが最初にグローバルレベルでデフォルトの取得モードを``Doctrine\_Core::FETCH\_BATCH``に設定してテーブルの取得モードを``Doctrine\_Core::FETCH_LAZY``に設定すれば、遅延取得戦略はテーブルのレコードが取得されているときはいつでも使えます。

-  **グローバルレベル** -
   グローバルレベルで設定された属性はすべての接続と接続ごとのすべてのテーブルに影響を及ぼします。
-  **接続レベル** -
   接続レベルで設定される属性はその接続のそれぞれのテーブルのみに影響を及ぼします。
-  **テーブルレベル** -
   テーブルレベルで設定される属性はそのテーブルのみに影響を及ぼします。

次の例ではグローバルレーベルで1つの属性を設定します:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_ALL);

次の例は与えられた接続のグローバル属性をオーバーライドします:

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_NONE);

最後の例ではテーブルレベルで接続レベルの属性を再度オーバーライドします:

 // bootstrap.php

// ... $table = Doctrine\_Core::getTable('User');

$table->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_ALL);

    **NOTE**
    ``Doctrine_Core::getTable()``メソッドを使った例は紹介しませんでした。次の章の[doc
    component-overview:table
    :name]のセクションでテーブルオブジェクトを詳しく学びます。

==============
ポータビリティ
==============

それぞれのデータベース管理システム(DBMS - Database Management
System)は独自の振る舞いを行います。例えば、出力する際にテーブルの名前の最初の文字を大文字にするものもがあれば、小文字にしたりそのままにするものがあります。これらの挙動によってアプリケーションを別の種類のデータベースに移植させるのが難しくなります。Doctrineはこれらの困難に打ち勝つために努力してくれるのでアプリケーションを変更せずにDBMSを切り替えることができます。例えばsqliteからmysqlに切り替えることです。

ポータビリティモードはビット単位なので、\|を使い結合したり^を使い削除できます。これを行う方法の例は下記のセクションをご覧ください。

.. tip::

    ビット演算子の詳細な情報は[http://www.php.net/language.operators.bitwise
    PHP公式サイト]をご覧ください。

--------------------------
ポータビリティモードの属性
--------------------------

すべてのポータビリティ属性と説明の一覧です:

\|\|~ 名前 \|\|~ 説明 \|\| \|\| \|\| ``PORTABILITY_ALL`` \|\|
すべてのポータビリティモードを有効にする。これはデフォルトの設定です。\|\|
\|\| ``PORTABILITY\_DELETE_COUNT`` \|\|
削除される列の数のレポートを強制する。シンプルな``DELETE
FROM``テーブル名のクエリを実行する際に削除される列の数をカウントしないDBMSがあります。このモードでは``DELETE``クエリの最後に``WHERE
1=1``を追加することでRDBMSをだましてカウントするようにします \|\| \|\|
``PORTABILITY\_EMPTY\_TO_NULL`` \|\|
データと出力において空の文字列の値をnullに変換する。必要なのはOracleは空の文字列をnullと見なす一方で、その他の主なDBMSは空とnullの違いを知っているからです。\|\|
\|\| ``PORTABILITY_ERRORS`` \|\|
特定のドライバの特定のエラーメッセージを他のDBMSと互換性があるようにする
\|\| \|\| ``PORTABILITY\_FIX\_ASSOC\_FIELD_NAMES`` \|\|
これは連想配列形式の取得においてキーから修飾子を削除します。SQLiteなどは、クエリで省略されていない場合に連想配列形式のカラムに対して省略されていない名前をデフォルトで使用します。\|\|
\|\| ``PORTABILITY\_FIX_CASE`` \|\|
すべてのメソッドで小文字もしくは大文字にするためにテーブルとフィールドの名前を変換する。事例はfield\_caseオプションに依存し``CASE\_LOWER``(デフォルト)もしくは``CASE_UPPER``のどちらかに設定できます。\|\|
\|\| ``PORTABILITY_NONE`` \|\|
すべてのポータビリティ機能を無効にする。\|\| \|\|
``PORTABILITY_NUMROWS`` \|\|
Oracleで``numRows()``を動作させるためのハックを有効にする \|\| \|\|
``PORTABILITY_EXPR`` \|\| ポータブルではない式が使われる場合にDQL
APIが例外を投げる。\|\| \|\| ``PORTABILITY_RTRIM`` \|\|
すべてのデータ取得する際にデータ出力の右トリミングする。固定長の文字の値を右トリミングしない場合でも、これは固定長の文字の値を自動的に右トリミングするRDBMSには適用されない。\|\|

----------
例
----------

小文字化とトリミングのためにポータビリティモードを有効にする``setAttribute()``メソッドを次のコードのように使うことができます:

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_PORTABILITY,
Doctrine\_Core::PORTABILITY\_FIX\_CASE \|
Doctrine\_Core::PORTABILITY\_RTRIM);

トリミングを除いたすべてのポータビリティモードを有効にする

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_PORTABILITY,
Doctrine\_Core::PORTABILITY\_ALL ^ Doctrine\_Core::PORTABILITY\_RTRIM);

================
識別子のクォート
================

``quoteIdentifier()``でDBの識別子(テーブルとフィールド名)をクォートできます。区切りのスタイルはデータベースドライバによります。

    **NOTE**
    区切られた識別子を使うことができるので、これらを使うべきであることを意味しません。一般的に、これらが解決する問題よりも多くの問題を引き起こします。ともかく、フィールドの名前として予約語がある場合に必要です(この場合、できるのであれば、予約語を変更することを提案します)。

Doctrineの内部メソッドの中にはクエリを生成するものがあります。``quote_identifier``属性を有効にすることで、これらの生成クエリの中で識別子をクォートするようDoctrineに伝えることができます。すべてのユーザー提供のクエリに対してこのオプションは無意味です。

区切られた識別子内部で次の文字を使うとポータビリティが壊れます:

\|\|~ 名前 \|\|~ 文字 \|\|~ ドライバ \|\| \|\| backtick \|\| ``\``` \|\|
MySQL \|\| \|\| double quote \|\| ``"`` \|\| Oracle \|\| \|\| brackets
\|\| ``[`` or ``]`` \|\| Access \|\|

次のドライバの元で識別子の区切りが一般的に正しく動作することが知られています:
Mssql、Mysql、Oracle、Pgsql、SqliteとFirebird

``Doctrine\_Core::ATTR\_QUOTE_IDENTIFIER``オプションを使うとき、フィールドの識別子のすべては結果のSQL文において自動的にクォートされます:

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_QUOTE\_IDENTIFIER,
true);

結果のSQL文においてすべてのフィールド名はバッククォート演算子'\`'でクォートされます(MySQL)。

 SELECT \* FROM sometable WHERE ``id`` = '123'

対照的に:

 SELECT \* FROM sometable WHERE id = '123'

========================
ハイドレーションの上書き
========================

デフォルトではあたかもすでに問い合わせされ修正されたオブジェクトを問い合わせしたようにDoctrineはオブジェクトでのローカルの変更を上書きするように設定されています。

 $user = Doctrine\_Core::getTable('User')->find(1); $user->username =
'newusername';

上記のオブジェクトを修正したのであたかも同じデータを再度問い合わせしたように、ローカルな変更は上書きされます。

 $user = Doctrine\_Core::getTable('User')->find(1); echo
$user->username; // データベースのオリジナルのユーザー名を出力する

``ATTR_HYDRATE_OVERWRITE``属性を使うことでこのふるまいを無効にできます:

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_HYDRATE\_OVERWRITE,
false);

これで上記で同じテストを実行したとしても、修正されたユーザー名は上書きされません。

========================
テーブルクラスを設定する
========================

``Doctrine_Core::getTable()``メソッドを使うときに返されるクラスを設定したい場合``ATTR_TABLE_CLASS``属性をセットできます。唯一の要件は``Doctrine_Table``を継承するクラスです。

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_TABLE\_CLASS,
'MyTableClass');

``MyTableClass``は次のようになります:

 class MyTableClass extends Doctrine\_Table { public function myMethod()
{ // 何らかのクエリを実行し結果を返す } }

これで次のコードを実行するとき``MyTableClass``のインスタンスが返されるようになります:

 $user = $conn->getTable('MyModel')->myMethod();

テーブルクラスをさらにカスタマイズしたい場合それぞれのモデルごとにカスタマイズできます。
``MyModelTable``という名前のクラスを作りオートロード可能であることを確認します。

 class MyModelTable extends MyTableClass { public function
anotherMethod() { // 何らかのクエリを実行し結果を返す } }

次のコードを実行するとき``MyModelTable``のインスタンスが返されます:

 echo get\_class($conn->getTable('MyModel')); // MyModelTable

======================
クエリクラスを設定する
======================

新しいクエリインスタンスを作るとき基底のクエリクラスを設定したいとき、``ATTR_QUERY_CLASS``属性を使うことができます。唯一の要件は``Doctrine_Query``クラスを継承することです。

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_QUERY\_CLASS,
'MyQueryClass');

``MyQueryClass``は次のようになります:

 class MyQueryClass extends Doctrine\_Query {

}

これで新しいクエリを作ると``MyQueryClass``のインスタンスが返されるようになります:

 $q = Doctrine::getTable('User') ->createQuery('u');

echo get\_class($q); // MyQueryClass

============================
コレクションクラスを設定する
============================

基底クラスとテーブルクラスを設定できるので、Doctrineが使うコレクションクラスもカスタマイズできることのみに意味をなします。``ATTR_COLLECTION_CLASS``属性をセットする必要があるだけです。

 // bootstrap.php

// ... $conn->setAttribute(Doctrine\_Core::ATTR\_COLLECTION\_CLASS,
'MyCollectionClass');

``MyCollectionClass``の唯一の要件は``Doctrine_Collection``を継承しなければならないことです:

 $phonenumbers = :code:`user->Phonenumber; echo get_class(`\ phonenumbers);
// MyCollectionClass

==================================
カスケーディングセーブを無効にする
==================================

オプションとして利便性のために``ATTR_CASCADE_SAVES``属性によってデフォルトで有効になっているカスケーディングセーブオペレーションを無効にできます。この属性を``false``にするとレコードがダーティである場合のみカスケードとセーブが行われます。このことは階層において1つのレベルより深くダーティなレコードをカスケードしてセーブできないことを意味しますが、顕著なパフォーマンスの改善の効果を得られます。

 $conn->setAttribute(Doctrine::ATTR\_CASCADE\_SAVES, false);

================
エクスポートする
================

テーブル作成用にデータベースにクラスをエクスポートする際にDoctrineにエクスポートするものを伝えるためにエクスポート属性が使われます。

何もエクスポートしたくない場合は次のように行います:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_EXPORT,
Doctrine\_Core::EXPORT\_NONE);

(制約は伴わずに)テーブルだけをエクスポートするためだけなら次のようにできます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_EXPORT,
Doctrine\_Core::EXPORT\_TABLES);

上記と同じ内容を次の構文でも実現できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_EXPORT,
Doctrine\_Core::EXPORT\_ALL ^ Doctrine\_Core::EXPORT\_CONSTRAINTS);

すべて(テーブルと制約)をエクスポートするには:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_EXPORT,
Doctrine\_Core::EXPORT\_ALL);

==============
命名規約の属性
==============

命名規約の属性は、テーブル、インデックスとシーケンスのような要素に関連する異なるデータベースの命名に影響を及ぼします。データベースからクラスまでのスキーマをインポートするときとクラスをデータベーステーブルにエクスポートするとき、基本的にすべての命名規約属性は両方の方法で影響を及ぼします。

例えばDoctrineのインデックス用の命名規約のデフォルトは``%s_idx``です。インデックスだけでなく特別な接尾辞を設定可能で、インポートされるクラスは接尾辞を持たない対応物にマッピングされるインデックスを取得します。これはすべての命名規約属性に適用されます。

----------------------------
インデックス名のフォーマット
----------------------------

``Doctrine\_Core::ATTR\_IDXNAME\_FORMAT``は命名規約のインデックスを変更するために使われます。デフォルトではDoctrineは``[name]_idx``のフォーマットを使用します。'ageindex'と呼ばれるインデックスの定義は実際には'ageindex\_idx'に変換されます。

次のコードでインデックスの命名規約を変更できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_IDXNAME\_FORMAT,
'%s\_index');

--------------------------
シーケンス名のフォーマット
--------------------------

``Doctrine\_Core::ATTR\_IDXNAME\_FORMAT``と同じように、``Doctrine\_Core::ATTR\_SEQNAME\_FORMAT``はシーケンスの命名規約を変更するために使うことができます。デフォルトではDoctrineは``[name]\_seq``のフォーマットを使います。``mysequence``の名前を持つ新しいシーケンスを作ると``mysequence_seq``という名前のシーケンスに作成につながるからです。

次のコードでシーケンスの命名規約を変更できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_SEQNAME\_FORMAT,
'%s\_sequence');

------------------------
テーブル名のフォーマット
------------------------

インデックスとシーケンス名のフォーマットと同じようにテーブル名のフォーマットは次のコードで変更できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_TBLNAME\_FORMAT,
'%s\_table');

----------------------------
データベース名のフォーマット
----------------------------

インデックス、シーケンスとテーブル名のフォーマットと同じようにデータベース名のフォーマットを次のコードで変更できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_DBNAME\_FORMAT,
'myframework\_%s');

------------------
バリデーション属性
------------------

Doctrineはバリデーションに対して完全なコントロール機能を提供します。バリデーション処理は``Doctrine\_Core::ATTR_VALIDATE``でコントロールされます。

バリデーションモードはビット単位なので、``\|``を使用して結合し``^``を使用して削除できます。これを行う方法は下記の例をご覧ください。

--------------------------
バリデーションモードの定数
--------------------------

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``VALIDATE_NONE`` \|\|
バリデーション処理全体をオフに切り替える。\|\| \|\|
``VALIDATE_LENGTHS`` \|\|
すべてのフィールドの長さをバリデートする。\|\| \|\| ``VALIDATE_TYPES``
\|\|
すべてのフィールドの型をバリデートする。Doctrineは緩い型のバリデーションを行う。例えば'13.3'などを含む文字列は整数としてパスしないが'13'はパスする。\|\|
\|\| ``VALIDATE_CONSTRAINTS`` \|\|
notnull、emailなどのすべてのフィールド制約をバリデートする。\|\| \|\|
``VALIDATE_ALL`` \|\| すべてのバリデーションをオンにする。\|\|

    **NOTE**
    デフォルトのバリデーションは無効になっているのでデータをバリデートしたい場合有効にする必要があります。この設定を変更する方法の例のいくつかは下記で示されています。

----------
例
----------

次のコードで``Doctrine\_Core::VALIDATE_ALL``属性を利用してすべてのバリデーションを有効にできます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_ALL);

次のコードで長さと型をバリデートし、制約には行わないようにDoctrineを設定できます:

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_LENGTHS \| Doctrine\_Core::VALIDATE\_TYPES);

======
まとめ
======

Doctrineを設定するために最も良く使われる属性の一部を検討してきました。今のところこれらの属性はよくわからないかもしれません。次の章を読めば必要な属性がわかります。

上記の値を変更したい属性がありましたら、これを``bootstrap.php``ファイルに追加するとコードは次のようになります:

 /\*\* \* Bootstrap Doctrine.php, register autoloader and specify \*
configuration attributes \*/

require\_once('../doctrine/branches/1.2/lib/Doctrine.php');
spl\_autoload\_register(array('Doctrine', 'autoload')); $manager =
Doctrine\_Manager::getInstance();

$conn = Doctrine\_Manager::connection('sqlite::memory:', 'doctrine');

$manager->setAttribute(Doctrine\_Core::ATTR\_VALIDATE,
Doctrine\_Core::VALIDATE\_ALL);
$manager->setAttribute(Doctrine\_Core::ATTR\_EXPORT,
Doctrine\_Core::EXPORT\_ALL);
$manager->setAttribute(Doctrine\_Core::ATTR\_MODEL\_LOADING,
Doctrine\_Core::MODEL\_LOADING\_CONSERVATIVE);

次の章に移動する準備ができました。Doctrineの[doc connections
:name]に関するすべての内容を学びます。
