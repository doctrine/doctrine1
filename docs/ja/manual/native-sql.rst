========
はじめに
========

``Doctrine\_RawSql``は生のSQLクエリを構築するための便利なインターフェイスを提供します。``Doctrine\_Query``と同じように、``Doctrine_RawSql``は配列とオブジェクト取得のための手段を提供します。

Oracleでクエリヒントもしくは``CONNECT``キーワードのようなデータベース固有の機能を活用したいときに生のSQLを使う方法は便利です。

``Doctrine_RawSql``オブジェクトの作成は簡単です:

 // test.php

// ... $q = new Doctrine\_RawSql();

オプションとして接続パラメータが与えられた場合``Doctrine_Connection``のインスタンスが受け取られます。[doc
connections :name]の章で接続の作成方法を学びます。

 // test.php

// ... $conn = Doctrine\_Manager::connection();
:code:`q = new Doctrine_RawSql(`\ conn);

====================
コンポーネントクエリ
====================

``Doctrine_RawSql``を使う際に最初に注意しなければならないことは波かっこ({})で選択するフィールドを置かなければならないことです。またすべての選択されたコンポーネントに対して``addComponent()``を呼び出さなければなりません。

次の例はこれらの使い方を明確にします:

 // test.php

// ... $q->select('{u.\*}') ->from('user u') ->addComponent('u',
'User');

$users = :code:`q->execute(); print_r(`\ users->toArray());

    **NOTE**
    ``addComponent()``メソッドを使用して``user``テーブルは``User``クラスにバインドしていることに注目してください。

次のことに注意を払ってください:

-  フィールドは波かっこで囲まなければならない。
-  それぞれの選択されたテーブルに対して``addComponent()``コールが1つ存在しなければならない。

================================
複数のコンポーネントから取得する
================================

複数のコンポーネントから取得するとき``addComponent()``コールは少し複雑になります。どのテーブルがどのコンポーネントにバインドされるのか伝えるだけでなく、どのコンポーネントがどれに所属するのかパーサーに伝えなければならないからです。

次の例においてすべての``users``と``phonenumbers``を取得します。最初に新しい``Doctrine_RawSql``オブジェクトを作成し選択する部分を追加します:

 // test.php

// ... $q = new Doctrine\_RawSql(); $q->select('{u.*}, {p.*}');

``FROM``の部分を``user``テーブルからphonenumberテーブルへのJOINクエリに追加してすべてを一緒にマッピングする必要があります:

 // test.php

// ... $q->from('user u LEFT JOIN phonenumber p ON u.id = p.user\_id')

``user``テーブルを``User``クラスにバインドし``User``クラスのエイリアスとして``u``も追加します。``User``クラスを参照するときにこのエイリアスが使われます。

 // test.php

// ... $q->addComponent('u', 'User u');

``phonenumber``テーブルにバインドされる別のテーブルを追加します:

 // test.php

// ... $q->addComponent('p', 'u.Phonenumbers p');

    **NOTE**
    ``Phonenumber``クラスはUserの電話番号を指し示していることに注意してください。

あたかも``Doctrine\_Query``オブジェクトを実行するように``Doctrine_RawSql``クエリを実行できます:

 // test.php

// ... $users = :code:`q->execute(); echo get_class(`\ users) . ""; echo
get\_class(:code:`users[0]) . "\n"; echo get_class(`\ users[0]['Phonenumbers'][0])
. "";

上記の例が実行されるときに次の内容が出力されます:

 $ php test.php Doctrine\_Collection User Phonenumber

======
まとめ
======

この章はすぐに役に立つかもしれませんしそうでないかもしれません。多くの場合Doctrine
Query
Languageは複雑なデータセットを読み取るために十分です。しかし``Doctrine\_Query``ができる範囲を超えるものが必要であれば``Doctrine_RawSql``が役立ちます。

以前の章でたくさんのYAMLスキーマファイルとその例を見てきましたが独自のものを書く練習は十分ではありません。次の章ではモデルを[doc
yaml-schema-files
YAMLスキーマファイル]として維持する詳細な方法を説明します。
