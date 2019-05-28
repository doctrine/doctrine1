==================
マネージャーの例外
==================

接続管理で何かがエラーになると``Doctrine\_Manager_Exception``が投げられます。

 try { $manager->getConnection('unknown'); } catch
(Doctrine\_Manager\_Exception) { // エラーを補足する }

==================
リレーションの例外
==================

リレーションの解析の間にエラーになるとリレーションの例外が投げられます。

==========
接続の例外
==========

データベースレベルで何かがエラーになると接続例外が投げられます。Doctrineは完全にデータベースにポータルなエラーハンドリングを提供します。このことはsqliteやその他のデータベースを使っていようが起きたエラーに関するポータブルなエラーとメッセージを常に得られることを意味します。

 try { $conn->execute('SELECT \* FROM unknowntable'); } catch
(Doctrine\_Connection\_Exception $e) { echo 'Code : ' .
$e->getPortableCode(); echo 'Message : ' . $e->getPortableMessage(); }

============
クエリの例外
============

DQLクエリが無効な場合にクエリが実行されるときに例外が投げられます。

======
まとめ
======

Doctrineの例外を扱い方を学んだので[doc real-world-examples
実際の世界のスキーマ]の章に移動して今日のウェブで見つかる共通のウェブアプリケーションで使われている例を見ます。
