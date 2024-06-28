<?php
/**
 *  ファイル名 : list.php(セッション関係のクラスファイル,Model)
 *  (商品一覧を表示するプログラム、Controller)
 */
namespace shopping;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Item;
use shopping\lib\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$itm = new Item($db);
$cart = new cart($db);

//テンプレート指定
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

// 初期化、変数をリセットしないとエラー
$errorMessage = '';

$customer_no = isset($_SESSION['customer_no']) ? $_SESSION['customer_no'] : '';

if (!$_SESSION['customer_no']) {
  header('Location: ' . Bootstrap::ENTRY_URL . 'login.php');
}

// GETからctg_idを取得、存在しない場合は空文字を設定
$currentCtg = $_GET['ctg_id'] ?? '';

// 検索クエリを取得
$query = isset($_GET['query']) ? $_GET['query'] : '';

$ctg_id = isset($_GET['ctg_id']) && preg_match('/^[0-9]+$/' , $_GET['ctg_id']) ? (int)$_GET['ctg_id'] : '';
$order = (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'price ASC' : 'price DESC';

$page = isset($_GET['page']) && preg_match('/^[0-9]+$/', $_GET['page']) ? (int)$_GET['page'] : 1;

  

$totalItemCount = $itm->getTotalItemCount($ctg_id);
// $totalItemCount = $itm->getTotalItemCountForAll();
 
$categories = $itm->getCategoryDetail();

// LIMITに使われる値(1ページあたり12項目を表示)
$itemsPerPage = 12;

// 1ページ目、$offset = (1 - 1) * 12 = 0なので1~12
$offset = ($page - 1) * $itemsPerPage;

// 前アイテム数を取得/
$totalItems = $itm->getTotalItemCount($ctg_id);

// 全アイテム数を1ページあたりのアイテム数で割る(小数点以下を切り上げ)
$totalPages = ceil($totalItems / $itemsPerPage);
  
// カテゴリーリスト(一覧)を取得する
$cateArr = $itm->getCategoryList();

list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

// 検索クエリがある場合とない場合の処理
if ($query !== '') {
  // $totalItems = $itm->getSearchResultCount($query, $ctg_id,  $offset, $itemsPerPage);
  $searchResults = $itm->searchItems($query, $ctg_id, $offset, $itemsPerPage);
  $dataArr = $searchResults;
  if (empty($searchResults)) {
    $errorMessage = "検索結果が見つかりませんでした";
  } 
} else {
  // $totalItems = $itm->getTotalItemCount($ctg_id);
  $dataArr = $itm->getItemList($ctg_id, $order, $offset, $itemsPerPage);
}

$context = [
  'family_name' => (!empty($_SESSION['family_name'])) ? $_SESSION['family_name'] : 'ゲスト',
  'cateArr' => $cateArr,
  'ctg_id' => $ctg_id,
  'query' => $query,
  'categories' => $categories,
  // 全てのアイテムを取得
  'totalItemCount' => $totalItemCount,
  // 現在のページ番号を表す変数
  'currentPage' => $page,  
   // 全体のページ数を表す変数
  'totalPages' => $totalPages, 
  'order' => $order,
  'currentCtg' => $currentCtg,
  'sumNum' => $sumNum,
  'dataArr' => $dataArr,
  'errorMessage' => $errorMessage,
  
  // ユーザーがログインしているかどうかでページの表示内容を変える
  'isUserLogin' => $ses->isUserLogin()
];

$template = $twig->loadTemplate('list.html.twig');
$template->display($context);

