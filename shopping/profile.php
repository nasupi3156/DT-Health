<?php

namespace shopping;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Item;
use shopping\lib\Cart;
use shopping\lib\Error;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$itm = new Item($db);
$cart = new cart($db);
$error = new Error($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

$customer_no = isset($_SESSION['customer_no']) ? $_SESSION['customer_no'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// ユーザープロフィール取得
$userInfo = (!empty($user_id)) ? $db->getUserProfile($user_id) : null;

$dataArr = [];
$errArr = [];
$duplicateEmail = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['delete'])) {
    // 退会
    $userDelete = $db->logicalDeleteUser($user_id);
    if ($userDelete) {
      session_destroy();
      // sessionを破壊
      header('Location:' . Bootstrap::ENTRY_URL . 'login.php');
      exit();
    } else {
      echo "退会できませんでした。";
    }
  } else {
    // プロフィール
    $dataArr = [
      'family_name' => filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
      'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'year' => filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT) ?? 0,
      'month' => filter_input(INPUT_POST, 'month', FILTER_SANITIZE_NUMBER_INT) ?? 0,
      'day' => filter_input(INPUT_POST, 'day', FILTER_SANITIZE_NUMBER_INT) ?? 0,
      'zip1' => filter_input(INPUT_POST, 'zip1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'zip2' => filter_input(INPUT_POST, 'zip2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
      'tel1' => filter_input(INPUT_POST, 'tel1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'tel2' => filter_input(INPUT_POST, 'tel2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      'tel3' => filter_input(INPUT_POST, 'tel3', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
      // 'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''
  ]; 

  // 更新
  $updateResult = $db->updateUserProfile($dataArr, $user_id);

  if ($updateResult) {
    // データベースから再度ユーザー情報を取得
    $userInfo = $db->getUserProfile($user_id);
    // セッションデータの更新
    $_SESSION['family_name'] = $userInfo['family_name'];

    header('Location:' . Bootstrap::ENTRY_URL . 'profile.php');
    exit();
    }
  }
} 


list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

$context = [
  'family_name' => (!empty($_SESSION['family_name'])) ? $_SESSION['family_name'] : 'ゲスト',
  'user' => $userInfo,
  'duplicateEmail' => $duplicateEmail,
  'errArr' => $errArr,
  'dataArr' => $dataArr,
];

$context['sumNum'] = $sumNum; 
$context['isUserLogin'] = $ses->isUserLogin();

$template = $twig->loadTemplate('profile.html.twig');
$template->display($context);