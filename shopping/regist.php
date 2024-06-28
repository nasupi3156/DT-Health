<?php

namespace shopping;

require_once dirname(__FILE__) .'/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Error;
use shopping\lib\Initial;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$error = new Error();

//テンプレート指定
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

// エラー配列を初期化
$errArr = []; 
$dataArr = [];
$delete = false;
// 重複確認変数
$duplicateEmail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
 
  // サニタイズ  ユーザー入力の値取得
  $dataArr = [
    'family_name' => filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'family_name_kana' => filter_input(INPUT_POST, 'family_name_kana', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'first_name_kana' => filter_input(INPUT_POST, 'first_name_kana', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'sex' => filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'year' => filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT) ?? 0, // 空の場合は0を代入
    'month' => filter_input(INPUT_POST, 'month', FILTER_SANITIZE_NUMBER_INT) ?? 0,
    'day' => filter_input(INPUT_POST, 'day', FILTER_SANITIZE_NUMBER_INT) ?? 0,
    'zip1' => filter_input(INPUT_POST, 'zip1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'zip2' => filter_input(INPUT_POST, 'zip2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
    'tel1' => filter_input(INPUT_POST, 'tel1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'tel2' => filter_input(INPUT_POST, 'tel2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'tel3' => filter_input(INPUT_POST, 'tel3', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  ];

  // メールアドレスの重複チェック
  $user = $db->emailCheck($dataArr['email']);
    if($user['cnt'] > 0) {
      $duplicateEmail = true;  
    }

  // エラーチェック
  $errArr = $error->errorCheck($dataArr);
  

  $delete = $db->isUserDeleted($dataArr['email']);
   if ($delete) {
    $errArr['email'] = "このメールアドレスは退会済みのため使用することができません";
  } 

  
  if ($error->getErrorFlg() === true && !$duplicateEmail && !$delete) {
  // エラーがなく、かつメールアドレスの重複もない
  
  $_SESSION['formData'] = $dataArr; 
  // フォームデータをセッションに保存

  header('Location: ' . Bootstrap::ENTRY_URL . 'confirm.php');
    exit();
  } else {
    $context['error_message'] = "申し訳ございません。システムエラーが発生しました。もう一度お試しください。";
  // }
  } 
}

// $errArr = isset($_SESSION['errArr']) ? $_SESSION['errArr'] : [];
// $dataArr = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// // confirmページから戻ってきた時、セッションからフォームデータとエラーを取得して使用
// if (isset($_SESSION['formData'])) {
//   $dataArr = $_SESSION['formData'];
//   $errArr = isset($_SESSION['errArr']) ? $_SESSION['errArr'] : [];
//   // エラーメッセージの後セッションをクリア
//   unset($_SESSION['errArr']);
// } else {
//   // confirmページから戻ってこない場合は、$dataArrと$errArr を初期化。
//   $dataArr = [];
//   $errArr = [];
// }

// $formData = isset($_SESSION['formData']) ? $_SESSION['formData'] : [];

// // エラーチェック
// $errArr = $error->errorCheck($dataArr);

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error->getErrorFlg() === true) // 

list($yearArr, $monthArr, $dayArr) = Initial::getDate();

$sexArr = Initial::getSex();
// $trafficArr = Initial::getTrafficWay();



$context = [
  'yearArr' => $yearArr,
  'monthArr' => $monthArr,
  'dayArr' => $dayArr,
  'sexArr' => $sexArr,
  // 'trafficArr' => $trafficArr,
  'formData' => $dataArr,
  // 'dataArr' => $dataArr,
  'errArr' => $errArr,
  'delete' => $delete,
  'duplicateEmail' => $duplicateEmail,
];

$template = $twig->loadTemplate('regist.html.twig');
$template->display($context);


   // $dataArr['family_name'],
    // $dataArr['first_name'], 
    // $dataArr['family_name_kana'],
    // $dataArr['first_name_kana'],
    // $dataArr['sex'],
    // $dataArr['year'],
    // $dataArr['month'],
    // $dataArr['day'],
    // $dataArr['email'],
    // $dataArr['zip1'],
    // $dataArr['zip2'],
    // $dataArr['address'],
    // $dataArr['tel1'],
    // $dataArr['tel2'],
    // $dataArr['tel3'],
    // $dataArr['password'],
    // $dataArr['registDate'] ?? date('Y-m-d H:i:s'),
    // registDateが未定義の場合は現在の日時を使用