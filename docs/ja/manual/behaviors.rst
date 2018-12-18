..  vim: set ts=4 sw=4 tw=79 :

**********
Behaviours
**********

========
はじめに
========

モデルの中で似た内容を持つクラスを見つけることはよくあります。これらの内容はコンポーネント自身のスキーマ(リレーション、カラムの定義、インデックスの定義など)に関連することがあります。このコードをリファクタリングする明らかな方法は基底クラスとそれを継承するクラスを用意することです。

しかしながら継承はこれらの一部しか解決しません。次のセクションで``Doctrine_Template``が継承よりもはるかに強力で柔軟であることを示します。

``Doctrine_Template``はクラステンプレートシステムです。基本的にテンプレートはRecordクラスがロードできるすぐ使える小さなコンポーネントです。テンプレートがロードされるとき``setTableDefinition()``と``setUp()``メソッドが起動されこれらの内部でメソッドが呼び出され渦中のクラスに導かれます。

この章ではDoctrineで利用できる様々なビヘイビアの使い方を説明します。独自ビヘイビアの作り方も説明します。この章のコンセプトを掴むために``Doctrine\_Template``と``Doctrine\_Record_Generator``の背景にある理論に慣れなければなりません。これらのクラスがどんなものであるのか手短に説明します。

ビヘイビアを言及するときテンプレート、ジェネレータとリスナーを広範囲に渡って使用するクラスパッケージを意味します。この章で紹介されるすべてのコンポーネントは``core``ビヘイビアとしてみなされています。これはDoctrineのメインリポジトリに保管されていることを意味します。

通常ビヘイビアはテンプレートクラス(``Doctrine_Template``を継承するクラス)でジェネレータを使用します。共通のワークフローは次の通りです:

-  新しいテンプレートが初期化される
-  テンプレートはジェネレータを作成し``initialize()``メソッドを呼び出す
-  テンプレートはクラスに添付される

ご存知かもしれませんがテンプレートは共通の定義とオプションをレコードクラスに追加するために使われます。ジェネレータの目的はとても複雑です。通常これらはジェネリックレコードクラスを動的に作成するために使われます。これらのジェネリッククラスの定義はオーナーのクラスによります。例えばクラスをバージョニングする``AuditLog``カラムの定義はすべてのsequenceとautoincrementの定義が削除された親クラスのカラムです。

===========
シンプルなテンプレート
===========

次の例において``TimestampBehavior``と呼ばれるテンプレートを定義します。基本的にテンプレートの目的はこのテンプレートをロードするレコードクラスに日付カラムの'created'と'updated'を追加することです。加えてこのテンプレートはレコードのアクションに基づいてこれらのカラムを更新するTimestampリスナーを使用します。

 // models/TimestampListener.php

class TimestampListener extends Doctrine\_Record\_Listener { public
function preInsert(Doctrine\_Event $event) {
$event->getInvoker()->created = date('Y-m-d', time());
$event->getInvoker()->updated = date('Y-m-d', time()); }

::

    public function preUpdate(Doctrine_Event $event)
    {
        $event->getInvoker()->updated = date('Y-m-d', time());
    }

}

``actAs()``メソッドでモデルに添付できるように``TimestampTemplate``という名前の子の``Doctrine_Template``を作りましょう:

 // models/TimestampBehavior.php

class TimestampTemplate extends Doctrine\_Template { public function
setTableDefinition() { $this->hasColumn('created', 'date');
$this->hasColumn('updated', 'date');

::

        $this->setListener(new TimestampListener());
    }

}

タイムスタンプの機能を必要とする``BlogPost``クラスを考えてみましょう。行う必要があるのはクラスの定義に``actAs()``の呼び出しを追加することです。

 class BlogPost extends Doctrine\_Record

::

    public function setTableDefinition()
    {
        $this->hasColumn('title', 'string', 200);
        $this->hasColumn('body', 'clob');
    }

    public function setUp()
    {
        $this->actAs('TimestampBehavior');
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 BlogPost: actAs: [TimestampBehavior] columns: title: string(200) body:
clob

``BlogPost``モデルを活用しようとするとき``created``と``updated``カラムが追加され保存されるときに自動的に設定されたことがわかります:

 $blogPost = new BlogPost(); $blogPost->title = 'Test'; $blogPost->body
= 'test'; $blogPost->save();

print\_r($blogPost->toArray());

上記の例は次の出力を表示します:

 $ php test.php Array ( [id] => 1 [title] => Test [body] => test
[created] => 2009-01-22 [updated] => 2009-01-22 )

    **NOTE**
    上記で説明した機能は既にお話した``Timestampable``ビヘイビアを通して利用できます。この章の[doc
    behaviors:core-behaviors:timestampable
    :name]セクションに戻って詳細内容を読むことができます。

===============
リレーション付きのテンプレート
===============

以前の章よりも状況は複雑になりがちです。他のモデルクラスへのリレーションを持つクラスがあり任意のクラスを格調されたクラスで置き換えたいことがあります。

次の定義を持つ``User``と``Email``の2つのクラスを考えてみましょう:

 class User extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255);
$this->hasColumn('password', 'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('Email', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );
    }

}

class Email extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('address', 'string');
$this->hasColumn('user\_id', 'integer'); }

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

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 User: columns: username: string(255) password: string(255)

Email: columns: address: string user\_id: integer relations: User:

``User``と``Email``クラスを拡張し、例えば``ExtendedUser``と``ExtendedEmail``クラスを作る場合、``ExtendedUser``は``Email``クラスへのリレーションを保存しますが``ExtendedEmail``クラスへのリレーションは保存しません。もちろん``User``クラスの``setUp()``メソッドをオーバーライドして``ExtendedEmail``クラスへのリレーションを定義することはできますが、継承の本質を失います。``Doctrine_Template``はこの問題を依存オブジェクトの注入(dependency
injection)の方法でエレガントに解決します。

次の例では2つのテンプレート、``UserTemplate``と``EmailTemplate``を``User``と``Email``クラスが持つほぼ理想的な定義で定義します。

 // models/UserTemplate.php

class UserTemplate extends Doctrine\_Template { public function
setTableDefinition() { $this->hasColumn('username', 'string', 255);
$this->hasColumn('password', 'string', 255); }

::

    public function setUp()
    {
        $this->hasMany('EmailTemplate as Emails', array(
                'local' => 'id',
                'foreign' => 'user_id'
            )
        );
    }

}

``EmailTemplate``を定義しましょう:

 // models/EmailTemplate.php

class EmailTemplate extends Doctrine\_Template { public function
setTableDefinition() { $this->hasColumn('address', 'string');
$this->hasColumn('user\_id', 'integer'); }

::

    public function setUp()
    {
        $this->hasOne('UserTemplate as User', array(
                'local' => 'user_id',
                'foreign' => 'id'
            )
        );
    }

}

リレーションの設定方法に注目してください。Record具象クラスを指し示すのではなく、テンプレートへのリレーションを設定しています。これはDoctrineにこれらのテンプレート用のRecord具象クラスを探すように伝えています。Doctrineがこれらの具象継承を見つけられない場合リレーションパーサーは例外を投げますが、前に進む前に、実際のレコードクラスは次の通りです:

 class User extends Doctrine\_Record { public function setUp() {
$this->actAs('UserTemplate'); } }

class Email extends Doctrine\_Record { public function setUp() {
$this->actAs('EmailTemplate'); } }

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 User: actAs: [UserTemplate]

Email: actAs: [EmailTemplate]

次のコードスニペットを考えてみましょう。テンプレート用の具象実装を設定していないのでこのコードスニペットは動きません。

 // test.php

// ... $user = new User(); $user->Emails; // throws an exception

次のバージョンが動作します。``Doctrine_Manager``を使用してグローバルにテンプレート用の具象実装の設定をする方法を注目してください:

 // bootstrap.php

// ... $manager->setImpl('UserTemplate', 'User')
->setImpl('EmailTemplate', 'Email');

このコードは動作しますが以前のように例外を投げません:

 $user = new User(); $user->Emails[0]->address = 'jonwage@gmail.com';
$user->save();

print\_r($user->toArray(true));

上記の例は次の内容を出力します:

 $ php test.php Array ( [id] => 1 [username] => [password] => [Emails]
=> Array ( [0] => Array ( [id] => 1 [address] => jonwage@gmail.com
[user\_id] => 1 )

::

        )

)

.. tip::

    テンプレート用の実装はマネージャー、接続とテーブルレベルでも設定できます。

=========
デリゲートメソッド
=========

フルテーブル定義のデリゲートシステムとして振る舞うことに加えて、``Doctrine\_Template``はメソッドの呼び出しのデリゲートを可能にします。これはロードされたテンプレート内のすべてのメソッドはテンプレートをロードしたレコードの中で利用できることを意味します。この機能を実現するために内部では``\__call()``と呼ばれるマジックメソッドが使用されます。

以前の例に``UserTemplate``にカスタムメソッドを追加してみましょう:

 // models/UserTemplate.php

class UserTemplate extends Doctrine\_Template { // ...

::

    public function authenticate($username, $password)
    {
        $invoker = $this->getInvoker();
        if ($invoker->username == $username && $invoker->password == $password) {
            return true;
        } else {
            return false;
        }
    }

}

次のコードで使い方を見ましょう:

 $user = new User(); $user->username = 'jwage'; $user->password =
'changeme';

if ($user->authenticate('jwage', 'changemte')) { echo 'Authenticated
successfully!'; } else { echo 'Could not authenticate user!'; }

``Doctrine_Table``クラスにメソッドをデリゲートすることも簡単にできます。しかし名前衝突を避けるために、テーブルクラス用のメソッドはメソッド名の最後に追加される``TableProxy``の文字列を持たなければなりません。

新しいファインダーメソッドを追加する例は次の通りです:

 // models/UserTemplate.php

class UserTemplate extends Doctrine\_Template { // ...

::

    public function findUsersWithEmailTableProxy()
    {
        return Doctrine_Query::create()
            ->select('u.username')
            ->from('User u')
            ->innerJoin('u.Emails e')
            ->execute();
    }

}

``User``モデル用の``Doctrine_Table``オブジェクトからのメソッドにアクセスできます:

 $userTable = Doctrine\_Core::getTable('User');

$users = $userTable->findUsersWithEmail();

.. tip::

    それぞれのクラスは複数のテンプレートから構成されます。テンプレートが似たような定義を格納する場合最新のロードされたテンプレートは
    前のものを常にオーバーライドします。

==========
ビヘイビアを作成する
==========

この節では独自ビヘイビア作成用の方法を説明します。一対多のEメールが必要な様々なRecordクラスを考えてみましょう。Emailクラスを即座に作成する一般的なビヘイビアを作成することでこの機能を実現します。

``EmailBehavior``と呼ばれるビヘイビアを``setTableDefinition()``メソッドで作成することからこのタスクを始めます。``setTableDefinition()``メソッドの内部では動的なレコードの定義に様々なヘルパーメソッドが使われます。次のメソッドが共通で使われています:

 public function initOptions() public function buildLocalRelation()
public function buildForeignKeys(Doctrine\_Table
:code:`table) public function buildForeignRelation(`\ alias = null)
public function buildRelation() //
buildForeignRelation()とbuildLocalRelation()を呼び出す

 class EmailBehavior extends Doctrine\_Record\_Generator { public
function initOptions() { $this->setOption('className', '%CLASS%Email');

::

        // ほかのオプション
        // $this->setOption('appLevelDelete', true);
        // $this->setOption('cascadeDelete', false);
    }

    public function buildRelation()
    {
        $this->buildForeignRelation('Emails');
        $this->buildLocalRelation();
    }

    public function setTableDefinition()
    {
        $this->hasColumn('address', 'string', 255, array(
                'email'  => true,
                'primary' => true
            )
        );
    }

}

=======
コアビヘイビア
=======

コアビヘイビアを使う次のいくつかの例のために以前の章で作成したテスト環境から既存のスキーマとモデルをすべて削除しましょう。

 $ rm schema.yml $ touch schema.yml $ rm -rf models/\*

--
紹介
--

Doctrineにはモデルにそのまま使える機能を提供するテンプレートが搭載されています。モデルでこれらのテンプレートを簡単に有効にできます。``Doctrine_Records``で直接行うもしくはYAMLでモデルを管理しているのであればこれらをYAMLスキーマで指定できます。

次の例ではDoctrineに搭載されているビヘイビアの一部を実演します。

-----------
Versionable
-----------

バージョン管理の機能を持たせるために``BlogPost``モデルを作成しましょう:

 // models/BlogPost.php

class BlogPost extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('body', 'clob'); }

::

    public function setUp()
    {
        $this->actAs('Versionable', array(
                'versionColumn' => 'version',
                'className' => '%CLASS%Version',
                'auditLog' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 BlogPost: actAs: Versionable: versionColumn: version className:
%CLASS%Version auditLog: true columns: title: string(255) body: clob

    **NOTE**
    ``auditLog``オプションはauditのログ履歴を無効にするために使われます。これはバージョン番号を維持したいがそれぞれのバージョンでのデータを維持したくない場合に使います。

上記のモデルで生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('BlogPost'));
echo $sql[0] . ""; echo $sql[1];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE blog\_post\_version (id BIGINT, title VARCHAR(255), body
LONGTEXT, version BIGINT, PRIMARY KEY(id, version)) ENGINE = INNODB
CREATE TABLE blog\_post (id BIGINT AUTO\_INCREMENT, title VARCHAR(255),
body LONGTEXT, version BIGINT, PRIMARY KEY(id)) ENGINE = INNODB ALTER
TABLE blog\_post\_version ADD FOREIGN KEY (id) REFERENCES blog\_post(id)
ON UPDATE CASCADE ON DELETE CASCADE

    **NOTE**
    おそらく予期していなかったであろう2の追加ステートメントがあることに注目してください。ビヘイビアは自動的に``blog\_post\_version``テーブルを作成しこれを``blog_post``に関連付けます。

``BlogPost``を挿入もしくは更新するときバージョンテーブルは古いバージョンのレコードをすべて保存していつでも差し戻しできるようにします。最初に``NewsItem``をインスタンス化するとき内部で起きていることは次の通りです:

-  ``BlogPostVersion``という名前のクラスが即座に作成される。レコードが指し示すテーブルは``blog\_post_version``である
-  ``BlogPost``オブジェクトが削除/更新されるたびに以前のバージョンは``blog\_post_version``に保存される
-  ``BlogPost``オブジェクトが更新されるたびにバージョン番号が増える。

``BlogPost``モデルで遊びましょう:

 $blogPost = new BlogPost(); $blogPost->title = 'Test blog post';
$blogPost->body = 'test'; $blogPost->save();

$blogPost->title = 'Modified blog post title'; $blogPost->save();

print\_r($blogPost->toArray());

上記の例では次の内容が出力されます:

 $ php test.php Array ( [id] => 1 [title] => Modified blog post title
[body] => test [version] => 2 )

    **NOTE**
    ``version``カラムの値が``2``であることに注目してください。2つのバージョンの``BlogPost``モデルを保存したからです。ビヘイビアが格納する``revert()``メソッドを使用することで別のバージョンに差し戻すことができます。

最初のバージョンに差し戻してみましょう:

 :code:`blogPost->revert(1); print_r(`\ blogPost->toArray());

上記の例は次の内容を出力する:

 $ php test.php Array ( [id] => 2 [title] => Test blog post [body] =>
test [version] => 1 )

    **NOTE**
    ``version``カラムの値が1に設定され``title``は``BlogPost``を作成するときに設定されたオリジナルの値に戻ります。

-------------
Timestampable
-------------

Timestampableビヘイビアは``created\_at``と``updated_at``カラムを追加しレコードが挿入と更新されたときに値を自動的に設定します。

日付を知ることは共通のニーズなので``BlogPost``モデルを展開してこれらの日付を自動的に設定するために``Timestampable``ビヘイビアを追加します。

 // models/BlogPost.php

class BlogPost extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        $this->actAs('Timestampable');
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

BlogPost: actAs: # ... Timestampable: # ...

``updated\_at``フィールドではなく``created_at``タイムスタンプといったカラムの1つだけを使うことに興味があるのであれば、下記の例のようにフィールドのどちらかに対して``disabled``をtrueに設定します。

 BlogPost: actAs: # ... Timestampable: created: name: created\_at type:
timestamp format: Y-m-d H:i:s updated: disabled: true # ...

新しい投稿を作成するときに何が起きるのか見てみましょう:

 $blogPost = new BlogPost(); $blogPost->title = 'Test blog post';
$blogPost->body = 'test'; $blogPost->save();

print\_r($blogPost->toArray());

上記の例は次の内容を出力します:

 $ php test.php Array ( [id] => 1 [title] => Test blog post [body] =>
test [version] => 1 [created\_at] => 2009-01-21 17:54:23 [updated\_at]
=> 2009-01-21 17:54:23 )

    **NOTE**
    ``created\_at``と``updated_at``の値が自動的に設定されることに注目してください！

ビヘイビアの作成側の``Timestampable``ビヘイビアで使うことができるすべてのオプションのリストです:

\|\|~ 名前 \|\|~ デフォルト \|\|~ 説明 \|\| \|\| ``name`` \|\|
``created_at`` \|\| カラムの名前 \|\| \|\| ``type`` \|\| ``timestamp``
\|\| カラムの型 \|\| \|\| ``options`` \|\| ``array()`` \|\|
カラム用の追加オプション \|\| \|\| ``format`` \|\| ``Y-m-d H:i:s`` \|\|
timestampカラム型を使いたくない場合のタイムスタンプのフォーマット。日付はPHPの[http://www.php.net/date
date()]関数で生成される \|\| \|\| ``disabled`` \|\| ``false`` \|\|
作成日を無効にするか \|\| \|\| ``expression`` \|\| ``NOW()`` \|\|
カラムの値を設定するために使用する式 \|\|

作成側では不可能な更新側のビヘイビアで``Timestampable``ビヘイビアで使うことができるすべてのオプションのリストは次の通りです:

\|\|~ 名前 \|\|~ デフォルト\|\|~ 説明 \|\| \|\| ``onInsert``
\|\|``true`` \|\| レコードが最初に挿入されるときに更新日付を設定するか
\|\|

---------
Sluggable
---------

``Sluggable``ビヘイビアは素晴らしい機能の1つでタイトル、題目などのカラムから作成できる人間が読解できるユニークな識別子を保存するためにモデルにカラムを自動的に追加します。これらの値は検索エンジンにフレンドリーなURLに使うことができます。

投稿記事用のわかりやすいURLが欲しいので``Sluggable``ビヘイビアを使うように``BlogPost``モデルを拡張してみましょう:

 // models/BlogPost.php

class BlogPost extends Doctrine\_Record { // ...

::

    public function setUp()
    {
        // ...

        $this->actAs('Sluggable', array(
                'unique'    => true,
                'fields'    => array('title'),
                'canUpdate' => true
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

BlogPost: actAs: # ... Sluggable: unique: true fields: [title]
canUpdate: true # ...

新しい投稿を作成する際に何が起きるのか見てみましょう。slugカラムは自動的に設定されます:

 $blogPost = new BlogPost(); $blogPost->title = 'Test blog post';
$blogPost->body = 'test'; $blogPost->save();

print\_r($blogPost->toArray());

上記の例は次の内容を出力します:

 $ php test.php Array ( [id] => 1 [title] => Test blog post [body] =>
test [version] => 1 [created\_at] => 2009-01-21 17:57:05 [updated\_at]
=> 2009-01-21 17:57:05 [slug] => test-blog-post )

    **NOTE**
    ``title``カラムの値に基づいて``slug``カラムの値が自動的に設定されることに注目してください。スラッグが作成されるとき、デフォルトでは``urlized``が使われます。これはURLにフレンドリーではない文字は削除されホワイトスペースはハイフン(-)に置き換えられます。

uniqueフラグは作成されたスラッグがユニークであることを強制します。ユニークではない場合データベースに保存される前にauto
incrementな整数がスラッグに自動的に追加されます。

``canUpdate``フラグはurlフレンドリーなスラッグを生成する際にユーザーが使用するスラッグを自動的に設定することを許可します。

``Sluggable``ビヘイビアで使うことができるすべてのオプションのリストは次の通りです:

\|\|~ 名前 \|\|~ デフォルト\|\|~ 説明 \|\| \|\| ``name`` \|\| ``slug``
\|\| スラッグカラムの名前 \|\| \|\| ``alias`` \|\| ``null`` \|\|
スラッグカラムのエイリアス \|\| \|\| ``type`` \|\| ``string`` \|\|
スラッグカラムの型 \|\| \|\| ``length`` \|\| ``255`` \|\|
スラッグカラムの長さ \|\| \|\| ``unique`` \|\| ``true`` \|\|
ユニークスラッグの値が強制されるかどうか \|\| \|\| ``options`` \|\|
``array()`` \|\| スラッグカラム用の他のオプション \|\| \|\| ``fields``
\|\| ``array()`` \|\| スラッグの値をビルドするために使用するフィールド
\|\| \|\| ``uniqueBy`` \|\| ``array()`` \|\|
ユニークスラッグを決定するフィールド \|\| \|\| ``uniqueIndex``\|\|
``true`` \|\| ユニークインデックスを作成するかどうか \|\| \|\|
``canUpdate`` \|\| ``false`` \|\| スラッグが更新できるかどうか \|\| \|\|
``builder`` \|\| ``array('Doctrine_Inflector', 'urlize')`` \|\|
スラッグをビルドするために使う``Class::method()`` \|\| \|\|
``indexName`` \|\| ``sluggable`` \|\| 作成するインデックスの名前 \|\|

----
I18n
----

``Doctrine_I18n``パッケージはレコードクラス用の国際化サポートを提供するビヘイビアです。次の例では``title``と``content``の2つのフィールドを持つ``NewsItem``クラスがあります。異なる言語サポートを持つ``title``フィールドを用意したい場合を考えます。これは次のように実現できます:

 class NewsItem extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('body', 'blog'); }

::

    public function setUp()
    {
        $this->actAs('I18n', array(
                'fields' => array('title', 'body')
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 NewsItem: actAs: I18n: fields: [title, body] columns: title:
string(255) body: clob

``I18n``ビヘイビアで使うことができるすべてのオプションのリストは次の通りです:

\|\|~ 名前 \|\|~ デフォルト \|\|~ 説明 \|\| \|\| ``className`` \|\|
``%CLASS%Translation`` \|\| 生成クラスに使う名前のパターン \|\| \|\|
``fields`` \|\| ``array()`` \|\| 国際化するフィールド \|\| \|\| ``type``
\|\| ``string`` \|\| ``lang``カラムの型 \|\| \|\| ``length`` \|\| ``2``
\|\| ``lang``カラムの長さ \|\| \|\| ``options`` \|\| ``array()`` \|\|
``lang``カラム用の他のオプション \|\|

上記のモデルで生成されるSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('NewsItem'));
echo $sql[0] . ""; echo $sql[1];

上記のコードは次のSQLを出力します:

 CREATE TABLE news\_item\_translation (id BIGINT, title VARCHAR(255),
body LONGTEXT, lang CHAR(2), PRIMARY KEY(id, lang)) ENGINE = INNODB
CREATE TABLE news\_item (id BIGINT AUTO\_INCREMENT, PRIMARY KEY(id))
ENGINE = INNODB

    **NOTE**
    ``title``フィールドが``news_item``テーブルに存在しないことに注目してください。翻訳テーブルにあるとメインテーブルで同じフィールドが存在してリソースの無駄遣いになるからです。基本的にDoctrineは常にメインテーブルから翻訳されたフィールドをすべて削除します。

初めて新しい``NewsItem``レコードを初期化するときDoctrineは次の内容をビルドするビヘイビアを初期化します:

1. ``NewsItemTranslation``と呼ばれるRecordクラス
2. ``NewsItemTranslation``と``NewsItem``の双方向なリレーション

``NewsItem``の翻訳を操作する方法を見てみましょう:

 // test.php

// ... $newsItem = new NewsItem(); $newsItem->Translation['en']->title =
'some title'; $newsItem->Translation['en']->body = 'test';
$newsItem->Translation['fi']->title = 'joku otsikko';
$newsItem->Translation['fi']->body = 'test'; $newsItem->save();

print\_r($newsItem->toArray());

上記の例は次の内容を出力します:

 $ php test.php Array ( [id] => 1 [Translation] => Array ( [en] => Array
( [id] => 1 [title] => some title [body] => test [lang] => en ) [fi] =>
Array ( [id] => 1 [title] => joku otsikko [body] => test [lang] => fi )

::

        )

)

翻訳データをどのように読み取るのでしょうか？これは簡単です！すべての項目を見つけて翻訳を終わらせましょう:

 // test.php

// ... $newsItems = Doctrine\_Query::create() ->from('NewsItem n')
->leftJoin('n.Translation t') ->where('t.lang = ?')
->execute(array('fi'));

echo $newsItems[0]->Translation['fi']->title;

上記のコードは次の内容を出力します:

 $ php test.php joku otsikko

---------
NestedSet
---------

``NestedSet``ビヘイビアによってモデルを入れ子集合ツリー構造(nested set
tree
structure)に変換できます。ツリー構造全体を1つのクエリで効率的に読み取ることができます。このビヘイビアはツリーのデータを操作するための素晴らしいインターフェイスも提供します。

例として``Category``モデルを考えてみましょう。カテゴリを階層ツリー構造で編成する必要がある場合は次のようになります:

 // models/Category.php

class Category extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', 255); }

::

    public function setUp()
    {
        $this->actAs('NestedSet', array(
                'hasManyRoots' => true,
                'rootColumnName' => 'root_id'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Category: actAs: NestedSet: hasManyRoots: true rootColumnName: root\_id
columns: name: string(255)

上記のモデルで生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('Category'));
echo $sql[0];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE category (id BIGINT AUTO\_INCREMENT, name VARCHAR(255),
root\_id INT, lft INT, rgt INT, level SMALLINT, PRIMARY KEY(id)) ENGINE
= INNODB

    **NOTE**
    ``root_id``、``lft``、``rgt``と``level``カラムが自動的に追加されることに注目してください。これらのカラムはツリー構造を編成して内部の自動処理に使われます。

ここでは``NestedSet``ビヘイビアの100％を検討しません。とても大きなビヘイビアなので[doc
hierarchical-data 専用の章]があります。

----------
Searchable
----------

``Searchable``ビヘイビアは全文インデックス作成と検索機能を提供します。データベースとファイルの両方のインデックスと検索に使われます。

求人投稿用の``Job``モデルがあり簡単に検索できるようにすることを考えてみましょう:

 // models/Job.php

class Job extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('description', 'clob'); }

::

    public function setUp()
    {
        $this->actAs('Searchable', array(
                'fields' => array('title', 'content')
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 Job: actAs: Searchable: fields: [title, description] columns: title:
string(255) description: clob

上記のモデルで生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('Job')); echo
$sql[0] . ""; echo $sql[1] . ""; echo $sql[2];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE job\_index (id BIGINT, keyword VARCHAR(200), field
VARCHAR(50), position BIGINT, PRIMARY KEY(id, keyword, field, position))
ENGINE = INNODB CREATE TABLE job (id BIGINT AUTO\_INCREMENT, title
VARCHAR(255), description LONGTEXT, PRIMARY KEY(id)) ENGINE = INNODB
ALTER TABLE job\_index ADD FOREIGN KEY (id) REFERENCES job(id) ON UPDATE
CASCADE ON DELETE CASCADE

    **NOTE**
    ``job\_index``テーブルおよび``job``と``job_index``の間の外部キーが自動的に生成されることに注目してください。

``Searchable``ビヘイビアは非常に大きなトピックなので、詳細は[doc
searching :name]の章で見つかります。

------------
Geographical
------------

下記のコードはデモのみです。Geographicalビヘイビアは2つのレコードの間のマイルもしくはキロメータの数値を決定するためのレコードデータで使うことができます。

 // models/Zipcode.php

class Zipcode extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('zipcode', 'string', 255);
$this->hasColumn('city', 'string', 255); $this->hasColumn('state',
'string', 2); $this->hasColumn('county', 'string', 255);
$this->hasColumn('zip\_class', 'string', 255); }

::

    public function setUp()
    {
        $this->actAs('Geographical');
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

Zipcode: actAs: [Geographical] columns: zipcode: string(255) city:
string(255) state: string(2) county: string(255) zip\_class: string(255)

上記のモデルで生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql = Doctrine\_Core::generateSqlFromArray(array('Zipcode'));
echo $sql[0];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE zipcode (id BIGINT AUTO\_INCREMENT, zipcode VARCHAR(255),
city VARCHAR(255), state VARCHAR(2), county VARCHAR(255), zip\_class
VARCHAR(255), latitude DOUBLE, longitude DOUBLE, PRIMARY KEY(id)) ENGINE
= INNODB

    **NOTE**
    Geographicalビヘイビアが2つのレコードの距離を算出するために使われるレコードに``latitude``と``longitude``カラムを自動的に追加することに注目してください。使い方の例は下記の通りです。

最初に2つの異なるzipcodeレコードを読み取りましょう:

 // test.php

// ... $zipcode1 =
Doctrine\_Core::getTable('Zipcode')->findOneByZipcode('37209');
$zipcode2 =
Doctrine\_Core::getTable('Zipcode')->findOneByZipcode('37388');

ビヘイビアが提供する``getDistance()``メソッドを使用してこれら2つのレコードの間の距離を取得できます:

 // test.php

// ... echo :code:`zipcode1->getDistance(`\ zipcode2, $kilometers =
false);

    **NOTE**
    ``getDistance()``メソッドの2番目の引数はキロメーターで距離を返すかどうかです。デフォルトはfalseです。

同じ市にはない50の近いzipcodeを取得してみましょう:

 // test.php

// ... $q = $zipcode1->getDistanceQuery();

:code:`q->orderby('miles asc') ->addWhere(`\ q->getRootAlias() . '.city
!= ?', $zipcode1->city) ->limit(50);

echo $q->getSqlQuery();

``getSql()``への上記の呼び出しは次のSQLクエリを出力します:

 SELECT z.id AS z**id, z.zipcode AS z**zipcode, z.city AS z**city,
z.state AS z**state, z.county AS z**county, z.zip\_class AS
z**zip\_class, z.latitude AS z**latitude, z.longitude AS z**longitude,
((ACOS(SIN(\* PI() / 180) \* SIN(z.latitude \* PI() / 180) + COS(\* PI()
/ 180) \* COS(z.latitude \* PI() / 180) \* COS((- z.longitude) \* PI() /
180)) \* 180 / PI()) \* 60 \* 1.1515) AS z**0, ((ACOS(SIN(\* PI() / 180)
\* SIN(z.latitude \* PI() / 180) + COS(\* PI() / 180) \* COS(z.latitude
\* PI() / 180) \* COS((- z.longitude) \* PI() / 180)) \* 180 / PI()) \*
60 \* 1.1515 \* 1.609344) AS z**1 FROM zipcode z WHERE z.city != ? ORDER
BY z\_\_0 asc LIMIT 50

    **NOTE**
    上記のSQLクエリが書かなかったSQLの束を含んでいることに注目してください。これはレコードの間のマイル数を計算するためにビヘイビアによって自動的に追加されます。

クエリを実行して算出されたマイル数の値を使用します:

 // test.php

// ... $result = $q->execute();

foreach ($result as $zipcode) { echo $zipcode->city . " - " .
$zipcode->miles . ""; // You could also access $zipcode->kilometers }

これをテストするためにサンプルのzipcodeを取得します

``http://www.populardata.com/zip_codes.zip``

csvファイルをダウンロードして次の関数でインポートしてください:

 // test.php

// ... function parseCsvFile($file, $columnheadings = false, $delimiter
= ',', $enclosure = """) { $row = 1; $rows = array();
:code:`handle = fopen(`\ file, 'r');

::

    while (($data = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== FALSE) {

        if (!($columnheadings == false) && ($row == 1)) {
            $headingTexts = $data;
        } elseif (!($columnheadings == false)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$headingTexts[$key]] = $value;
            }
            $rows[] = $data;
        } else {
            $rows[] = $data;
        }
        $row++;
    }

    fclose($handle);
    return $rows;

}

$array = parseCsvFile('zipcodes.csv', false);

foreach ($array as $key => $value) { $zipcode = new Zipcode();
:code:`zipcode->fromArray(`\ value); $zipcode->save(); }

----------
SoftDelete
----------

``SoftDelete``ビヘイビアは``delete()``機能をオーバーライドし``deleted``カラムを追加するとてもシンプルだが大いにおすすめできるモデルビヘイビアです。``delete()``が呼び出されるとき、データベースからレコードを削除する代わりに、削除フラグを1にセットします。下記のコードは``SoftDelete``ビヘイビアでモデルを作る方法です。

 // models/SoftDeleteTest.php

class SoftDeleteTest extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('name', 'string', null, array(
'primary' => true ) ); }

::

    public function setUp()
    {
        $this->actAs('SoftDelete');
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 # schema.yml

SoftDeleteTest: actAs: [SoftDelete] columns: name: type: string(255)
primary: true

上記のモデルによって生成されたSQLをチェックしてみましょう:

 // test.php

// ... $sql =
Doctrine\_Core::generateSqlFromArray(array('SoftDeleteTest')); echo
$sql[0];

上記のコードは次のSQLクエリを出力します:

 CREATE TABLE soft\_delete\_test (name VARCHAR(255), deleted TINYINT(1)
DEFAULT '0' NOT NULL, PRIMARY KEY(name)) ENGINE = INNODB

ビヘイビアを動かしてみましょう。

    **NOTE**
    すべての実行されるクエリのためにDQLコールバックを有功にする必要があります。``SoftDelete``ビヘイビアにおいて追加のWHERE条件で``deleted_at``フラグが設定されているすべてのレコードを除外するSELECT文をフィルタリングするために使われます。

**DQLコールバックを有効にする**

 // bootstrap.php

// ... $manager->setAttribute(Doctrine\_Core::ATTR\_USE\_DQL\_CALLBACKS,
true);

``SoftDelete``の機能を実行できるように新しいレコードを保存します:

 // test.php

// ... $record = new SoftDeleteTest(); $record->name = 'new record';
$record->save();

``delete()``を呼び出すとき``deleted``フラグが``true``にセットされます:

 // test.php

// ... $record->delete();

print\_r($record->toArray());

上記の例は次の内容を出力します:

 $ php test.php Array ( [name] => new record [deleted] => 1 )

また、クエリを行うとき、``deleted``がnullではないレコードは結果から除外されます:

 // test.php

// ... $q = Doctrine\_Query::create() ->from('SoftDeleteTest t');

echo $q->getSqlQuery();

``getSql()``の呼び出しは次のSQLクエリを出力します:

 SELECT s.name AS s**name, s.deleted AS s**deleted FROM
soft\_delete\_test s WHERE (s.deleted = ? OR s.deleted IS NULL)

    **NOTE**
    削除されていないレコードだけを返すためにwhere条件が自動的に追加されたことに注目してください。

クエリを実行する場合:

 // test.php

// ... $count = $q->count(); echo $count;

上記のコード0をechoします。deleteフラグが設定されたので保存されたレコードが除外されます。

=========
入れ子のビヘイビア
=========

versionable、searchable、sluggable、と完全なI18nである完全なwikiデータベースを与える複数のビヘイビアの例です。

 class Wiki extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('content', 'string'); }

::

    public function setUp()
    {
        $options = array('fields' => array('title', 'content'));
        $auditLog = new Doctrine_Template_Versionable($options);
        $search = new Doctrine_Template_Searchable($options);
        $slug = new Doctrine_Template_Sluggable(array(
                'fields' => array('title')
            )
        );
        $i18n = new Doctrine_Template_I18n($options);

        $i18n->addChild($auditLog)
             ->addChild($search)
             ->addChild($slug);

        $this->actAs($i18n);

        $this->actAs('Timestampable');
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 WikiTest: actAs: I18n: fields: [title, content] actAs: Versionable:
fields: [title, content] Searchable: fields: [title, content] Sluggable:
fields: [title] columns: title: string(255) content: string

.. note::

    現在上記の入れ子のビヘイビアは壊れています。開発者は後方互換性を修正するために懸命に取り組んでいます。修正ができたときにアナウンスを行いドキュメントを更新します。

=========
ファイルを生成する
=========

デフォルトではビヘイビアによって生成されるクラスは実行時に評価されクラスを格納するファイルはディスクに書き込まれません。これは設定オプションで変更できます。下記のコードは実行時にクラスを評価する代わりにクラスを生成してファイルに書き込むためのI18nビヘイビアを設定する方法の例です。

 class NewsArticle extends Doctrine\_Record { public function
setTableDefinition() { $this->hasColumn('title', 'string', 255);
$this->hasColumn('body', 'string', 255); $this->hasColumn('author',
'string', 255); }

::

    public function setUp()
    {
        $this->actAs('I18n', array(
                'fields'          => array('title', 'body'),
                'generateFiles'   => true,
                'generatePath'    => '/path/to/generate'
            )
        );
    }

}

YAMLフォーマットでの例は次の通りです。[doc yaml-schema-files
:name]の章でYAMLの詳細を読むことができます:

 NewsArticle: actAs: I18n: fields: [title, body] generateFiles: true
generatePath: /path/to/generate columns: title: string(255) body:
string(255) author: string(255)

コードを生成して実行時に評価するために``[http://www.php.net/eval
eval()]``を使用する代わりにこれでビヘイビアはファイルを生成します。

===========
生成クラスをクエリする
===========

自動生成モデルをクエリしたい場合添付されたモデルを持つモデルがロードされ初期化されることを確認する必要があります。``Doctrine_Core::initializeModels()``スタティックメソッドを使用することでこれをできます。例えば``BlogPost``モデル用の翻訳テーブルにクエリをしたい場合、次のコードを実行する必要があります:

 Doctrine\_Core::initializeModels(array('BlogPost'));

$q = Doctrine\_Query::create() ->from('BlogPostTranslation t')
->where('t.id = ? AND t.lang = ?', array(1, 'en'));

$translations = $q->execute();

.. note::

    モデルが最初にインスタンス化されるまでビヘイビアはインスタンス化されないのでこれは必須です。上記の``initializeModels()``メソッドは渡されたモデルをインスタンス化して情報がロードされたモデルの配列に適切にロードされることを確認します。

===
まとめ
===

Doctrineビヘイビアについて多くのことを学びます。Doctrineに搭載されている素晴らしいビヘイビアの使い方と同じようにモデル用の独自ビヘイビアの書き方を学びます。

[doc searching
Searchable]ビヘイビアを詳しく検討するために移動する準備ができています。これは大きなトピックなので専門の章が用意されています。
