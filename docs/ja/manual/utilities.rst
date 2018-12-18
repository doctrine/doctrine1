==========
ページ分割
==========

--------
はじめに
--------

実際の世界のアプリケーションでは、データベースからコンテンツを表示するのは共通のタスクです。またコンテンツが何千もの項目を含む検索結果である場合を想像してください。うたがいなく、巨大なリストになり、メモリーの消費量が多くなりユーザーは正しい項目を見つけづらくなります。この問題に対してコンテンツ表示の編成が必要でページ分割が手助けになります。

Doctrineは高度で柔軟なページャーパッケージを実装します。これによってリストを複数のページに分割できるだけでなく、ページリンクのレイアウトもコントロールできます。この章では、ページャーオブジェクトの作り方、ページャースタイルをコントロール仕方を学び、最後にページャーレイアウトオブジェクト
- Doctrineの強力なページリンク表示機能の概要を見ます。

----------------
ページャーを扱う
----------------

クエリのページ分割はクエリ自身と同じぐらいシンプルで効率的にできます。``Doctrine_Pager``はクエリを処理してページ分割することを担います。次の小さなピースのコードで確認しましょう:

 // 初期値を定義する $currentPage = 1; $resultsPerPage = 50;

// ページャーオブジェクトを作成する $pager = new Doctrine\_Pager(
Doctrine\_Query::create() ->from( 'User u' ) ->leftJoin( 'u.Group g' )
->orderby( 'u.username ASC' ), $currentPage, // リクエストの現在のページ
$resultsPerPage // (オプション)ページごとの結果数。デフォルトは25 );

この場所までは、このコードは古い``Doctrine\_Query``オブジェクトと同じです。唯一の違いは新しい2つの引数が存在することです。これら2の引数に加えて古いクエリオブジェクトは``Doctrine\_Pager``オブジェクトによってカプセル化されます。この段階では、``Doctrine_Pager``はページ分割をコントロールするために必要な基本データを定義します。ページャーの実際のステータスを知りたい場合、行うべきことはこれが既に実行されたかどうかチェックすることです:

 $pager->getExecuted();

``Doctrine\_Pager``によって提供される任意のメソッドにアクセスしようとする場合、Pagerがまだ実行されなかったことを報告する``Doctrine\_Pager\_Exception``が投げられるのを経験することになります。実行されたとき、``Doctrine_Pager``は情報を検索する強力なメソッドを提供します。APIの使い方はこのトピックの最後に並べてあります。

クエリを実行するには、プロセスは現存する``Doctrine_Query``実行呼び出しと似ています。オプションのパラメータを含む構文の完全な例は次の通りです:

 $items = :code:`pager->execute([`\ args = array() [, $fetchType =
null]]);

foreach ($items as $item) { // ... }

レコードクエリがカウンタークエリと異なる特別なケースがあります。この状況に対応するために、``Doctrine_Pager``にはカウントしてから実行できるようにするメソッドがあります。最初に行わなければならないのはカウントクエリを定義することです:

 :code:`pager->setCountQuery(`\ query [, $params = null]);

// ...

$rs = $pager->execute();

``setCountQuery``の最初のパラメータは有効な``Doctrine_Query``オブジェクトかDQL文字列です。2番目の引数はカウンタークエリに送信されるオプションパラメータです。このパラメータを定義しないので、後で``setCountQueryParams``を呼び出して定義することができます:

 :code:`pager->setCountQueryParams([`\ params = array() [, $append =
false]]);

このメソッドは2つのパラメータを受けとります。最初のパラメータはカウントパラメータに送信されるもので2番目のパラメータは``:code:`params``がリストに追加されるもしくはカウントクエリパラメータがオーバーライドされるかどうかです。デフォルトのビヘイビアはリストをオーバーライドします。カウントクエリに関して最後に言うことは、カウントクエリ用のパラメータを定義しない場合、```\ pager->execute()``の呼び出しで定義するパラメータを送り出すことができます。

カウントクエリは常にアクセスできます。これを定義して``$pager->getCountQuery()``を呼び出す場合、"取得(fetcher)"クエリが返されます。

``Doctrine_Pager``が提供する他の機能にアクセスする必要がある場合、APIを通してアクセスできます:

 // Pagerが既に実行されたかのチェックを返す $pager->getExecuted();

// クエリ検索で見つかるアイテムの合計数を返す $pager->getNumResults();

// 最初のページを返す(常に1) $pager->getFirstPage();

// ページの合計数を返す $pager->getLastPage();

// 現在のページを返す $pager->getPage();

//
現在の新しいページを定義する(実行を再度呼び出してオフセットと値を調整する必要がある)
:code:`pager->setPage(`\ page);

// 次のページを返す $pager->getNextPage();

// 前のページを返す $pager->getPreviousPage();

// 現在のページの最初のインデックスを返す $pager->getFirstIndice();

// 現在のページの最後のインデックスを返す $pager->getLastIndice();

// ページ分割をする必要がある場合はtrueそうでなければfalseを返す
$pager->haveToPaginate();

// ページごとの最大数を返す $pager->getMaxPerPage();

//
ページごとのレコードの最大数を定義する(再度呼び出してオフセットと値を調整する必要がある)
:code:`pager->setMaxPerPage(`\ maxPerPage);

// 現在のページのアイテム数を返す $pager->getResultsInPage();

//
カウント結果をページャーにするために使われるDoctrine\_Queryオブジェクトを返す
$pager->getCountQuery();

// ページャーによって使われるカウンタクエリを定義する
:code:`pager->setCountQuery(`\ query, $params = null);

//
Doctrine\_Queryカウントによって使われるパラメータを返す(パラメータが定義されていない場合$defaultParamsを返す)
:code:`pager->getCountQueryParams(`\ defaultParams = array());

// Doctrine\_Queryカウンタによって使われるパラメータを定義する
:code:`pager->setCountQueryParams(`\ params = array(), $append = false);

// Doctrine\_Queryオブジェクトを返す $pager->getQuery();

// 関連するDoctrine\_Pager\_Range\_\* インスタンスを返す
:code:`pager->getRange(`\ rangeStyle, $options = array());

--------------------------------
レンジスタイルをコントロールする
--------------------------------

シンプルなページ分割では不十分なケースがあります。1つの例はページリンクのリストを書くときです。ページャーを越えるより強力なコントロール機能を有効にするために、レンジを作ることを可能にするページャーパッケージの小さなサブセットがあります。

現在Doctrineは2種類(2つのスタイル)のレンジ:
スライディング(``Doctrine\_Pager\_Range\_Sliding``)とジャンピング(``Doctrine\_Pager\_Range_Jumping``)を実装します。

^^^^^^^^^^^^^^
スライディング
^^^^^^^^^^^^^^

スライディングページレンジスタイルは、ページレンジは現在のページでスムーズに移動します。最初と最後のページのレンジ以外、現在のページは常に真ん中です。5つのアイテムのチャンクの長さでどのように動作するのか確認してください:

 Listing 1 2 3 4 5 6 7 8 9 10 11 12 13 14 Page 1: o-------\| Page 2:
\|-o-----\| Page 3: \|---o---\| Page 4: \|---o---\| Page 5: \|---o---\|
Page 6: \|---o---\| Page 7: \|---o---\| Page 8: \|---o---\|

^^^^^^^^^^^^
ジャンピング
^^^^^^^^^^^^

ジャンピングページレンジスタイルでは、ページリンクのレンジは常に"フレーム"の固定長の1つです:
1-5、6-10、11-15など。

 Listing 1 2 3 4 5 6 7 8 9 10 11 12 13 14 Page 1: o-------\| Page 2:
\|-o-----\| Page 3: \|---o---\| Page 4: \|-----o-\| Page 5: \|-------o
Page 6: o---------\| Page 7: \|-o-------\| Page 8: \|---o-----\|

ページレンジのスタイルの違いがわかったので、使い方を学びましょう:

 $pagerRange = new Doctrine\_Pager\_Range\_Sliding( array( 'chunk' => 5
// チャンクの長さ ), $pager //
以前のトピックで作り方を学んだDoctrine\_Pagerオブジェクト );

代わりに、次のコードを使うこともできます:

 $pagerRange = $pager->getRange( 'Sliding', array( 'chunk' => 5 ) );

``Doctrine_Pager``の代わりにこのオブジェクトを使う利点は何でしょうか？たった1つです;
現在のページ周辺のレンジを読み取ることができることです。

次の例を見てみましょう:

 // 現在のページ周辺のレンジを読み取る //
この例では、スライディングスタイルを使用しページ1にいる $pages =
$pager\_range->rangeAroundPage();

// Outputs: [1][2][3][4][5] echo '['. implode('][', $pages) .']';

レンジオブジェクトの範囲内で``Doctrine\_Pager``をビルドする場合、APIによって``Doctrine\_Pager_Range``サブクラスのインスタンスに関連する情報を読み取ることができます:

 // このPager\_Rangeに関連するページャーを返す
$pager\_range->getPager();

// 新しいDoctrine\_Pagerを定義する(自動的なprotectされたcall
\_initializedメソッド) :code:`pager_range->setPager(`\ pager);

// 現在のPager\_Rangeに割り当てられたオプションを返す
$pager\_range->getOptions();

// カスタムのDoctrine\_Pager\_Range実装のオフセットオプションを返す
:code:`pager_range->getOption(`\ option);

// 渡されたページがレンジの中にあるかチェックする
:code:`pager_range->isInRange(`\ page);

// 現在のページ周辺のレンジを返す //
($pager\_rangeインスタンスに関連するDoctrine\_Pagerから取得)
$pager\_range->rangeAroundPage();

--------------------------------
ページャーによる高度なレイアウト
--------------------------------

これまで、ページ分割と現在のページ周辺のレンジを読み取る方法を学びました。ページリンク生成を含むビジネスロジックを抽象化するために、``Doctrine\_Pager_Layout``と呼ばれる強力なコンポーネントがあります。このコンポーネントのメインのアイディアはPHPロジックを抽象化してHTMLをDoctrineの開発者に定義させることです。

``Doctrine\_Pager_Layout``は必須の引数を3つ受け取ります: a
``Doctrine\_Pager``インスタンス、``Doctrine\_Pager\_Range``サブクラスインスタンスとテンプレートの{%url}マスクとして割り当てられるURLを含む文字列です。ご覧の通り、``Doctrine\_Pager_Layout``の"変数"が2種類あります:

^^^^^^
マスク
^^^^^^

マスクはテンプレート内部で置き換えるものとして定義される文字列のピースです。これらは**{%mask\_name}**として定義されオプションで定義するものもしくは``Doctrine\_Pager_Layout``コンポーネントによって内部で定義されたものによって置き換えられます。現在、これらは内部マスクとして利用可能です:

-  **{%page}**はページ番号、すなわち、page\_numberを保有しますが、別のマスクもしくは値のように振る舞う``addMaskReplacement()``で上書きできます。
-  **{%page\_number}**は現在のページ番号を保存しますが、上書き可能ではありません
-  **{%url}**は``setTemplate()``と``setSelectedTemplate()``メソッドでのみ利用可能です。コンストラクタで定義され、処理されたURLを保有します

^^^^^^^^^^^^
テンプレート
^^^^^^^^^^^^

その名の通り、これはHTMLのスケルトンもしくはその他のスケルトンで``Doctrine\_Pager_Range::rangeAroundPage()``サブクラスによって返されるそれぞれのページに適用されるその他のリソースです。定義できるテンプレートは3種類あります:

-  ``setTemplate()``は``Doctrine\_Pager_Range::rangeAroundPage()``によって返されるすべてのページで使われるテンプレートを定義します。
-  処理されるページが現在のページであるときに``setSelectedTemplate()``テンプレートは適用されます。何も定義されていない場合(空白文字もしくは定義無し)、``setTemplate()``で定義したテンプレートが使われます
-  ``setSeparatorTemplate()``セパレータテンプレートはそれぞれの処理されたページの間で適用される文字列です。最初のコールの前と最後のコールの後では含まれません。このメソッドの定義されたテンプレートはオプションによって影響を受けますが、マスクは処理できません。

``Doctrine\_Pager_Layout``とこのコンポーネント周囲のタイプの作り方を理解したので、基本的な使い方を見てみましょう:

ページャーレイアウトの作り方は簡単です:

 $pagerLayout = new Doctrine\_Pager\_Layout( new Doctrine\_Pager(
Doctrine\_Query::create() ->from( 'User u' ) ->leftJoin( 'u.Group g' )
->orderby( 'u.username ASC' ), $currentPage, $resultsPerPage ), new
Doctrine\_Pager\_Range\_Sliding(array( 'chunk' => 5 )),
'http://wwww.domain.com/app/User/list/page,{%page\_number}' );

ページリンク作成のためにテンプレートを割り当てます:

 $pagerLayout->setTemplate('[{%page}]');
$pagerLayout->setSelectedTemplate('[{%page}]');

// Doctrine\_Pagerインスタンスを読み取る $pager =
$pagerLayout->getPager();

// ユーザーを取得する $users = $pager->execute(); // これも可能！

// ページリンクを表示する // 表示: [1][2][3][4][5] //
$currentPageを除いて、すべてのページでリンクがつく(この例では、ページ1)
$pagerLayout->display();

このソースを説明すると、最初の部分はページャーレイアウトのインスタンスを作成します。2番目に、すべてのページと現在のページ用のテンプレートを定義します。最後の部分では、``Doctrine\_Pager``オブジェクトを読み取りクエリを実行し、変数``$users``を返します。最後のっ部分はオプションのマスク無しでディスプレイヤーを呼び出します。これは``Doctrine\_Pager_Range::rangeAroundPage()``サブクラスで見つかるすべてのページにテンプレートを適用します。

ご覧の通り、内部マスク以外に他のマスクを使う必要はありません。既存のアプリケーションでUsersを検索機能を実装することを考えてみましょう。またページャーレイアウトでこの機能をサポートする必要があるとします。我々のケースを簡略化するために、検索パラメータは"search"と名付け、スーパーグローバル配列``$\_GET``を通して受け取ります。他のページに送信できるようにするために、最初に行う必要のある変更は``Doctrine_Query``オブジェクトとURLを調整することです。

ページャーレイアウトを作成する:


:code:`pagerLayout = new Doctrine_Pager_Layout( new Doctrine_Pager( Doctrine_Query::create() ->from( 'User u' ) ->leftJoin( 'u.Group g' ) ->where('LOWER(u.username) LIKE LOWER(?)', array( '%'.`\ \_GET['search'].'%'
) ) ->orderby( 'u.username ASC' ), $currentPage, $resultsPerPage ), new
Doctrine\_Pager\_Range\_Sliding(array( 'chunk' => 5 )),
'http://wwww.domain.com/app/User/list/page,{%page\_number}?search={%search}'
);

コードを確認して``{%search}``と呼ばれる新しいマスクを追加したことに注目してください。後の段階で処理するテンプレートにこのマスクを送る必要があります。変更せずに、以前定義したように、テンプレートを割り当てます。そして、クエリの実行を変更する必要もありません。

ページリンク作成のためにテンプレートを割り当てます:

 $pagerLayout->setTemplate('[{%page}]');
$pagerLayout->setSelectedTemplate('[{%page}]');

// Fetching users $users = $pagerLayout->execute();

foreach ($users as $user) { // ... }

``display()``メソッドは作成したカスタムのマスクを定義する場所ですこのメソッドは2つのオプション引数を受け取ります:
オプションマスクの1つの配列でスクリーンに出力される代わりに返される出力です。我々の場合、新しいマスクである``{%search``}を定義する必要があります。このマスクはスーパーグローバル配列``$_GET``のsearchオフセットです。このマスクはURLとして送られるので、エンコードする必要があります。カスタムのマスクは「キー
=>
値」のペアで定義されます。ですので必要なコードはオフセットと置き換える値で配列を定義することです:

 // Displaying page links
:code:`pagerLayout->display( array( 'search' => urlencode(`\ \_GET['search'])
) );

``Doctrine\_Pager_Layout``コンポーネントは定義されたリソースへのアクセサを提供します。ページャーとページャレンジを変数として定義してページャーレイアウトを送る必要はありません。これらのインスタンスは次のアクセサによって読み取られます:

 // Pager\_Layoutに関連するPagerを返す $pagerLayout->getPager();

// Pager\_Layoutに関連するPager\_Rangeを返す
$pagerLayout->getPagerRange();

// Pager\_Layoutに関連するURLマスクを返す $pagerLayout->getUrlMask();

// Pager\_Layoutに関連するテンプレートを返す
$pagerLayout->getTemplate();

// Pager\_Layoutに関連する現在のページテンプレートを返す
$pagerLayout->getSelectedTemplate();

// それぞれのページに適用されるSeparatorテンプレートを定義する
:code:`pagerLayout->setSeparatorTemplate(`\ separatorTemplate);

// Pager\_Layoutに関連する現在のページテンプレートを返す
$pagerLayout->getSeparatorTemplate();

// Pagerインスタンスを読み取らずにクエリを実行するハンディメソッド
:code:`pagerLayout->execute(`\ params = array(), $hydrationMode = null);

カスタムのレイアウト作成機能を作るために``Doctrine\_Pager_Layout``を継承したい場合、利用可能な他のメソッドはたくさんあります。次のセクションでこれらのメソッドを見ます。

--------------------------------------
ページャーレイアウトをカスタマイズする
--------------------------------------

``Doctrine\_Pager_Layout``は本当に良い仕事をしますが、ときに十分ではないことがあります。次のようなページ分割のレイアウトを作らなければならない状況を考えてみましょう:

<< < 1 2 3 4 5 > >>

現在、生の``Doctrine\_Pager_Layout``では不可能ですが、このクラスを継承して利用可能なメソッドを使えば実現可能です。基底レイアウトクラスは独自の実装を作成するために使われるメソッドを提供します。内容は次の通りです:

 // $thisはDoctrine\_Pager\_Layoutのインスタンスを参照する

//
マスクの置き換えを定義する。テンプレートを解析するとき、置き換えマスクを
// 新しいもの(もしくは値)に変換する。即座にマスクを変更できます
:code:`this->addMaskReplacement(`\ oldMask, $newMask, $asValue = false);

// マスク置き換えを削除する :code:`this->removeMaskReplacement(`\ oldMask);

// すべてのマスク置き換えを削除する $this->cleanMaskReplacements();

// テンプレートを解析し処理されたページの文字列を返す
:code:`this->processPage(`\ options = array()); //
少なくとも配列$optionsのpage\_numberで必要

// Protectされたメソッドであるが、とても便利

// 渡されたページのテンプレートを解析し処理されたテンプレートを返す
:code:`this->_parseTemplate(`\ options = array());

//
送られたオプションによって正しいテンプレートを返すようにURLマスクを解析する
// 既に割り当てられたマスク置き換えを処理する
:code:`this->_parseUrlTemplate(`\ options = array());

// 与えられたページのマスク置き換えを解析する
:code:`this->_parseReplacementsTemplate(`\ options = array());

// 与えられたページのURLマスクを解析し処理されたURLを返す
:code:`this->_parseUrl(`\ options = array());

//
置き換え予定のマスクを新しいマスク/値に変更して、マスク置き換えを解析する
:code:`this->_parseMaskReplacements(`\ str);

``Doctrine\_Pager_Layout``を継承するとき便利で小さなメソッドがあるので、実装されたクラスを見てみましょう:

 class PagerLayoutWithArrows extends Doctrine\_Pager\_Layout { public
function display($options = array(), $return = false) { $pager =
$this->getPager(); $str = '';

::

        // 最初のページ
        $this->addMaskReplacement('page', '&laquo;', true);
        $options['page_number'] = $pager->getFirstPage();
        $str .= $this->processPage($options);

        // 以前のページ
        $this->addMaskReplacement('page', '&lsaquo;', true);
        $options['page_number'] = $pager->getPreviousPage();
        $str .= $this->processPage($options);

        // ページの一覧
        $this->removeMaskReplacement('page');
        $str .= parent::display($options, true);

        // 次のページ
        $this->addMaskReplacement('page', '&rsaquo;', true);
        $options['page_number'] = $pager->getNextPage();
        $str .= $this->processPage($options);

        // 最後のページ
        $this->addMaskReplacement('page', '&raquo;', true);
        $options['page_number'] = $pager->getLastPage();
        $str .= $this->processPage($options);

        // スクリーンに表示する代わりに値を返すことが可能
        if ($return) {
            return $str;
        }

        echo $str;
    }

}

ご覧の通り、<<、<、>と>>のアイテムを手動で処理しなければなりません。生の値を設定することで**{%page}**マスクをオーバーライドします(生の値は3番目のパラメータをtrueとして設定します)。それからページを処理する必須情報のみを定義しこれを呼び出します。戻り値は文字列として処理されたテンプレートです。これをカスタムボタンにします。

これで全体的に異なる状況をサポートでいます。Doctrineは透過的なフレームワークですが、多くのユーザーはsymfonyと一緒に使います。``Doctrine\_Pager``とサブクラスはsymfonyと100%互換性がありますが、``Doctrine\_Pager\_Layout``はsymfonyの``link\_to``ヘルパー関数と連携するために調整が必要です。``Doctrine\_Pager_Layout``でこれを使うことができるようにするにはこのクラスを継承しカスタムプロセッサーを追加しなければなりません。例として(symfonyと連携させる場合)、**{link\_to}...{/link\_to}**をテンプレートプロセッサーとして使います。継承クラスとsymfonyでの使い方は次の通りです:

 class sfDoctrinePagerLayout extends Doctrine\_Pager\_Layout { public
function \_\_construct($pager, $pagerRange,
:code:`urlMask) { sfLoader::loadHelpers(array('Url', 'Tag')); parent::__construct(`\ pager,
$pagerRange, $urlMask); }

::

    protected function _parseTemplate($options = array())
    {
        $str = parent::_parseTemplate($options);

        return preg_replace(
            '/\{link_to\}(.*?)\{\/link_to\}/', link_to('$1', $this->_parseUrl($options)), $str
        );
    }

}

使い方:

 $pagerLayout = new sfDoctrinePagerLayout( $pager, new
Doctrine\_Pager\_Range\_Sliding(array('chunk' => 5)),
'@hostHistoryList?page={%page\_number}' );

$pagerLayout->setTemplate('[{link\_to}{%page}{/link\_to}]');

============
Facade
============

------------------------
データベースの作成と削除
------------------------

Doctrineは接続からデータベースを作成したり削除する機能を提供します。これを使うためのしかけはDoctrineの接続名がデータベースの名前でなければならないことです。これが必須なのはPDOは接続するデータベースの名前を読み取るメソッドを提供しないことによります。データベースの作成と削除をできるようにするにはDoctrine自身がデータベースの名前を認識できなければなりません。

----------------------
コンビニエンスメソッド
----------------------

Doctrineはメインクラスで利用可能なスタティックなコンビニエンスメソッドを提供します。これらのメソッドはDoctrineの最もよく使われる複数の機能を1つのメソッドで実行します。これらのメソッドの大半は``Doctrine\_Task``システムを使用します。これらのタスクは``Doctrine_Cli``からも実行されます。

 // デバッグモードをon/offに切り替えこれがon/offであるかチェックする
Doctrine\_Core::debug(true);

if (Doctrine\_Core::debug() { echo 'debugging is on'; } else { echo
'debugging is off'; }

// Doctrineライブラリへのパスを取得する $path =
Doctrine\_Core::getPath();

//
Doctrineライブラリへのパスがデフォルトの位置ではない場合パスをセットする
Doctrine\_Core::setPath('/path/to/doctrine/libs');

// Doctrineと連携させるためにモデルをロードする //
発見されロードされたDoctrine\_Recordsの配列を返す
:code:`models = Doctrine_Core::loadModels('/path/to/models', Doctrine_CoreMODEL_LOADING_CONSERVATIVE); // or Doctrine_Core::MODEL_LOADING_AGGRESSIVE print_r(`\ models);

// ロードされたすべてのモデルの配列を取得する $models =
Doctrine\_Core::getLoadedModels();

//
クラスの配列を上記のメソッドに渡しDoctrine\_Recordsではないものを除去する
:code:`models = Doctrine_Core::filterInvalidModels(array('User', 'Formatter', 'Doctrine_Record')); print_r(`\ models);
// FormatterとDoctrine\_Recordが有効ではないのでarray('User')を返す

// 実際のテーブル名用のDoctrine\_Connectionオブジェクトを取得する $conn
= Doctrine\_Core::getConnectionByTableName('user'); //
テーブル名が関連する接続オブジェクトを返す with.

// 既存のデータベースからYAMLスキーマを生成する
Doctrine\_Core::generateYamlFromDb('/path/to/dump/schema.yml',
array('connection\_name'), $options);

// 既存のデータベースからモデルを生成する
Doctrine\_Core::generateModelsFromDb('/path/to/generate/models',
array('connection\_name'), $options);

// オプションとデフォルト値の配列 $options = array('packagesPrefix' =>
'Package', 'packagesPath' => '', 'packagesFolderName' => 'packages',
'suffix' => '.php', 'generateBaseClasses' => true, 'baseClassesPrefix'
=> 'Base', 'baseClassesDirectory' => 'generated', 'baseClassName' =>
'Doctrine\_Record');

// YAMLスキーマからモデルを生成する
Doctrine\_Core::generateModelsFromYaml('/path/to/schema.yml',
'/path/to/generate/models', $options);

// 配列で提供されるテーブルを作成する
Doctrine\_Core::createTablesFromArray(array('User', 'Phoneumber'));

// 既存のモデルセットからすべてのテーブルを作成する //
ディレクトリが渡されなければロードされたすべてのモデル用のSQLを生成する
Doctrine\_Core::createTablesFromModels('/path/to/models');

// 既存のモデルのセットからSQLコマンドの文字列を生成する //
ディレクトリが渡されなければロードされたすべてのモデル用のSQLを生成する
Doctrine\_Core::generateSqlFromModels('/path/to/models');

// 渡されたモデルの配列を作成するSQL文の配列を生成する
Doctrine\_Core::generateSqlFromArray(array('User', 'Phonenumber'));

// 既存のモデルセットからYAMLスキーマを生成する
Doctrine\_Core::generateYamlFromModels('/path/to/schema.yml',
'/path/to/models');

// 接続用のすべてのデータベースを作成する // 接続名の配列はオプション
Doctrine\_Core::createDatabases(array('connection\_name'));

// 接続に対するすべてのデータベースを削除する //
接続名の配列はオプション
Doctrine\_Core::dropDatabases(array('connection\_name'));

// モデル用のすべてのデータをYAMLフィクスチャファイルにダンプする //
2番目の引数はbool値でそれぞれのモデルに大して個別のフィクスチャファイルを生成するかどうか
// trueの場合ファイルの代わりにフォルダを指定する必要がある
Doctrine\_Core::dumpData('/path/to/dump/data.yml', true);

// YAMLフィクスチャファイルからデータをロードする //
2番目の引数はブール値でロードするときにデータを追加するかロードする前にすべてのデータを最初に削除するか
Doctrine\_Core::loadData('/path/to/fixture/files', true);

// マイグレーションクラスのセット用のマイグレーション処理を実行する $num
= 5; // バージョン #5にマイグレートする
Doctrine::migration('/path/to/migrations', $num);

// 空白のマイグレーションクラスのテンプレートを生成する
Doctrine\_Core::generateMigrationClass('ClassName',
'/path/to/migrations');

// 既存のデータベース用のすべてのマイグレーションクラスを生成する
Doctrine\_Core::generateMigrationsFromDb('/path/to/migrations');

// 既存のモデルのセット用のすべてのマイグレーションクラスを生成する //
2番目の引数はloadModels()を使用して既にモデルをロードしている場合のオプション
Doctrine\_Core::generateMigrationsFromModels('/path/to/migrations',
'/path/to/models');

// モデル用のDoctrine\_Tableインスタンスを取得する $userTable =
Doctrine\_Core::getTable('User');

// Doctrineを単独のPHPファイルにコンパイルする $drivers =
array('mysql');
//コンパイルされたバージョンに含めたいドライバの配列を指定する
Doctrine\_Core::compile('/path/to/write/compiled/doctrine', $drivers);

// デバッグ用にDoctrineオブジェクトをダンプする
:code:`conn = Doctrine_Manager::connection(); Doctrine_Core::dump(`\ conn);

------
タスク
------

タスクはコアのコンビニエンスメソッドを搭載するクラスです。必須の引数を設定することでタスクを簡単に実行できます。これらのタスクはDoctrineコマンドラインインターフェイスで直接使われます。

 BuildAll BuildAllLoad BuildAllReload Compile CreateDb CreateTables Dql
DropDb DumpData Exception GenerateMigration GenerateMigrationsDb
GenerateMigrationsModels GenerateModelsDb GenerateModelsYaml GenerateSql
GenerateYamlDb GenerateYamlModels LoadData Migrate RebuildDb

独自スクリプトでDoctrine Tasksを単独で実行する方法は下記の通りです。

==============================
コマンドラインインターフェイス
==============================

--------
はじめに
--------

``Doctrine_Cli``はタスクのコレクションで開発とテストの手助けをしてくれます。このマニュアルの典型例に関して、必要なタスクを実行するためにPHPスクリプトをセットアップします。このcliツールはこれらのタスクのためにそのまま使えることを目的としています。

------
タスク
------

Doctrineの実装を管理するために利用できるタスクの一覧は下記の通りです。

 $ ./doctrine Doctrine Command Line Interface

./doctrine build-all ./doctrine build-all-load ./doctrine
build-all-reload ./doctrine compile ./doctrine create-db ./doctrine
create-tables ./doctrine dql ./doctrine drop-db ./doctrine dump-data
./doctrine generate-migration ./doctrine generate-migrations-db
./doctrine generate-migrations-models ./doctrine generate-models-db
./doctrine generate-models-yaml ./doctrine generate-sql ./doctrine
generate-yaml-db ./doctrine generate-yaml-models ./doctrine load-data
./doctrine migrate ./doctrine rebuild-db

CLI用のタスクは独立しており単独で使うことができます。下記のコードは例です。

 $task = new Doctrine\_Task\_GenerateModelsFromYaml();

$args = array('yaml\_schema\_path' => '/path/to/schema', 'models\_path'
=> '/path/to/models');

:code:`task->setArguments(`\ args);

try { if ($task->validate()) { $task->execute(); } } catch (Exception
:code:`e) { throw new Doctrine_Exception(`\ e->getMessage()); }

------
使い方
------

"doctrine"という名前のファイルを実行可能にします。

 #!/usr/bin/env php

``Doctrine_Cli``を実装する実際の"doctrine.php"という名前のPHPファイルは次の通りです。

 // Doctrineの設定/セットアップ、接続、モデルなどを含める

// Doctrine Cliを設定する //
通常cliタスクの引数がありますがここで設定すれば引数は自動的に入力されスクリプト実行時に入力する必要がなくなる

$config = array('data\_fixtures\_path' => '/path/to/data/fixtures',
'models\_path' => '/path/to/models', 'migrations\_path' =>
'/path/to/migrations', 'sql\_path' => '/path/to/data/sql',
'yaml\_schema\_path' => '/path/to/schema');

:code:`cli = new Doctrine_Cli(`\ config); :code:`cli->run(`\ \_SERVER['argv']);

これで次のようにコマンドを実行できます。

 ./doctrine generate-models-yaml ./doctrine create-tables

==============
サンドボックス
==============

----------------
インストール方法
----------------

http://www.doctrine-project.org/download
からもしくはsvnリポジトリから特別なサンドボックスをインストールできます。

 svn co http://www.doctrine-project.org/svn/branches/0.11 doctrine cd
doctrine/tools/sandbox chmod 0777 doctrine

./doctrine

上記のステップによってサンドボックスのcliが実行できるようになります。引数無しで./doctrineコマンドを実行すると利用可能なすべてのcliタスクのインデックスが表示されます。

======
まとめ
======

この章で検討したこれらのユーティリティが役に立つことを願います。[doc
unit-testing
:name]を使用することでDoctrineの安定性を維持し回帰を避ける方法を検討します。
