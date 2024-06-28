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

    $adminOrderList = $cart->getAdminContact();


    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="signup.csv"');

    $output = fopen('php://output', 'w'); 
    fputcsv($output,['お問い合わせ番号', 'お名前', 'メールアドレス', '詳細', 'お問い合わせ日時']);

    foreach($adminOrderList as $order) {
      fputcsv($output, [
        $order['id'], 
        $order['username'], 
        $order['email'], 
        $order['contents'], 
        $order['created_at'], 
      ]);
    }

    fclose($output);
    exit();
} catch(\Exception $e) {
    error_log('エラーが発生しました: ' .  $e->getMessage());
    echo 'CSVのファイルの生成中にエラーが発生しました。';
    exit();
}





