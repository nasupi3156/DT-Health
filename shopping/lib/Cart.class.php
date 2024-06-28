<?php
/* ファイル名 : Cart.class.php(カートに関するプログラムのクラスファイル、Model) */
namespace shopping\lib;

class Cart
{
  private $db = null;
  
  public function __construct($db = null)
  {
    $this->db = $db;
  } 
  // 顧客番号 ($customer_no)、商品番号 ($item_id)、および数量 ($qty)
  // カートに商品を追加する, デフォルト値1
  public function insCartData($customer_no, $item_id, $qty = 1)
  {
    $table = ' cart ';
    $qtyItem = $this->getCartItemData($customer_no, $item_id);
    // 現在の商品の数量を更新  /ここではすでにitemの情報があるかを確認するだけで更新はしない
    
    if ($qtyItem && isset($qtyItem[0])) {
      
      // 商品が既にカートに存在する場合、その数量を更新、
      $newQty = $qtyItem[0]['quantity'] + $qty; // + $qty 今の数量にプラスして
      $this->updateCartData($qtyItem[0]['crt_id'], $newQty);
      // ctg_idはcartテーブルの行を識別
      // $this->updateCartData($qtyItem[0]['crt_id'], $qtyItem[0]['quantity']);
    } else {   
    // 新しい商品をカートに追加
    // insertされた瞬間はブラウザーに表示しない。cart.phpにリダイレクトされることで、再度selectを行い最新のデータを取得
    // ユーザーが過去に購入したが、現在のカートには存在しない商品を再購入する場合、新しいアイテムとしてカートに追加する必要がある
    $insData = [
      'customer_no' => $customer_no,
      'item_id' => $item_id,
      'quantity' => $qty
    ];
     $this->db->insert($table, $insData);
  }
   // 更新後にカートページにリダイレクト
   header('Location: ./cart.php');
   exit;
  }

  public function getCartItemData($customer_no, $item_id)
  {
  //このようなSELECT文を作るイメージ
  // SELECT
  // c.crt_id,
  // i.item_id,
  // i.item_name,
  // i.price,
  // i.image ';
  // FROM
  // cart c
  // LEFT JOIN
  // item i
  // ON
  // c.item_id = i.item_id ';
  // WHERE
  // c.customer_no = ? AND c.delete_flg = ? ';
  $table = ' cart c LEFT JOIN item i  ON c.item_id = i.item_id  ';
  // LEFT JOIN : tableどうしを繋げるSQL文 : 一致する行だけ結合
  // ON : 結合情報を指定
  // cart tableとitem tableを連結させたものから情報をとってきた
  $column = ' c.crt_id, c.quantity, i.item_id, i.item_name, i.price, i.image ';
  // c.quantity追記 
  $where = ' c.customer_no = ? AND c.delete_flg = ? AND c.item_id = ?';
  // ここでdelete_flgの0をとってきて、1が削除
  $arrVal = [$customer_no, 0, $item_id];

  return $this->db->select($table, $column, $where, $arrVal);
  }
  
  public function getCartData($customer_no)
  {
  //このようなSELECT文を作るイメージ
  // SELECT
  // c.crt_id,
  // i.item_id,
  // i.item_name,
  // i.price,
  // i.image ';
  // FROM
  // cart c
  // LEFT JOIN
  // item i
  // ON
  // c.item_id = i.item_id ';
  // WHERE
  // c.customer_no = ? AND c.delete_flg = ? ';
  $table = ' cart c LEFT JOIN item i  ON c.item_id = i.item_id  '; 
  $column = ' c.crt_id, c.quantity, i.item_id, i.item_name, i.price, i.image ';
  $where = ' c.customer_no = ? AND c.delete_flg = ?';
  $arrVal = [$customer_no, 0];
  
  return $this->db->select($table, $column, $where, $arrVal);
  }
  
  // カートに商品を追加(再購入)
  public function addItemCart($customer_no, $item_id, $quantity, $qty)
  {
    // カートに同じ商品が既に存在するか確認
    $table = 'cart';
    $where =  'customer_no = ? AND item_id = ? AND delete_flg = 0';
    $arrVal = [$customer_no, $item_id];
    $cartItem = $this->db->select($table, '*', $where, $arrVal);

    if ($cartItem) {
    // 既に存在する場合は数量を更新
      $newQuantity = $cartItem[0]['num'] + $qty; // $quantityを$qtyに変更(数量を加算)
      $insData = ['num' => $newQuantity];
      $where =  'customer_no = ? AND item_id = ?';
      $arrVal = [$customer_no, $item_id];
      return $this->db->update($table, $insData, $where, $arrVal);
    } else {
       // 存在しない場合は新しいアイテムを追加
      $insData = [
        'customer_no' => $customer_no,
        'item_id' => $item_id,
        'num' => $quantity,
      ];
      return $this->db->insert($table, $insData);
    }
  }

  // カート情報を削除する : 必要な情報はどのカートを($crt_id)
  public function delCartData($crt_id)
  {
    $table = ' cart ';
    $insData = ['delete_flg ' => 1];
    $where = ' crt_id = ? '; // 削除対象のカートID
    $arrWhereVal = [$crt_id]; 
    return $this->db->update($table, $insData, $where, $arrWhereVal);
  }  

  public function clearUserCart($customer_no)
  {
    $table = ' cart ';
    $insData = ['delete_flg' => 1]; // キーと値の形式で更新データを指定
    $where = 'customer_no = ?';  // プレースホルダ
    $arrWhereVal = [$customer_no]; // バインドする値の配列
    return $this->db->update($table, $insData, $where, $arrWhereVal);
  }
  
  // 商品数と合計金額を取得する
  public function getItemAndSumPrice($customer_no)
  {
    //合計金額
    //SELECT
    //SUM( i.price ) AS totalPrice ";
    //FROM
    //cart c
    //LEFT JOIN
    //item i 
    //ON
    //c.item_id = i.item_id "
    //WHERE
    //c.customer_no = ? AND c.delete_flg =?';
    //合計金額
    $table = " cart c  LEFT JOIN item i ON c.item_id = i.item_id ";
    $column = " SUM( i.price * c.quantity ) AS totalPrice ";
    // SUMは特定の列の合計値を計算する関数, ASは名前変更、totalPriceにカラムを変更
    $where = ' c.customer_no  = ? AND c.delete_flg = ?';
    //（delete_flgが0)削除されたデータを区別するために0であることを条件に含めないと、削除されたデータも計算に含まれてしまう可能性がある
    $arrWhereVal = [$customer_no, 0];

    $res = $this->db->select($table, $column, $where, $arrWhereVal);
    $price = ($res !== false && count($res) !== 0) ? $res[0]['totalPrice'] : 0;

     // アイテム数 合計値
     $table = ' cart c ';
     // SUMはquantity列の全ての合計値
     $column = "SUM( c.quantity ) AS totalItems";
     $where = ' c.customer_no  = ? AND c.delete_flg = ?'; 
     $arrWhereVal = [$customer_no, 0];

     $res = $this->db->select($table, $column, $where, $arrWhereVal);
     $num = ($res !== false && count($res) !== 0) ? $res[0]['totalItems'] : 0;

     return [$num, $price];
     // $num - カートに入っている商品個数の合計。
     // $Price - カートに入っている商品金額の合計。
  }

 
  // crt_id、更新対象のカート内の商品を特定するための識別子、quantity更新される商品の数量を指定
  public function updateCartData($crt_id, $quantity)
  {
    $table = 'cart';
    $insData = ['quantity' => $quantity];
    // ['quantity' => $quantity] は、SET quantity = ? と同じ
    $where =  'crt_id = ? ';
    // 更新条件として、カートID (crt_id) を設定
    $arrWhereVal = [$crt_id];
    // 条件に使用する値として、カートID(crt_id)を設定
    return $this->db->update($table, $insData, $where, $arrWhereVal);
    // UPDATE テーブル名 SET 列1 = 値1, 列2 = 値2, ... WHERE 条件
    // UPDATE cart SET quantity = $quantity WHERE crt_id = ?
  }
  

  public function getOrderHistoryData($user_id, $offset, $orderPerPage)
  {
    // JOINするテーブルと取得するカラム
    $table = "orders o INNER JOIN order_details od ON o.order_id = od.order_id";
    $column = 'o.order_id, o.purchase_date, o.total_price, od.item_id, od.item_name, od.quantity, od.price, od.image';

    $where = 'o.user_id = ?';
    $arrVal = [$user_id];
    $orderBy = 'o.purchase_date DESC, o.order_id DESC';
    $limit = "$offset, $orderPerPage";
    return $this->db->select($table, $column, $where, $arrVal, $orderBy, $limit);

  } 

  // 注文履歴、ページネーション
  public function getTotalOrderCount($user_id) {
    $table = 'orders o INNER JOIN order_details od ON o.order_id = od.order_id';
    $col = 'COUNT(*) as total_count'; 

    $where = 'user_id = ?';
    $arrVal = [$user_id];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false) ? $res[0]['total_count'] : 0;
  }

  // 個人情報詳細
  public function getOrders($user_id, $offset, $orderPerPage)
  {
    $table = "orders o LEFT JOIN order_details od ON o.order_id = od.order_id";
    $column = 'o.order_id, o.family_name, o.first_name, o.zip1, o.zip2, o.address, o.tel1,  o.tel2, o.tel3, o.shipping_fee, o.total_price, o.payment_method, o.purchase_date, od.item_id, od.item_name, od.image, od.price, od.quantity'; 
    $where = 'user_id = ?';
    $arrVal = [$user_id];
    $orderBy = 'purchase_date DESC, order_id DESC';
    $limit = "$offset, $orderPerPage";
   
    return $this->db->select($table, $column, $where, $arrVal, $orderBy, $limit);
  }

  public function getOrderItems($order_id) {
    $table = "order_details";
    $column = 'item_id, item_name, image, quantity, price';
    $where = 'order_id = ?';
    $arrVal = [$order_id];
    return $this->db->select($table, $column, $where, $arrVal);
  }


  public function getReorderData($user_id)
  {
  //このようなSELECT文を作るイメージ
  // SELECT
  // c.crt_id,
  // i.item_id,
  // i.item_name,
  // i.price,
  // i.image ';
  // FROM
  // cart c
  // LEFT JOIN
  // item i
  // ON
  // c.item_id = i.item_id ';
  // WHERE
  // c.customer_no = ? AND c.delete_flg = ? ';
  $table = ' cart c LEFT JOIN order_details od  ON c.item_id = od.item_id  '; //　LEFT 
  $column = ' c.crt_id, od.item_id, od.item_name, od.price, od.image, od.quantity ';
  $where = ' od . user_id = ? , c.delete_flg = ?';
  $arrVal = [$user_id, 0];

  return $this->db->select($table, $column, $where, $arrVal);
  }

  public function getAdminOrderList()
  {
    try {
        $table = 'orders o INNER JOIN order_details od ON o.order_id = od.order_id LEFT JOIN signup s ON o.user_id = s.user_id';
        $column = 'o.order_id, o.user_id, o.shipping_fee, o.total_price, o.payment_method, o.purchase_date, od.item_id, od.item_name, od.image, od.quantity, od.price';
        $where = 'od.delete_flg = ?';
        $arrVal = [0];
        return $this->db->select($table, $column, $where, $arrVal);
     } catch(\Exception $e) {
        error_log('getAdminOrderListでエラーが発生しました : ' . $e->getMessage());
        throw new \Exception('管理者注文リストの取得に失敗しました。');
     }
  }

  public function delAdminOrderList($order_id)
  { 
    $table = 'orders';
    $insData = ['delete_flg' => 1];
    $where = 'order_id = ?'; //削除対象のカートID
    $arrVal = [$order_id]; 
    $this->db->update($table, $insData, $where, $arrVal);

    $orderDetailsTable = 'order_details';
    $this->db->update($orderDetailsTable, $insData, $where, $arrVal);
  }  

  public function getAdminContact()
  {
    try {
    $table = 'contact';
    $column = 'id, username, email, contents, created_at';
    $where = 'delete_flg = ?';
    $arrVal = [0];
    return $this->db->select($table, $column, $where, $arrVal); 
    } catch(\Exception $e) {
      error_log('getAdminContactでエラーが発生しました : ' . $e->getMessage());
      throw new \Exception('管理者お問い合わせリストの取得に失敗しました。');
    }
  }

  public function delAdminContact($id)
  {
    $table = 'contact';
    $insData = ['delete_flg' => 1];
    $where = 'id = ?';
    $arrVal = [$id];
    return $this->db->update($table, $insData, $where, $arrVal);
  }

  public function getAdminSignup()
  {
    try {
        $table = 'signup';
        $column = 'user_id, family_name, first_name, family_name_kana, first_name_kana, sex, year, month, day, zip1, zip2, address, email, tel1, tel2, tel3';
        $where = 'delete_flg = ?';
        $arrVal = [0];
        return $this->db->select($table, $column, $where, $arrVal);
     } catch(\Exception $e) {
        error_log('getAdminSignupでエラーが発生しました : ' . $e->getMessage());
        throw new \Exception('管理者新規情報登録リストの取得に失敗しました。');
     } 
  }

  public function delAdminSignup($user_id)
  {
    $table = 'signup';
    $insData = ['delete_flg' => 1];
    $where = 'user_id = ?';
    $arrVal = [$user_id];
    return $this->db->update($table, $insData, $where, $arrVal); 
  } 

}



