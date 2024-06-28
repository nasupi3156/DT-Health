<?php

namespace shopping;

require_once dirname(__FILE__) .'/Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap :: TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap :: CACHE_DIR
]);

$ses->checkSession();

$customer_no = isset($_SESSION['customer_no']) ? $_SESSION['customer_no'] : '';

$context = [
'family_name' => (!empty($_SESSION['family_name'])) ? $_SESSION['family_name'] : 'ゲスト',

'isUserLogin' => $ses->isUserLogin()
];
// $context['isUserLogin'] = $ses->isUserLogin();
$template = $twig->loadTemplate('thanks.html.twig');
$template->display($context);