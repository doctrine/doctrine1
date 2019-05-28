.. vim: set ft=rst tw=4 sw=4 et :

====
はじめに
====

.. sidebar::

    [http://www.postgresql.jp/document/pg820doc/html/ddl-constraints.html
    PostgreSQLのドキュメント]より引用: >
    データ型は、テーブルに格納するデータの種類を限定するための方法です。しかし、多くのアプリケーションでは、型が提供する制約では精密さに欠けます。例えば、製品の価格が入る列には、おそらく正数のみを受け入れるようにする必要があります。しかし、正数のみを受け入れるという標準のデータ型はありません。また、他の列や行に関連して列データを制約したい場合もあります。例えば、製品の情報が入っているテーブルでは、1つの製品番号についての行が2行以上あってはなりません。

Doctrineによってカラムとテーブルで*ポータブルな*制約を定義できます。制約によってテーブルのデータを望むままにコントロールできます。ユーザーがカラムの制約に違反するデータを保存しようとすると、エラーが起動します。値がデフォルトの値の定義から来る場合でもこれが適用されます。

アプリケーションレベルのバリデータと同じようにDoctrineの制約はデータベースレベルの制約として振る舞います。このことは二重のセキュリティを意味します:
間違った種類の値とアプリケーションを許容しません。

Doctrineの範囲内で利用可能なバリデーションの全リストは次の通りです:

\|\|~ バリデータ(引数) \|\|~ 制約 \|\|~ 説明 \|\| \|\| ``notnull`` \|\|
``NOT NULL`` \|\| アプリケーションとデータベースの両方のレベルで'not
null'制約を確認する \|\| \|\| ``email`` \|\| \|\|
値が有効なEメールであるかチェックする \|\| \|\| ``notblank`` \|\| ``NOT
NULL`` \|\| not blankであることをチェックする \|\| \|\| ``nospace`` \|\|
\|\| スペースがないことをチェックする \|\| \|\| ``past`` \|\|
``CHECK``制約 \|\| 値が過去の日付がチェックする \|\| \|\| ``future``
\|\| \|\| 値が未来の日付かチェックする \|\| \|\| ``minlength(length)``
\|\| \|\| 値が最小長を満たすかチェックする \|\| \|\| ``country`` \|\|
\|\| 値が有効な国コードであるかチェックする \|\| \|\| ``ip`` \|\| \|\|
値が有効なIP(internet protocol)アドレスかチェックする \|\| \|\|
``htmlcolor`` \|\| \|\| 値が妥当なhtmlの色であるかチェックする \|\| \|\|
``range(min, max)`` \|\| ``CHECK``制約 \|\|
値が引数で指定された範囲に収まるかチェックする \|\| \|\| ``unique`` \|\|
``UNIQUE``制約 \|\| 値がデータベーステーブルでユニークかチェックする
\|\| \|\| ``regexp(expression)``\|\| \|\|
値が正規表現にマッチするかチェックする \|\| \|\| ``creditcard`` \|\|
\|\| 文字列が適正なクレジットカード番号であるかチェックする \|\| \|\|
``digits(int, frac)`` \|\| 精度とスケール \|\|
値が整数の//int//の桁数を持ち分数の//frac//の桁数を持つかチェックする
\|\| \|\| ``date`` \|\| \|\| 値が有効な日付であるかチェックする \|\|
``readonly`` \|\| \|\|
フィールドが修正され読み込み限定のフィールドに強制するようにfalseを返すかチェックする
\|\| \|\| ``unsigned`` \|\| \|\| 整数値が符号無しであるかチェックする
\|\| \|\| ``usstate`` \|\| \|\|
値が有効なUSの州コードであるかチェックする \|\|

下記のコードはバリデータの使い方とカラムでバリデータ用の引数を指定する方法の例です。

``minlength``バリデータを使う例です。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'minlength' => 12
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: type: string(255) minlength: 12 # ...

=
例
=

--------
Not Null
--------

``not-null``制約はカラムがnullの値を想定してはならないことを指定します。``not-null``制約は常にカラムの制約として記述されます。

次の定義はカラム名用に``notnull``制約を使います。このことは指定されたカラムはnullの値を受け取らないことを意味します。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'notnull' => true,
                'primary' => true,
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます。:

 # schema.yml

User: columns: username: type: string(255) notnull: true primary: true #
...

このクラスがデータベースにエクスポートされるとき次のSQL文が実行されます(MySQLにて):

 CREATE TABLE user (username VARCHAR(255) NOT NULL, PRIMARY
KEY(username))

not-null制約はアプリケーションレベルのバリデータとして振る舞います。このことはDoctrineのバリデータが有効な場合、Doctrineは指定されたカラムを保存するときにnullの値が含まれないことを自動的にチェックします。

これらのカラムがnullの値を含む場合``Doctrine\_Validator_Exception``が起動します。

----
Eメール
----

Eメールバリデータは入力された値が本当に有効なEメールアドレスでありアドレスドメイン用のMXレコードがEメールアドレスとして解決することをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('email', 'string', 255, array(
                'email'   => true
            )
        );
    }

}

YAMLフォーマットでの同じサンプルは次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... email: type: string(255) email: true # ...

無効なEメールアドレスを持つユーザーを作成しようとするとバリデートは行われません:

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->email =
'jonwage';

if ( ! $user->isValid()) { echo 'User is invalid!'; }

``jonwage``は有効なEメールアドレスではないので上記のコードは例外を投げます。これをさらに推し進めてEメールアドレスは有効であるがドメイン名が無効な例は次の通りです:

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->email =
'jonwage@somefakedomainiknowdoesntexist.com';

if ( ! $user->isValid()) { echo 'User is invalid!'; }

ドメインの``somefakedomainiknowdoesntexist.com``が存在せずPHPの``[http://www.php.net/checkdnsrr
checkdnsrr()]``関数はfalseを返すので上記のコードはエラーになります。

---------
Not Blank
---------

not blankバリデータはnot
nullバリデートと似ていますが空の文字列もしくは空白文字が含まれる場合はエラーになります。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'notblank'   => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: type: string(255) notblank: true # ...

1つの空白スペースを含むusernameを持つ``User``レコードを保存しようとすると、バリデーションはエラーになります:

 // test.php

// ... $user = new User(); $user->username = ' ';

if ( ! $user->isValid()) { echo 'User is invalid!'; }

--------
No Space
--------

no
spaceバリデータは単純です。値にスペースが含まれないことをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'nospace'   => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: type: string(255) nospace: true # ...

スペースを含む``username``を持つ``User``を保存しようとするとバリデーションが失敗します:

 $user = new User(); $user->username = 'jon wage';

if ( ! $user->isValid()) { echo 'User is invalid!'; }

----
Past
----

pastバリデータは値が過去の有効な日付であるかをチェックします。この例では``birthday``カラムを持つ``User``モデルがあり日付が過去のものであることバリデートします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('birthday', 'timestamp', null, array(
                'past' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... birthday: type: timestamp past: true # ...

過去にはない誕生日を設定しようとするとバリデーションエラーになります。

------
Future
------

futureバリデータはpastバリデータの反対でデータが未来の有効な日付であることをチェックします。この例では``next\_appointment_date``カラムを持つ``User``モデルがあり日付が未来のものであることをバリデートします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('next_appointment_date', 'timestamp', null, array(
                'future' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... next\_appointment\_date: type: timestamp future:
true # ...

予約日が未来のものでなければ、バリデーションエラーになります。

---
最小長
---

最小長は正確な表現ではありません。文字列の長さが最小の長さよりも大きいことをチェックします。この例では``password``カラムを持つ``User``モデルがあり``password``の長さが少なくとも5文字であることを確認します。

 // models/User.php

class User extends BaseUser { public function setTableDefinition() {
parent::setTableDefinition();

::

        // ...

        $this->hasColumn('password', 'timestamp', null, array(
                'minlength' => 5
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... password: type: timestamp minlength: 5 # ...

5文字より短い``password``を持つ``User``を保存しようとすると、バリデーションはエラーになります。

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->password =
'test';

if ( ! $user->isValid()) { echo 'User is invalid because "test" is only
4 characters long!'; }

-------
Country
-------

countryバリデータは値が有効なcountryコードであるかチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('country', 'string', 2, array(
                'country' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... country: type: string(2) country: true # ...

無効な国コードを持つ``User``を保存しようとするとバリデーションがエラーになります。

 // test.php

// ... $user = new User(); $user->username = 'jwage';
$user->country\_code = 'zz';

if ( ! $user->isValid()) { echo 'User is invalid because "zz" is not a
valid country code!'; }

------
IPアドレス
------

IPアドレスバリデータは値が有効なIPアドレスであることをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('ip_address', 'string', 15, array(
                'ip' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... ip\_address: type: string(15) ip: true # ...

無効なIPアドレスを持つ``User``を保存しようとするとバリデーションはエラーになります。

 $user = new User(); $user->username = 'jwage'; $user->ip\_address =
'123.123';

if ( ! $user->isValid()) { echo 'User is invalid because "123.123" is
not a valid ip address }

----------
HTML Color
----------

htmlcolorバリデータは値が有効な16進法のhtmlカラーであることをチェックします。

 // models/User.php

class User extends BaseUser { public function setTableDefinition() {
parent::setTableDefinition();

::

        // ...

        $this->hasColumn('favorite_color', 'string', 7, array(
                'htmlcolor' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... favorite\_color: type: string(7) htmlcolor: true #
...

``favorite_color``カラム用の無効なhtmlカラーの値を持つ``User``を保存しようとするとバリデーションはエラーになります。

 // test.php

// ... $user = new User(); $user->username = 'jwage';
$user->favorite\_color = 'red';

if ( ! $user->isValid()) { echo 'User is invalid because "red" is not a
valid hex color'; }

-----
Range
-----

rangeバリデータは値が与えられた数の範囲にあることをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('age', 'integer', 3, array(
                'range' => array(10, 100)
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... age: type: integer(3) range: [10, 100] # ...

10才未満もしくは100才を越える``User``を保存しようとすると、バリデーションはエラーになります。

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->age = '3';

if ( ! $user->isValid()) { echo 'User is invalid because "3" is less
than the minimum of "10"'; }

範囲配列の``0``もしくは``1``キーのどちらかを省略することで最大と最小の値をバリデートするために``range``バリデータを使うことができます:

 // models/User.php

class User extends BaseUser { public function setTableDefinition() {
parent::setTableDefinition();

::

        // ...

        $this->hasColumn('age', 'integer', 3, array(
                'range' => array(1 => 100)
            )
        );
    }

}

上記の例では最大年齢は100才になります。最小値を指定するには、範囲配列で``1``の代わりに``0``を指定します。

YAML構文の例は次のようになります:

 # schema.yml

User: columns: # ... age: type: integer(3) range: 1: 100 # ...

------
Unique
------

unique制約は1つのカラムもしくはカラムのグループに含まれるデータがテーブルのすべての列に関してユニークであること保証します。

一般的に、制約に含まれるカラムのすべての値が等しい複数の列が存在するときにunique制約は破られます。しかしながら、この比較では2つのnull値は等しいとはみなされません。このことはunique制約の下で制約の課された少なくとも1つのカラムでnull値を含む重複列を保存することが可能であることを意味します。このビヘイビアはSQL標準に準拠しますが、一部のデータベースはこのルールに従いません。ですのでポータルなアプリケーションを開発するときは注意してください。

次の定義はカラム名に対して``unique``制約を使います。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'unique' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: type: string(255) unique: true # ....

    **NOTE**
    主キーは既にuniqueなので主キー以外のカラムに対してのみunique制約を使うべきです。

----
正規表現
----

正規表現バリデータは独自の正規表現に対してカラムの値をバリデートするシンプルな方法です。この例ではユーザー名は有効な文字もしくは数字だけを含むことを確認します。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('username', 'string', 255, array(
                'regexp' => '/[a-zA-Z0-9]/'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: type: string(255) regexp: '/ [1]_+$/' # ...

文字か数字以外の文字を含む``username``を持つ``User``を保存しようとすると、バリデーションはエラーになります:

 // test.php

// ... $user = new User(); $user->username = '[jwage';

if ( ! $user->isValid()) { echo 'User is invalid because the username
contains a [ character'; }

--------
クレジットカード
--------

creditcardバリデータは値が本当に有効なクレジットカード番号であることをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('cc_number', 'integer', 16, array(
                'creditcard' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... cc\_number: type: integer(16) creditcard: true #
...

---------
Read Only
---------

``readonly``バリデータが有効なカラムを修正しようとするとバリデーションに失敗します。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('readonly_value', 'string', 255, array(
                'readonly' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... readonly\_value: type: integer(16) readonly: true #
...

``User``オブジェクトインスタンスから``readonly_value``という名前のカラムを修正しようとすると、バリデーションはエラーになります。

--------
Unsigned
--------

unsignedバリデータは整数が符号無しであることをチェックします。

 // models/User.php

class User extends BaseUser { // ...

::

    public function setTableDefinition()
    {
        parent::setTableDefinition();

        // ...

        $this->hasColumn('age', 'integer', 3, array(
                'unsigned' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: # ... age: type: integer(3) unsigned: true # ...

マイナス年齢の``User``を保存しようとするとバリデーションはエラーになります:

 // test.php

// ... $user = new User(); $user->username = 'jwage'; $user->age =
'-100';

if ( ! $user->isValid()) { echo 'User is invalid because -100 is
signed'; }

--------
US State
--------

usstateバリデータは文字列が有効なUSの州コードであることをチェックします。

 // models/State.php

class State extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255);
$this->hasColumn('code', 'string', 2, array( 'usstate' => true ) ); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

State: columns: name: string(255) code: type: string(2) usstate: true

無効な州コードで``State``を保存しようとするとバリデーションがエラーになります。

 $state = new State(); $state->name = 'Tennessee'; $state->code = 'ZZ';

if ( ! $state->isValid()) { echo 'State is invalid because "ZZ" is not a
valid state code'; }

===
まとめ
===

データを永続的にデータベースに保存する前にDoctrineにデータのバリデーションを行わせる方法を理解しDoctrineコアが提供する共通のバリデータを使うことができます。

[doc inheritance 次の章]では[doc inheritance
:name]を検討するので重要です！継承は最小のコードで複雑な機能を実現するための偉大な方法です。継承を検討した後で[doc
behaviors
:name]と呼ばれる継承よりも優れた機能を提供するカスタム戦略に移ります。

.. [1]
   a-zA-Z0-9
