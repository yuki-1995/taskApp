<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「タスク詳細ページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// タスクIDのGETパラメータを取得
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
// DBからタスクデータを取得
$viewData = getTasksOne($t_id);
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ遷移します
}
debug('取得したDBデータ:'.print_r($viewData,true));


// POSTされていた場合
if(!empty($_POST['submit'])){
  debug('POST送信があります');

  // ログイン認証
  require('auth.php');

  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'DELETE FROM tasks WHERE id = :t_id';
    $data = array(':t_id' => $t_id);

    // クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    // クエリ成功の場合
    if($stmt){
      $_SESSION['msg_success'] = SUC05;
      debug('メインページへ遷移します');
      header("Location:main.php");
    }

  } catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php
$siteTitle = 'タスク詳細ページ';
require('head.php');
?>

  <body class="task-detail1 page-logined">

    <!-- ヘッダー -->
    <?php
      require('header.php');
    ?>



    <section id="main" class="site-width">

      <div class="title">
        <span class="badge">タスク名</span><?php echo sanitize($viewData['name']); ?>
        <span class="badge">チーム</span><?php echo sanitize($viewData['group']) ;?>
      </div>

      <div class="task-detail">
        <p><?php echo sanitize($viewData['comment']); ?></p>
      </div>

      <div class="task-complete">
      <div class="item-left">
        <?php
        //ホスト名を取得
        $h = $_SERVER['HTTP_HOST'];
        // リファラ値があれば、かつ外部サイトでなければaタグで戻るリンクを表示
        if (!empty($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],$h) !== false)) {
          echo '<a href="' . $_SERVER['HTTP_REFERER'] . '">前の画面に戻る</a>';
        }
        ?>
      </div>
      <form action="" method="post" class="task-comp-btn"> <!-- formタグを追加し、ボタンをinputに変更し、style追加 -->
        <div class="item-right">
          <input type="submit" value="完了" name="submit" class="btn btn-primary" style="margin-top:-20px;float:right;">
        </div>
      </form>
      </div>

    </section>

    <!-- フッター -->
    <?php
      require('footer.php');
    ?>
