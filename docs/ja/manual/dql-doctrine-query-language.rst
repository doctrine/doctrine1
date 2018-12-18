========
はじめに
========

Doctrine Query Language
(DQL)は複雑なオブジェクト読み取りを手助けするためのObject Query
Languageです。リレーショナルデータを効率的に読み取るときに(例えばユーザーと電話番号を取得するとき)DQL(もしくは生のSQL)を使うことを常に考えるべきです。

この章ではDoctrine Query
Languageの使い方の例をたくさん実演します。これらすべての例では[doc
defining-models
:name]の章で定義したスキーマを使うことを想定します。またテスト用に1つの追加モデルを定義します。

 // models/Account.php

class Account extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255);
$this->hasColumn('amount', 'decimal'); } }

YAMLフォーマットでの同じ例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Account: columns: name: string(255) amount: decimal

生のSQLを使う場合と比較すると、DQLは次の恩恵があります:

-  始めから結果セットの列ではなくレコード(オブジェクト)の読み取りのために設計されている
-  DQLはリレーションを理解するのでSQLのjoinとjoinの条件を手動で入力する必要がない
-  DQLは異なるデータベースでポータブルである
-  DQLはレコード制限などとても複雑なアルゴリズムが組み込まれておりこれは開発者がオブジェクトを効率的に読み取るのを手助けする
-  条件付きの取得で一対多、多対多のリレーショナルデータを扱うときに時間を節約できる機能をサポートする

DQLの力が十分でなければ、オブジェクト投入に対して[doc native-sql RawSql
API]を使うことを考えるべきです。

既に次の構文に慣れている方もいらっしゃるでしょうx:

    **CAUTION** **次のコードは決して使わないでください。**
    これはオブジェクト投入用に多くのSQLクエリを使います。

 // test.php

// ... $users = Doctrine\_Core::getTable('User')->findAll();

foreach($users as $user) { echo $user->username . " has phonenumbers: ";

::

    foreach($user->Phonenumbers as $phonenumber) {
        echo $phonenumber->phonenumber . "\n";
    }

}

.. tip::

   
    上記と同じ内容ですがオブジェクト投入のために1つのSQLクエリのみを使うより効率的な実装です。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumbers p');

echo $q->getSqlQuery();

上記のクエリによって生成されるSQLを見てみましょう:

 SELECT u.id AS u**id, u.is\_active AS u**is\_active, u.is\_super\_admin
AS u**is\_super\_admin, u.first\_name AS u**first\_name, u.last\_name AS
u**last\_name, u.username AS u**username, u.password AS u**password,
u.type AS u**type, u.created\_at AS u**created\_at, u.updated\_at AS
u**updated\_at, p.id AS p**id, p.user\_id AS p**user\_id, p.phonenumber
AS p\_\_phonenumber FROM user u LEFT JOIN phonenumber p ON u.id =
p.user\_id

クエリを実行してデータで遊んでみましょう:

 // test.php

// ... $users = $q->execute();

foreach($users as $user) { echo $user->username . " has phonenumbers: ";

::

    foreach($user->Phonenumbers as $phonenumber) {
        echo $phonenumber->phonenumber . "\n";
    }

}

    **CAUTION**
    DQLの文字列で二重引用符(")を使うのは非推奨です。これはMySQLの標準では使えますがDQLにおいて識別子と混同される可能性があります。代わりに値に対してプリペアードステートメントを使うことが推奨されます。これによって適切にエスケープされます。

==================
SELECTクエリ
==================

``SELECT``文の構文:

 SELECT [ALL \| DISTINCT] , ... [FROM [WHERE ] [GROUP BY [ASC \| DESC],
... ] [HAVING ] [ORDER BY [ASC \| DESC], ...] [LIMIT OFFSET }]

``SELECT``文は1つもしくは複数のコンポーネントからデータを読み取るために使われます。
　
それぞれの``select_expr``は読み取りたいカラムもしくは集約関数の値を示します。
すべての``SELECT``文で少なくとも1つの``select_expr``がなければなりません。

最初にサンプルの``Account``レコードをinsertします:

 // test.php

// ... $account = new Account(); $account->name = 'test 1';
$account->amount = '100.00'; $account->save();

$account = new Account(); $account->name = 'test 2'; $account->amount =
'200.00'; $account->save();

``test.php``を実行します:

 $ php test.php

次のサンプルクエリでデータのselectをテストできます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.name') ->from('Account
a');

echo $q->getSqlQuery();

上記のクエリによって生成されたSQLを見てみましょう:

 SELECT a.id AS a**id, a.name AS a**name FROM account a

 // test.php

// ... $accounts = :code:`q->execute(); print_r(`\ accounts->toArray());

上記の例では次の出力が生み出されます:

 $ php test.php Array ( [0] => Array ( [id] => 1 [name] => test 1
[amount] => )

::

    [1] => Array
        (
            [id] => 2
            [name] => test 2
            [amount] => 
        )

)

アスタリスクは任意のコンポーネントからすべてのカラムをselectするために使われます。アスタリスクを使うときでも実行されるSQLクエリは実際にはそれを使いません(Doctrineはアスタリスクを適切なカラムの名前に変換することで、データベースでのパフォーマンスの向上につながります)。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.\*') ->from('Account
a');

echo $q->getSqlQuery();

最後のクエリの例から生成されたSQLとすぐ前に生成されたクエリで生成されたSQLを比較します:

 SELECT a.id AS a**id, a.name AS a**name, a.amount AS a\_\_amount FROM
account a

    **NOTE**
    アスタリスクは``Account``モデルに存在する実際のすべてのカラム名に置き換えられることに留意してください。

クエリを実行して結果を検査してみましょう:

 // test.php

// ... $accounts = :code:`q->execute(); print_r(`\ accounts->toArray());

上記の例は次の出力を生み出します:

 $ php test.php Array ( [0] => Array ( [id] => 1 [name] => test 1
[amount] => 100.00 )

::

    [1] => Array
        (
            [id] => 2
            [name] => test 2
            [amount] => 200.00
        )

)

``FROM``句はレコードから読み取るコンポーネントを示します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username, p.\*')
->from('User u') ->leftJoin('u.Phonenumbers p')

echo $q->getSqlQuery();

``getSql()``への上記の呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, p.id AS p**id,
p.user\_id AS p**user\_id, p.phonenumber AS p\_\_phonenumber FROM user u
LEFT JOIN phonenumber p ON u.id = p.user\_id

``WHERE``句は、選択されるためにレコードが満たさなければならない条件を示します。``where_condition``は選択されるそれぞれの列に対してtrueに表示する式です。``WHERE``句が存在しない場合ステートメントはすべての列を選択します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.name') ->from('Account
a') ->where('a.amount > 2000');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT a.id AS a**id, a.name AS a**name FROM account a WHERE a.amount >
2000

``WHERE``句において、集約(要約)関数を除いて、DQLがサポートする任意の関数と演算子を使うことができます。``HAVING``句は集約関数で結果を絞るために使うことができます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->leftJoin('u.Phonenumbers p') ->having('COUNT(p.id) >
3');

echo $q->getSqlQuery();

``getSql()``を呼び出すと次のSQLクエリが出力されます:

 SELECT u.id AS u**id, u.username AS u**username FROM user u LEFT JOIN
phonenumber p ON u.id = p.user\_id HAVING COUNT(p.id) > 3

``ORDER BY``句は結果のソートに使われます。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->orderBy('u.username');

echo $q->getSqlQuery();

上記の``getSql()``を呼び出すと次のSQLクエリが出力されます:

 SELECT u.id AS u**id, u.username AS u**username FROM user u ORDER BY
u.username

``LIMIT``と``OFFSET``句はレコードの数を``row_count``に効率的に制限するために使われます。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->limit(20);

echo $q->getSqlQuery();

上記の``getSql()``を呼び出すと次のSQLクエリが出力されます:

 SELECT u.id AS u**id, u.username AS u**username FROM user u LIMIT 20

--------------------------
DISTINCTキーワード
--------------------------

------
集約値
------

集約値用の``SELECT``構文:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, COUNT(t.id) AS
num\_threads') ->from('User u, u.Threads t') ->where('u.id = ?', 1)
->groupBy('u.id');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, COUNT(f.id) AS f**0 FROM user u LEFT JOIN
forum\_\_thread f ON u.id = f.user\_id WHERE u.id = ? GROUP BY u.id

クエリを実行して結果をインスペクトします:

 // test.php

// ... $users = $q->execute();

次のコードで``num_threads``のデータに簡単にアクセスできます:

 // test.php

// ... echo $users->num\_threads . ' threads found';

==================
UPDATEクエリ
==================

``UPDATE``文の構文:

 UPDATE SET = , = WHERE ORDER BY LIMIT

-  ``UPDATE``文は``component_name``の既存のレコードのカラムを新しい値で更新し影響を受けたレコードの数を返します。
-  ``SET``句は修正するカラムとそれらに渡される値を示します。
-  オプションの``WHERE``句は更新するレコードを特定する条件を指定します。``WHERE``句がなければ、すべてのレコードが更新されます。
-  オプションの``ORDER BY``句はレコードが更新される順序を指定します。
-  ``LIMIT``句は更新できるレコードの数に制限をおきます。``UPDATE``の範囲を制限するために``LIMIT
   row\_count``を使うことができます。``LIMIT``句は列を変更する制限ではなく**列にマッチする制限**です。実際に変更されたのかに関わらず``WHERE``句を満たす``record_count``の列が見つかるとステートメントはすぐに停止します。

 // test.php

// ... $q = Doctrine\_Query::create() ->update('Account')
->set('amount', 'amount + 200') ->where('id > 200');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 UPDATE account SET amount = amount + 200 WHERE id > 200

更新の実行はシンプルです。次のクエリを実行するだけです:

 // test.php

// ... $rows = $q->execute();

echo $rows;

==================
DELETEクエリ
==================

 DELETE FROM WHERE ORDER BY LIMIT

-  ``DELETE``文は``component_name``からレコードを削除し削除されるレコードの数を返します。
-  オプションの``WHERE``句は削除するレコードを特定する条件を指定します。``WHERE``句なしでは、すべてのレコードが削除されます。
-  ``ORDER
   BY``句が指定されると、指定された順序でレコードが削除されます。
-  ``LIMIT``句は削除される列の数に制限を置きます。``record_count``の数のレコードが削除されると同時にステートメントは停止します。

 // test.php

// ... $q = Doctrine\_Query::create() ->delete('Account a')
->where('a.id > 3');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 DELETE FROM account WHERE id > 3

``DELETE``クエリの実行は次の通りです:

 // test.php

// ... $rows = $q->execute();

echo $rows;

    **NOTE**
    DQLのUPDATEとDELETEクエリを実行すると影響を受けた列の数が返されます。

==========
FROM句
==========

構文:

 FROM [[LEFT \| INNER] JOIN ] ...

``FROM``句はレコードを読み取るコンポーネントを示します。複数のコンポーネントを名付けると、joinを実行することになります。指定されたそれぞれのテーブルに対して、オプションとしてエイリアスを指定できます。

次のDQLクエリを考えます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u

``User``はクラス(コンポーネント)の名前で``u``はエイリアスです。常に短いエイリアスを使うべきです。大抵の場合これらによってクエリははるかに短くなるのと例えばキャッシュを利用するときに短いエイリアスが使われていればクエリのキャッシュされたフォームの取るスペースが少なくなるからです。

==============
JOINの構文
==============

DQL JOINの構文:

 [[LEFT \| INNER] JOIN ] [ON \| WITH] [INDEXBY] , [[LEFT \| INNER] JOIN
] [ON \| WITH] [INDEXBY] , ... [[LEFT \| INNER] JOIN ] [ON \| WITH]
[INDEXBY]

DQLはINNER JOINとLEFT
JOINをサポートします。それぞれのjoinされたコンポーネントに対して、オプションとしてエイリアスを指定できます。

デフォルトのjoinの形式は``LEFT JOIN``です。このjoinは``LEFT
JOIN``句もしくはシンプルな'``,``'のどちらかを使うことで示せます。なので次のクエリは等しいです:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, p.id')
->from('User u') ->leftJoin('u.Phonenumbers p');

$q = Doctrine\_Query::create() ->select('u.id, p.id') ->from('User u,
u.Phonenumbers p');

echo $q->getSqlQuery();

.. tip::

    推奨される形式は前者です。より冗長で読みやすく何が行われているのか理解しやすいからです。

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, p.id AS p**id FROM user u LEFT JOIN phonenumber p
ON u.id = p.user\_id

    **NOTE**
    JOINの条件が自動的に追加されることに注意してください。Doctrineは``User``と``Phonenumber``は関連していることを知っているのであなたに代わって追加できるからです。

``INNER
JOIN``は共通集合を生み出します(すなわち、最初のコンポーネントのありとあらゆるレコードが2番目のコンポーネントのありとあらゆるレコードにjoinされます)。ですので例えば電話番号を1つかそれ以上持つすべてのユーザーを効率的に取得したい場合、基本的に``INNER
JOIN``が使われます。

デフォルトではDQLは主キーのjoin条件を自動追加します:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, p.id')
->from('User u') ->leftJoin('u.Phonenumbers p');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, p.id AS p**id FROM User u LEFT JOIN Phonenumbers
p ON u.id = p.user\_id

--------------
ONキーワード
--------------

このビヘイビアをオーバーライドして独自のカスタムjoin条件を追加したい場合``ON``キーワードで実現できます。次のDQLクエリを考えてみましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, p.id')
->from('User u') ->leftJoin('u.Phonenumbers p ON u.id = 2');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, p.id AS p**id FROM User u LEFT JOIN Phonenumbers
p ON u.id = 2

    **NOTE**
    通常追加される``ON``条件が現れず代わりにユーザーが指定した条件が使われていることに注目してください。

------------------
WITHキーワード
------------------

大体の場合最初のjoin条件をオーバーライドする必要はありません。むしろカスタム条件を追加したいことがあります。これは``WITH``キーワードで実現できます。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, p.id')
->from('User u') ->leftJoin('u.Phonenumbers p WITH u.id = 2');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, p.id AS p**id FROM User u LEFT JOIN Phonenumbers
p ON u.id = p.user\_id AND u.id = 2

    **NOTE**
    ``ON``条件が完全には置き換えられていないことに注意してください。代わりに指定する条件が自動条件に追加されます。

Doctrine\_Query
APIはJOINを追加するための2つのコンビニエンスメソッドを提供します。これらは``innerJoin()``と``leftJoin()``と呼ばれ、これらの使い方は次のようにとても直感的です:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->leftJoin('u.Groups g') ->innerJoin('u.Phonenumbers p WITH u.id > 3')
->leftJoin('u.Email e');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u LEFT JOIN user\_group u2 ON u.id =
u2.user\_id LEFT JOIN groups g ON g.id = u2.group\_id INNER JOIN
phonenumber p ON u.id = p.user\_id AND u.id > 3 LEFT JOIN email e ON
u.id = e.user\_id

========================
INDEXBYキーワード
========================

``INDEXBY``キーワードはコレクション/配列のキーなどの特定のカラムをマッピングする方法を提供します。デフォルトではDoctrineは数値のインデックス付きの配列/コレクションに複数の要素のインデックスを作成します。マッピングはゼロから始まります。このビヘイビアをオーバーライドするには下記で示されるように``INDEXBY``キーワードを使う必要があります:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u INDEXBY
u.username');

$users = $q->execute();

    **NOTE**
    ``INDEXBY``キーワードは生成されるSQLを変えません。コレクションのそれぞれのレコードのキーとして指定されたカラムでデータをハイドレイトするために``Doctrine_Query``によって内部で使われます。

これで``$users``コレクションのユーザーは自身の名前を通してアクセスできます:

 // test.php

// ... echo $user['jack daniels']->id;

``INDEXBY``キーワードは任意のJOINに適用できます。これは任意のコンポーネントがそれぞれ独自のインデックス作成のビヘイビアを持つことができることを意味します。次のコードにおいて``Users``と``Groups``の両方に対して異なるインデックス作成機能を使用しています。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u INDEXBY
u.username') ->innerJoin('u.Groups g INDEXBY g.name');

$users = $q->execute();

drinkers clubの作成日を出力してみましょう。

 // test.php

// ... echo $users['jack daniels']->Groups['drinkers club']->createdAt;

============
WHERE句
============

構文:

 WHERE

-  ``WHERE``句は、与えられた場合、選択されるためにレコードが満たさなければならない条件を示します。
-  ``where_condition``は選択されるそれぞれの列に対してtrueに評価される式です。
-  ``WHERE``句が存在しない場合ステートメントはすべての列を選択します。
-  集約関数の値で結果を絞るとき``WHERE``句の代わりに``HAVING``句が使われます。

``Doctrine_Query``オブジェクトを使用する複雑なwhere条件を構築するために``addWhere()``、``andWhere()``、``orWhere()``、``whereIn()``、``andWhereIn()``、``orWhereIn()``、``whereNotIn()``,
``andWhereNotIn()``、``orWhereNotIn()``メソッドを使うことができます。

すべてのアクティブな登録ユーザーもしくは管理者を読み取る例は次の通りです:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.type = ?', 'registered') ->andWhere('u.is\_active = ?', 1)
->orWhere('u.is\_super\_admin = ?', 1);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.type = ? AND u.is\_active =
? OR u.is\_super\_admin = ?

======
条件式
======

--------
リテラル
--------

**文字列**

文字列リテラルはシングルクォートで囲まれます; 例:
'literal'。シングルクォートを含む文字列リテラルは2つのシングルクォートで表現されます;
例: 'literal''s'。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, u.username')
->from('User u') ->where('u.username = ?', 'Vincent');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u WHERE
u.username = ?

    **NOTE**
    ``where()``メソッドに``username``の値をパラメータとして渡したので生成SQLに含まれません。クエリを実行するときにPDOが置き換え処理をします。``Doctrine_Query``インスタンス上でパラメータをチェックするには``getParams()``メソッドを使うことができます。

**整数**

整数リテラルはPHPの整数リテラル構文の使用をサポートします。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('User u')
->where('u.id = 4');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.id = 4

**浮動小数**

浮動小数はPHPの浮動小数リテラルの構文の使用をサポートします。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('Account
a') ->where('a.amount = 432.123');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT a.id AS a\_\_id FROM account a WHERE a.amount = 432.123

**論理値**

論理値リテラルはtrueとfalseです。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('User u')
->where('u.is\_super\_admin = true');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.is\_super\_admin = 1

**列挙値**

列挙値は文字リテラルと同じ方法で動作します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('User u')
->where("u.type = 'admin'");

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.type = 'admin'

予め定義され予約済みのリテラルは大文字と小文字を区別しますが、これらを大文字で書くのが良い標準です。

--------------
入力パラメータ
--------------

位置パラメータの使用の例は次の通りです:

**単独の位置パラメータ:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.username = ?', array('Arnold'));

echo $q->getSqlQuery();

    **NOTE**
    位置パラメータ用に渡されたパラメータが1つの値しか格納しないとき1つの値を含む配列の代わりに単独のスカラー値を渡すことができます。

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.username = ?

**複数の位置パラメータ:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u') ->where('u.id > ?
AND u.username LIKE ?', array(50, 'A%'));

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE (u.id > ? AND u.username LIKE
?)

名前付きパラメータの使い方の例は次の通りです:

**単独の名前付きパラメータ:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.username = :name', array(':name' => 'Arnold'));

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.username = :name

**LIKEステートメントを伴う名前付きパラメータ:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.id > :id', array(':id' => 50)) ->andWhere('u.username LIKE
:name', array(':name' => 'A%'));

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.id > :id AND u.username LIKE
:name

----------------
演算子と優先順位
----------------

演算子の一覧は優先順位が低い順です。

\|\|~ 演算子 \|\|~ 説明 \|\| \|\| . \|\| ナビゲーション演算子\|\| \|\|
\|\| //算術演算子: // \|\| \|\| +, - \|\| 単項式 \|\| \|\| \*, / \|\|
乗法と除法 \|\| \|\| +, - \|\| 加法と減法 \|\| \|\| =, >, >=, <, <=, <>
(not equal), \|\| 比較演算子 \|\| \|\| [NOT] LIKE, [NOT] IN, IS [NOT]
NULL, IS [NOT] EMPTY \|\| \|\| \|\| \|\| //論理演算子: // \|\| \|\| NOT
\|\| \|\| \|\| AND \|\| \|\| \|\| OR \|\| \|\|

------
IN式
------

構文:

 IN (\|)

//サブクエリ//の結果から//オペランド//が見つかるもしくは指定しされたカンマで区切られた//値リスト//にある場合``IN``の条件式はtrueを返します。サブクエリの結果が空の場合``IN``の式は常にfalseです。

//値リスト//が使われているときそのリストには少なくとも1つの要素がなければなりません。

``IN``に対してサブクエリを使う例は次の通りです:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u') ->where('u.id IN
(SELECT u.id FROM User u INNER JOIN u.Groups g WHERE g.id = ?)', 1);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id FROM user u WHERE u.id IN (SELECT u2.id AS u2**id
FROM user u2 INNER JOIN user\_group u3 ON u2.id = u3.user\_id INNER JOIN
groups g ON g.id = u3.group\_id WHERE g.id = ?)

整数のリストを使うだけの例は次の通りです:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->whereIn('u.id', array(1, 3, 4, 5));

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.id IN (?, ?, ?, ?)

----------
LIKE式
----------

構文:

 string\_expression [NOT] LIKE pattern\_value [ESCAPE escape\_character]

string\_expressionは文字列の値でなければなりません。pattern\_valueは文字列リテラルもしくは文字列の値を持つ入力パラメータです。アンダースコア(``\_``)は任意の単独の文字を表し、パーセント(``%``)の文字は文字のシーケンス(空のシーケンスを含む)を表し、そして他のすべての文字はそれら自身を表します。オプションのescape\_characterは単独文字の文字列リテラルもしくは文字の値を持つ入力パラメータ(すなわちcharもしくはCharacter)で``pattern_value``で特別な意味を持つアンダースコアとパーセントの文字をエスケープします。

例:

-  address.phone LIKE
   '12%3'は'123'、'12993'に対してtrueで'1234'に対してfalseです。
-  asentence.word LIKE
   'l\_se'は'lose'に対してtrueで'loose'に対してfalseです。
-  aword.underscored LIKE '\_%' ESCAPE
   ''は'\_foo'に対してtrueで'bar'に対してfalseです。
-  address.phone NOT LIKE
   '12%3'は'123'と'12993'に対してfalseで'1234'に対してtrueです。

string\_expressionもしくはpattern\_valueの値はNULLもしくはunknownで、LIKE式の値はunknownです。escape\_characterが指定されNULLである場合、LIKE式の値はunknownです。

'@gmail.com'で終わるEメールを持つユーザーを見つける:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->leftJoin('u.Email e') ->where('e.address LIKE ?', '%@gmail.com');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u LEFT JOIN email e ON u.id =
e.user\_id WHERE e.address LIKE ?

'A'で始まる名前を持つすべてのユーザーを見つける:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.username LIKE ?', 'A%');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u WHERE u.username LIKE ?

--------------
EXISTS式
--------------

構文:

 [NOT ]EXISTS ()

``EXISTS``演算子はサブクエリが1つもしくは複数の列を返す場合は``TRUE``を返しそうでなければ``FALSE``を返します。

``NOT
EXISTS``演算子はサブクエリが0を返す場合``TRUE``を返しそうでなければ``FALSE``を返します。

    **NOTE** 次の例では``ReaderLog``モデルを追加する必要があります。

 // models/ReaderLog.php

class ReaderLog extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('article\_id', 'integer', null,
array( 'primary' => true ) );

::

        $this->hasColumn('user_id', 'integer', null, array(
                'primary' => true
            )
        );
    }

} YAMLフォーマットでの同じは次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

ReaderLog: columns: article\_id: type: integer primary: true user\_id:
type: integer primary: true

    **NOTE**
    ``ReaderLog``モデルを追加した後で``generate.php``スクリプトを実行することをお忘れなく！

 $ php generate.php

これでテストを実行できます！最初に、読者を持つすべての記事を見つけます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('Article
a') ->where('EXISTS (SELECT r.id FROM ReaderLog r WHERE r.article\_id =
a.id)');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT a.id AS a**id FROM article a WHERE EXISTS (SELECT r.id AS r**id
FROM reader\_log r WHERE r.article\_id = a.id)

読者を持たないすべての記事を見つけます:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('a.id') ->from('Article
a') ->where('NOT EXISTS (SELECT r.id FROM ReaderLog r WHERE
r.article\_id = a.id));

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT a.id AS a**id FROM article a WHERE NOT EXISTS (SELECT r.id AS
r**id FROM reader\_log r WHERE r.article\_id = a.id)

------------------
AllとAnyの式
------------------

構文:

 operand comparison\_operator ANY (subquery) operand
comparison\_operator SOME (subquery) operand comparison\_operator ALL
(subquery)

サブクエリの結果のすべての値に対して比較演算子がtrueである場合もしくはサブクエリの結果が空の場合、ALLの条件式はtrueを返します。すべての条件式のALLは少なくとも1つの列に対して比較の結果がfalseである場合はfalseで、trueもしくはfalseのどちらでもない場合はunknownです。

 $q = Doctrine\_Query::create() ->from('C') ->where('C.col1 < ALL (FROM
C2(col1))');

サブクエリの結果の値に対して比較演算子がtrueの場合条件式のANYはtrueを返します。サブクエリの結果が空の場合もしくはサブクエリの結果のすべての値に対して比較式がfalseの場合、ANY条件式はfalseで、trueでもfalseでもなければunknownです。

 $q = Doctrine\_Query::create() ->from('C') ->where('C.col1 > ANY (FROM
C2(col1))');

SOMEキーワードはANY用のエイリアスです。

 $q = Doctrine\_Query::create() ->from('C') ->where('C.col1 > SOME (FROM
C2(col1))');

ALLもしくはANY条件式で使うことができる比較演算子は=、<、<=、>、>=、<>です。サブクエリの結果は条件式で同じ型を持たなければなりません。

NOT INは<> ALL用のエイリアスです。これら2つのステートメントは等しいです:

 FROM C WHERE C.col1 <> ALL (FROM C2(col1)); FROM C WHERE C.col1 NOT IN
(FROM C2(col1));

 $q = Doctrine\_Query::create() ->from('C') ->where('C.col1 <> ALL (FROM
C2(col1))');

$q = Doctrine\_Query::create() ->from('C') ->where('C.col1 NOT IN (FROM
C2(col1))');

----------
サブクエリ
----------

サブクエリは通常のSELECTクエリが含むことができる任意のキーワードもしくは句を含むことができます。

サブクエリの利点です:

-  これらはクエリを構造化するのでそれぞれの部分のステートメントを分離することが可能です。
-  これらは複雑なjoinとunionを必要とするオペレーションを実行する代替方法を提供します。
-  多くの人の意見によればこれらは読みやすいです。本当に、人々に初期のSQL
   "Structured Query
   Language."と呼ばれるオリジナルのアイディアを与えたサブクエリのイノベーションでした。

idが1であるグループに所属しないすべてのユーザーを見つける例は次の通りです:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.id NOT IN (SELECT u2.id FROM User u2 INNER JOIN u2.Groups g
WHERE g.id = ?)', 1);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id FROM user u WHERE u.id NOT IN (SELECT u2.id AS
u2**id FROM user u2 INNER JOIN user\_group u3 ON u2.id = u3.user\_id
INNER JOIN groups g ON g.id = u3.group\_id WHERE g.id = ?)

グループに所属していないすべてのユーザーを見つける例は次の通りです。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.id NOT IN (SELECT u2.id FROM User u2 INNER JOIN u2.Groups
g)');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id FROM user u WHERE u.id NOT IN (SELECT u2.id AS
u2**id FROM user u2 INNER JOIN user\_group u3 ON u2.id = u3.user\_id
INNER JOIN groups g ON g.id = u3.group\_id)

======
関数式
======

----------
文字列関数
----------

//CONCAT//関数は引数を連結した文字列を返します。上記の例においてユーザーの``first\_name``と``last_name``を連結して``name``という値にマッピングします。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('CONCAT(u.first\_name,
u.last\_name) AS name') ->from('User u');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, CONCAT(u.first\_name, u.last\_name) AS u**0 FROM
user u

これでクエリを実行してマッピングされた関数値を取得できます:

 $users = $q->execute();

foreach($users as :code:`user) { // 'name'は`\ userのプロパティではなく、
// マッピングされた関数値である echo $user->name; }

//SUBSTRING//関数の2番目と3番目の引数は開始位置と返される部分文字列の長さを表します。これらの引数は整数です。文字列の最初の位置は1によって表されます。//SUBSTRING//関数は文字列を返します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->where("SUBSTRING(u.username, 0, 1) = 'z'");

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u WHERE
SUBSTRING(u.username FROM 0 FOR 1) = 'z'

    **NOTE**
    SQLは使用しているDBMSに対して適切な``SUBSTRING``構文で生成されることに注目してください！

//TRIM//関数は文字列から指定された文字をトリムします。トリムされる文字が指定されていない場合、スペース(もしくは空白)が想定されます。オプションのtrim\_characterは単独文字の文字列リテラルもしくは文字の値を持つ入力パラメータです(すなわちcharもしくはCharacter)[30]。トリムの仕様が提供されていない場合、BOTHが想定されます。//TRIM//関数はトリムされた文字列を返します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->where('TRIM(u.username) = ?', 'Someone');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u WHERE
TRIM(u.username) = ?

//LOWER//と//UPPER//関数はそれぞれ文字列を小文字と大文字に変換します。これらは文字列を返します。

 // test.php

// ... $q = Doctrine\_Query::create(); ->select('u.username')
->from('User u') ->where("LOWER(u.username) = 'jon wage'");

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u WHERE
LOWER(u.username) = 'someone'

//LOCATE//関数は文字列の範囲内で任意の文字列の位置を返します。検索は指定された位置で始められます。文字列が整数として見つかった位置で、これは最初の位置を返します。最初の引数は検索される文字列です;
2番目の引数は検索文字列です;
3番目のオプション引数は検索が始まる文字列の位置を表す整数です(デフォルトでは、検索文字列の始め)。文字列の最初の位置は1によって表現されます。文字列が見つからない場合、0が返されます。

//LENGTH//関数は文字の文字列の長さを整数として返します。

--------
算術関数
--------

利用可能なDQLの算術関数です:

 ABS(simple\_arithmetic\_expression)
SQRT(simple\_arithmetic\_expression) MOD(simple\_arithmetic\_expression,
simple\_arithmetic\_expression)

-  //ABS//関数は与えられた数の絶対値を返します。
-  //SQRT//関数は与えられた数の平方根を返します。
-  //MOD//関数は2番目の引数で最初の引数を割ったときの余りを返します。

==========
サブクエリ
==========

--------
はじめに
--------

DoctrineではFROM、SELECTとWHERE文でDQLのサブクエリを使うことができます。下記のコードではDoctrineが提供する異なる型のすべてのサブクエリの例が見つかります。

------------------------
サブクエリを利用する比較
------------------------

指定されたグループに所属しないすべてのユーザーを見つける。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->where('u.id NOT IN (SELECT u.id FROM User u INNER JOIN u.Groups g
WHERE g.id = ?)', 1);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id FROM user u WHERE u.id NOT IN (SELECT u2.id AS
u2**id FROM user u2 INNER JOIN user\_group u3 ON u2.id = u3.user\_id
INNER JOIN groups g ON g.id = u3.group\_id WHERE g.id = ?)

サブクエリでユーザーの電話番号を読み取りユーザー情報の結果セットに格納します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id')
->addSelect('(SELECT p.phonenumber FROM Phonenumber p WHERE p.user\_id =
u.id LIMIT 1) as phonenumber') ->from('User u');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, (SELECT p.phonenumber AS p**phonenumber FROM
phonenumber p WHERE p.user\_id = u.id LIMIT 1) AS u\_\_0 FROM user u

================================
GROUP BY、HAVING句
================================

DQLのGROUP BY構文:

 GROUP BY groupby\_item {, groupby\_item}\*

DQL HAVINGの構文:

 HAVING conditional\_expression

``GROUP
BY``と``HAVING``句は集約関数を扱うために使われます。次の集約関数がDQLで利用可能です:
``COUNT``、``MAX``、``MIN``、``AVG``、``SUM``

アルファベット順で最初のユーザーを名前で選択する。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('MIN(a.amount)')
->from('Account a');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT MIN(a.amount) AS a\_\_0 FROM account a

すべてのアカウントの合計数を選択する。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('SUM(a.amount)')
->from('Account a');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT SUM(a.amount) AS a\_\_0 FROM account a

GROUP
BY句を含まないステートメントで集約関数を使うと、すべての列でグルーピングすることになります。下記の例ではすべてのユーザーと彼らが持つ電話番号の合計数を取得します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->addSelect('COUNT(p.id) as num\_phonenumbers') ->from('User u')
->leftJoin('u.Phonenumbers p') ->groupBy('u.id');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, COUNT(p.id) AS p\_\_0
FROM user u LEFT JOIN phonenumber p ON u.id = p.user\_id GROUP BY u.id

``HAVING``句は集約値を使用する結果を狭めるために使われます。次の例では少なくとも2つの電話番号を持つすべてのユーザーを取得します。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->addSelect('COUNT(p.id) as num\_phonenumbers') ->from('User u')
->leftJoin('u.Phonenumbers p') ->groupBy('u.id')
->having('num\_phonenumbers >= 2');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, COUNT(p.id) AS p**0
FROM user u LEFT JOIN phonenumber p ON u.id = p.user\_id GROUP BY u.id
HAVING p**0 >= 2

次のコードで電話番号の数にアクセスできます:

 // test.php

// ... $users = $q->execute();

foreach($users as $user) { echo $user->name . ' has ' .
$user->num\_phonenumbers . ' phonenumbers'; }

==================
ORDER BY句
==================

--------
はじめに
--------

レコードのコレクションはORDER
BY句を使用してデータベースレベルで効率的にソートできます。

構文:

 [ORDER BY {ComponentAlias.columnName} [ASC \| DESC], ...]

例:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->leftJoin('u.Phonenumbers p') ->orderBy('u.username,
p.phonenumber');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u LEFT JOIN
phonenumber p ON u.id = p.user\_id ORDER BY u.username, p.phonenumber

逆順でソートするためにソートするORDER
BY句のカラム名にDESC(降順)キーワードを追加できます。デフォルトは昇順です;
これはASCキーワードを使用して明示的に指定できます。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->leftJoin('u.Email e') ->orderBy('e.address DESC, u.id
ASC');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u LEFT JOIN
email e ON u.id = e.user\_id ORDER BY e.address DESC, u.id ASC

------------------
集約値でソートする
------------------

次の例ではすべてのユーザーを取得しユーザーが持つ電話番号の数でユーザーをソートします。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username, COUNT(p.id)
count') ->from('User u') ->innerJoin('u.Phonenumbers p')
->orderby('count');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, COUNT(p.id) AS p**0
FROM user u INNER JOIN phonenumber p ON u.id = p.user\_id ORDER BY p**0

----------------
ランダム順を使う
----------------

次の例ではランダムな投稿を取得するために``ORDER
BY``句でランダム機能を使います。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('t.id, RANDOM() AS rand')
->from('Forum\_Thread t') ->orderby('rand') ->limit(1);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT f.id AS f**id, RAND() AS f**0 FROM forum**thread f ORDER BY f**0
LIMIT 1

==========================
LIMITとOFFSET句
==========================

おそらく最も複雑機能であるDQLパーサーは``LIMIT``句パーサーです。DQL
LIMIT句パーサーは``LIMIT``データベースポータビリティを考慮するだけでなく複雑なクエリ分析とサブクエリを使用することで列の代わりにレコードの数を制限できる機能を持ちます。

最初の20ユーザーと関連する電話番号を読み取ります:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username,
p.phonenumber') ->from('User u') ->leftJoin('u.Phonenumbers p')
->limit(20);

echo $q->getSqlQuery();

.. tip::

   
    ``Doctrine_Query``オブジェクトの``offset()``メソッドは実行SQLクエリで望みどおりの``LIMIT``と``OFFSET``を生み出すために``limit()``メソッドと組み合わせて使うことmできます。

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, p.id AS p**id,
p.phonenumber AS p**phonenumber FROM user u LEFT JOIN phonenumber p ON
u.id = p.user\_id

--------------------------
ドライバーのポータビリティ
--------------------------

DQLの``LIMIT``句はサポートされるすべてのデータベース上でポータブルです。次の事実に対して特別な注意を払う必要があります:

-  Mysql、PgsqlとSqliteのみがLIMIT / OFFSET句をネイティブに実装します。
-  Oracle / Mssql / FirebirdではLIMIT /
   OFFSET句はドライバー専用の方法でシミュレートされる必要があります
-  limit-subquery-algorithmはmysqlで個別にサブクエリを実行する必要があります。まだmysqlがサブクエリでLIMIT句をサポートしていないからです。
-  PgsqlはSELECT句で保存するフィールドごとの順序を必要とします。limit-subquery-algorithmがpgsqlドライバーが使われるときに考慮される必要があるからです。
-  Oracleは< 30個未満のオブジェクト識別子のみを許可します(=
   テーブル/カラム
   名前/エイリアス)、limitサブクエリは可能な限りショートエイリアスを使いメインクエリでエイリアスの衝突を回避しなければなりません。

------------------------------------------------
limit-subquery-algorithm
------------------------------------------------

limit-subquery-algorithmはDQLパーサーが内部で使用するアルゴリズムです。1対多/多対多のリレーショナルデータは同時に取得されているときに内部で使用されます。SQLの結果セットの列の代わりにレコードの数を制限するためにこの種の特別なアルゴリズムはLIMIT句に必要です。

このビヘイビアは設定システムを使用してオーバーライドできます(グローバル、接続もしくはテーブルレベル):

 $table->setAttribute(Doctrine\_Core::ATTR\_QUERY\_LIMIT,
Doctrine\_Core::LIMIT\_ROWS);
$table->setAttribute(Doctrine\_Core::ATTR\_QUERY\_LIMIT,
Doctrine\_Core::LIMIT\_RECORDS); // リバート

次の例ではユーザーと電話番号がありこれらのリレーションは1対多です。最初の20ユーザーを取得しすべての関連する電話番号を取得することを考えてみましょう。

クエリの最後でシンプルなドライバ固有のLIMIT
20を追加すれば正しい結果が返されるとお考えの方がいらっしゃるかもしれません。これは間違っています。1から20までの任意のユーザーを20の電話番号を持つ最初のユーザーとして取得しレコードセットが20の列で構成されることがあるからです。

DQLはサブクエリと複雑だが効率的なサブクエリの解析でこの問題に打ち勝ちます。次の例では最初の20人のユーザーとそのすべての電話番号を効果的な1つのクエリで取得しようとしています。DQLパーサーがサブクエリでもカラム集約継承を使うほど賢くまたエイリアスの衝突を回避するサブクエリのテーブルに異なるテーブルを使うほど賢いことに注目してください。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, u.username, p.\*')
->from('User u') ->leftJoin('u.Phonenumbers p') ->limit(20);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, p.id AS p**id,
p.user\_id AS p**user\_id, p.phonenumber AS p\_\_phonenumber FROM user u
LEFT JOIN phonenumber p ON u.id = p.user\_id

次の例では最初の20人のユーザーとすべての電話番号かつ実際に電話番号を持つユーザーのみを1つの効率的なクエリで取得します。これは``INNER
JOIN``を使います。サブクエリで``INNER
JOIN``を使うほどDQLパーサーが賢いことに注目してください:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, u.username, p.\*')
->from('User u') ->innerJoin('u.Phonenumbers p') ->limit(20);

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username, p.id AS p**id,
p.user\_id AS p**user\_id, p.phonenumber AS p\_\_phonenumber FROM user u
INNER JOIN phonenumber p ON u.id = p.user\_id

==============
名前付きクエリ
==============

変化する可能性があるモデルを扱うが、クエリを簡単に更新できるようにする必要があるとき、クエリを定義する簡単な方法を見つける必要があります。例えば1つのフィールドを変更して何も壊れていないことを確認するためにアプリケーションのすべてのクエリを追跡する必要がある状況を想像してください。

名前付きクエリはこの状況を解決する素晴らしく効率的な方法です。これによって``Doctrine_Queries``を作成しこれらを書き直すこと無く再利用できるようになります。

名前付きクエリのサポートは``Doctrine\_Query\_Registry``のサポートの上で構築されます。``Doctrine\_Query_Registry``はクエリを登録して名前をつけるためのクラスです。これはアプリケーションクエリの編成を手助けしこれに沿ってとても便利な機能を提供します。

レジストリオブジェクトの``add()``メソッドを使用してこのクエリは追加されます。これは2つのパラメータ、クエリの名前と実際のDQLクエリを受け取ります。

 // test.php

// ... $r = Doctrine\_Manager::getInstance()->getQueryRegistry();

$r->add('User/all', 'FROM User u');

$userTable = Doctrine\_Core::getTable('User');

// すべてのユーザーを見つける $users = $userTable->find('all');

このサポートを簡略化するために、``Doctrine\_Table``は``Doctrine\_Query_Registry``へのアクセサをサポートします。

------------------------
名前付きクエリを作成する
------------------------

trueとして定義された``generateTableClasses``オプションでモデルをビルドするとき、それぞれのレコードクラスは``Doctrine_Table``を継承する``\*Table``クラスも生成します。

それから、名前付きクエリを含めるために``construct()``メソッドを実装できます:

 class UserTable extends Doctrine\_Table { public function construct() {
// DQL文字列を使用して定義されたNamed Query
$this->addNamedQuery('get.by.id', 'SELECT u.username FROM User u WHERE
u.id = ?');

::

        // Doctrine_Queryオブジェクトを使用して定義された名前付きのクエリ
        $this->addNamedQuery(
            'get.by.similar.usernames', Doctrine_Query::create()
                ->select('u.id, u.username')
                ->from('User u')
                ->where('LOWER(u.username) LIKE LOWER(?)')
        );
    }

}

----------------------------
名前付きクエリにアクセスする
----------------------------

``Doctrine_Table``のサブクラスである``MyFooTable``クラスにリーチするには、次のようにできます:

 $userTable = Doctrine\_Core::getTable('User');

名前付きクエリにアクセスするには(常に``Doctrine_Query``インスタンスを返す):

 $q = $userTable->createNamedQuery('get.by.id');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u**id, u.username AS u**username FROM user u WHERE u.id
= ?

------------------------
名前付きクエリを実行する
------------------------

名前付きクエリを実行するには2つの方法があります。1つめの方法は通常のインスタンスとして``Doctrine_Query``を読み取り通常通りに実行します:

 // test.php

// ... $users = Doctrine\_Core::getTable('User')
->createNamedQuery('get.by.similar.usernames')
->execute(array('%jon%wage%'));

次のようにも実行を簡略化できます:

 // test.php

// ... $users = Doctrine\_Core::getTable('User')
->find('get.by.similar.usernames', array('%jon%wage%'));

``find()``メソッドはハイドレーションモード用の3番目の引数を受け取ります。

----------------------------------
名前付きクエリにクロスアクセスする
----------------------------------

それで十分でなければ、Doctrineは``Doctrine\_Query_Registry``を利用しオブジェクト間の名前付きクエリにクロスアクセスできるようにする名前空間クエリを使います。``Article``レコードの``\*Table``クラスのインスタンスがあることを想定します。``User``レコードの"get.by.id"
名前付きクエリを呼び出したいとします。名前付きクエリにアクセスするには、次のように行わなければなりません:

 // test.php

// ... $articleTable = Doctrine\_Core::getTable('Article');

$users = $articleTable->find('User/get.by.id', array(1, 2, 3));

======
BNF
======

 QL\_statement ::= select\_statement \| update\_statement \|
delete\_statement select\_statement ::= select\_clause from\_clause
[where\_clause] [groupby\_clause] [having\_clause] [orderby\_clause]
update\_statement ::= update\_clause [where\_clause] delete\_statement
::= delete\_clause [where\_clause] from\_clause ::= FROM
identification\_variable\_declaration {,
{identification\_variable\_declaration \|
collection\_member\_declaration``\*
identification\_variable\_declaration ::= range\_variable\_declaration {
join \| fetch\_join }\* range\_variable\_declaration ::=
abstract\_schema\_name [AS ] identification\_variable join ::=
join\_spec join\_association\_path\_expression [AS ]
identification\_variable fetch\_join ::= join\_specFETCH
join\_association\_path\_expression association\_path\_expression ::=
collection\_valued\_path\_expression \|
single\_valued\_association\_path\_expression join\_spec::= [LEFT [OUTER
] \|INNER ]JOIN join\_association\_path\_expression ::=
join\_collection\_valued\_path\_expression \|
join\_single\_valued\_association\_path\_expression
join\_collection\_valued\_path\_expression::=
identification\_variable.collection\_valued\_association\_field
join\_single\_valued\_association\_path\_expression::=
identification\_variable.single\_valued\_association\_field
collection\_member\_declaration ::= IN (
collection\_valued\_path\_expression) [AS ] identification\_variable
single\_valued\_path\_expression ::= state\_field\_path\_expression \|
single\_valued\_association\_path\_expression
state\_field\_path\_expression ::= {identification\_variable \|
single\_valued\_association\_path\_expression}.state\_field
single\_valued\_association\_path\_expression ::=
identification\_variable.{single\_valued\_association\_field.}\*
single\_valued\_association\_field collection\_valued\_path\_expression
::=
identification\_variable.{single\_valued\_association\_field.}*collection\_valued\_association\_field
state\_field ::= {embedded\_class\_state\_field.}*simple\_state\_field
update\_clause ::=UPDATE abstract\_schema\_name [[AS ]
identification\_variable] SET update\_item {, update\_item}\*
update\_item ::= [identification\_variable.]{state\_field \|
single\_valued\_association\_field} = new\_value new\_value ::=
simple\_arithmetic\_expression \| string\_primary \| datetime\_primary
\|

boolean\_primary \| enum\_primary simple\_entity\_expression \| NULL
delete\_clause ::=DELETE FROM abstract\_schema\_name [[AS ]
identification\_variable] select\_clause ::=SELECT [DISTINCT ]
select\_expression {, select\_expression}\* select\_expression ::=
single\_valued\_path\_expression \| aggregate\_expression \|
identification\_variable \| OBJECT( identification\_variable) \|
constructor\_expression constructor\_expression ::= NEW
constructor\_name( constructor\_item {, constructor\_item}*)
constructor\_item ::= single\_valued\_path\_expression \|
aggregate\_expression aggregate\_expression ::= {AVG \|MAX \|MIN \|SUM
}( [DISTINCT ] state\_field\_path\_expression) \| COUNT ( [DISTINCT ]
identification\_variable \| state\_field\_path\_expression \|
single\_valued\_association\_path\_expression) where\_clause ::=WHERE
conditional\_expression groupby\_clause ::=GROUP BY groupby\_item {,
groupby\_item}* groupby\_item ::= single\_valued\_path\_expression \|
identification\_variable having\_clause ::=HAVING
conditional\_expression orderby\_clause ::=ORDER BY orderby\_item {,
orderby\_item}\* orderby\_item ::= state\_field\_path\_expression [ASC
\|DESC ] subquery ::= simple\_select\_clause subquery\_from\_clause
[where\_clause] [groupby\_clause] [having\_clause]
subquery\_from\_clause ::= FROM
subselect\_identification\_variable\_declaration {,
subselect\_identification\_variable\_declaration}\*
subselect\_identification\_variable\_declaration ::=
identification\_variable\_declaration \| association\_path\_expression
[AS ] identification\_variable \| collection\_member\_declaration
simple\_select\_clause ::=SELECT [DISTINCT ] simple\_select\_expression
simple\_select\_expression::= single\_valued\_path\_expression \|
aggregate\_expression \| identification\_variable
conditional\_expression ::= conditional\_term \|
conditional\_expressionOR conditional\_term conditional\_term ::=
conditional\_factor \| conditional\_termAND conditional\_factor
conditional\_factor ::= [NOT ] conditional\_primary conditional\_primary
::= simple\_cond\_expression \|( conditional\_expression)
simple\_cond\_expression ::= comparison\_expression \|
between\_expression \| like\_expression \| in\_expression \|
null\_comparison\_expression \|
empty\_collection\_comparison\_expression \|

collection\_member\_expression \| exists\_expression between\_expression
::= arithmetic\_expression [NOT ]BETWEEN arithmetic\_expressionAND
arithmetic\_expression \| string\_expression [NOT ]BETWEEN
string\_expressionAND string\_expression \| datetime\_expression [NOT
]BETWEEN datetime\_expressionAND datetime\_expression in\_expression ::=
state\_field\_path\_expression [NOT ]IN ( in\_item {, in\_item}\* \|
subquery) in\_item ::= literal \| input\_parameter like\_expression ::=
string\_expression [NOT ]LIKE pattern\_value [ESCAPE escape\_character]
null\_comparison\_expression ::= {single\_valued\_path\_expression \|
input\_parameter}IS [NOT ] NULL
empty\_collection\_comparison\_expression ::=
collection\_valued\_path\_expressionIS [NOT] EMPTY
collection\_member\_expression ::= entity\_expression [NOT ]MEMBER [OF ]
collection\_valued\_path\_expression exists\_expression::= [NOT ]EXISTS
(subquery) all\_or\_any\_expression ::= {ALL \|ANY \|SOME } (subquery)
comparison\_expression ::= string\_expression comparison\_operator
{string\_expression \| all\_or\_any\_expression} \| boolean\_expression
{= \|<> } {boolean\_expression \| all\_or\_any\_expression} \|
enum\_expression {= \|<> } {enum\_expression \|
all\_or\_any\_expression} \| datetime\_expression comparison\_operator
{datetime\_expression \| all\_or\_any\_expression} \| entity\_expression
{= \|<> } {entity\_expression \| all\_or\_any\_expression} \|
arithmetic\_expression comparison\_operator {arithmetic\_expression \|
all\_or\_any\_expression} comparison\_operator ::== \|> \|>= \|< \|<=
\|<> arithmetic\_expression ::= simple\_arithmetic\_expression \|
(subquery) simple\_arithmetic\_expression ::= arithmetic\_term \|
simple\_arithmetic\_expression {+ \|- } arithmetic\_term
arithmetic\_term ::= arithmetic\_factor \| arithmetic\_term {\* \|/ }
arithmetic\_factor arithmetic\_factor ::= [{+ \|- }] arithmetic\_primary
arithmetic\_primary ::= state\_field\_path\_expression \|
numeric\_literal \| (simple\_arithmetic\_expression) \| input\_parameter
\| functions\_returning\_numerics \| aggregate\_expression
string\_expression ::= string\_primary \| (subquery) string\_primary ::=
state\_field\_path\_expression \| string\_literal \| input\_parameter \|
functions\_returning\_strings \| aggregate\_expression

datetime\_expression ::= datetime\_primary \| (subquery)
datetime\_primary ::= state\_field\_path\_expression \| input\_parameter
\| functions\_returning\_datetime \| aggregate\_expression
boolean\_expression ::= boolean\_primary \| (subquery) boolean\_primary
::= state\_field\_path\_expression \| boolean\_literal \|
input\_parameter \| enum\_expression ::= enum\_primary \| (subquery)
enum\_primary ::= state\_field\_path\_expression \| enum\_literal \|
input\_parameter \| entity\_expression ::=
single\_valued\_association\_path\_expression \|
simple\_entity\_expression simple\_entity\_expression ::=
identification\_variable \| input\_parameter
functions\_returning\_numerics::= LENGTH( string\_primary) \| LOCATE(
string\_primary, string\_primary[, simple\_arithmetic\_expression]) \|
ABS( simple\_arithmetic\_expression) \| SQRT(
simple\_arithmetic\_expression) \| MOD( simple\_arithmetic\_expression,
simple\_arithmetic\_expression) \| SIZE(
collection\_valued\_path\_expression) functions\_returning\_datetime ::=
CURRENT\_DATE \| CURRENT\_TIME \| CURRENT\_TIMESTAMP
functions\_returning\_strings ::= CONCAT( string\_primary,
string\_primary) \| SUBSTRING( string\_primary,
simple\_arithmetic\_expression, simple\_arithmetic\_expression)\| TRIM(
[[trim\_specification] [trim\_character]FROM ] string\_primary) \|
LOWER( string\_primary) \| UPPER( string\_primary) trim\_specification
::=LEADING \| TRAILING \| BOTH

====================
マジックファインダー
====================

Doctrineはモデルに存在する任意のカラムでレコードを見つけることを可能にするDoctrineモデル用のマジックファインダー(magic
finder)を提供します。ユーザーの名前でユーザーを見つけたり、グループの名前でグループを見つけるために役立ちます。通常これは``Doctrine_Query``インスタンスを書き再利用できるようにこれをどこかに保存することが必要です。このようなシンプルな状況にはもはや必要ありません。

ファインダーメソッドの基本パターンは次の通りです:
``findBy%s(:code:`value)``もしくは``findOneBy%s(`\ value)``です。``%s``はカラム名もしくはリレーションのエイリアスです。カラムの名前の場合探す値を提供しなければなりません。リレーションのエイリアスを指定する場合、見つけるリレーションクラスのインスタンスを渡すか、実際の主キーの値を渡すことができます。

最初に扱う``UserTable``インスタンスを読み取りましょう:

 // test.php

// ... $userTable = Doctrine\_Core::getTable('User');

``find()``メソッドを利用して主キーで``User``レコードを簡単に見つけられます:

 // test.php

// ... $user = $userTable->find(1);

ユーザー名で1人のユーザーを見つけたい場合は次のようにマジックファインダーを使うことができます:

 // test.php

// ... $user = $userTable->findOneByUsername('jonwage');

レコード間のリレーションを利用してレコードでユーザーを見つけることができます。``User``は複数の``Phonenumbers``を持つので``findBy\*\*()``メソッドに``User``インスタンスを渡すことでこれらの``Phonenumber``を見つけることができます:

 // test.php

// ... $phonenumberTable = Doctrine::getTable('Phonenumber');

$phonenumbers = :code:`phonenumberTable->findByUser(`\ user);

マジックファインダーはもう少し複雑な検索を可能にします。複数のプロパティによってレコードを検索するためにメソッド名で``And``と``Or``キーワードを使うことができます。

 $user = $userTable->findOneByUsernameAndPassword('jonwage',
md5('changeme'));

条件を混ぜることもできます。

 $users = $userTable->findByIsAdminAndIsModeratorOrIsSuperAdmin(true,
true, true);

    **CAUTION**
    これらは語句限られたマジックメソッドの用例で、つねに手書きのDQLクエリで展開することをおすすめします。これらのメソッドはリレーションシップなしの単独のレコードに素早くアクセスするための手段であり、素早くプロトタイプを書くのにもよいものです。

    **NOTE** 上記のマジックファインダーはPHPの``[http://php.net/\_\_call
    **call()]``のオーバーロード機能を使うことで作成されます。内在する関数は``Doctrine\_Query``オブジェクトがビルドされる``Doctrine_Table::**call()``に転送され、実行されてユーザーに返されます。

====================
クエリをデバッグする
====================

``Doctrine_Query``オブジェクトはクエリの問題をデバッグするための手助けになる機能を少々提供します:

ときに``Doctrine_Query``オブジェクトに対して完全なSQL文字列を見たいことがあります:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id') ->from('User u')
->orderBy('u.username');

echo $q->getSqlQuery();

``getSql()``への呼び出しは次のSQLクエリを出力します:

 SELECT u.id AS u\_\_id FROM user u ORDER BY u.username

    **NOTE**
    上記の``Doctrine\_Query::getSql()``メソッドによって返されるSQLはトークンをパラメータに置き換えません。これはPDOのジョブでクエリを実行するとき置き換えが実行されるPDOにパラメータを渡します。``Doctrine_Query::getParams()``メソッドでパラメータの配列を読み取ることができます。

``Doctrine_Query``インスタンス用のパラメータの配列を取得します:

 // test.php

// ... print\_r($q->getParams());

======
まとめ
======

Doctrine Query
Languageはこれまでのところ最も高度で役に立つDoctrineの機能です。これによってRDBMSのリレーションからとても複雑なデータを簡単にかつ効率的に選択できます！

これでDoctrineの主要なコンポーネントの使い方を見たので[doc
component-overview :name]の章に移りすべてを鳥の目で見ることにします。
