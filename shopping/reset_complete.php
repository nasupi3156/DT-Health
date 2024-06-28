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





$context = [];


$template = $twig->loadTemplate('reset_complete.html.twig');
$template->display($context);

