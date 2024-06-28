$(function(){
  // 定義
  $('#address_search').click(function(){
    var zip1 = $('#zip1').val();
    var zip2 = $('#zip2').val();// val:値：000-0000など入ります

    var entry_url = $('#entry_url').val();
    // match : falseの場合はnullを返す
    if (zip1.match(/[0-9]{3}/) === null || zip2.match(/[0-9]{4}/) === null) {
      // 3桁、4桁じゃなければ、alert
      alert('正確な郵便番号を入力してください。');
      return false; // ページ遷移をしない(同じページにとどまる)
    } else {
      $.ajax({
        type : "get",
        url : entry_url + "/postcode_search.php?zip1=" + escape(zip1) + "&zip2=" + escape(zip2),
        // get通信 urlパラメータ
        // type : getの値がdataに入ってくる
      }) .then(
        // 今回はthen
        function(data){
          if (data == 'no' || data == ''){
            alert('該当する郵便番号がありません');
          } else {
            $('#address').val(data);
          }
        },
        function(data){
          alert("読み込みに失敗しました。")
        },
      );
    }
  });
});





