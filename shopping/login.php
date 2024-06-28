<?php

namespace shopping;

require_once dirname(__FILE__) .'/Bootstrap.class.php';

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

$ses->checkSession();

if (isset($_GET['logout']) && $_GET['logout'] = '1') {
  $ses->logout(); 
  header ('Location: ' . Bootstrap::ENTRY_URL . 'login.php');
  exit();
}

// 変数が「空の箱」、初期化、ユーザーからの入力やエラーメッセージを動的に収集する容器として使用
$dataArr = [];
$errArr = [];

// 退会済みのユーザーかどうかを示すフラグ、falseで初期化
$delete = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
  $dataArr = [
  'email' => $email,
  'password' => $password,
  ];

  // エラーチェック、入力データに問題があれば、$errArr配列にエラーメッセージ追加
  $errArr = $error->loginErrorCheck($dataArr); 
 
  if ($error->getErrorFlg() === true) {
  // エラーがなければ
    
    $delete = $db->isUserDeleted($email);
    // returnで$deleteを返してないけど、この場合はreturnでis_deletedの1を返す
    if ($delete) {
      $errArr['email'] = "このメールアドレスは退会済みのためログインできません。再登録してください。";
    } else {
    
    if ($ses->loginUser($dataArr['email'], $dataArr['password'])) {
      
      //  // 新しいセッションIDを取得
      //  $newSessionId = session_id();
  
      // //  データベースのセッションID(session_key)を更新
      //  $db->updateSessionKey($customer_no, $newSessionId);
       
      //  $db->updateLoginStatus($user_id, 1);

      if ($_SESSION['is_admin']) {
        unset($_SESSION['family_name']);
        unset($_SESSION['user_id']);

        header('Location:' . Bootstrap::ENTRY_URL . 'admin/admin_login.php');
        exit();
      } else {
        unset($_SESSION['is_admin']);
        header('Location: ' . Bootstrap::ENTRY_URL . 'list.php');
        exit();
      }
      } else {
        $errArr['login'] = "メールアドレスまたはパスワードが正しくありません";
      }
    }
  }
}

$context = [];
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['delete'] = $delete;

$template = $twig->loadTemplate('login.html.twig');
$template->display($context);



