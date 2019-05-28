..  vim: set ts=4 sw=4 tw=79 :

**********
Extensions
**********

Doctrineエクステンションは任意のプロジェクトに入れて有効にできる再利用可能なDoctrineエクステンションを作成する方法です。エクステンションはコードの命名や、オートロードなどDoctrineの標準に従う単なるコードです。

エクステンションを使うには最初にどこにエクステンションがあるのかDoctrineにわかるように設定しなければなりません:

 Doctrine\_Core::setExtensionsPath('/path/to/extensions');

SVNから既存のエクステンションをチェックアウトしてみましょう。ソートの上げ下げを提供するモデルのビヘイビアを搭載する``Sortable``エクステンションを見てみましょう。

 $ svn co
http://svn.doctrine-project.org/extensions/Sortable/branches/1.2-1.0/
/path/to/extensions/Sortable

``/path/to/extensions/Sortable``を見てみると次のようなディレクトリ構造を見ることになります:

 Sortable/ lib/ Doctrine/ Template/ Listener/ Sortable.php Sortable.php
tests/ run.php Template/ SortableTestCase.php

このエクステンションがあなたのマシンで動くことを確認するためにエクステンションのテストスイートを実行します。必要なのは``DOCTRINE_DIR``環境変数をセットすることです。

 $ export DOCTRINE\_DIR=/path/to/doctrine

    **NOTE**
    上記のDoctrineへのパスはlibフォルダーではなくメインフォルダーへのパスでなければなりません。テストを実行するにはDoctrineを含めた``tests``ディレクトリにアクセスできなければなりません。

``Sortable``エクステンションのテストを実行することが可能です:

 $ cd /path/to/extensions/Sortable/tests $ php run.php

次のようなテストが成功したことを示すテストの出力が表示されます:

 Doctrine Unit Tests ===================
Doctrine\_Template\_Sortable\_TestCase.............................................passed

Tested: 1 test cases. Successes: 26 passes. Failures: 0 fails. Number of
new Failures: 0 Number of fixed Failures: 0

Tests ran in 1 seconds and used 13024.9414062 KB of memory

プロジェクトでエクステンションを使いたい場合Doctrineでエクステンションを登録しエクステンションのオートロードメカニズムをセットアップする必要があります。

最初にエクステンションのオートロードをセットアップしましょう。

 // bootstrap.php

// ... spl\_autoload\_register(array('Doctrine', 'extensionsAutoload'));

これでエクステンションを登録したのでエクステンション内部のクラスがオートロードされます。

 $manager->registerExtension('Sortable');

    **NOTE**
    異なる場所からエクステンションを登録する必要がある場合、``registerExtension()``メソッドの2番目の引数でエクステンションディレクトリへのフルパスを指定します。
