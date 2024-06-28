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


$id = (isset($_GET['id']) === true && preg_match('/^\d+$/', $_GET['id']) === 1) ? $_GET['id'] : '';


if ($id !== '') {
   $cart->delAdminContact($id);
}


$adminContact = $cart->getAdminContact();




$context = [
  'adminContact' => $adminContact,
  // 'modalOrder' => $adminOrder[0]
];



$template = $twig->loadTemplate('admin_contact.html.twig');
$template->display($context);