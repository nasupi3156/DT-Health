<?php

namespace shopping\lib;

class Error {
  
  // 配列にユーザーの入力データをセット、初期化をし他のユーザーが同じクラスを使用する際に前回のデータが残らないように
  private $dataArr = [];  
  // 各エラーチェックメソッドがエラーを検出しエラーメッセージを格納するためのもの
  private $errArr = [];   
  
  // 初期化,実行
  public function __construct()
  {
  }
  
  public function errorCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    // エラーメッセージを空で初期化する。
    $this->createErrorMessage();
    $this->familyNameCheck();
    $this->firstNameCheck();
    $this->familyNameKanaCheck();
    $this->firstNameKanaCheck();
    $this->sexCheck();
    $this->birthCheck();
    $this->zipCheck();
    $this->addCheck();
    $this->telCheck();
    $this->mailCheck();
    // $this->trafficCheck();
    $this->passCheck();
  
    return $this->errArr;
  }

  public function loginErrorCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();
    $this->mailCheck();
    $this->passCheck();

    return $this->errArr;
  }

  public function contactErrorCheck($dataArr) 
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();
    $this->usernameCheck();
    $this->mailCheck();
    $this->contentsCheck();
    return $this->errArr;
  }
  
  // public function modalErrorCheck($dataArr) 
  // {
  //   $this->dataArr = $dataArr;
  //   $this->createErrorMessage();
  //   $this->familyNameCheck();
  //   $this->firstNameCheck();
  //   $this->birthCheck();
  //   $this->zipCheck();
  //   $this->addCheck();
  //   $this->telCheck();
  //   $this->mailCheck();
  //   return $this->errArr;
  // }

  public function profileModalErrorCheck($dataArr) 
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();
    $this->familyNameCheck();
    $this->firstNameCheck();
    $this->birthCheck();
    $this->zipCheck();
    $this->addCheck();
    $this->mailCheck();
    $this->telCheck();
    return $this->errArr;
  }
  
  private function createErrorMessage()
  {
    foreach($this->dataArr as $key =>$val) {
      $this->errArr[$key] = '';
      // errArrの配列と同じ処理
      // 各キーに対して空文字列で初期化
    }
  }
  
  private function familyNameCheck()
  {
    // ユーザーからの入力データを格納するために使用
    if ($this->dataArr['family_name'] === '')
    {
      $this->errArr['family_name'] = 'お名前(氏)を入力してください';
    }
  }
  
  private function firstNameCheck()
  {
    if ($this->dataArr['first_name'] === '')
    {
      $this->errArr['first_name'] = 'お名前(名)を入力してください';
    }
  } 

  public function familyNameKanaCheck()
  {
    if ($this->dataArr['family_name_kana'] === '')
    {
      $this->errArr['family_name_kana'] = 'お名前(氏)(カナ)を入力してください';
    }
  }

  public function firstNameKanaCheck()
  {
    if ($this->dataArr['first_name_kana'] === '')
    {
      $this->errArr['first_name_kana'] = 'お名前(名)(カナ)を入力してください';
    }
  }
  
  // お問い合わせ
  public function usernameCheck() 
  {
    if ($this->dataArr['username'] === '') 
    {
      $this->errArr['username'] = 'お名前を入力してください';
    }
  }
  
  private function sexCheck()
  {
    if ($this->dataArr['sex'] === '') {
      $this->errArr['sex'] = '性別を選択してください';
    }
  }
  
  private function birthCheck()
  {
    if (!$this->dataArr['year'] === '') {
      $this->errArr['year'] = '生年月日の年を選択してください';
    }
    if (!$this->dataArr['month'] === '') {
      $this->errArr['month'] = '生年月日の月を選択してください';
    }
    if (!$this->dataArr['day'] === '') {
      $this->errArr['day'] = '生年月日の日を選択してください';
    }
    // checkdateの関数を用いて正しい日付かチェックする
    // 月、日にち、年数、2月30日はないからエラー
    // if (checkdate($this->dataArr['month'], $this->dataArr['day'], $this->dataArr['year']) === false) 
    if ($this->dataArr['year'] && $this->dataArr['month'] && $this->dataArr['day'] && !checkdate($this->dataArr['month'], $this->dataArr['day'], $this->dataArr['year']))
    {
      $this->errArr['year'] = '正しい日付を入力してください。';
    } 
    // strtotime : タイムスタンプにし現在時刻より前かどうか未来の設定ができない
    // nowで現在の日時で0より大きかったら(未来の数値を設定できない)今現在の日時より前のものかを確かめてる。後ならエラー
    if (strtotime($this->dataArr['year'] . '-' .$this->dataArr['month'] . '-' . $this->dataArr['day']) - strtotime('now') > 0) {
      $this->errArr['year'] = '正しい日付を入力してください。';
    }
  }
  
  private function zipCheck()
  {
    if (preg_match('/^[0-9]{3}$/',$this->dataArr['zip1']) === 0) {
      $this->errArr['zip1'] = '郵便番号の上は半角数字3桁で入力してください';
    }
    if (preg_match('/^[0-9]{4}$/',$this->dataArr['zip2']) === 0) {
      $this->errArr['zip2'] = '郵便局の下は半角数字4桁で入力してください';
    }
  }
  
  private function addCheck() 
  {
    if ($this->dataArr['address'] === '') {
      $this->errArr['address'] = '住所を入力してください';
    }
  }
  
  private function mailCheck()
  {
    $pattern = '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/';
    $email = $this->dataArr['email'] ?? '';
    if (preg_match($pattern, $email) === 0) {
        $this->errArr['email'] = 'メールアドレスを入力してください';
      
    // if (preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/',$this->dataArr['email']) === 0) {
    //   $this->errArr['email'] = 'メールアドレスを正しい形式で入力してください';
    }
    
  }
  
  private function telCheck()
  {  
      //dは数値 , 一桁から六桁まで
    if (preg_match('/^\d{3}$/',$this->dataArr['tel1']) === 0) {
      $this->errArr['tel1'] = '電話番号の上は半角数字で3桁で入力してください';
    }
    if (preg_match('/^\d{4}$/',$this->dataArr['tel2']) === 0) {
      $this->errArr['tel2'] = '電話番号の真ん中は半角数字で4桁で入力してください';
    }
    if (preg_match('/^\d{4}$/',$this->dataArr['tel3']) === 0) {
      $this->errArr['tel3'] = '電話番号の下は半角数字で4桁で入力してください';
    }
    
      // strlen($this->dataArr['tel1'] . $this->dataArr['tel2'] . $this->dataArr['tel3']) >= 12) {
      //strlen,文字列を返す関数
      //合計12以上ならエラーの処理
  }
  
  private function passCheck()
  {
    $password = $this->dataArr['password'] ?? '';
    if ($this->dataArr['password'] === '') 
    {
      $this->errArr['password'] = 'パスワードを入力してください';
    } elseif (!preg_match('/^(?=.*\d)(?=.*[a-zA-Z]).{6,}$/', $password)) 
    { //パスワードのが指定の条件を満たしているか確認
      $this->errArr['password'] = 'アルファベットと数字6文字以上で入力してください';
    } 
  }
  
  // private function trafficCheck()
  // {
  //   if ($this->dataArr['traffic'] === []){
  //     $this->errArr['traffic'] = '最低1つの食べ物を入力してください';
  //   }
  // }
  
  public function contentsCheck() 
  {
    if ($this->dataArr['contents'] === '') {
      $this->errArr['contents'] = 'お問い合わせ内容を入力してください。';
    }
  }
  
  public function getErrorFlg()
  {
    $err_check = true;
    foreach($this->errArr as $key => $value) {
      if ($value !== '') {
        $err_check = false;
        break;
        }
      }
    return $err_check;
    }
  }
  // err_checkで検証、getErrorFlgで検証が正しいのかを確認
  // $err_check = true : 初めはエラーがないと仮定してtrue
  // $this->errArrを$key => $valueでエラーのループ処理
  // 空の文字列は、エラーがないことを示す、フォームからバリデーションチェックに合格した場合は、問題がない
  // 空じゃない文字列は、バリデーションが空ではないので、エラーチェックと紐付けられ、エラーチェックに合格しなかったのでfalse 