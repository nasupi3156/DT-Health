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

$errArr = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['click'])) {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errArr['invalid_email'] = '正しいメールアドレスを入力してください。';
  } else {
    // 安全なランダムトークンを生成
  $token = bin2hex(random_bytes(16));
  // トークンの有効期限を1時間
  $expires = time() + 3600;

  $emailUser = $db->emailConfirm($email);
  if ($emailUser) {
    $user_id = $emailUser['user_id'];
    $db->insertPasswordReset($user_id, $email, $token, $expires);
      
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  $to = $email; // これは受信者のメールアドレス

  $url = "http://localhost/DTMarket/shopping/password_reset.php?token=$token";

  $subject = 'パスワードリセット用URLをお送りします';
  // ヒアドキュメント,<<<DODとEODまで : それを$bodyに代入
  // "や'でくくることなく、変数や改行を含む複数行の文字列を定義{$url}を埋め込む事ができる。
  $body = 
  '1時間以内に下記のURLへアクセスし、パスワードの変更を完了してください' . "\r\n\r\n" . $url . "\r\n\r\n" . 'このメールは送信用です。';


  // 送信者情報の設定
  $header = "From : nasubi54kk@gmail.com\n"; // これは送信者のメールアドレス

  // text/htmlを指定し、html形式で送ることも可能
  $header .= "Content-Type : text/plain";

  // メール送信
  $response = mb_send_mail($to, $subject, $body, $header);

  if ($response) {
    header('Location:' . Bootstrap::ENTRY_URL . 'request_success.php');
    exit();

    } else {
      // メールサーバーや設定の問題でパスワードリセットメールの送信に失敗した場合
      $errArr['email_not_found'] = 'メール送信に失敗しました。後ほどもう一度お試しください。';
    }
    
    } else {
      // メールアドレスが違う、ユーザ入力がデータベースにない
      $errArr['email_failed'] = "入力されたメールアドレスは当システムに登録されていません。再確認してもう一度お試しください。";  
    }
  }
}

  $context = [];
  $context['errArr'] = $errArr;
  $template = $twig->loadTemplate('request_form.html.twig');
  $template->display($context);

