<?php

namespace shopping;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Cart;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$cart = new Cart($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);

$ses->checkSession();
$customer_no = $_SESSION['customer_no'];
$user_id = $_SESSION['user_id'] ?? null;

$userInfo = (!empty($user_id)) ? $db->getUserProfile($user_id) : null;

// セッションに'user_id'が存在しない場合にログインページにリダイレクト
if(!isset($_SESSION['user_id'])) {
  header('Location: ' . Bootstrap::ENTRY_URL . 'login.php');
  exit(); 
} 


// purchase edit
if (isset($_POST['btn-modal'])) {
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn-modal'])) {
  $dataArr = [
  'family_name' => filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
  'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'zip1' => filter_input(INPUT_POST, 'zip1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'zip2' => filter_input(INPUT_POST, 'zip2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'tel1' => filter_input(INPUT_POST, 'tel1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'tel2' => filter_input(INPUT_POST, 'tel2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  'tel3' => filter_input(INPUT_POST, 'tel3', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  // 'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''
  ]; 
  $updateEdit = $db->userPurchaseProfile($dataArr, $user_id);

    if ($updateEdit) 
    {
      // データベースから再度ユーザー情報を取得
      $userInfo = $db->getUserProfile($dataArr, $user_id);
      // セッションデータの更新
      $_SESSION['family_name'] = $userInfo['family_name'];

      header('Location:' . Bootstrap::ENTRY_URL . 'purchase.php');
      exit(); 
    }
  }
 
  // phpの関数を定義
  function calculateShippingFee($sumPrice) {
    // 2000円と以上
    return ($sumPrice >= 2000) ? 0 : 300;
  } 

if (isset($_POST['submit'])) {
  $dataArr = $cart->getCartData($customer_no);
  if (empty($dataArr)) {
    echo '<div style="color: #ff0000; background-color: #f8d7da; padding: 23px; border-radius: 5px; margin: -7px; font-size: 18px;  font-weight: 599;">カートに商品がありません。商品を選択してカートに追加してください。</div>';
    exit();
  }
}

if (isset($_POST['submit'])) {
  
  $user_id = $_SESSION['user_id'] ?? null;
  
  $family_name = $_POST['family_name'] ?? '';
  $first_name = $_POST['first_name'] ?? '';
  $zip1 = $_POST['zip1'] ?? '';
  $zip2 = $_POST['zip2'] ?? '';
  $address = $_POST['address'] ?? '';
  $tel1 = $_POST['tel1'] ?? '';
  $tel2 = $_POST['tel2'] ?? '';
  $tel3 = $_POST['tel3'] ?? '';
  $purchase_date = date('Y-m-d H:i:s');
  
  $sumNum = isset($_POST['sumNum']) && is_numeric($_POST['sumNum']) ? $_POST['sumNum'] : 0;
  // is_numeric : 数値を検証 未設定(数値でない)デフォルト値0
  $sumPrice = isset($_POST['sumPrice']) && is_numeric($_POST['sumPrice']) ? $_POST['sumPrice'] : 0;  
  
  $payment_method = "代金引換"; 
  
  // 送料を一律300円
  $shipping_fee = 300; 

  // 合計金額に基づいて送料の値段を計算、上書き
  $shipping_fee = calculateShippingFee($sumPrice);

  // 総合計金額の計算（商品の合計金額 + 送料）            
  $total_price = $sumPrice + $shipping_fee; 

  // 注文情報をordersテーブルに挿入
  $orderId = $db->insert('orders', [
    'user_id' => $user_id,
    'family_name' => $family_name,
    'first_name' => $first_name,
    'zip1' => $zip1,
    'zip2' => $zip2,
    'address' => $address,
    'tel1' => $tel1,
    'tel2' => $tel2,
    'tel3' => $tel3,
    'shipping_fee' => $shipping_fee,
    'total_price' => $total_price,
    'payment_method' => $payment_method,
    'purchase_date' => $purchase_date
  ]);

  $orderId = $db->getLastId();
  // ordersテーブルのorder_id(プライマリー、オートイン)を挿入から取得したorder_idを使用して、getLastIdメソッド(lastInsertId)を使用して、新しく挿入された注文のorder_idを取得。
  // lastInsertIdはデータベース接続で生成された最後の自動インクリメント値を返す。
  // order_idを取得しorder_detailsテーブルにorder_idレコードを挿入詳細がそれぞれの注文に正しく関連付けられる。
  // selectやupdateでは新しい行ができないので、insertの時だけ

  // 商品情報
  // 注文IDがありかつアイテム情報がある、item_idは識別子 
if ($orderId && isset($_POST['item_id'])) {
  
    // $indexキー、$itemId値なのでindexを使い値にアクセス
  foreach ($_POST['item_id'] as $index => $itemId) {
    $itemImage = $_POST['item_image'][$index] ?? '';
    // formからpostで受け取ったアイテムを個別で処理し確認、アイテム情報には複数の情報が含まれてるのでループ処理、配列をループで1つのアイテムに
    // $_postのitem_imageはアイテム画像の配列、それをindexがキー[0]と値で連想配列をイメージ
  
    $itemName = $_POST['item_name'][$index] ?? '';
    $quantity = isset($_POST['quantity'][$index]) && is_numeric($_POST['quantity'][$index]) ? $_POST['quantity'][$index] : 0;
    $price = isset($_POST['price'][$index]) && is_numeric($_POST['price'][$index]) ? $_POST['price'][$index] : 0;

    
    $db->insert('order_details', [
      'order_id' => $orderId,
      // order_idはorderテーブルとorder_detailテーブルの間のリンク、これにより特定の注文に属するすべての商品詳細を取得
      'item_id' => $itemId,
      'image' => $itemImage, 
      'item_name' => $itemName,
      'quantity' => $quantity,
      'price' => $price,
    ]);
    }
    // 論理削除
      $cart->clearUserCart($customer_no);
      header('Location: thanks.php');
      exit();
  }
}

// ユーザごとに名前、郵便番号、住所、電話番号などを表示させる処理
$result = $db->getUserProfile($user_id);

list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

// カート情報を取得する
$dataArr = $cart->getCartData($customer_no);

// 最後の送料を計算してcontextからtwigに入れる値、insertされたので再追加
$shipping_fee = calculateShippingFee($sumPrice);

$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
  'user' => $userInfo,
  'profile' => $result,
  'shipping_fee' => $shipping_fee
];
$context['dataArr'] = $dataArr;

// 数の合計
$context['sumNum'] = $sumNum; 
// 金額合計
$context['sumPrice'] = $sumPrice;
$context['isUserLogin'] = $ses->isUserLogin();
$template = $twig->loadTemplate('purchase.html.twig');
$template->display($context);