<?php

namespace shopping;

require_once dirname(__FILE__). '/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\Bootstrap;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

if (isset($_GET['zip1']) === true && isset($_GET['zip2']) === true){
  // $zip1 = $_GET['zip1'];
  // $zip2 = $_GET['zip2'];
  $zip1 = trim($_GET['zip1']);
  $zip2 = trim($_GET['zip2']);
  // trim ：スペースをなくす、スペースがあるとエラーやデータの不一致などを起こす、224 0012, 2240012など。 今回の場合はmaxlengthで文字を指定し、分けて指定しているが何が起きるかわからないから指定はした方がいい
  // $zip = $zip1 . $zip2;  "1234567"


  $res = $db -> getPostcodeSearch($zip1. $zip2); // 郵便番号を結合

  if ($res) {
    echo ($res !== '' && count($res) !== 0) ? $res['pref']. $res['city'].$res['town']: false;
    // echo($res !== "" && count($res) !== 0) ? $res[0]['pref'].$res[0]
    // ['city'].$res[0]['town']:''; 

  } else {
    echo 'no';
  }
}
