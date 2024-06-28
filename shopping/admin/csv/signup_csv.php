<?php

namespace shopping;

require_once dirname(__FILE__) . '/../../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Cart;
use shopping\lib\Session;

try {
    $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

    $ses = new Session($db);
    $cart = new Cart($db);

    $loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
    $twig = new \Twig\Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
    ]);

    $ses->checkSession();

    $adminSignup = $cart->getAdminSignup();


    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="signup.csv"');

    $output = fopen('php://output', 'w'); 
    fputcsv($output,['ユーザID', '名字', '名前', '名字(カナ)', '名前(カナ)', '性別', '生年月日', '郵便番号', '住所', 'メールアドレス', '電話番号']);

    foreach($adminSignup as $signup) {
      fputcsv($output, [
          $signup['user_id'], 
          $signup['family_name'], 
          $signup['first_name'], 
          $signup['family_name_kana'], 
          $signup['first_name_kana'], 
          $signup['sex'], 
          $signup['year'], 
          $signup['month'], 
          $signup['day'], 
          $signup['zip1'], 
          $signup['zip2'], 
          $signup['address'], 
          $signup['email'], 
          $signup['tel1'], 
          $signup['tel2'], 
          $signup['tel3']
      ]);
    }

    fclose($output);
    exit();
} catch(\Exception $e) {
    error_log('エラーが発生しました: ' . $e->getMessage());
    echo 'CSVのファイルの生成中にエラーが発生しました。';
    exit();
}




