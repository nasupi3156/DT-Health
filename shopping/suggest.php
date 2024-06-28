<?php

namespace shopping;

// require_once 'Bootstrap.class.php';
require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Item;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$itm = new Item($db);

$query = isset($_GET['query']) ? $_GET['query'] : '';

$ctg_id = isset($_GET['ctg_id']) ? $_GET['ctg_id'] : '';

if ($query !== '') {
  // suggest関数から受け取ったクエリとカテゴリIDに基づいてデータベースを検索。検索結果を取得し、HTMLとして返す。
  $results = $itm->searchItems($query, $ctg_id);
  if (empty($results)) {
    echo "<div>検索できませんでした。</div>";
  } else {
  // 結果をループ
  foreach ($results as $result) {
    echo '<div>' . $result['item_name'] . '</div>'; 
  }
}
}