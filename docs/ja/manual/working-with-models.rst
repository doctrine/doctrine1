========================
テストスキーマを定義する
========================

    **NOTE**
    以前の章からの既存のスキーマ情報をモデルを削除しておいてください。

 $ rm schema.yml $ touch schema.yml $ rm -rf models/\*

次のいくつかの例に対して次のスキーマを使います:

 // models/User.php

class User extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255,
array( 'type' => 'string', 'length' => '255' ) );

::

        $this->hasColumn('password', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            )
        );
    }

    public function setUp()
    {
        $this->hasMany('Group as Groups', array(
                'refClass' => 'UserGroup',
                'local' => 'user_id',
                'foreign' => 'group_id'
            )
        );

        $this->hasOne('Email', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );

        $this->hasMany('Phonenumber as Phonenumbers', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );
    }

}

// models/Email.php

class Email extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'type' => 'integer' ) );

::

        $this->hasColumn('address', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            )
        );
    }

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

// models/Phonenumber.php

class Phonenumber extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'type' => 'integer' ) );

::

        $this->hasColumn('phonenumber', 'string', 255, array(
                'type' => 'string',
                'length' => '255'
            )
        );
        $this->hasColumn('primary_num', 'boolean');
    }

    public function setUp()
    {
        $this->hasOne('User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

// models/Group.php

class Group extends Doctrine\_Record { public function
setTableDefinition() { $this->setTableName('groups');
$this->hasColumn('name', 'string', 255, array( 'type' => 'string',
'length' => '255' ) ); }

::

    public function setUp()
    {
        $this->hasMany('User as Users', array(
                'refClass' => 'UserGroup',
                'local' => 'group_id',
                'foreign' => 'user_id'
            )
        );
    }

}

// models/UserGroup.php

class UserGroup extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('user\_id', 'integer', null,
array( 'type' => 'integer', 'primary' => true ) );

::

        $this->hasColumn('group_id', 'integer', null, array(
                'type' => 'integer',
                'primary' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

User: columns: username: string(255) password: string(255) relations:
Groups: class: Group local: user\_id foreign: group\_id refClass:
UserGroup foreignAlias: Users

Email: columns: user\_id: integer address: string(255) relations: User:
foreignType: one

Phonenumber: columns: user\_id: integer phonenumber: string(255)
primary\_num: boolean relations: User: foreignAlias: Phonenumbers

Group: tableName: groups columns: name: string(255)

UserGroup: columns: user\_id: type: integer primary: true group\_id:
type: integer primary: true

スキーマを定義したので以前の章で利便性のために作成した``generate.php``スクリプトを実行してデータベースをインスタンス化できます。

 $ php generate.php

==================
リレーションを扱う
==================

----------------------
関連レコードを作成する
----------------------

Doctrineで関連レコードにアクセスするのは簡単です:
レコードプロパティに関してまったく同じゲッターとセッターを使うことができます。

3つの方法はどれでも使えますが、配列のポータビリティを目的にするなら最後の方法がお勧めです。

 // test.php

// ... $user = new User(); $user['username'] = 'jwage';
$user-['password'] = 'changeme';

$email = $user->Email;

$email = $user->get('Email');

$email = $user['Email'];

存在しない一対一の関連レコードにアクセスするとき、Doctrineは自動的にオブジェクトを作成します。That
is why the above

 // test.php

// ... $user->Email->address = 'jonwage@gmail.com'; $user->save();

一対多の関連レコードにアクセスするとき、Doctrineは関連コンポーネント用の``Doctrine_Collection``を作成します。リレーションが一対多である``users``と``phonenumbers``を考えてみましょう。上記で示されるように``phonenumbers``を簡単に追加できます:

 // test.php

// ... $user->Phonenumbers[]->phonenumber = '123 123';
$user->Phonenumbers[]->phonenumber = '456 123';
$user->Phonenumbers[]->phonenumber = '123 777';

ユーザーと関連の電話番号を簡単に保存できます:

 // test.php

// ... $user->save();

2つの関連コンポーネントの間でリンクを簡単に作る別の方法は``Doctrine_Record::link()``を使うことです。既存の2つのレコードをお互いに関連づける(もしくはリンクする)ことはよくあります。この場合、関わるレコードクラスの間で定義されたリレーションがある場合、関連レコードの識別子だけが必要です:

新しい
``Phonenumber``オブジェクトを作成し新しい電話番号の識別子を追跡しましょう:

 // test.php

// ... $phoneIds = array();

$phone1 = new Phonenumber(); $phone1['phonenumber'] = '555 202 7890';
$phone1->save();

$phoneIds[] = $phone1['id'];

$phone2 = new Phonenumber(); $phone2['phonenumber'] = '555 100 7890';
$phone2->save();

$phoneIds[] = $phone2['id'];

``User``レコード用に存在するので電話番号をユーザーにリンクしましょう。

 // test.php

$user = new User(); $user['username'] = 'jwage'; $user['password'] =
'changeme'; $user->save();

$user->link('Phonenumbers', $phoneIds);

``User``レコードクラスへのリレーションが``Phonenumber``レコードクラスに対して定義された場合、次のようにもできます:

最初に連携するユーザーを作ります:

 // test.php

// ... $user = new User(); $user['username'] = 'jwage';
$user['password'] = 'changeme'; $user->save();

新しい``Phonenumber``インスタンスを作成します:

 // test.php

// ... $phone1 = new Phonenumber(); $phone1['phonenumber'] = '555 202
7890'; $phone1->save();

``User``を``Phonenumber``にリンクできます:

 // test.php

// ... :code:`phone1->link('User', array(`\ user['id']));

別の電話番号を作成できます:

 // test.php

// ... $phone2 = new Phonenumber(); $phone2['phonenumber'] = '555 100
7890';

この``Phonenumber``も``User``にリンクしましょう:

 // test.php

// ... :code:`phone2->link('User', array(`\ user['id']));

----------------------
関連レコードを読み取る
----------------------

前の節とまったく同じな``Doctrine_Record``メソッドで関連レコードを読み取ることができます。既にロードされていない関連コンポーネントにアクセスするときDoctrineが取得に1つのSELECT文を使用することに注意してください。次の例では3つの``SQL
SELECT``が実行されます。

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(1);

echo $user->Email['address'];

echo $user->Phonenumber[0]->phonenumber;

これをもっと効率的に行うにはDQLを使います。次の例では関連コンポーネントの読み取りに1つのSQLクエリのみを使用します。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Email e') ->leftJoin('u.Phonenumber p') ->where('u.id =
?', 1);

$user = $q->fetchOne();

echo $user->Email['address'];

echo $user->Phonenumber[0]['phonenumber'];

----------------------
関連レコードを更新する
----------------------

それぞれの関連オブジェクト/コレクションに対してsaveを個別に呼び出すもしくは他のオブジェクトを所有するオブジェクトでsave()を呼び出すことで関連レコードを更新できます。すべての追加オブジェクトを保存する``Doctrine_Connection::flush``を呼び出すこともできます。

 // test.php

// ... $user->Email['address'] = 'koskenkorva@drinkmore.info';

$user->Phonenumber[0]['phonenumber'] = '123123';

$user->save();

    **NOTE**
    上記の例では``$user->save()``を呼び出すことで``email``と``phonenumber``が保存されます。

------------------------
関連レコードをクリアする
------------------------

オブジェクトから関連レコードリファレンスをクリアすることができます。これはこれらのオブジェクトが関連しているという事実を変更することはなく、また保存するのであればデータベースでこれを変更することはありません。これはPHPの1つのオブジェクトの参照を別のものにクリアするだけです。

次のコードを実行することですべてのリファレンスをクリアできます:

 // test.php

// ... $user->clearRelated();

もしくは特定のリレーションシップをクリアすることもできます:

 // test.php

// ... $user->clearRelated('Email');

これは次のようなことをしたい場合に便利です:

 // test.php

// ... if ($user->Email->exists()) { // ユーザーがメールを持つ } else {
// ユーザーがメールを持たない }

$user->clearRelated('Email');

``Email``オブジェクトが存在しない場合Doctrineが新しい``Email``オブジェクトを自動的に作成するので、あたかも``$user->save()``を呼び出し``User``の空白の``Email``レコードを保存しないように、この参照をクリアする必要があります。

``relatedExists()``メソッドを使うことで上記のシナリオを簡略化できます。これで上記のチェックはより短いコードになり後で不必要な参照をクリアすることにわずらわずに済みます。

 if ($user->relatedExists('Email')) { // ユーザーがメールを持つ } else {
// ユーザーがメールを持たない }

----------------------
関連レコードを削除する
----------------------

レコードもしくはコレクション上で``delete()``を呼び出すことで関連レコードを個別に削除できます。

個別の関連レコードを削除できます:

 // test.php

// ... $user->Email->delete();

レコードのコレクションの範囲から個別のレコードを削除できます:

 // test.php

// ... $user->Phonenumber[3]->delete();

望むのであればコレクション全体を削除できます:

 // test.php

// ... $user->Phonenumbers->delete();

もしくはユーザー全体とすべての関連オブジェクトを削除できます:

 // test.php

// ... $user->delete();

典型的なウェブアプリケーションでは削除される関連オブジェクトの主キーはフォームからやってきます。この場合関連レコードの最も効率的な削除はDQLのDELETEステートメントを使用することです。リレーションが一対多である``Users``と``Phonenumbers``を再度考えてみましょう。与えられたユーザーidに対して``Phonenumbers``を削除することは次のように実現できます:

 // test.php

// ... $q = Doctrine\_Query::create() ->delete('Phonenumber')
->addWhere('user\_id = ?', 5) ->whereIn('id', array(1, 2, 3));

$numDeleted = $q->execute();

ときに``Phonenumber``レコードを削除したくないが外部キーをnullに設定することでリレーションのリンクを解除したいことがあります。もちろんこれはDQLを使えば実現できますが最もエレガントな方法は``Doctrine_Record::unlink()``を使う方法です。

    **NOTE**
    ``unlink()``メソッドが非常にスマートであることに留意してください。このメソッドは関連する``Phonenumbers``用の外部キーをnullにする設定するだけでなく{User``オブジェクトから``Phonenumber``のすべての参照も削除します。

``User``が3つの``Phonenumbers``(識別子は1、2と3)を持つことを考えましょう。``Phonenumbers``
1と3のリンク解除は次のように実現できます:

 // test.php

// ... $user->unlink('Phonenumber', array(1, 3));

echo $user->Phonenumbers->count(); // 1

----------------------
関連レコードに取り組む
----------------------

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
リレーションの存在をテストする
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

下記の例ではリレーションがまだインスタンス化されないのでfalseが返されます:

 // test.php

// ... :code:`user = new User(); if (isset(`\ user->Email)) { // ... }

次の例では``Email``リレーションをインスタンス化したのでtrueが返されます:

 // test.php

// ... $obj->Email = new Email();

if(isset($obj->Email)) { // ... }

====================
多対多のリレーション
====================

    **CAUTION**
    Doctrineは多対多のリレーションが双方向であることを求めます。例:
    ``User``は複数の``Groups``を持たなければならず``Group``は複数の``User``を持たなければならない

------------------------
新しいレコードを作成する
------------------------

``User``と``Group``の2つのクラスを考えてみましょう。これらはGroupUserアソシエーションクラスを通してリンクされます。一時的な(新しい)レコードを扱うときに``User``と``Groups``の組を追加するための最速の方法は次の通りです:

 // test.php

// ... $user = new User(); $user->username = 'Some User';
$user->Groups[0]->username = 'Some Group'; $user->Groups[1]->username =
'Some Other Group'; $user->save();

しかしながら実際の世界のシナリオではユーザーを追加したい既存のグループがあることはよくあります、これを行う最も効率的な方法は次の通りです:

 // test.php

// ... $groupUser = new GroupUser(); $groupUser->user\_id = $userId;
$groupUser->group\_id = $groupId; $groupUser->save();

----------------
リンクを削除する
----------------

多対多の関連レコード間のリンクを削除する正しい方法はDQL
DELETEステートメントを使うことです。DQL
DELETEを利用する際に便利で推奨される方法はQuery APIを通して行われます。

 // test.php

// ... $q = Doctrine\_Query::create() ->delete('UserGroup')
->addWhere('user\_id = ?', 5) ->whereIn('group\_id', array(1, 2));

$deleted = $q->execute();

関連オブジェクトの間のリレーションを``unlink``する別の方法は``Doctrine_Record::unlink``メソッドを通したものです。しかしながら、こｎメソッドは最初にデータベースにクエリを行うので親モデルが既に存在しない限りこのメソッドは避けるべきです。

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(5);
$user->unlink('Group', array(1, 2)); $user->save();

2番目の引数を省略することで``Group``へのすべてのリレーションのリンクを解除することもできます:

 // test.php

// ... $user->unlink('Group');

``User``と``Group``の間のリンクを削除する明確で便利な方法が次であるとしても、これを行うべきでは*ありません*:

 // test.php

// ... $user = Doctrine\_Core::getTable('User')->find(5);
$user->GroupUser->remove(0)->remove(1); $user->save();

この方法は``$user->GroupUser``への呼び出しは与えられた``User``に対するすべての``Group``リンクをロードしているからです。``User``が多くの``Groups``に所属している場合この方法が時間のかかるタスクになる可能性があります。ユーザーがわずかな``groups``に所属する場合でも、これが不要なSELECTステートメントを実行します。

==========================
オブジェクトをフェッチする
==========================

通常データベースからデータをフェッチするとき次のフレーズが実行されます:

クエリをデータベースに送信する
==============================

データベースから戻り値を検索する
================================

オブジェクトフェッチの観点からこれら2つのフェーズを'フェッチ'フェーズにします。Doctrineにはハイドレーションフェーズと呼ばれる別のフェーズもあります。ハイドレーションフェーズは構造化あれた配列／オブジェクトをフェッチするときに起こります。Doctrineで明示的に指定されないものはハイドレイトされます。

リレーションシップが1対多である``Users``と``Phonenumbers``がある場合を考えてみましょう。次のプレーンなSQLクエリを考えましょう:

 // test.php

// ... $sql = 'SELECT u.id, u.username, p.phonenumber FROM user u LEFT
JOIN phonenumber p ON u.id = p.user\_id'; $results =
:code:`conn->getDbh()->fetchAll(`\ sql);

この種の一対多のJOINに慣れている場合
基本の結果セットをコンストラクトする方法に親しみやすいかもしれません。ユーザーが複数の電話番号を持つときは結果セットに重複データが存在します。結果セットは次のようになります:

\|\|~ index \|\|~ ``u.id`` \|\|~ ``u.username`` \|\|~ ``p.phonenumber``
\|\| \|\| 0 \|\| 1 \|\| Jack Daniels \|\| 123 123 \|\| \|\| 1 \|\| 1
\|\| Jack Daniels \|\| 456 456 \|\| \|\| 2 \|\| 2 \|\| John Beer \|\|
111 111 \|\| \|\| 3 \|\| 3 \|\| John Smith \|\| 222 222 \|\| \|\| 4 \|\|
3 \|\| John Smith \|\| 333 333 \|\| \|\| 5 \|\| 3 \|\| John Smith \|\|
444 444 \|\|

Jack Danielsが2つの``Phonenumbers``を持ち、John
Beerは1つ持つのに対してJohn
Smithは3つ持ちます。この結果セットがいかにぶかっこうなことにお気づきのことでしょう。あちらこちらで重複データのチェックが必要なのでイテレートするのは難しいです。

Doctrineのハイドレーションはすべての重複データを削除します。これは次のようなほかの多くのことも実行します:

結果セット要素のカスタムインデックス登録
========================================

値のキャストと準備
==================

一覧を表示する値の割り当て
==========================

2次元の結果セットの配列から多次元配列を生み出す。次元の数は入れ子のjoinの数と等しい(HYDRATE\_ARRAYのみ)
=======================================================================================================

SQLクエリに同等なDQLを考えてみましょう:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.id, u.username,
p.phonenumber') ->from('User u') ->leftJoin('u.Phonenumbers p');

$results = $q->execute(array(), Doctrine\_Core::HYDRATE\_ARRAY);

print\_r($results);

ハイドレイトされた配列の構造は次の通りです:

 $ php test.php Array ( [0] => Array ( [id] => 1 [username] =>
[Phonenumbers] => Array ( [0] => Array ( [id] => 1 [phonenumber] => 123
123 )

::

                    [1] => Array
                        (
                            [id] => 2
                            [phonenumber] => 456 123
                        )

                    [2] => Array
                        (
                            [id] => 3
                            [phonenumber] => 123 777
                        )

                )

        )
    // ...

)

この構造はオブジェクト(レコード)のハイドレーションにも適用されます。これはDoctrineのデフォルトのハイドレーションモードです。唯一の違いは個別の要素が``Doctrine\_Record``オブジェクトと``Doctrine_Collection``オブジェクトに変換される配列として表現されることです。オブジェクトの配列を扱うとき、次のことができます:

//foreach// を使用して結果をイテレートする
==========================================

配列アクセスの角かっこを使用して個別の要素にアクセスできる
==========================================================

//count()// 関数を使用して結果の数を取得する
============================================

//isset()// を使用して要素が存在するかチェックする
==================================================

//unset()// を使用して任意の要素の割り当てを解除する
====================================================

アクセスオンリーの目的でデータが必要なときはつねに配列ハイドレーションを使うべきである一方でフェッチされたデータを変更する必要があるときはレコードハイドレーションを使うべきです。

ハイドレーションアルゴリズムのコンスタントなO(n)パフォーマンスはスマートアイデンフィファーキャッシングソリューションによって保証されます。

.. tip::


    データベースの1つのレコードで複数のオブジェクトが存在しないことを確認するためにDoctrineは内部でアイデンティティマップを使います。オブジェクトをフェッチしプロパティの一部を修正する場合、後で同じオブジェクトを取得すれば、修正されたプロパティはデフォルトでオーバーライドされます。``ATTR\_HYDRATE_OVERWRITE``属性を``false``に変更することでこのふるまいを変更することができます。

----------------
サンプルのクエリ
----------------

**リレーションに対してレコードの数をカウントする:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.\*, COUNT(DISTINCT
p.id) AS num\_phonenumbers') ->from('User u') ->leftJoin('u.Phonenumbers
p') ->groupBy('u.id');

$users = $q->fetchArray();

echo $users[0]['Phonenumbers'][0]['num\_phonenumbers'];

**ユーザーとユーザーが所属するグループを読み取る:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Groups g');

$users = $q->fetchArray();

foreach ($users[0]['Groups'] as $group) { echo $group['name']; }

**1つのパラメータの値を持つシンプルなWHERE:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->where('u.username = ?', 'jwage');

$users = $q->fetchArray();

**複数のパラメータの値を持つマルチプルWHERE:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumbers p') ->where('u.username = ? AND p.id = ?',
array(1, 1));

$users = $q->fetchArray();

.. tip::

   
    オプションとして既存のwhere部分に追加するために``andWhere()``メソッドを使うこともできます。

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumbers p') ->where('u.username = ?', 1)
->andWhere('p.id = ?', 1);

$users = $q->fetchArray();

**``whereIn()``コンビニエンスメソッドを使用する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u') ->whereIn('u.id',
array(1, 2, 3));

$users = $q->fetchArray();

**次のコードは上記と同じ:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u') ->where('u.id IN
(1, 2, 3)');

$users = $q->fetchArray();

**WHEREでDBMS関数を使う:**

 // test.php

// ... $userEncryptedKey = 'a157a558ac00449c92294c7fab684ae0'; $q =
Doctrine\_Query::create() ->from('User u')
->where("MD5(CONCAT(u.username, 'secret\_key')) = ?",
$userEncryptedKey);

$user = $q->fetchOne();

$q = Doctrine\_Query::create() ->from('User u')
->where('LOWER(u.username) = LOWER(?)', 'jwage');

$user = $q->fetchOne();

**集約関数を使用して結果セットを制限する。1つ以上の電話番号を持つユーザーに制限する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.\*, COUNT(DISTINCT
p.id) AS num\_phonenumbers') ->from('User u') ->leftJoin('u.Phonenumbers
p') ->having('num\_phonenumbers > 1') ->groupBy('u.id');

$users = $q->fetchArray();

**WITHを使用して最初の電話番号のみをJOINする:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Phonenumbers p WITH p.primary\_num = ?', true);

$users = $q->fetchArray();

**最適化用に特定のカラムを選択する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username, p.phone')
->from('User u') ->leftJoin('u.Phonenumbers p');

$users = $q->fetchArray();

**1つの``Phonenumber``カラムのみ以外のすべての``User``カラムを選択するためにワイルドカードを使用する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.\*, p.phonenumber')
->from('User u') ->leftJoin('u.Phonenumbers p');

$users = $q->fetchArray();

**シンプルなWHEREでDQLのdeleteを実行する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->delete('Phonenumber')
->addWhere('user\_id = 5');

$deleted = $q->execute();

**1つのカラムに対してシンプルなDQLのupdateを実行する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->update('User u')
->set('u.is\_active', '?', true) ->where('u.id = ?', 1);

$updated = $q->execute();

**DBMSの関数でDQL updateを実行する。すべてのユーザー名を小文字にする:**

 // test.php

// ... $q = Doctrine\_Query::create() ->update('User u')
->set('u.username', 'LOWER(u.username)');

$updated = $q->execute();

**レコードを検索するためにMySQLのLIKEを使用する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->where('u.username LIKE ?', '%jwage%');

$users = $q->fetchArray();

**レコードエントリのキーが割り当てたカラムの名前であるデータをハイドレイトするためにINDEXBYキーワードを使用する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u INDEXBY
u.username');

$users = $q->fetchArray();

**jwageのユーザー名を持つユーザーを表示できます:**

 // test.php

// ... print\_r($users['jwage']);

**位置パラメータを使用する**

 $q = Doctrine\_Query::create() ->from('User u') ->where('u.username =
?', array('Arnold'));

$users = $q->fetchArray();

**名前付きパラメータを使用する**

 $q = Doctrine\_Query::create() ->from('User u') ->where('u.username =
:username', array(':username' => 'Arnold'));

$users = $q->fetchArray();

**WHEREでサブクエリを使用する。Group
2という名前のグループに存在しないユーザーを見つける:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u') ->where('u.id NOT
IN (SELECT u.id FROM User u2 INNER JOIN u2.Groups g WHERE g.name = ?)',
'Group 2');

$users = $q->fetchArray();

.. tip::

   
    サブクエリなしでこれを実現できます。下記の2つの例は上記の例と同じ結果が得られます。

**グループを持つユーザーを読み取るためにINNER JOINを使用する**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->innerJoin('u.Groups g WITH g.name != ?', 'Group 2')

$users = $q->fetchArray();

**グループを持つユーザーを読み取るためにWHERE条件を使用する**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u')
->leftJoin('u.Groups g') ->where('g.name != ?', 'Group 2');

$users = $q->fetchArray();

Doctrineはクエリを実行してデータを読み取るための多くの方法を持ちます。下記のコードはクエリを実行する異なるすべての方法の例です:

**最初にテストするサンプルクエリを作成する:**

 // test.php

// ... $q = Doctrine\_Query::create() ->from('User u');

**``fetchArray()``メソッドで配列のハイドレーションを実行できます:**

 $users = $q->fetchArray();

**``execute()``メソッドの2番目の引数でハイドレーションメソッドを指定することでも配列のハイドレーションを利用できます:**

 // test.php

// ... $users = $q->execute(array(), Doctrine::HYDRATE\_ARRAY)

**``setHydrationMethod()``メソッドを利用することでもハイドレーションメソッドを指定できます:**

 $users = $q->setHydrationMode(Doctrine::HYDRATE\_ARRAY)->execute(); //
So is this

**ときにはハイドレーションを完全に回避してPDOが返す生のデータが欲しいことがあります:**

 // test.php

// ... $users = $q->execute(array(), Doctrine::HYDRATE\_NONE);

**クエリから1つのレコードだけを取得したい場合:**

 // test.php

// ... $user = $q->fetchOne();

// Fetch all and get the first from collection $user =
$q->execute()->getFirst();

----------------------
フィールドの遅延ロード
----------------------

データベースからロードされるすべてのフィールドを持たないオブジェクトを取得するときこのオブジェクトの状態はプロキシ(proxy)と呼ばれます。プロキシオブジェクトはまたロードされていないフィールドを遅延ロードできます。

次の例では直接ロードされた``username``フィールドを持つすべてのUsersを取得します。それからpasswordフィールドを遅延ロードします:

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.username')
->from('User u') ->where('u.id = ?', 1)

$user = $q->fetchOne();

次に``password``フィールドを遅延ロードし値を読み取るために追加のデータベースクエリを実行します:

 // test.php

// ... $user->password;

Doctrineはロードされたフィールドのカウントに基づいてプロキシの評価を行います。フィールドごとにどのフィールドがロードされるのかは評価しません。この理由は単純でパフォーマンスです。PHPの世界ではフィールドの遅延ロードはほとんど必要ないので、どのフィールドがロードされるのかチェックするこの種の変数を導入することは基本的な取得に不要なオーバーロードを持ち込むことになります。

==================
配列とオブジェクト
==================

``Doctrine\_Record``と``Doctrine_Collection``は配列との連携を円滑にするメソッドを提供します:
``toArray()``、``fromArray()``と``synchronizeWithArray()``。

------
配列に
------

``toArray()``メソッドはレコードもしくはコレクションの配列表現です。これはオブジェクトが持つリレーションにもアクセスします。デバッグ目的でレコードを表示する必要がある場合オブジェクトの配列表現を取得して出力できます。

 // test.php

// ... print\_r($user->toArray());

配列にリレーションを格納したい場合、//true//の値を持つ引数``$deep``を渡す必要があります:

 // test.php

// ... print\_r($user->toArray(true));

--------
配列から
--------

配列の値がありレコードもしくはコレクションを満たすために使いたい場合、``fromArray()``メソッドはこの共通のタスクを簡略化します。

 // test.php

// ... $data = array( 'name' => 'John', 'age' => '25', 'Emails' =>
array( array('address' => 'john@mail.com'), array('address' =>
'john@work.com') 'Groups' => array(1, 2, 3) );

$user = new User(); :code:`user->fromArray(`\ data); $user->save();

次のようにカスタムモデルのミューテータで``fromArray()``を使うことが可能です:

 // models/User.php

class User extends Doctrine\_Record { // ...

::

    public function setEncryptedPassword($password)
    {
        return $this->_set('password', md5($password));
    }

}

``fromArray()``を使う場合``encrypted_password``という名前の値を渡すことで``setEncryptedPassword()``メソッドを使うことができます。

 // test.php

// ... $user->fromArray(array('encrypted\_password' => 'changeme'));

--------------
配列で同期する
--------------

``synchronizeWithArray()``によってレコードと配列を同期できます。モデルの配列表現がありフィールドを修正する場合、リレーションのフィールドを修正もしくはリレーションを削除もしくは作成します。この変更はレコードに適用されます。

 // test.php

// ... $q = Doctrine\_Query::create() ->select('u.*, g.*') ->from('User
u') ->leftJoin('u.Groups g') ->where('id = ?', 1);

$user = $q->fetchOne();

これを配列に変換してプロパティの一部を修正します:

 // test.php

// ... $arrayUser = $user->toArray(true);

$arrayUser['username'] = 'New name'; $arrayUser['Group'][0]['name'] =
'Renamed Group'; $arrayUser['Group'][] = array('name' => 'New Group');

レコードを読み取るために同じクエリを使いレコードと変数``$arrayUser``を同期します:

 // test.php

// ... $user = Doctrine\_Query::create() ->select('u.*, g.*')
->from('User u') ->leftJoin('u.Groups g') ->where('id = ?', 1)
->fetchOne();

:code:`user->synchronizeWithArray(`\ arrayUser); $user->save();

レコードをリンクするidの配列を指定することでリレーションをシンクロナイズすることもできます。

 $user->synchronizeWithArray(array('Group' => array(1, 2, 3)));
$user->save();

上記のコードは既存のグループを削除しユーザーをグループid
1、2、3にリンクします。

.. tip::

   
    リレーションを一緒にリンクするために内部では``Doctrine\_Record::link()``と``Doctrine_Record::unlink()``が使われています。

==================================
コンストラクタをオーバーライドする
==================================

ときどきオブジェクト作成時に同じオペレーションを行いたい場合があります。``Doctrine\_Record::\__construct()``メソッドをオーバーライドできませんが代わりの方法があります:

 class User extends Doctrine\_Record { public function construct() {
$this->username = 'Test Name'; $this->doSomething(); }

::

    public function doSomething()
    {
        // ...
    }

    // ...

}

唯一の欠点はコンストラクタにパラメータを渡す方法がないことです。

======
まとめ
======

これでモデルのことがよくわかりました。これらを作り、ロードする方法を知っています。最も大事なことはモデルとカラムとリレーションを連携させる方法です。[doc
dql-doctrine-query-language :name]の章に移動して使い方を学びます。
