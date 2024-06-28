// formを使わずjQueryだけで
$(function () {
  var entry_url = $("#entry_url").val();
  // hiddenのid="entry_url"の値を取得
  $("#cart_in").click(function () {
    // cart_idというボタンをクリック
    var item_id = $("#item_id").val();
    // hiddenのid="item_id"の値を取得
    location.href = entry_url + "cart.php?item_id=" + item_id;
    // item_idを取得しitem_idをクエリパラメータとしてURLを構築しリダイレクト
    // この時URLには「item_idだけが渡される」、item_idは1や2  +は数値
    // そしてcart.phpではクエリパラメータからitem_idを取得、($item_id = $_GET['item_id'];)
    // item_idを使用してデータベースから商品詳細を取得
  });
});

// pageを追加
function suggest(query, ctg_id) {
  // クエリが空の場合、サジェストエリアをクリアして終了
  if (query.length == 0) {
    document.getElementById("suggestions").innerHTML = "";
    return;
  }

  // XMLHttpRequest(非同期通信、ページ全体をリロードすることなく)オブジェクトを作成
  var xhr = new XMLHttpRequest();
  
  // リクエストの状態が変わったときに呼び出される関数を定義
  xhr.onreadystatechange = function () {
    // 4でリクエストが完了、かつステータスが200で(リクエストが成功)の場合
    if (this.readyState == 4 && this.status == 200) {
      // サーバーからのレスポンスをサジェストエリアに表示
      document.getElementById("suggestions").innerHTML = this.responseText;
    }
  };

   // GETリクエストを初期化。クエリとカテゴリIDをエンコード(特殊文字が正しく処理)してURLに追加
  xhr.open("GET", "suggest.php?query=" + encodeURIComponent(query) + "&ctg_id" + encodeURIComponent(ctg_id), true);
  xhr.sent();
}
// 検索ボックスのsearchBoxに文字を入力、javascriptのkeyupイベントが発生
document.getElementById("searchBox").addEventListener("keyup", function() {
  // keyupイベントリスナーが起動、検索ボックスのqueryとカテゴリーのctg_idを取得  
  var query = this.value; 
  var ctg_id = document.getElementById("categorySelect").value;
  // イベントリスナーは、これらの値を引数としてサジェスト関数を呼び出す
  // suggest関数は、XMLHttpRequestを使用して非同期のGETリクエストをsuggest.phpに送信。クエリとカテゴリIDがURLパラメータとして追加
  suggest(query, ctg_id);
});
