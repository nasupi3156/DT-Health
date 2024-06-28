<?php

namespace shopping\lib;

class Initial
{
  public static function getDate()
  {
    $yearArr = [];
    $monthArr = [];
    $dayArr = [];
    //カラー配列付与
    $next_year = date('Y') + 1;
    //来年のデータを定義、Y(year)は年数(今年の数字2023年)、に+1で2024年
    
    for($i = 1900; $i < $next_year; $i ++) {
    $year = sprintf("%04d",$i);
    $yearArr[$year] = $year . '年'; 
    //年を作成  未満の場合繰り返し
    //sprintf : (4桁)の数値を文字列にしフォーマット化して値を返す 
    }

    for($i = 1; $i < 13; $i ++) {
    $month = sprintf("%02d", $i);
    $monthArr[$month] = $month . '月'; 
    //月を作成
    }

    for($i = 1; $i < 32; $i ++) {
    $day = sprintf("%02d", $i);
    $dayArr[$day] = $day . '日';
    //日を作成 
    }
    
    return [$yearArr, $monthArr, $dayArr];
  }
  
  public static function getSex()
  {
    $sexArr = ['男性' => '男性', '女性' => '女性', 'その他' => 'その他'];
    // キーと値 : この場合は両方が、男性なのでデータベースとブラウザー両方に保存される
    // $sexArr = ['male' => '男性', 'female' => '女性', 'other' => 'その他'];
    // キーがデータベース、値がブラウザー
    return $sexArr;
  }

  // public static function getTrafficWay()
  // {
  //   $trafficArr = ['野菜', '果物', '肉・卵', '魚介類', '乳製品'];
  //   return $trafficArr;
  // }
  
}