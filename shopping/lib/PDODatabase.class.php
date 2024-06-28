<?php

/**
 * ファイル名 : PDODatabase.class.php(商品に関するプログラムのクラスファイル、Model)
 * PDO(PHP Data Objects) : PHP標準(5.1.0以降)のDB接続クラス
 */

namespace shopping\lib;

  class PDODatabase
  {
    private $dbh = null; 
    private $db_host = '';
    private $db_user = '';
    private $db_pass = '';
    private $db_name = '';
    private $db_type = '';
    private $order = '';
    private $limit = '';
    private $offset = '';
    private $groupby = '';
  
    public function __construct($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
      $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name, $db_type);
      $this->db_host = $db_host;
      $this->db_user = $db_user;
      $this->db_pass = $db_pass;
      $this->db_name = $db_name;
      //SQL関連
      $this->order = '';
      $this->limit = '';
      $this->offset = '';
      $this->groupby = '';
      // データベース接続情報を受け取りconnectDBメソッドを呼び出しデータベースに接続、接続情報はクラスのプロパティーに保存
    }
    // connectDBの中でMYSQLを使う
    private function connectDB($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
      try
      {
        // 接続エラー発生 → PDOExceptionオブジェクトがスローされる → 例外処理をキャッチする
        switch ($db_type) {
          // データベースに接続する処理
          case 'mysql':
            $dsn = 'mysql:host =' . $db_host . ';dbname=' . $db_name;
            $dbh = new \PDO($dsn, $db_user, $db_pass);
            // データベース接続 
            $dbh->query('SET NAMES utf8'); 
            // 文字コード指定
            break;

          case 'pgsql': // 使わない 
            $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . ' port=5432';
            $dbh = new \PDO($dsn, $db_user, $db_pass);
          break;
        }
      } catch (\PDOException $e) {
          var_dump($e->getMessage());
          // getMessage : PDOでもともと用意
          exit();
        }  
          return $dbh;
          //エラーになった時の処理
    }

    // 今回は使われていない
    public function setQuery($query = '', $arrVal = [])
    {
      $stmt = $this->dbh->prepare($query);
      $stmt->execute($arrVal);
    }
   
    public function select($table, $column = '', $where = '', $arrVal = [], $orderBy = '', $limit = '', $groupBy = '')
    {
      $sql = $this->getSql('select', $table, $column, $where, $orderBy, $limit, $groupBy);
     
      // if ($orderBy != '') {
      //   $sql .= " $orderBy";
      // }

      // if ($limit !== '') {
      //   $sql .= " $limit";
      // }
     
      // SQLクエリとバインドされる値をログに記録
      $this->sqlLogInfo($sql, $arrVal);
      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($arrVal);

      if ($res === false) {
        // エラーがあればエラーを返す処理
        $this->catchError($stmt->errorInfo());
      }

        
      
        // データを保存するために配列を初期化
        $data = [];
        // fetch_assoc : 1行ずつデータ取得、resultに連想配列として格納
        // whileループはfetchがデータを取得するたびに実行、取得できるデータがなくなると終了。
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        // resultではループ処理しても1行しか取得できないので、dataに置き換えてdataで全ての値を取得
        // array_pushでresultの1行からdataで全ての値を取得すると多次元配列になるイメージ
        array_push($data, $result);
      }
      return $data;
    }

    public function count($table, $where = '', $arrVal = [])
    {
      $sql = $this->getSql('count', $table, $where);

      $this->sqlLogInfo($sql, $arrVal);
     
      $stmt = $this->dbh->prepare($sql);

      $res = $stmt->execute($arrVal);

      if ($res === false) {
        $this->catchError($stmt->errorInfo());
      }
      
      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      // データベースがからとってきた配列を数値型にして返す
      return intval($result['NUM']);
      // intval : カウントを整数として返す
      // count : レコードからとってきた値をid=1 数値にするイメージ
    }

    public function setOrder($order = '')
    {
      if ($order !== '') {
        $this->order = ' ORDER BY ' . $order;
      }
    }
    
    public function setLimitOff($limit = '', $offset = '')
    {
      if ($limit !== "") {
        $this->limit = " LIMIT " . $limit;
      }
      if ($offset !== "") {
        $this->offset = " OFFSET " . $offset;
      }
    }

    public function setGroupBy($groupby)
    {
      if ($groupby !== "") {
        $this->groupby = ' GROUP BY ' . $groupby;
      }
    }

    // 引数$typeはswitch文でどのsqlを生成するか、getsqlの引数'select'はどのクエリのタイプか
    private function getSql($type, $table, $column = '', $where = '', $orderBy = '', $limit = '', $groupBy = '')
    {
      switch ($type) {
        case 'select':
          $columnKey = ($column !== '') ? $column : "*";
          // 三項演算子
        break;        
   
        case 'count':
          $columnKey = 'COUNT(*) AS NUM ';
          // count : レコード 値がNUMに3つなら3
          // とってきた全ての値をNUMで取得、行数取得
        break;
          default: 
          //switch終了したら全体処理から抜ける
        break;
      }

      $whereSQL = ($where !== '') ? ' WHERE ' . $where : '';
     
      // $groupBySQL = ($groupBy !== '') ? " GROUP BY " . $groupBy : '';

      $orderSQL = ($orderBy !== '') ? ' ORDER BY ' . $orderBy : '';

      $limitSQL = ($limit !== '') ? ' LIMIT ' . $limit : '';

      $other = $this->groupby . "  " . $this->order . "  " . $this->limit . "  " . $this->offset;
      // $other : 今回は使ってないので省略
        
      //sql文の作成
      $sql = " SELECT " . $columnKey . " FROM " . $table . $whereSQL . $orderSQL . $limitSQL . $other; 
      return $sql;
    }

    // cart.class.phpのinsCartDataの場合
    public function insert($table, $insData = [])
    {
      $insDataKey = [];
      $insDataVal = []; // [$customer_no, $item_id]
      $preCnt = [];

      $columns = '';
      $preSt = '';
      // 繰り返し処理
      foreach ($insData as $col => $val)
      {
        // とってきた値を上記の $insDataKey = []; $insDataVal = [];に入れる
        $insDataKey[] = $col; // [$customer_no, $item_id]
        $insDataVal[] = $val; // [$customer_no, $item_id]
        $preCnt[] = '?';  // [?,?]
      }

      $columns = implode(",", $insDataKey); // 配列を文字列にした形 : 'customer_no, item_id' 
      $preSt = implode(",", $preCnt); // '?, ?'
      // sql文を作り実行
      $sql = " INSERT INTO "
        . $table. " ("            //cart
        . $columns. ") VALUES ("  //$customer_no, $item_id
        . $preSt                  // '?, ?'
        . ") ";
        //INSERT INTO cart (customer_no, item_id) VALUES (?,?)

        $this->sqlLogInfo($sql, $insDataVal);

        $stmt = $this->dbh->prepare($sql); //INSERT INTO実行準備

        $res = $stmt->execute($insDataVal); //引数$insDataValに([$customer_no, $item_id)がVALUESの(?, ?)の中に入る 
        // (?, ?)に$customer_no, $item_idが入る
        if ($res === false) {
          $this->catchError($stmt->errorInfo());
        }
        return $res;
    }
    
    /* update引数の$insData = [], $where = ''が逆で論理削除ができなかったので注意 */
    public function update($table, $insData = [],  $where = '', $arrWhereVal = [])
    {
      // if ($customer != '') {
      //   $sql .= " $orderBy";
      // }

      $arrPreSt = [];
      foreach ($insData as $col => $val) {
        $arrPreSt[] = $col . " =? ";
        // delCartDataでdelete_flg ' => 1がinsDataなので$colに入りdelete_flg ' = ?が作られ、配列[]なのでimplodeで文字列に処理
      }

      $preSt = implode(',', $arrPreSt);
      
      
      $sql = " UPDATE "
        . $table
        . " SET "
        . $preSt    
        . " WHERE "
        . $where;    

      $updateData = array_merge(array_values($insData), $arrWhereVal);
      // array_merge : 値を単一の配列に結合するために使用、なぜならexecuteは全てのパラメータを単一の配列で受け取る必要があるから
      // array_values : 配列からキーを除外して値だけ取り出す関数
      // 更新データ$insData,更新条件（$arrWhereVal）を結合、一つの配列 $updateData
      $this->sqlLogInfo($sql, $updateData);

      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($updateData);

      if ($res === false) {
        $this->catchError($stmt->errorInfo()); 
      }
      return $res;
    }

    public function getLastId()
    {
      return $this->dbh->lastInsertId();
    }
    // lastInsertId, PDOでもともと用意されてるメソッド
    // データベースに新しいレコードを挿入した直後に、そのレコードの自動生成されたID（自動増分のAUTO_INCREMENTで指定されたprimary_key）を取得するために使われる
    // lastInsertId()はPDOの機能であり、PDOインスタンスを使ってデータベース操作を行う際に直接呼び出される。
    // IDはデータベースに新しい行が挿入された直後に使用され、その行を一意に特定するために利用
    // getLastId()はlastInsertId()を利用して、より使いやすい形でアプリケーション内で最後に挿入されたIDを取得するための関数
    // 一つのテーブルに新しいレコードを挿入し、この新しいレコードを別のテーブルで参照する必要がある時など

    private function catchError($errArr = [])
    {
      $errMsg = (!empty($errArr[2]))? $errArr[2]:""; 
      die("SQLエラーが発生しました。" . $errArr[2]);
    }
  
    private function makeLogFile()
    {
      $logDir = dirname(__DIR__) . "/logs";
      if (!file_exists($logDir)) {
        mkdir($logDir, 0777);
      }
      // file_exists : ファイルがあるかどうかを調べる関数
      // ファイルがない場合ディレクトリを作りファイルを作る
      // 0777、リナックスコマンド : 権限を付与
      // デフォルトで0777が入る

      $logPath = $logDir . '/shopping.log';
      if (!file_exists($logPath)) {
        touch($logPath);
      }
      return $logPath;
    }

    private function sqlLogInfo($str, $arrVal = [])
    {
      $logPath = $this->makeLogFile();
      $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", $arrVal));
      error_log($logData, 3, $logPath);
    }

    public function getPostcodeSearch($zip)
    {
      $sql = "SELECT pref, city, town FROM postcode Where zip = :zip LIMIT 1";

      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(':zip', $zip, \PDO::PARAM_STR);
      $stmt->execute();
      $res = $stmt->fetch(\PDO::FETCH_ASSOC);
      return $res;
      // zip1.zip2を結合し、postcode内のzipで検索
    }

    public function loginInfo($email, $password)
    {
      $sql = 'SELECT user_id, family_name, password, is_admin FROM signup WHERE email = :email';
    
      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
      
      $stmt->execute();
      $user = $stmt->fetch(\PDO::FETCH_ASSOC);
      // 連想配列 : 1行の結果(user_id、familyなど)を取得
    
      if ($user && password_verify($password, $user['password'])) {
      return $user; 
      }
      return false;
    }

    public function adminLogin($email, $password)
    {
      $sql = "SELECT * FROM signup WHERE email = :email AND is_admin = 1";
      $stmt = $this->dbh->prepare($sql);

      $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
      $stmt->execute();
      $adminUser = $stmt->fetch(\PDO::FETCH_ASSOC);
      if ($adminUser && password_verify($password, $adminUser['password'])) {
        return $adminUser; 
      }
      return false;
    }

    public function getUserProfile($user_id) 
    {
      $user_id = $_SESSION['user_id'];
      $sql = 'SELECT * FROM signup WHERE user_id = ?';
      $stmt = $this->dbh->prepare($sql);
      $stmt->execute([$user_id]);

      $user = $stmt->fetch(\PDO::FETCH_ASSOC);
  
      return $user;
      // ログインなどでsessionのuser_idを取得して、それを?プレースフォルダーに入れる。
      // 実行されるSQLクエリはSELECT * FROM signup WHERE user_id = 'user_id';
      // $_SESSION['user_id']に保存されているuser_idを使用して、signupテーブルから該当するユーザーの情報を全て取得し、その情報をメソッドの呼び出し元に返す。取得したユーザー情報をもとに、購入確認画面などでユーザーの登録情報を表示。
    }
        
    public function emailCheck($email) 
    {
      $sql = 'SELECT COUNT(*) as cnt FROM signup WHERE email=?';
      // count(*)関数 : クエリに一致する行の合計数、count結果をas cntでcntという名前で参照
      $stmt = $this->dbh->prepare($sql);
      $stmt->execute([$email]);
      $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    
      return $user;
    }
    
    public function insertUser($dataArr)
    {
      // パスワードをハッシュ化
      $hashedPassword = password_hash($dataArr['password'], PASSWORD_DEFAULT);
      // $dataArr['registDate'] = $dataArr['registDate'] ?? date('Y-m-d H:i:s'); 
      try {
      $sql = "INSERT INTO signup (family_name, first_name, family_name_kana,  first_name_kana, sex, year, month, day, email, zip1, zip2, address, tel1, tel2, tel3, password, regist_Date) VALUES (?, ?, ?, ?, ? ,?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?, ?)";
      
      $stmt = $this->dbh->prepare($sql);
      
      $params = [
        $dataArr['family_name'], 
        $dataArr['first_name'],
         $dataArr['family_name_kana'],  
         $dataArr['first_name_kana'], 
         $dataArr['sex'], 
         $dataArr['year'], 
         $dataArr['month'],
         $dataArr['day'],
         $dataArr['email'], 
         $dataArr['zip1'], 
         $dataArr['zip2'], 
         $dataArr['address'], 
         $dataArr['tel1'], 
         $dataArr['tel2'], 
         $dataArr['tel3'], 
         $hashedPassword, date('Y-m-d H:i:s')
        //  $dataArr['registDate'] ?? date('Y-m-d H:i:s')
        // 今回フォームからの日時指定はないので使わない
    ];
      $stmt->execute($params);
      return true;
    } catch (\PDOException $e) {
      // $eからエラーメッセージを取得
      echo "データベースにエラーが発生しました。" . $e->getMessage();
      return false;  
    }
  }
  // emailのUNIQUE : 制約をカラム try,catch

  public function insertContact($dataArr)
  {
    $sql = "INSERT INTO contact (username, email, contents) VALUES (?, ?, ?)";
    $stmt = $this->dbh->prepare($sql);
    $params = array_values($dataArr);
    $stmt->execute($params);
      return true;
  } 
  
  // purchase
  public function userPurchaseProfile($params, $user_id)
  {
    $sql = "UPDATE signup SET
            family_name = :family_name,
            first_name = :first_name, 
            zip1 = :zip1, 
            zip2 = :zip2,   
            address = :address, 
            tel1 = :tel1, 
            tel2 = :tel2, 
            tel3 = :tel3
            WHERE user_id = :user_id";
    $stmt = $this->dbh->prepare($sql);
    $params['user_id'] = $user_id;
    $updateEdit = $stmt->execute($params); 
    return $updateEdit;
  }

  // profile
  public function updateUserProfile($params, $user_id) 
  {
    try {
    $sql = "UPDATE signup SET 
            family_name = :family_name,
            first_name = :first_name,  
            email = :email, 
            year = :year, 
            month = :month, 
            day = :day, 
            zip1 = :zip1, 
            zip2 = :zip2,   
            address = :address, 
            tel1 = :tel1, 
            tel2 = :tel2, 
            tel3 = :tel3
       
            WHERE user_id = :user_id";
  
    $stmt = $this->dbh->prepare($sql);
    $params['user_id'] = $user_id;
    $stmt->execute($params);
    return true;
  }  catch (\PDOException $e) {
    echo "データベースにエラーが発生しました。" . $e->getMessage();
    return false;  
  }
}

  public function emailConfirm($email)
  {
    // LIMIT : 最初の1行だけを取得
    $sql = 'SELECT * FROM signup WHERE email = :email LIMIT 1';
    $stmt = $this->dbh->prepare($sql);
    // :emailはsqlのプレースホルダー何処に値を挿入するかを指定、$emailがプレースホルダーにバインドする値
    // \PDO::PARAM_STRはデータ型を指定、これは文字列型  
    $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
    $stmt->execute();
    $emailUser = $stmt->fetch(\PDO::FETCH_ASSOC);
   
    return $emailUser ;
  }

  public function insertPasswordReset($user_id, $email, $token, $expires)
  {
    $sql = "INSERT INTO password_reset (user_id, email, token, expires) VALUES (:user_id, :email, :token, :expires)";
    // expiresはUNIXタイムスタンプなのでDATETIME形式に変換
    $expires_time = date('Y-m-d H:i:s', $expires);
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
    $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
    $stmt->bindParam(':expires', $expires_time, \PDO::PARAM_STR);
     // $expires_time : 変換した日時をバインド
    $stmt->execute();
  }
  
  // reset
  public function selectToken($token)
  {
    $sql = "SELECT * FROM password_reset WHERE token = :token";
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
    $stmt->execute();
    $passwordResetUser = $stmt->fetch(\PDO::FETCH_ASSOC);
    return  $passwordResetUser;
  }
  
  // pass-reset
  public function updatePassword($email, $hashedPassword) 
  {
    $sql =  "UPDATE signup SET password = :password WHERE email = :email";
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindValue(':password', $hashedPassword, \PDO::PARAM_STR);
    // 文字列としてpasswordをバインド、
    $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
    // 整数としてユーザIDをバインド, 整数はint
    $updateSuccess = $stmt->execute();
    return $updateSuccess;
  }
 
  public function deleteToken($token)
  {
    $sql = "DELETE FROM password_reset WHERE token = :token";
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
    $stmt->execute();
  }

  // session_keyをupdate
  public function updateSessionKey($customer_no, $newSessionId)
  {
    $sql = "UPDATE session SET session_key = :newSessionId WHERE customer_no = :customerNo";
    
    $stmt = $this->dbh->prepare($sql);
 
    $stmt->bindParam(':newSessionId', $newSessionId, \PDO::PARAM_STR);
    $stmt->bindParam(':customer_no', $customer_no, \PDO::PARAM_INT);
    $stmt->execute();
  }

  public function logicalDeleteUser($user_id)
  {
    $sql = "UPDATE signup SET is_deleted = true WHERE user_id = :user_id";
    
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
    // trueとfalseだけど0と1なのでint(整数)
    $userDelete = $stmt->execute();
    return $userDelete;
  }

  public function isUserDeleted($email)
  {
    $sql = "SELECT is_deleted FROM signup WHERE email = :email";
    $stmt = $this->dbh->prepare($sql);
    $stmt -> bindParam(':email', $email, \PDO::PARAM_STR);
    $stmt -> execute();
    // 単一の列の値を取得する、fetchColumn()を使用してis_deleted列の値を取得し、その値が1であればtrueを返す
    return $stmt->fetchColumn() == 1;
  }
  
  public function updateLoginStatus($user_id, $logged_in)
  {
    $sql = "UPDATE signup SET logged_in = :logged_in WHERE user_id = :user_id";
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
    $stmt->bindParam(':logged_in', $logged_in, \PDO::PARAM_INT);
    $stmt->execute();
  } 
}
