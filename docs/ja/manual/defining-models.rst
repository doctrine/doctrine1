以前述べたように、Doctrineの最も低いレベルにおいてスキーマはデータベーステーブル用のスキーマメタデータをマッピングするPHPクラスの一式で表現されます。

この章ではPHPコードを使用してスキーマ情報をマッピングする方法を詳しく説明します。

======
カラム
======

データベースの互換性の問題の1つは多くのデータベースにおいて返されるクエリの結果セットが異なることです。MySQL
はフィールドの名前はそのままにします。このことは``"SELECT myField FROM
..."``形式のクエリを発行する場合、結果セットは``myField``のフィールドを含むことを意味します。

不幸にして、これはMySQLとその他のいくつかのデータベースだけの挙動です。例えばPostgresはすべてのフィールド名を小文字で返す一方でOracleは大文字ですべてのフィールド名を返します。"だから何？Doctrineを使う際にこれがどのような方法で影響を及ぼすの？"、と疑問に思うかもしれません。幸いにして、この問題を悩む必要はまったくありません。

Doctrineはこの問題を透過的に考慮します。Record派生クラスを定義し``myField``という名前のフィールドを定義する場合、MySQLもしくはPostgresもしくはOracleその他を使おうが、``:code:`record->myField`` (もしくは```\ record['myField']``、好きな方で)を通してアクセスできることを意味します。

要するに:
under\_scores(アンダースコア)、camelCase(キャメルケース)もしくは望む形式を使用してフィールドを好きなように名付けることができます。

    **NOTE**
    Doctrineにおいてカラムとカラムのエイリアスは大文字と小文字を区別します。DQLクエリでカラムを使用するとき、カラム/フィールドの名前はモデルの定義のケースにマッチしなければなりません。

------------------
カラムのエイリアス
------------------

Doctrineはカラムのエイリアスを定義する方法を提供します。これはデータベースのロジックからアプリケーションのロジックを分離するのを維持したい場合にとても役に立ちます。例えば
データベースフィールドの名前を変更したい場合、アプリケーションで変更する必要のあるのはカラムの定義だけです。

 // models/Book.php

class Book extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('bookTitle as title', 'string');
} }

下記のコードはYAMLフォーマットのサンプルです。[doc yaml-schema-files
:name]の章でYAMLの詳しい情報を読むことができます:

 # schema.yml

Book: columns: bookTitle: name: bookTitle as title type: string

Now
データベースのカラムはbookTitleという名前ですがtitleを使用してオブジェクトのプロパティにアクセスできます。

 // test.php

// ... $book = new Book(); $book->title = 'Some book'; $book->save();

--------------
デフォルトの値
--------------

Doctrineはすべてのデータ型のデフォルト値をサポートします。デフォルト値がレコードのカラムに付属するとき2つのことを意味します。まずこの値はすべての新しく作成されたRecordに添付されDoctrineがデータベースを作成するときにcreate
tableステートメントにデフォルト値を含めます。

 // models/generated/BaseUser.php

class User extends BaseUser { public function setTableDefinition() {
$this->hasColumn('username', 'string', 255, array('default' => 'default
username'));

::

        // ...
    }

    // ...

}

YAMLフォーマットのサンプルコードは次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細情報を読むことができます:

 # schema.yml

User: # ... columns: username: type: string(255) default: default
username # ...

真新しいUserレコードで名前を表示するときデフォルト値が表示されます:

 // test.php

// ... $user = new User(); echo $user->username; //
デフォルトのユーザー名

----------
データの型
----------

^^^^^^^^
はじめに
^^^^^^^^

データベースフィールドに保存できる情報用にすべてのDBMSはデータの型の複数の選択肢を提供します。しかしながら、利用可能なデータ型の一式はDBMSによって異なります。

DoctrineによってサポートされるDBMSでインターフェイスを簡略化するために、内在するDBMSにおいてアプリケーションが個別にアクセスできるデータ型の基本セットが定義されました

Doctrineのアプリケーションプログラミングインターフェイスはデータベースオプションを管理する際にデータ型のマッピングを考慮します。それぞれのドライバを使用して内在するDBMSに送るかつDBMSから受け取るものを変換することも可能です。

次のデータ型の例ではDoctrineの``createTable()``メソッドを使います。データ型セクションの最後の配列の例では選んだDBMSでポータブルなテーブルを作成するために``createTable()``メソッドを使うことがあります(何のDBMSが適切にサポートされているか理解するためにDoctrineのメインドキュメントを参照してくださるようお願いします)。次の例ではインデックスの作成と維持はカバーされないことも注意してください。この章はデータ型と適切な使い方のみを考慮します。

アプリケーションレベルでバリデートされた長さ(Doctrineバリデータでバリデートされた長さ)と同様に、カラムの長さはデータベースレベルで影響があることを気を付けてください。

例 1.
'string'型と長さ3000の'content'という名前のカラムはデータベースレベルの長さ4000を持つ'TEXT'データベースの型になります。しかしながらレコードがバリデートされるとき最大長が3000である'content'カラムを持つことのみ許可されます。

例 2.
多くのデータベースでは'integer'型と長さ1を持つカラムは'TINYINT'になります。

一般的に
Doctrineは指定された長さによってどのinteger/string型を使うのか知っているほど賢いです。

^^^^^^^^
型修飾子
^^^^^^^^

Doctrine APIの範囲内で
オプションのテーブルデザインに役立つように設計された修飾子が少しあります:

-  notnull修飾子
-  length修飾子
-  default修飾子
-  フィールド定義用のunsigned修飾子、integerフィールド型に対して
   すべてのDBMSはこの修飾子をサポートしません。
-  collation修飾子(すべてのドライバでサポートされない)
-  フィールド定義用の固定長修飾子

上記の内容に基づいて話しを進めると、特定の使い方のシナリオ用の特定のフィールドの型を作成するために、修飾子がフィールド定義を変更することが言えます。DBMSのフィールド値の定義によって、フィールド上でデフォルトのDBMS
NOT NULL
Flagをtrueもしくはfalseに設定するためにnotnull修飾子は次の方法で使われます:
PostgreSQLにおいて"NOT NULL"の定義は"NOT
NULL"に設定される一方で、(例えば)MySQLでは"NULL"オプションは"NO"に設定されます。"NOT
NULL"フィールド型を定義するために、定義配列に追加パラメータを追加するだけです(例は次のセクションを参照)。

 'sometime' = array( 'type' => 'time', 'default' => '12:34:05',
'notnull' => true, ),

上記の例を利用することで、デフォルトのフィールド演算子も研究できます。フィールド用のデフォルト値はnotnull演算子と同じ方法で設定されます。この値はDBMSがテキストフィールド用にサポートする文字集合、フィールドのデータ型用の他の有効な値に設定されます。上記の例において、"Time"データ型に対して有効な時間である'12:34:05'を指定しました。datetimeと同じく日付と時間を設定するとき、調べて選択したDBMSのエポックの範囲に収まるようにすべきであることを覚えておいてください。さもなければエラーを診断するのが困難な状況に遭遇します！

 'sometext' = array( 'type' => 'string', 'length' => 12, ),

上記の例ではデータベースのテーブルで長さ12のフィールドを変更する文字が作られます。長さの定義が省略される場合、Doctrineは指定されたデータ型で許容される最大長を作成されます。これはフィールドの型とインデックス作成において問題を引き起こす可能性があります。ベストプラクティスはすべてもしくは大抵のフィールドに対して長さを定義することです。

^^^^^^
論理型
^^^^^^

論理型は0か1の2つの値のどちらかだけを表します。効率性の観点からDBMSドライバの中には単独の文字のテキストフィールドで整数型を実装するものがあるのでこれらの論理型を整数型として保存されることを想定しないでください。この型のフィールドに割り当てできる3番目の利用可能な値としてnullを使うことで三値論理は可能です。

    **NOTE**
    次のいくつかの例では使ったり試したりすることを想定していません。これらは単にPHPコードもしくはYAMLスキーマファイルを利用してDoctrineの異なるデータ型を使う方法を示すことだけを目的としています。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('booltest', 'boolean'); } }

YAMLフォーマットでの同じ例です。[doc yaml-schema-files
:name]の章でYAMLの詳細内容を見ることができます:

 Test: columns: booltest: boolean

^^^^^^
整数型
^^^^^^

整数型はPHPの整数型と同じです。それぞれのDBMSが扱える大きさの整数型の値を保存します。

オプションとしてこの型のフィールドは符号なしの整数として作成されますがすべてのDBMSはこれをサポートしません。それゆえ、このようなオプションは無視されることがあります。本当にポータルなアプリケーションはこのオプションの利用可能性に依存すべきではありません。

整数型はカラムの長さによって異なるデータベースの型にマッピングされます。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('integertest', 'integer', 4,
array( 'unsigned' => true ) ); } }

YAMLフォーマットでの例です。[doc yaml-schema-files
:name]の章っでYAMLの詳細情報を読むことができます:

 Test: columns: integertest: type: integer(4) unsigned: true

^^^^^^^^^^^^
浮動小数点型
^^^^^^^^^^^^

浮動小数点のデータ型は10進法の浮動小数点数を保存できます。このデータ型は高い精度を必要としない大きなスケールの範囲の数値を表現するのに適しています。スケールと精度に関してデータベースに保存される値の制限は使用されるDBMSに依存します。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('floattest', 'float'); } }

下記のコードはYAMLフォーマットでの例です。[doc yaml-schema-files
:name]の章でYAMLの詳細情報を読むことができます:

 Test: columns: floattest: float

^^^^^^
小数型
^^^^^^

小数型のデータは固定精度の小数を保存できます。このデータ型は高い正確度と精度を必要とする数値を表現するのに適しています。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('decimaltest', 'decimal'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を学ぶことができます:

 Test: columns: decimaltest: decimal

他のカラムの``length``を設定するように小数の長さを指定することが可能で3番目の引数でオプションとして``scale``を指定できます:

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('decimaltest', 'decimal', 18,
array( 'scale' => 2 ) ); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細情報をみることができます:

 Test: columns: decimaltest: type: decimal(18) scale: 2

^^^^^^^^
文字列型
^^^^^^^^

テキストデータ型では長さに対して2つのオプションが利用可能です:
1つは明示的に制限された長さでもう一つはデータベースが許容する限りの大きさの未定義の長さです。

効率の点で制限オプション付きの長さは大抵の場合推奨されます。未定義の長さオプションはとても大きなフィールドを許可しますがインデックスとnullの利用を制限することがあり、その型のフィールド上でのソートを許可しません。

この型のフィールドは8ビットの文字を扱うことができます。文字列の値をこの型に変換することでドライバはDBMSで特別な意味を持つ文字のエスケープを考慮します。

デフォルトではDoctrineは可変長の文字型を使用します。固定長の型が使われる場合、fixed修飾子を通してコントロールできます。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('stringtest', 'string', 200,
array( 'fixed' => true ) ); } }

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細情報を見ることができます:

 Test: columns: stringtest: type: string(200) fixed: true

^^^^
配列
^^^^

これはPHPの'array'型と同じです。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('arraytest', 'array', 10000); }
}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細情報を見ることができます:

 Test: columns: arraytest: array(10000)

^^^^^^^^^^^^
オブジェクト
^^^^^^^^^^^^

Doctrineはオブジェクトをカラム型としてサポートします。基本的にオブジェクトをフィールドに設定可能でDoctrineはそのオブジェクトのシリアライズ/アンシリアライズを自動的に処理します。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('objecttest', 'object'); } }

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細情報を読むことができます:

 Test: columns: objecttest: object

    **NOTE**
    配列とオブジェクト型はデータベースで永続化するときはデータをシリアライズしデータベースから引き出すときはデータをアンシリアライズします

^^^^^^^^
blob
^^^^^^^^

blob(Binary Large
OBject)データ型は、通常はファイルに保存されるデータのようにテキストフィールドに大きすぎる未定義の長さのデータを保存することを意味します。

内在するDBMSが"全文検索"として知られる機能をサポートしない限りblobフィールドはエリーの検索句(``WHERE``)のパラメータを使用することを意味しません。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('blobtest', 'blob'); } }

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: blobtest: blob

^^^^^^^^
clob
^^^^^^^^

clob (Character Large
OBject)データ型は、通常はファイルに保存されるデータのように、テキストフィールドで保存するには大きすぎる未定義の長さのデータを保存することを意味します。

blogフィールドがデータのすべての型を保存するのが想定されているのに対してclobフィールドは印字可能なASCII文字で構成されるデータのみを保存することを想定しています。

内在するDBMSが"全文検索"として知られる機能をサポートしない限りclobフィールドはクエリ検索句(WHERE)のパラメータとして使われることが想定されています。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('clobtest', 'clob'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: clobtest: clob

^^^^^^^^^^^^^^^^^^
timestamp
^^^^^^^^^^^^^^^^^^

timestampデータ型は日付と時間のデータ型の組み合わせに過ぎません。timestamp型の値の表記は日付と時間の文字列の値は1つのスペースで連結することで実現されます。それゆえ、フォーマットのテンプレートは``YYYY-MM-DD
HH:MI:SS``です。表される値は日付と時間データ型で説明したルールと範囲に従います。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('timestamptest', 'timestamp'); }
}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: timestamptest: timestamp

^^^^^^^^
time
^^^^^^^^

timeデータ型はその日の与えられた瞬間の時間を表します。DBMS独自の時間の表記もISO-8601標準に従ってテキストの文字列を使用することで実現できます。

日付の時間用にISO-8601標準で定義されたフォーマットはHH:MI:SSでHHは時間で00から23まででMIとSSは分と秒で00から59までです。時間、分と秒は10より小さな数値の場合は左側に0が詰められます。

DBMSの中にはネイティブで時間フォーマットをサポートするものがありますが、DBMSドライバの中にはこれらを整数もしくはテキストの文字列として表現しなければならないものがあります。ともかく、この型のフィールドによるソートクエリの結果と同じように時間の値の間で比較することは常に可能です。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('timetest', 'time'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: timetest: time

^^^^^^^^
date
^^^^^^^^

dateデータ型は年、月と日にちのデータを表します。DBMS独自の日付の表記はISO-8601標準の書式のテキスト文字列を使用して実現されます。

日付用にISO-8601標準で定義されたフォーマットはYYYY-MM-DDでYYYYは西暦の数字(グレゴリオ暦)、MMは01から12までの月でDDは01か31までの日の数字です。10より小さい月の日にちの数字には左側に0が追加されます。

DBMSの中にはネイティブで日付フォーマットをサポートするものがありますが、他のDBMSドライバではこれらを整数もしくはテキストの値として表現しなければならないことがあります。どの場合でも、この型のフィールドによるソートクエリの結果によって日付の間の比較は常に可能です。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('datetest', 'date'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: datetest: date

^^^^^^^^
enum
^^^^^^^^

Doctrineはunifiedなenum型を持ちます。カラムに対して可能な値は``Doctrine_Record::hasColumn()``でカラム定義に指定できます。

    **NOTE**
    DBMSに対してネイティブのenum型を使用したい場合次の属性を設定しなければなりません:

 $conn->setAttribute(Doctrine\_Core::ATTR\_USE\_NATIVE\_ENUM, true);

次のコードはenumの値を指定する方法の例です:

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('enumtest', 'enum', null,
array('values' => array('php', 'java', 'python')) ); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: enumtest: type: enum values: [php, java, python]

^^^^^^^^
gzip
^^^^^^^^

gzipデータ型は存続するときに自動的に圧縮取得されたときに解凍される以外は文字列と同じです。ビットマップ画像など、大きな圧縮率でデータを保存するときにこのデータ型は役に立ちます。

 class Test extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('gziptest', 'gzip'); } }

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Test: columns: gziptest: gzip

    **NOTE**
    内部ではgzipカラム型の内容の圧縮と解凍を行うために[http://www.php.net/gzcompress
    圧縮]系のPHP関数が使われています。

----------
例
----------

次の定義を考えましょう:

 class Example extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('id', 'string', 32, array(
'type' => 'string', 'fixed' => 1, 'primary' => true, 'length' => '32' )
);

::

        $this->hasColumn('someint', 'integer', 10, array(
                'type' => 'integer',
                'unsigned' => true,
                'length' => '10'
            )
        );

        $this->hasColumn('sometime', 'time', 25, array(
                'type' => 'time',
                'default' => '12:34:05',
                'notnull' => true,
                'length' => '25'
            )
        );

        $this->hasColumn('sometext', 'string', 12, array(
                'type' => 'string',
                'length' => '12'
            )
        );

        $this->hasColumn('somedate', 'date', 25, array(
                'type' => 'date',
                'length' => '25'
            )
        );

        $this->hasColumn('sometimestamp', 'timestamp', 25, array(
                'type' => 'timestamp',
                'length' => '25'
            )
        );

        $this->hasColumn('someboolean', 'boolean', 25, array(
                'type' => 'boolean',
                'length' => '25'
            )
        );

        $this->hasColumn('somedecimal', 'decimal', 18, array(
                'type' => 'decimal',
                'length' => '18'
            )
        );

        $this->hasColumn('somefloat', 'float', 2147483647, array(
                'type' => 'float',
                'length' => '2147483647'
            )
        );

        $this->hasColumn('someclob', 'clob', 2147483647, array(
                'type' => 'clob',
                'length' => '2147483647'
            )
        );

        $this->hasColumn('someblob', 'blob', 2147483647, array(
                'type' => 'blob',
                'length' => '2147483647'
            )
        );
    }

}

YAMLフォーマットでの同じ例です。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Example: tableName: example columns: id: type: string(32) fixed: true
primary: true someint: type: integer(10) unsigned: true sometime: type:
time(25) default: '12:34:05' notnull: true sometext: string(12)
somedate: date(25) sometimestamp: timestamp(25) someboolean: boolean(25)
somedecimal: decimal(18) somefloat: float(2147483647) someclob:
clob(2147483647) someblob: blob(2147483647)

上記の例はPgsqlで次のテーブルが作成します:

\|\|~ カラム \|\|~ 型 \|\| \|\| ``id`` \|\| ``character(32)`` \|\| \|\|
``someint`` \|\| ``integer`` \|\| \|\| ``sometime`` \|\|
タイムゾーンなしの``time`` \|\| \|\| ``sometext`` \|\|
``character``もしくは``varying(12)`` \|\| \|\| ``somedate`` \|\|
``date`` \|\| \|\| ``sometimestamp`` \|\|
タイムゾーンなしの``timestamp`` \|\| \|\| ``someboolean`` \|\|
``boolean`` \|\| \|\| ``somedecimal`` \|\| ``numeric(18,2)`` \|\| \|\|
``somefloat`` \|\| ``double``の精度 \|\| \|\| ``someclob`` \|\| ``text``
\|\| \|\| ``someblob`` \|\| ``bytea`` \|\|

Mysqlではスキーマは次のデータベーステーブルを作成します:

\|\|~ フィールド \|\|~ 型 \|\| \|\| ``id`` \|\| ``char(32)`` \|\| \|\|
``someint`` \|\| ``integer`` \|\| \|\| ``sometime`` \|\| ``time`` \|\|
\|\| ``sometext`` \|\| ``varchar(12)`` \|\| \|\| ``somedate`` \|\|
``date`` \|\| \|\| ``sometimestamp`` \|\| ``timestamp`` \|\| \|\|
``someboolean`` \|\| ``tinyint(1)`` \|\| \|\| ``somedecimal`` \|\|
``decimal(18,2)`` \|\| \|\| ``somefloat`` \|\| ``double`` \|\| \|\|
``someclob`` \|\| ``longtext`` \|\| \|\| ``someblob`` \|\| ``longblob``
\|\|

============
リレーション
============

--------
はじめに
--------

Doctrineにおいてすべてのレコードのリレーションは``Doctrine\_Record::hasMany``、``Doctrine_Record::hasOne``メソッドで設定されます。Doctrineはほとんどの種類のデータベースリレーションをサポートします
from
一対一のシンプルな外部キーのリレーションから自己参照型のリレーションまでサポートします。

カラムの定義とは異なり``Doctrine\_Record::hasMany``と``Doctrine_Record::hasOne``メソッドは``setUp()``と呼ばれるメソッドの範囲内で設置されます。両方のメソッドは2つの引数を受け取ります:
最初の引数はクラスの名前とオプションのエイリアスを含む文字列で、2番目の引数はリレーションのオプションで構成される配列です。オプションの配列は次のキーを含みます:

\|\|~ 名前 \|\|~ オプション \|\|~ 説明 \|\| \|\| ``local`` \|\| No \|\|
リレーションのローカルフィールド。ローカルフィールドはクラスの定義ではリンク付きのフィールド。
\|\| \|\| ``foreign`` \|\| No \|\|
リレーションの外部フィールド。外部フィールドはリンク付きのクラスのリンク付きフィールドです。\|\|
\|\| ``refClass`` \|\| Yes \|\|
アソシエーションクラスの名前。これは多対多のアソシエーションに対してのみ必要です。\|\|
\|\| ``owningSide``\|\| Yes \|\|
所有側のリレーションを示すには論理型のtrueを設定します。所有側とは外部キーを所有する側です。2つのクラスの間のアソシエーションにおいて所有側は1つのみです。Doctrineが所有側を推測できないもしくは間違った推測をする場合このオプションが必須であることに注意してください。'local'と'foreign'の両方が識別子(主キー)の一部であるときこれが当てはまります。この方法で所有側を指定することは害になることはありません。\|\|
\|\| ``onDelete`` \|\| Yes \|\|
Doctrineによってテーブルが適用されるときに``onDelete``整合アクションが外部キー制約に適用されます。
\|\| \|\| ``onUpdate`` \|\| Yes \|\|
Doctrineによってテーブルが作成されたときに``onUpdate``整合アクションが外部キー制約に適用されます。\|\|
\|\| ``cascade`` \|\| Yes \|\|
オペレーションをカスケーディングするアプリケーションレベルを指定する。現在削除のみサポートされる
\|\|

最初の例として、``Forum\_Board``と``Forum\_Thread``の2つのクラスがあるとします。リレーションが一対多なので、``Forum\_Board``は多くの``Forum\_Threads``を持ちます。リレーションにアクセスする際に``Forum_``を書きたくないので、リレーションのエイリアスを使用しエイリアスの``Threads``を使用します。

最初に``Forum_Board``クラスを見てみましょう。これはカラム: 名前,
説明を持ち主キーを指定していないので、Doctrineはidカラムを自動作成します。

``hasMany()``メソッドを使用することで``Forum\_Thread``クラスへのリレーションを定義します。localフィールドがboardクラスの主キーである一方でforeignフィールドが``Forum\_Thread``クラスの``board_id``フィールドです。

 // models/Forum\_Board.php

class Forum\_Board extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 100);
$this->hasColumn('description', 'string', 5000); }

::

    public function setUp()
    {
        $this->hasMany('Forum_Thread as Threads', array(
                'local' => 'id',
                'foreign' => 'board_id'
            )
        );
    }

}

    **NOTE**
    asキーワードが使われていることに注目してください。このことは``Forum\_Board``が``Forum_Thread``に定義された多数のリレーションを持ちますが``Threads``のエイリアスが設定されることを意味します。

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Forum\_Board: columns: name: string(100) description: string(5000)

``Forum\_Thread``クラスの内容を少しのぞいて見ましょう。カラムの内容は適当ですが、リレーションの定義方法に注意をはらってください。それぞれの``Thread``は1つの``Board``のみを持つことができるので``hasOne()``メソッドを使っています。またエイリアスの使い方とlocalカラムが``board_id``である一方で外部カラムは``id``カラムであることに注目してください。

 // models/Forum\_Thread.php

class Forum\_Thread extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer');
$this->hasColumn('board\_id', 'integer'); $this->hasColumn('title',
'string', 200); $this->hasColumn('updated', 'integer', 10);
$this->hasColumn('closed', 'integer', 1); }

::

    public function setUp() 
    {
        $this->hasOne('Forum_Board as Board', array(
                'local' => 'board_id',
                'foreign' => 'id'
            )
        );

        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Forum\_Thread: columns: user\_id: integer board\_id: integer title:
string(200) updated: integer(10) closed: integer(1) relations: User:
local: user\_id foreign: id foreignAlias: Threads Board: class:
Forum\_Board local: board\_id foreign: id foreignAlias: Threads

これらのクラスを使い始めることができます。プロパティに既に使用した同じアクセサはリレーションに対してもすべて利用できます。

最初に新しいboardを作りましょう:

 // test.php

// ... $board = new Forum\_Board(); $board->name = 'Some board';

boardの元で新しいthreadを作りましょう:

 // test.php

// ... $board->Threads[0]->title = 'new thread 1';
$board->Threads[1]->title = 'new thread 2';

それぞれの``Thread``はそれぞれのユーザーに関連付ける必要があるので新しい``User``を作りそれぞれの``Thread``に関連付けましょう:

 $user = new User(); $user->username = 'jwage'; $board->Threads[0]->User
= $user; $board->Threads[1]->User = $user;

これですべての変更を1つの呼び出しで保存できます。threadsと同じように新しいboardを保存します:

 // test.php

// ... $board->save();

上記のコードを使うときに作成されるデータ構造を見てみましょう。投入したばかりのオブジェクトグラフの配列を出力するために``test.php``にコードを追加します:

 print\_r($board->toArray(true));

 .. tip::

   
    レコードのデータを簡単にインスペクトできるように``Doctrine\_Record::toArray()``は``Doctrine_Record``インスタンスのすべてのデータを取り配列に変換します。これはリレーションを含めるかどうかを伝える``$deep``という名前の引数を受け取ります。この例では``Threads``のデータを含めたいので{[true]}を指定しました。

ターミナルで``test.php``を実行すると次の内容が表示されます:

 $ php test.php Array ( [id] => 2 [name] => Some board [description] =>
[Threads] => Array ( [0] => Array ( [id] => 3 [user\_id] => 1
[board\_id] => 2 [title] => new thread 1 [updated] => [closed] => [User]
=> Array ( [id] => 1 [is\_active] => 1 [is\_super\_admin] => 0
[first\_name] => [last\_name] => [username] => jwage [password] =>
[type] => [created\_at] => 2009-01-20 16:41:57 [updated\_at] =>
2009-01-20 16:41:57 )

::

                )

            [1] => Array
                (
                    [id] => 4
                    [user_id] => 1
                    [board_id] => 2
                    [title] => new thread 2
                    [updated] => 
                    [closed] => 
                    [User] => Array
                        (
                            [id] => 1
                            [is_active] => 1
                            [is_super_admin] => 0
                            [first_name] => 
                            [last_name] => 
                            [username] => jwage
                            [password] => 
                            [type] => 
                            [created_at] => 2009-01-20 16:41:57
                            [updated_at] => 2009-01-20 16:41:57
                        )

                )

        )

)

    **NOTE**
    Doctrine内部でautoincrementの主キーと外部キーが自動的に設定されることに注意してください。主キーと外部キーの設定に悩む必要はまったくありません！

--------------------------
外部キーのアソシエーション
--------------------------

^^^^^^
一対一
^^^^^^

一対一のリレーションは最も基本的なリレーションでしょう。次の例ではリレーションが一対一である``User``と``Email``の2つのクラスを考えます。

最初に``Email``クラスを見てみましょう。一対一のリレーションをバインドしているので``hasOne()``メソッドを使用しています。``Email``クラスで外部キーのカラム(``user_id``)を定義する方法に注目してください。これは``Email``が``User``によって所有され他の方法がないという事実に基づいています。実際次の慣習
- 所有側のクラスで外部キーを設置することに従うべきです。

外部キー用に推奨される命名規約は:
``[tableName]_[primaryKey]``です。外部テーブルは'user'で主キーは'id'なので外部キーのカラムは'user\_id'と名付けました。

 // models/Email.php

class Email extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer');
$this->hasColumn('address', 'string', 150); }

::

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Email: columns: user\_id: integer address: string(150) relations: User:
local: user\_id foreign: id foreignType: one

.. tip::

    リレーションは自動的に反転して追加されるので、YAMLスキーマファイルを使用するとき反対端(``User``)でリレーションを指定することは必須ではありません。リレーションはクラスの名前から名付けられます。ですのでこの場合``User``側のリレーションは``Email``と呼ばれ``many``になります。これをカスタマイズしたい場合``foreignAlias``と``foreignType``オプションを使用できます。

``Email``クラスは``User``クラスとよく似ています。localとforeignカラムは``Email``クラスの定義と比較される``hasOne()``の定義に切り替えられることに注目してください。

 // models/User.php

class User extends BaseUser { public function setUp() { parent::setUp();

::

        $this->hasOne('Email', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );
    }

}

    **NOTE**
    ``setUp()``メソッドをオーバーライドして``parent::setUp()``を呼び出していることに注目してください。これはYAMLもしくは既存のデータベースから生成された``BaseUser``クラスがメインの``setUp()``メソッドを持ちリレーションを追加するために``User``クラスでこのメソッドをオーバーライドしているからです。

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: # ... relations: # ... Email: local: id foreign: user\_id

^^^^^^^^^^^^^^
一対多と多対一
^^^^^^^^^^^^^^

一対多と多対一のリレーションは一対一のリレーションとよく似ています。以前の章で見た推奨される慣習は一対多と多対一のリレーションにも適用されます。

次の例では2つのクラス:
``User``と``Phonenumber``があります。一対多のリレーションとして定義します(ユーザーは複数の電話番号を持つ)。繰り返しますが``Phonenumber``は``User``によって所有されるので``Phonenumber``クラスに外部キーを設置します。

 // models/User.php

class User extends BaseUser { public function setUp() { parent::setUp();

::

        // ...

        $this->hasMany('Phonenumber as Phonenumbers', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );
    }

}

// models/Phonenumber.php

class Phonenumber extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer');
$this->hasColumn('phonenumber', 'string', 50); }

::

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

YAMLフォーマットでの同じです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: # ... relations: # ... Phonenumbers: type: many class: Phonenumber
local: id foreign: user\_id

Phonenumber: columns: user\_id: integer phonenumber: string(50)
relations: User: local: user\_id foreign: id

^^^^^^^^^^
ツリー構造
^^^^^^^^^^

ツリー構造は自己参照の外部キーのリレーションです。次の定義は階層データの概念の用語では隣接リスト(Adjacency
List)とも呼ばれます。

 // models/Task.php

class Task extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 100);
$this->hasColumn('parent\_id', 'integer'); }

::

    public function setUp() 
    {
        $this->hasOne('Task as Parent', array(
                'local' => 'parent_id',
                'foreign' => 'id'
            )
        );

        $this->hasMany('Task as Subtasks', array(
                'local' => 'id',
                'foreign' => 'parent_id'
            )
        );
    }

}

YAMLフォーマットでの同じ例です。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Task: columns: name: string(100) parent\_id: integer relations: Parent:
class: Task local: parent\_id foreign: id foreignAlias: Subtasks

    **NOTE**
    上記の実装は純粋な例で階層データを保存し読み取るための最も効率的な方法ではありません。階層データを扱い推奨方法に関してはDoctrineに含まれる``NestedSet``ビヘイビアを確認してください。

----------------------------------------
テーブルのアソシエーションをジョインする
----------------------------------------

^^^^^^
多対多
^^^^^^

リレーショナルデータベースの背景知識があれば、多対多のアソシエーションを扱う方法になれているかもしれません:
追加のアソシエーションテーブルが必要です。

多対多のリレーションにおいて2つのコンポーネントの間のリレーションは常に集約関係でアソシエーションテーブルは両端で所有されます。ユーザーとグループの場合:
ユーザーが削除されているとき、ユーザーが所属するグループは削除されません。しかしながら、ユーザーとユーザーが所属するグループの間のアソシエーションが代わりに削除されています。これはユーザーとユーザーが所属するグループの間のリレーションを削除しますが、ユーザーとグループは削除しません。

ときにはユーザー/グループを削除するときアソシエーションテーブルの列を削除したくないことがあります。リレーションをアソシエーションコンポーネントに設定する(このケースでは``Groupuser``)
ことで明示的にこのビヘイビアをオーバーライドできます。

次の例ではリレーションが多対多として定義されているGroupsとUsersがあります。このケースでは``Groupuser``と呼ばれる追加クラスも定義する必要があります。

 class User extends BaseUser public function setUp() { parent::setUp();

::

        // ...

        $this->hasMany('Group as Groups', array(
                'local' => 'user_id',
                'foreign' => 'group_id',
                'refClass' => 'UserGroup'
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 User: # ... relations: # ... Groups: class: Group local: user\_id
foreign: group\_id refClass: UserGroup

    **NOTE**
    多対多のリレーションをセットアップするとき上記の``refClass``オプションは必須です。

 // models/Group.php

class Group extends Doctrine\_Record { public function
setTableDefinition() { $this->setTableName('groups');
$this->hasColumn('name', 'string', 30); }

::

    public function setUp()
    {
        $this->hasMany('User as Users', array(
                'local' => 'group_id',
                'foreign' => 'user_id',
                'refClass' => 'UserGroup'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Group: tableName: groups columns: name: string(30) relations: Users:
class: User local: group\_id foreign: user\_id refClass: UserGroup

    **NOTE**
    ``group``は予約語であることにご注意ください。これが``setTableName``メソッドを使用してテーブルを``groups``にリネームする理由です。予約語がクォートでエスケープされるように他のオプションは``Doctrine::ATTR\_QUOTE_IDENTIFIER``属性を使用して識別子のクォート追加を有功にすることです。

 $manager->setAttribute(Doctrine\_Core::ATTR\_QUOTE\_IDENTIFIER, true);

 // models/UserGroup.php

class UserGroup extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'primary' => true ) );

::

        $this->hasColumn('group_id', 'integer', null, array(
                'primary' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

UserGroup: columns: user\_id: type: integer primary: true group\_id:
type: integer primary: true

リレーションが双方向であることに注目してください。``User``は複数の``Group``を持ち``Group``は複数の``User``を持ちます。Doctrineで多対多のリレーションを完全に機能させるためにこれは必須です。

新しいモデルで遊んでみましょう。ユーザーを作成しこれにいくつかのグループを割り当てます。最初に新しい``User``インス場合も考えてみましょう。注文テーブルが実在する製品の注文のみが含まれることを保証したい場合を考えます。ですので製品テーブルを参照する注文テーブルで外部キー制約を定義します:

 // models/Order.php

class Order extends Doctrine\_Record { public function
setTableDefinition() { $this->setTableName('orders');
$this->hasColumn('product\_id', 'integer'); $this->hasColumn('quantity',
'integer'); }

::

    public function setUp()
    {
        $this->hasOne('Product', array(
                'local' => 'product_id',
                'foreign' => 'id'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Order: tableName: orders columns: product\_id: integer quantity: integer
relations: Product: local: product\_id foreign: id

    **NOTE**
    外部キーを含むクエリを発行するときに最適なパフォーマンスを保証するために外部キーのカラムのインデックスは自動的に作成されます。

``Order``クラスがエクスポートされるとき次のSQLが実行されます:

 CREATE TABLE orders ( id integer PRIMARY KEY auto\_increment,
product\_id integer REFERENCES products (id), quantity integer, INDEX
product\_id\_idx (product\_id) )

``product``テーブルに現れない``product_id``で``orders``を作成するのは不可能です。

この状況においてordersテーブルは参照するテーブルでproductsテーブルはは参照されるテーブルです。同じように参照と参照されるカラムがあります。

^^^^^^^^^^^^^^
外部キーの名前
^^^^^^^^^^^^^^

Doctrineでリレーションを定義し外部キーがデータベースで作成されるとき、Doctrineは外部キーの名前をつけようとします。ときには、その名前が望んだものとは違うことがあるのでリレーションのセットアップで``foreignKeyName``オプションを使うことで名前をカスタマイズできます。

 // models/Order.php

class Order extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $this->hasOne('Product', array(
                'local' => 'product_id',
                'foreign' => 'id',
                'foreignKeyName' => 'product_id_fk'
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。YAMLの詳細は[doc
yaml-schema-files :name]の章で読むことができます:

 # schema.yml

Order: # ... relations: Product: local: product\_id foreign: id
foreignKeyName: product\_id\_fk

^^^^^^^^^^^^^^
整合アクション
^^^^^^^^^^^^^^

**CASCADE**

親テーブルから列を削除もしくは更新しコテーブルでマッチするテーブルを自動的に削除もしくは更新します。``ON
DELETE CASCADE``と``ON UPDATE
CASCADE``の両方がサポートされます。2つのテーブルの間では、親テーブルもしくは子テーブルの同じカラムで振る舞う``ON
UPDATE CASCADE``句を定義すべきではありません。

**SET NULL**

親テーブルから列を削除もしは更新し子テーブルで外部キーカラムを``NULL``に設定します。外部キーカラムが``NOT
NULL``修飾子が指定されない場合のみこれは有効です。``ON DELETE SET
NULL``と``ON UPDATE SET NULL``句の両方がサポートされます。

**NO ACTION**

標準のSQLにおいて、``NO
ACTION``はアクションが行われないことを意味し、具体的には参照されるテーブルで関連する外部キーの値が存在する場合、主キーの値を削除するもしくは更新する処理が許可されません。

**RESTRICT**

親テーブルに対する削除もしくは更新オペレーションを拒否します。``NO
ACTION``と``RESTRICT``は``ON DELETE``もしくは``ON
UPDATE``句を省略するのと同じです。

**SET DEFAULT**

次の例において``User``と``Phonenumber``の2つのクラスのリレーションを一対多に定義します。``onDelete``カスケードアクションで外部キーの制約も追加します。このことは``user``が削除されるたびに関連する``phonenumbers``も削除されることを意味します。

    **NOTE**
    上記で示されている整合性制約は大文字小文字を区別しスキーマで定義するときは大文字でなければなりません。下記のコードは削除カスケードが使用されるデータベース削除の例です。

 class Phonenumber extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        parent::setUp();

        // ...

        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE'
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Phonenumber: # ... relations: # ... User: local: user\_id foreign: id
onDelete: CASCADE

    **NOTE**
    外部キーがあるところで整合性制約がおかれていることに注目してください。整合性制約がデータベースのプロパティにエクスポートされるためにこれは必須です。

============
インデックス
============

--------
はじめに
--------

インデックスは特定のカラムの値を持つ列を素早く見つけるために使われます。インデックスなしでは、データベースは最初の列から始め関連する列をすべて見つけるためにテーブル全体を読み込まなければなりません。

テーブルが大きくなるほど、時間がかかります。テーブルが問題のカラム用のインデックスを持つ場合、データベースはデータをすべて見ることなくデータの中ほどで位置を素早く決定できます。テーブルが1000の列を持つ場合、これは列を1つづつ読み込むよりも少なくとも100倍以上速いです。

インデックスはinsertとupdateを遅くなるコストがついてきます。しかしながら、一般的に
SQLのwhere条件で使われるフィールドに対して**常に**インデックスを使うべきです。

----------------------
インデックスを追加する
----------------------

``Doctrine_Record::index``を使用してインデックスを追加できます。インデックスをnameという名前のフィールドに追加するシンプルな例です:

    **NOTE**
    次のインデックスの例はDoctrineの環境に実際に追加することは想定されていません。これらはインデックス追加用のAPIを示すためだけを意図しています。

 class IndexTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');

::

        $this->index('myindex', array(
                'fields' => array('name')
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 IndexTest: columns: name: string indexes: myindex: fields: [name]

``name``という名前のフィールドにマルチカラムインデックスを追加する例です:

 class MultiColumnIndexTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');
$this->hasColumn('code', 'string');

::

        $this->index('myindex', array(
                'fields' => array('name', 'code')
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を見ることができます:

 MultiColumnIndexTest: columns: name: string code: string indexes:
myindex: fields: [name, code]

同じテーブルで複数のインデックスを追加する例です:

 class MultipleIndexTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');
$this->hasColumn('code', 'string'); $this->hasColumn('age', 'integer');

::

        $this->index('myindex', array(
                'fields' => array('name', 'code')
            )
        );

        $this->index('ageindex', array(
                'fields' => array('age')
            )
        );
    }

}

YAMLフォーマットでの同じ例です。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 MultipleIndexTest: columns: name: string code: string age: integer
indexes: myindex: fields: [name, code] ageindex: fields: [age]

----------------------
インデックスオプション
----------------------

Doctrineは多くのインデックスオプションを提供します。これらの一部はデータベース固有のものです。利用可能なオプションの全リストは次の通りです:

\|\|~ 名前 \|\|~ 説明 \|\| \|\| ``sorting`` \|\|
文字列の値が'ASC'もしくは'DESC'の値を取れるか \|\| \|\| ``length`` \|\|
インデックスの長さ(一部のドライバのみサポート)。 \|\| \|\| ``primary``
\|\| インデックスがプライマリインデックスであるか。 \|\| \|\| ``type``
\|\|
文字列の値で'unique'、'fulltext'、'gist'もしくは'gin'が許可されるか\|\|

nameカラムでユニークインデックスを作る方法の例は次の通りです。

 class MultipleIndexTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');
$this->hasColumn('code', 'string'); $this->hasColumn('age', 'integer');

::

        $this->index('myindex', array(
                'fields' => array(
                    'name' => array(
                        'sorting' => 'ASC',
                        'length'  => 10),
                        'code'
                    ),
                'type' => 'unique',
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。YAMLの詳細は[doc
yaml-schema-files :name]の章で読むことができます:

 MultipleIndexTest: columns: name: string code: string age: integer
indexes: myindex: fields: name: sorting: ASC length: 10 code: - type:
unique

------------------
特別なインデックス
------------------

Doctrineは多くの特別なインデックスをサポートします。これらにはMysqlのFULLTEXTとPgsqlのGiSTインデックスが含まれます。次の例では'content'フィールドに対してMysqlのFULLTEXTインデックスを定義します。

 // models/Article.php

class Article extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255);
$this->hasColumn('content', 'string');

::

        $this->option('type', 'MyISAM');

        $this->index('content', array(
                'fields' => array('content'),
                'type'   => 'fulltext'
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Article: options: type: MyISAM columns: name: string(255) content:
string indexes: content: fields: [content] type: fulltext

    **NOTE**
    テーブルの型を``MyISAM``に設定していることに注目してください。これは``fulltext``インデックス型は``MyISAM``でのみサポートされるため``InnoDB``などを使う場合はエラーを受け取るからです。

========
チェック
========

``Doctrine_Record``の``check()``メソッドを使用することで任意の``CHECK``制約を作成できます。最後の例では価格がディスカウント価格よりも常に高いことを保証するために制約を追加します。

 // models/Product.php

class Product extends Doctrine\_Record { public function
setTableDefinition() { // ...

::

        $this->check('price > discounted_price');
    }

    // ...

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Product: # ... checks: price\_check: price > discounted\_price

生成されるSQL(pgsql):

 CREATE TABLE product ( id INTEGER, price NUMERIC, discounted\_price
NUMERIC, PRIMARY KEY(id), CHECK (price >= 0), CHECK (price <= 1000000),
CHECK (price > discounted\_price))

    **NOTE**
    データベースの中には``CHECK``制約をサポートしないものがあります。この場合Doctrineはチェック制約の作成をスキップします。

Doctrineバリデータが定義で有効な場合はレコードが保存されるとき価格が常にゼロ以上であることも保証されます。

トランザクションの範囲で保存される価格の中にゼロよりも小さいものがある場合、Doctrineは``Doctrine\_Validator_Exception``を投げトランザクションを自動的にロールバックします。

==================
テーブルオプション
==================

Doctrineはさまざまなテーブルオプションを提供します。すべてのテーブルオプションは``Doctrine_Record::option``関数を通して設定できます。

例えばMySQLを使用しINNODBテーブルを利用したい場合は次のようにできます:

 class MyInnoDbRecord extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');

::

        $this->option('type', 'INNODB');
    }

}

YAMLフォーマットの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を見ることができます:

 MyInnoDbRecord: columns: name: string options: type: INNODB

次の例では照合順序と文字集合のオプションを設定します:

 class MyCustomOptionRecord extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string');

::

        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

}

YAMLフォーマットでの同じ例です。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 MyCustomOptionRecord: columns: name: string options: collate:
utf8\_unicode\_ci charset: utf8

特定のデータベース(Firebird、MySqlとPostgreSQL)でcharsetオプションを設定しても無意味でDoctrineがデータを適切に返すのには不十分であることがあります。これらのデータベースに対して、データベース接続の``setCharset``関数を使うこともお勧めします:

 $conn = Doctrine\_Manager::connection(); $conn->setCharset('utf8');

==================
レコードフィルター
==================

Doctrineはモデルを定義するときにレコードフィルターを添付する機能を持ちます。レコードフィルターは無効なモデルのプロパティにアクセスするときに起動されます。ですのでこれらのフィルターの1つを使うことを通してプロパティをモデルに追加することが本質的に可能になります。

フィルターを添付するにはこれをモデル定義の``setUp()``メソッドに追加することだけが必要です:

 class User extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255);
$this->hasColumn('password', 'string', 255); }

::

    public function setUp()
    {
        $this->hasOne('Profile', array(
            'local' => 'id',
            'foreign' => 'user_id'
        ));
        $this->unshiftFilter(new Doctrine_Record_Filter_Compound(array('Profile')));
    }

}

class Profile extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer');
$this->hasColumn('first\_name', 'string', 255);
$this->hasColumn('last\_name', 'string', 255); }

::

    public function setUp()
    {
        $this->hasOne('Profile', array(
            'local' => 'user_id',
            'foreign' => 'id'
        ));
    }

}

上記の例のコードによって``User``のインスタンスを使うとき``Profile``リレーションのプロパティに簡単にアクセスできます。次のコードは例です:

 $user = Doctrine\_Core::getTable('User') ->createQuery('u')
->innerJoin('u.Profile p') ->where('p.username = ?', 'jwage')
->fetchOne();

echo $user->first\_name . ' ' . $user->last\_name;

``first\_name``と``last_name``プロパティに問い合わせるときこれらは``$user``インスタンスに存在しないのでこれらは``Profile``リレーションにフォワードされます。これは次の内容を行ったこととまったく同じです:

 echo $user->Profile->first\_name . ' ' . $user->Profile->last\_name;

独自のレコードフィルターをとても簡単に書くこともできます。必要なことは``Doctrine\_Record_Filter``を継承し``filterSet()``と``filterGet()``メソッドを実装するクラスを作ることです。例は次の通りです:

 class MyRecordFilter extends Doctrine\_Record\_Filter { public function
filterSet(Doctrine\_Record $record, $name, $value) { //
プロパティをトライしてセットする

::

        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }

    public function filterGet(Doctrine_Record, $name)
    {
        // プロパティをトライしてゲットする

        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
    }

}

これでフィルターをモデルに追加できます:

 class MyModel extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        // ...

        $this->unshiftFilter(new MyRecordFilter());
    }

}

    **NOTE**
    ``filterSet()``もしくは``filterGet()``がプロパティを見つけられない場合、例外クラスの``Doctrine_Record_UnknownPropertyException``のインスタンスが投げられていることをかならず確認してください。

==============
遷移的な永続化
==============

Doctrineはデータベースとアプリケーションレベルでカスケーディングオペレーションを提供します。このセクションではアプリケーションとデータベースレベルの両方でセットアップする詳細な方法を説明します。

----------------------------------
アプリケーションレベルのカスケード
----------------------------------

とりわけオブジェクトグラフを扱うとき、個別のオブジェクトの保存と削除はとても退屈です。Doctrineはアプリケーションレベルでオペレーションのカスケード機能を提供します。

^^^^^^^^^^^^^^
保存カスケード
^^^^^^^^^^^^^^

デフォルトでは``save()``オペレーションは関連オブジェクトに既にカスケードされていることにお気づきかもしれません。

^^^^^^^^^^^^^^
削除カスケード
^^^^^^^^^^^^^^

Doctrineは2番目のカスケードスタイル:
deleteを提供します。``save()``カスケードとは異なり、``delete``カスケードは次のコードスニペットのように明示的に有効にする必要があります:

 // models/User.php

class User extends BaseUser { // ...

::

    public function setUp()
    {
        parent::setup();

        // ...

        $this->hasMany('Address as Addresses', array(
                'local' => 'id',
                'foreign' => 'user_id',
                'cascade' => array('delete')
            )
        );
    }

}

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: # ... relations: # ... Addresses: class: Address local: id
foreign: user\_id cascade: [delete]

アプリケーションレベルで関連オブジェクトにカスケードされるオペレーションを指定するために``cascade``オプションが使われます。

    **NOTE**
    現在サポートされる値は``delete``のみであることにご注意ください。より多くのオプションは将来のDoctrineのリリースで追加されます。

上記の例において、Doctrineは関連する``Address``に``User``の削除をカスケードします。次の説明は``$record->delete()``を通してレコードを削除する際の一般的な手続きです:

**1.**
Doctrineは適用する必要のある削除カスケードが存在するかリレーションを探します。削除カスケードが存在しない場合、3に移動します)。

**2.**
指定された削除カスケードを持つそれぞれのリレーションに対して、Doctrineはカスケードのターゲットであるオブジェクトがロードされることを確認します。このことはDoctrineは関連オブジェクトがまだロードされていない場合データベースから関連オブジェクトが取得することを意味します。(例外:
すべてのオブジェクトがロードされていることを確認するために多くの値を持つアソシエーションはデータベースから再取得されます)。それぞれの関連オブジェクトに対して、ステップ1に進みます)。

**3.**
Doctrineは参照照合性を維持しながらすべての削除を並べ替え最も効果的な方法で実行します。

この説明から1つのことがすぐに明らかになります:
アプリケーションレベルのカスケードはオブジェクトレベルで行われ、参加しているオブジェクトが利用可能にすることを行うために1つのオブジェクトから別にオブジェクトにオペレーションがカスケードされることを意味します。

このことは重要な意味を示します:

-  関連の照合順序でたくさんのオブジェクトがあるとき多くの値を持つアソシエーションではアプリケーションレベルの削除カスケードはうまく実行されませんこれらがデータベースから取得される必要があるためで、実際の削除はとても効率的です)。
-  アプリケーションレベルの削除カスケードはデータベースレベルのカスケードが行うようにオブジェクトのライフサイクルをスキップしません(次の章を参照)。それゆえ登録されたすべてのイベントリスナーと他のコールバックメソッドはアプリケーションレベルのカスケードで適切に実行されます。

------------------------------
データベースレベルのカスケード
------------------------------

データベースレベルでカスケードオペレーションはとても効率的にできるものがあります。もっともよい例は削除カスケードです。

次のことを除いて一般的にデータベースレベルの削除カスケードはアプリケーションレベルよりも望ましいです:

-  データベースがデータベースレベルのカスケードをサポートしない(MySqlでMYISAMテーブルを使うとき)。
-  オブジェクトライフサイクルをリスニングするリスナーがありこれらを起動させたい。

データベースレベルの削除カスケードは外部キー制約に適用されます。それゆえこれらは外部キーを所有するリレーション側で指定されます。上記から例を拾うと、データベースレベルのカスケードの定義は次のようになります:

 // models/Address.php

class Address extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer');
$this->hasColumn('address', 'string', 255); $this->hasColumn('country',
'string', 255); $this->hasColumn('city', 'string', 255);
$this->hasColumn('state', 'string', 2); $this->hasColumn('postal\_code',
'string', 25); }

::

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE'
            )
        );
    }

}

YAMLフォーマットの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を詳しく読むことができます:

 # schema.yml

Address: columns: user\_id: integer address: string(255) country:
string(255) city: string(255) state: string(2) postal\_code: string(25)
relations: User: local: user\_id foreign: id onDelete: CASCADE

Doctrineがテーブルを作成するとき``onDelete``オプションは適切なDDL/DMLステートメントに翻訳されます。

    **NOTE** ``'onDelete' =>
    'CASCADE'``がAddressクラスで指定されることに注目してください。Addressは外部キー(``user_id``)を所有するのでデータベースレベルのカスケードは外部キーに適用されます。

現在、2つのデータベースレベルのカスケードスタイルは``onDelete``と``onUpdate``に対してのみです。Doctrineがテーブルを作成するとき両方とも外部キーを所有する側で指定されデータベーススキーマに適用されます。

======
まとめ
======

これでDoctrineのモデルを定義するすべての方法を知りました。アプリケーションで[doc
work-with-models モデルと連携する]方法を学ぶ準備ができています。

これはとても大きなトピックなので、少し休憩を取り、マウンテンデューを飲んで[doc
working-with-models 次の章]にすぐに戻ってください。
