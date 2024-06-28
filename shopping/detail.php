<?php
/**
 *  ファイル名 : detail.php(商品詳細を表示するプログラム,Controller)
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

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap :: TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);

$ses->checkSession();
$customer_no = $_SESSION['customer_no'];

$item_id = (isset($_GET['item_id']) === true && preg_match('/^\d+$/', $_GET['item_id']) === 1) ? $_GET['item_id'] : '';
//$_GETがitem_idというキーを持ち、かつその値がセットされているか、また$_GET['item_id']の値が数字のみで構成されているか、/^\d+$/ は数字のみ

//item_idが取得できていない場合、商品一覧へ遷移させる
if ($item_id === '') {
  header('Location: ' . Bootstrap::ENTRY_URL. 'list.php'); 
} 

//カテゴリーリスト(一覧)を取得する
$cateArr = $itm->getCategoryList();

//商品情報を取得する
$itemData = $itm->getItemDetailData($item_id);

list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
];
$context['cateArr'] = $cateArr;
$context['itemData'] = $itemData[0];
//　[0]を使用して連想配列の要素に直接アクセスできる。全体にアクセスしたい場合は[0]を入れないでループ処理
// 今回は商品詳細だけなのでその商品の情報のみを[0]で指定
$context['sumNum'] = $sumNum; 
$context['isUserLogin'] = $ses->isUserLogin();
$template = $twig->loadTemplate('detail.html.twig');
$template->display($context);

