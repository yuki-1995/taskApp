<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// =============================
// 画面処理
// =============================
// ログイン認証
require('auth.php');


// 画面処理
// =============================
$u_id = $_SESSION['user_id'];
// GETパラメータを取得
//----------------------------------
// 現在のページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ目
// パラメータに不正な値が入っているかチェック
if(!is_int((int)$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20
// DBからタスクデータを取得
$dbMyTasksData = getMyTasksList($currentMinNum, $u_id);

// DBからきちんとデータが全て取れているかチェックは行わず、取れてなければ表示しないこととする

debug('取得したタスクデータ:'.print_r($dbMyTasksData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!-- ヘッド -->
<?php
$siteTitle = 'マイページ';
require('head.php');
?>

<body class="page-mypage page-logined">


<!-- ヘッダー -->
<?php
require('header.php');
?>

<!-- メッセージ格納用 -->
<div class="msg-slide">
  <p id="js-show-msg" style="display:none;" class="">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
</div>

<!-- メイン -->
<section id="main" class="site-width">

  <section id="sidebar">

    <a href="task.php">タスクを登録する</a>
    <a href="profEdit.php">プロフィール編集</a>
    <a href="passEdit.php">パスワード変更</a>
    <a href="withdraw.php">退会</a>

  </section>

  <section class="list panel-list">
           <h2 class="title" style="margin-bottom:22px;">
            登録タスク一覧
           </h2>
           <?php
             if(!empty($dbMyTasksData)):
              foreach($dbMyTasksData['data'] as $key => $val):
            ?>
              <a href="taskDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="panel">
                <div class="panel-body">
                  <p class="panel-title">タスク名:<?php echo sanitize($val['name']); ?></p>
                </div>
              </a>
            <?php
              endforeach;
             endif;
            ?>
  </section>

    <?php pagination($currentPageNum,$dbMyTasksData['total_page']); ?>

</section>

<!-- フッター -->
<?php
require('footer.php');
?>
