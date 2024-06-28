<?php
/**
 *  ファイル名 : Bootstrap.class.php(設定に関するプログラム)
 */

namespace shopping;

date_default_timezone_set('Asia/Tokyo');

require_once dirname(__FILE__) . './../vendor/autoload.php';

class Bootstrap
{
  const DB_HOST = 'localhost'; //後でDB名を変更する
  const DB_NAME = 'DTshopping_db';
  const DB_USER = 'DTshopping_user';
  const DB_PASS = 'DTshopping_pass';
  const DB_TYPE = 'mysql';
  // MSLF
  const APP_DIR = '/Applications/MAMP/htdocs/DTMarket/';
  const TEMPLATE_DIR = self::APP_DIR . 'templates/shopping/';
  const CACHE_DIR = false;
  //const CACHE_DIR = self::APP_DIR . 'templates_c/shopping/';
  const APP_URL = 'http://localhost/DTMarket/';

  const ENTRY_URL = self::APP_URL . 'shopping/';

  public static function loadClass($class)
  {
    $path = str_replace('\\', '/', self::APP_DIR . $class . '.class.php');
    
    require_once $path;
  }
}
//これを実行しないとオートローダーとして動かない
  spl_autoload_register([
    'shopping\Bootstrap', 'loadClass'
  ]);