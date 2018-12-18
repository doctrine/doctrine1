==============
要件を確認する
==============

最初にサーバーでDoctrineを実行できることを確認できます。方法は2つあります:

最初に``phpinfo.php``という名前の小さなPHPスクリプトを作り、ウェブサーバーのウェブからアクセスできる場所にアップロードします:

 phpinfo();

``http://localhost/phpinfo.php``
にブラウザでアクセスしてスクリプトを実行します。PHPの構成の詳細情報の一覧が表示されます。PHPのバージョンが//5.2.3//以降でありPDOと必要なドライバがインストールされていることを確認します。

    **NOTE**
    ターミナルからコマンドを実行する方法でもインストールされているPHPが必要な要件を満たしていることを確認できます。次の例で示します。

次のコマンドでPHPのバージョンが5.2.3以降であることを確認してください:

 $ php -v

次のコマンドでPDOとお望みのドライバがインストールされていることを確認します:

 $ php -i

コマンドラインから``phpinfo.php``を実行しても上記と同じ結果を得られます:

 $ php phpinfo.php

================
インストールする
================

現在Doctrineをインストールする方法は4通りあり一覧は次の通りです:

-  SVN (subversion)
-  SVN externals
-  PEARインストーラ
-  PEARパッケージをダウンロードする

SVN
(subversion)を通してDoctrineをダウンロードする方法がお勧めです。この場合は更新が楽です。プロジェクトが既にSVNでバージョン管理している場合、SVN
externalsを使います。

.. tip::

    5分以内でDoctrineを試したいだけなら、サンドボックスのパッケージが推奨されます。次のセクションでサンドボックスパッケージを検討します。

--------------
サンドボックス
--------------

Doctrineは一行もテストを書かずにDoctrineをテストするために設定をまったくしなくてよいDoctrine実装の特別なパッケージも提供します。[http://www.doctrine-project.org/download
ダウンロードページ]から入手できます。

    **NOTE**
    サンドボックスの実装は本番アプリケーションには推奨されません。これはDoctrineの探求と地井さんテストの実行だけを目的としています。

------
SVN
------

SVNとexternalsオプションを通してDoctrineを使うことを大いにお勧めします。SVNから最新のバグ修正を入手できて最良の経験が保証されるのでこのオプションがベストです。

^^^^^^^^^^^^^^^^
インストールする
^^^^^^^^^^^^^^^^

SVNを通してDoctrineをインストールするのはとても簡単です。SVN サーバー:
``http://svn.doctrine-project.org``から任意のバージョンのDoctrineをダウンロードできます。

特定のバージョンをチェックアウトするにはターミナルから次のコマンドを使用します:

 svn co http://svn.doctrine-project.org/branches/1.1 .

SVNクライアントがなければ、下記のリストから1つ選んでください。チェックアウトのオプションを見つけてパスもしくはリポジトリのURLパラメータに
http://svn.doctrine-project.org/1.1
を入力します。Doctrineをチェックアウトするためにユーザー名もしくはパスワードは不要です。

-  [http://tortoisesvn.tigris.org/
   TortoiseSVN]はWindowsアプリケーションでWindows
   Explorerに統合されます。
-  [http://www.apple.com/downloads/macosx/development\_tools/svnx.html
   svnx]はMac OS X GUIのSVNアプリケーションです。
-  [http://www.eclipse.org/ Eclipse][http://subclipse.tigris.org/
   subeclipseプラグイン]を通したSVN統合機能を持ちます。
-  [http://versionsapp.com/ Versions]はMac用のSubversionクライアントです

^^^^^^^^
更新する
^^^^^^^^

インストール作業と同じようにSVNでDoctrineをアップグレードする作業は簡単です。ターミナルから次のコマンドを実行するだけです:

 $ svn update

--------------------------
SVN Externals
--------------------------

プロジェクトで既にSVNでバージョン管理されている場合、DoctrineをインストールするためにSVN
externalsを使うのがお勧めです。

ターミナルでプロジェクトをチェックアウトすることから始めます:

 $ cd /var/www/my\_project

プロジェクトをチェックアウトしたので、ターミナルから次のコマンドを実行してDoctrineをSVN
externalとしてセットアップします:

 $ svn propedit svn:externals lib/vendor

上記のコマンドでエディタを開き次のテキストを押し込んで保存します:

 doctrine http://svn.doctrine-project.com/branches/1.1/lib

svn updateを実行することでDoctrineをインストールできます:

 $ svn update

このコマンドによってDoctrineは次のパスでインストールされます:
``/var/www/my_project/lib/vendor/doctrine``

.. tip::

    SVN externalsへの変更をコミットすることをお忘れなく。

 $ svn commit

--------------------
PEARインストーラ
--------------------

Doctrineはサーバーでインストールとアップデート用のPEARサーバーも提供します。
次のコマンドでDoctrineを簡単にインストールできます:

 $ pear install pear.doctrine-project.org/Doctrine-1.1.x

    **NOTE**
    1.1.xをインストールしたいバージョンに置き換えます。例えば"1.2.1"です。

------------------------------------
Pearパッケージをダウンロードする
------------------------------------

PEARでインストールしたくないもしくはPEARがインストールされていない場合、[http://www.doctrine-project.org/download
公式サイト]からパッケージを手動でダウンロードできます。サーバーにパッケージをダウンロードした後でlinuxでは次のコマンドを利用してこれを展開できます。

 $ tar xzf Doctrine-1.2.1.tgz

========
実装する
========

Doctrineを手に入れたので、アプリケーションでDoctrineを実装する準備ができています。
これはDoctrineを始めることに向けた最初のステップです。

最初に``doctrine_test``という名前のディレクトリを作ります。ここはすべてのテストコードを設置する場所です:

 $ mkdir doctrine\_test $ cd doctrine\_test

--------------------------------------------
Doctrineライブラリをインクルードする
--------------------------------------------

最初に行わなければならないことはアプリケーションで読み込むことができるようにコアクラスを格納する``Doctrine.php``ファイルを見つけることです。``Doctrine.php``ファイルは前のセクションでダウンロードしたDoctrineのlibフォルダに存在します。

Doctrineライブラリを``doctrine\_test``ディレクトリから``doctrine_test/lib/vendor/doctrine``フォルダに移動させる必要があります:

 $ mkdir lib $ mkdir lib/vendor $ mkdir lib/vendor/doctrine $ mv
/path/to/doctrine/lib doctrine

もしくはSVNを利用しているのであれば、externalsを使います:

 $ svn co http://svn.doctrine-project.org/branches/1.1/lib
lib/vendor/doctrine

svn externalsにパスを追加します:

 $ svn propedit svn:externals lib/vendor

テキストエディタを開き次の内容を入力して保存します:

 doctrine http://svn.doctrine-project.org/branches/1.1/lib

SVN updateを行うとDoctrineのライブラリは更新されます:

 $ svn update lib/vendor

------------------------------------------------
Doctrineの基底クラスをrequireする
------------------------------------------------

Doctrineとすべての設定をブートストラップするためのPHPコードが必要です。
``bootstrap.php``という名前のファイルを作り次のコードをファイルに加えます:

 // bootstrap.php

/\*\* \* Bootstrap Doctrine.php, register autoloader specify \*
configuration attributes and load models. \*/

require\_once(dirname(**FILE**) . '/lib/vendor/doctrine/Doctrine.php');

------------------------
オートローダーを登録する
------------------------

``Doctrine``クラスの準備が終わったので、ブートストラップファイルでクラスのオートローダー関数を登録する必要があります:

 // bootstrap.php

// ... spl\_autoload\_register(array('Doctrine', 'autoload'));

``Doctrine_Manager``シングルトンインスタンスも作り``$manager``という名前の変数に割り当てます:

 // bootstrap.php

// ... $manager = Doctrine\_Manager::getInstance();

^^^^^^^^^^^^^^^^^^^^^^
オートロード機能の説明
^^^^^^^^^^^^^^^^^^^^^^

    **NOTE** [http://www.php.net/spl\_autoload\_register
    PHPの公式サイト]でオートロード機能の使い方がわかります。オートローダーを利用することで予めロードされたすべてのクラスの代わりにリクエストされたクラスを遅延ロードできます。これはパフォーマンスの面で大きな恩恵があります。

Doctrineのオートローダーの動作方法はシンプルです。クラスの名前とパスは相対的なので、名前に基づいてDoctrineクラスへのパスを決定できます。

``Doctrine\_Some_Class``という名前のクラスをインスタンス化することを考えてみましょう:

 $class = new Doctrine\_Some\_Class();

上記のコードは``Doctrine::autoload()``関数の呼び出しを実行しインスタンス化するクラスの名前を渡します。クラスの名前の文字列は操作されパスに変換され読み込まれます。下記はクラスの発見と読み込み方法を示す疑似コードです:

 class Doctrine { public function autoload($className) { $classPath =
str\_replace('\_', '/', $className) . '.php'; $path =
'/path/to/doctrine/' . :code:`classPath; require_once(`\ path); return
true; } }

上記の例では``Doctrine\_Some_Class``は``/path/to/doctrine/Doctrine/Some/Class.php``で見つかります。

    **NOTE**
    もちろん実際の``Doctrine::autoload()``メソッドはもっと複雑でファイルの存在を確認するエラーチェック機能を持ちますが上記のコードはどのように動作するのかを実演するためにあります。

------------------------
ブートストラップファイル
------------------------

.. tip::

   
    後の章とセクションでこのブートストラップクラスを使うので作ってください！

作成したブートストラップファイルの内容は次のようになります:

 // bootstrap.php

/\*\* \* Bootstrap Doctrine.php, register autoloader specify \*
configuration attributes and load models. \*/

require\_once(dirname(**FILE**) . '/lib/vendor/doctrine/Doctrine.php');
spl\_autoload\_register(array('Doctrine', 'autoload')); $manager =
Doctrine\_Manager::getInstance();

この新しいブートストラップファイルは実装の変更を行う場所でありまた段階的にDoctrineの使い方を学ぶのでこのファイルはこの本で何度も参照されます。

    **NOTE** 上記で説明された接続属性はDoctrineの機能です。[doc
    configuration
    :name]の章で属性の詳細とこれらのゲッター/セッターを学びます。

----------------
テストスクリプト
----------------

Doctrineの機能に関して学ぶので様々なテストを実行するために利用できるシンプルなテストスクリプトを作りましょう。

``doctrine_test``ディレクトリの中で``test.php``という名前の新しいファイルを作成し内部に次の内容を置きます:

 // test.php

require\_once('bootstrap.php');

echo Doctrine::getPath();

これでコマンドラインからテストスクリプトを実行できます。これはこの章全体でDoctrineでテストを実行する方法です。動作しているか確認してください！Doctrineがインストールされている場所のパスが出力されます。

 $ php test.php /path/to/doctrine/lib

======
まとめ
======

ふぅ！実際にコードに取り組んだ最初の章でした。ご覧の通り、最初にサーバーがDoctrineを実際に実行できることをチェックできました。それから異なる複数の方法でDoctrineのダウンロードとインストールができることを学びました。最後にこの本の残りの章で練習するために使う小さなテスト環境をセットアップすることでDoctrineを実装する方法を学びました。

[doc introduction-to-connections
:name]の章に移動してDoctrineの接続を初体験しましょう。
