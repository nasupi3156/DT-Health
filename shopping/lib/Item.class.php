<?php
/**
 * ファイル名 : Item.class.php(商品に関するプログラムのクラスファイル、Model)
 */
namespace shopping\lib;

class Item
{
  public $cateArr = [];
  public $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  // カテゴリーリストの取得 
  public function getCategoryList()
  {
    $table = ' category ';
    $col = ' ctg_id, category_name ';
    $res = $this->db->select($table, $col);
    return $res;
  }
  
  // public function getItemList($ctg_id, $orderBy, $start = 0, $itemsPerPage = null)
  // {
  //   $table = 'item';
  //   $col = 'item_id, item_name, price, image, ctg_id';
  //   $where = ($ctg_id != '') ? ' ctg_id = ? ' : '';
  //   $arrVal = ($ctg_id != '') ? [$ctg_id] : [];

  //   // $orderBy = 'item_id DESC';
  //   // $orderBy item_id DESC : カラムを降順に並べる / item_id ASC ,price : 昇順に並べる
    
  //   $orderBy = ($orderBy !== '') ? "ORDER BY $orderBy" : '';
    
  //   $limit = ($itemsPerPage !== null) ? "LIMIT $start, $itemsPerPage" : '';

     
  //   $res = $this->db->select($table, $col, $where, $arrVal, $limit, $orderBy);
    
  //   // shuffle ($res);
  //   // 取得したデータをシャッフル、ランダムな順序で表示

  //   return ($res !== false && count($res) !== 0) ? $res : false;
  // }

 
  // 商品,ソート、ページネーション
  public function getItemList($ctg_id, $orderBy, $offset, $itemsPerPage)
  {
    // ($ctg_id)の引数が空じゃなければctg_id = ?が入る
    // カテゴリーによって表示させるアイテムを変える
    $table = ' item ';
    $col = ' item_id, item_name, price,image, ctg_id ';
    $where = ($ctg_id !== '') ? '  ctg_id = ? ' : '';
    $arrVal = ($ctg_id !== '') ? [$ctg_id] : [];
    
    $limit = "$offset, $itemsPerPage";

    $res = $this->db->select($table, $col, $where, $arrVal, $orderBy, $limit);
    return ($res !== false && count($res) !== 0) ? $res : false;  
  }

  

  // 全てのカテゴリーと特定のカテゴリIDに基づいてアイテムの総数をカウント
  // ctg_idの引数をデフォルト値で空にすることで全てとカテゴリー別の両方に対応
  public function getTotalItemCount($ctg_id = '') {
    $table = 'item';
    $col = 'COUNT(*) as total_count';
    $where = ($ctg_id !== '') ? 'ctg_id = ?' : '';
    $arrVal = ($ctg_id !== '') ? [$ctg_id] : [];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false) ? $res[0]['total_count'] : 0;
}

  // 商品詳細
  public function getCategoryDetail()
  {
    $categories = [];
    // categoriesでは全アイテム数を持つ「全て」カテゴリを取得
    $categories = [
      [ 
      // ctgが空なのは特定の商品に限定されずに全てを表示するため
      'ctg_id' => '',
      'category_name' => '全て',
      // 全てのitemの数
      'total_count' => $this->getTotalItemCount()
      // 'total_count' => $this->getTotalItemCountForAll()
      ]
    ];
    // cateListでは個別のカテゴリーを取得
    $categoryList = $this->getCategoryList(); 
    
    foreach ($categoryList as $key => $category) {
      // categoryの配列ctg_idはパラメータで受け取り、カテゴリーごとにアイテムを取得
      $ctg_id = $category['ctg_id'];
      // totalメソッドからctg_idでアイテム情報を取得、メソッドからcategoryにtotal_count追加
      $category['total_count'] = $this->getTotalItemCount($ctg_id);
      $categories[] = $category; 
      
    }
    return $categories;
  }


  // 商品の詳細情報を取得する
  public function getItemDetailData($item_id)
  {
    $table = ' item ';
    $col = ' item_id, item_name, detail, price, image, ctg_id ';
    $where = ($item_id !== '') ? ' item_id = ? ' : ''; 
    // カテゴリーによって表示させるアイテムをかえる
    $arrVal = ($item_id !== '') ? [$item_id] : [];

    
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

   // 検索
   public function searchItems($query, $ctg_id)
   {
     $table = 'item';
     $column = 'item_id, item_name, price, image, ctg_id';
     $where = 'item_name LIKE ?';
     // LINKは部分一致に使用される演算子、$queryも前後に % を付けることで部分一致検索
     $arrVal = ['%' . $query . '%'];
 
     if ($ctg_id !== '') {
      // .= は文字列結合演算子、右辺の文字列を左辺の文字列に追加
       $where .= ' AND ctg_id = ?';
       $arrVal[] = $ctg_id;
     }
    //  三項演算子でも同じ
    //  $ctg_id !== '' ? ($where .= ' AND ctg_id = ?') && $arrVal[] = $ctg_id : '';
     return $this->db->select($table, $column, $where, $arrVal);
   }

 
  public function  getSearchResultCount($query, $ctg_id, $offset, $itemsPerPage)
  {
    $table = 'item';
    $column = 'item_id, item_name, price, image, ctg_id';
    $where = 'item_name LIKE ?';
    $arrVal = ['%' . $query . '%'];

    // 空ではない場合、カテゴリIDのWHEREの条件追加
    if ($ctg_id !== '') {
        $where .= ' AND ctg_id = ?';
        $arrVal[] = $ctg_id;
    }

    $limit = "$offset, $itemsPerPage";
    return $this->db->select($table, $column, $where, $arrVal, '', $limit);
}


}