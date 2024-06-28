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
// // エラーチェック
$dataArr = [];

// セッションからフォームデータを取得
if (isset($_SESSION['formData'])) {
  $formData = $_SESSION['formData'];
} else {
   // フォームデータがなければ、registにリダイレクト
  header('Location '. Bootstrap::ENTRY_URL . 'regist.php');
  exit();
}


// セッションからフォームデータを取得
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
  
  $dataArr = $_SESSION['formData'];
  
  $result = $db->insertUser($dataArr);
  
  if($result) {
    
    // 新規登録が成功したユーザーのIDを取得
    $userId = $db->getLastId();

    // 取得したユーザーIDをセッションに保存
    $_SESSION['user_id'] = $userId;
       
    // formDataからfamily_nameを取得
    $_SESSION['family_name'] = $_SESSION['formData']['family_name'];
    // $_SESSION['email'] = $_SESSION['formData']['email'];
    // セッションデータを削除
    unset($_SESSION['formData']);
    header('Location: ' . Bootstrap::ENTRY_URL . 'complete.php');
    exit();
  } else {
    header('Location:' . Bootstrap::ENTRY_URL . 'regist.php');
  }
}

list($yearArr, $monthArr, $dayArr) = Initial::getDate();

$sexArr = Initial::getSex();

$context = [
  'yearArr' => $yearArr,
  'monthArr' => $monthArr,
  'dayArr' => $dayArr,
  'sexArr' => $sexArr,
  
  'formData' => $formData,
];


$template = $twig->loadTemplate('confirm.html.twig');
$template->display($context);











