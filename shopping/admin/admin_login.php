<?php

namespace shopping;

require_once dirname(__FILE__) . '/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Error;
use shopping\lib\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$error = new Error();

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);



if (isset($_GET['logout'])) {
  $ses->logout(); 
  header ('Location: ' . Bootstrap::ENTRY_URL . 'login.php');
  exit();
}


$dataArr = [];
$errArr = [];
// 「空の箱」,ユーザーからの入力やエラーメッセージを使用されるためには、正しく初期化されている必要がある

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin'])) {
  //バリデーションチェック
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
  $dataArr = [
    'email' => $email,
    'password' => $password,
  ];

  $errArr = $error->loginErrorCheck($dataArr); 
  // エラーチェック、入力データに問題があれば、$errArr配列にエラーメッセージ追加

  if ($error->getErrorFlg() === true){
    // エラーがなければ
    $adminUser = $db->adminLogin($email, $password);
      if ($adminUser) {
        $_SESSION['is_admin'] = true;
        header('Location:' . Bootstrap::ENTRY_URL . 'admin/admin_signup.php');
        exit();
    }
  }
}


$context = [];
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;



$template = $twig->loadTemplate('admin_login.html.twig');
$template->display($context);




