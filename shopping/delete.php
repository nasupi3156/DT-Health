<?php

namespace shopping;

// require_once 'Bootstrap.class.php';
require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Item;
use shopping\lib\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$itm = new Item($db);
$cart = new Cart($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

$customer_no = isset($_SESSION['customer_no']) ? $_SESSION['customer_no'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';


list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

$context =[
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
  'sumNum' => $sumNum,
  'isUserLogin' => $ses->isUserLogin()
];

$template = $twig->loadTemplate('delete.html.twig');
$template->display($context);