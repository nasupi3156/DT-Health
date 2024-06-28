<?php

namespace shopping;

require_once dirname(__FILE__) . '/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Cart;
use shopping\lib\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$cart = new Cart($db);

//テンプレート指定
$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

// if ($ses->checkLoginStatus() || !$SESSION['is_admin']) {
//   // header ('Location:' . Bootstrap::ENTRY_URL . 'login.php');
//   // exit();
// } 


$user_id = (isset($_GET['user_id']) === true && preg_match('/^\d+$/', $_GET['user_id']) === 1) ? $_GET['user_id'] : '';


if ($user_id !== '') {
   $cart->delAdminSignup($user_id);
}


$adminSignup = $cart->getAdminSignup();




$context = [
  'adminSignup' => $adminSignup,
  // 'modalOrder' => $adminOrder[0]
];



$template = $twig->loadTemplate('admin_signup.html.twig');
$template->display($context);