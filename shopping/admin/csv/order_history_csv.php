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

    $adminOrderList = $cart->getAdminOrderList();


    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="signup.csv"');

    $output = fopen('php://output', 'w'); 
    fputcsv($output,['注文番号', 'ユーザーID', '商品ID', '商品名', '価格', '数量', '手数料', '合計金額', '支払い方法', '注文日']);

    foreach($adminOrderList as $order) {
      fputcsv($output, [
        $order['order_id'], 
        $order['user_id'], 
        $order['item_id'], 
        $order['item_name'], 
        $order['price'], 
        $order['quantity'], 
        $order['shipping_fee'], 
        $order['total_price'], 
        $order['payment_method'], 
        $order['purchase_date']
      ]);
    }

    fclose($output);
    exit();

} catch (\Exception $e){
    error_log('エラーが発生しました : ' . $e->getMessage());
    echo 'CSVのファイルの生成中にエラーが発生しました。';
    exit();
}




