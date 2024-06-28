<?php
/**
 * ファイル名 : Session.class.php(セッション関係のクラスファイル,Model)
 * セッション: サーバ-側に一時的にデータを保存する仕組みのこと
 *  基本的に、keyで判断をして、IDを取るというのが流れ
 */
namespace shopping\lib;

class Session
{
  public $session_key = ''; // 初期化(まっさらな箱)
  public $db = NULL; 
   
  // ($db)クラス内でデータベース関連の操作が可能
  public function __construct($db)
  {  
    session_start();
    // セッションをスタートする
    $this->session_key = session_id();
    // セッションIDを取得する
    $this->db = $db;
    // PDOでdbを使用設定
  }
 
  public function checkSession()
  {
    // セッションIDのチェック
    // セッションIDがある(過去にショッピングカートに来たことがある)
    $customer_no = $this->selectSession();
    if ($customer_no !== false) {
      // ユーザーのセッション情報を保持します。
      $_SESSION['customer_no'] = $customer_no;
    } else {
     // セッションIDがない(初めてこのサイトに来ている)
     $res = $this->insertSession();
    if ($res === true) {
      $_SESSION['customer_no'] = $this->db->getLastId();
      // getLastID関数 : データベースに最後にinsertされたIDが入る、insertされた後この関数を使えばcustomer_noに簡単にアクセスできて便利
      $_SESSION['family_name'] = '';
      // 空は初期値で真っ白な箱、ログインしてない状態でもエラーを防ぐ
    } else {
      $_SESSION['customer_no'] = '';
      $_SESSION['family_name'] = '';
    }
  }
  // selectSession メソッドを使用して、データベースに保存されているセッションIDと現在のセッションIDを比較。
}

  private function selectSession()
  {
    $table = 'session';
    $col = 'customer_no';
    $where = ' session_key = ? ';
    $arrVal = [$this->session_key];
   
    $res = $this->db->select($table, $col, $where, $arrVal); 
    return (count($res) !== 0) ? $res[0]['customer_no'] : false;
    // selectでfethとarray_push使って多次元配列になっているので$res[0]要素のインデックス番号指定、その要素のcustomer_noを持ってきている
    // customer_noの1,2,3などのどれかをとってきている、session_keyと一緒にcustomer_noを返してる
  }

  private function insertSession()
  {
    $table = 'session';
    $insData = ['session_key' => $this->session_key];
    $res = $this->db->insert($table, $insData);
    return $res;
  }

  public function loginUser($email, $password)
  {
    $loginInfo = $this->db->loginInfo($email, $password);
    if ($loginInfo) {
      $this->userInfo($loginInfo);
      return true;
    }
    return false;
  }

  // loginInfoメソッドがデータベースからユーザーのログイン情報を取得し、その情報が $loginInfoに格納。userInfoメソッド内で$dataとして$loginInfoを受け取り、その中身（ユーザーの情報)を$_SESSIONに格納。
  public function userInfo($data) 
  {
    // 引数を取ることでメソッド内で$dataが使える
    $_SESSION['user_id'] = $data['user_id'];         
    $_SESSION['customer_no'] = $data['customer_no'];  // 顧客番号
    $_SESSION['family_name'] = $data['family_name']; 
    $_SESSION['is_admin'] = $data['is_admin'];  
  }
  

  public function isUserLogin() 
  {
    if (isset($_SESSION) && isset($_SESSION['family_name']) && isset($_SESSION['user_id'])
    && !empty($_SESSION['family_name']) && !empty($_SESSION['user_id']) 
    ){
      return true;
    }
      return false;
  }

  public function logout() {
    // 必要に応じて、特定のセッション変数をアンセット
    unset($_SESSION['customer_no']);
    unset($_SESSION['family_name']);
    unset($_SESSION['user_id']);
    // セッション変数を破棄
    session_unset();
    // セッションを完全に破棄
    session_destroy();
  } 

}



