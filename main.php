<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「 メインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETパラメータを取得
//----------------------------------
// 現在のページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ目
// チーム
$group = (!empty($_GET['g_id'])) ? $_GET['g_id'] : '';
// パラメータに不正な値が入っているかチェック
if(!is_int((int)$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*5 = 0 、 ２ページ目なら(2-1)*5 = 5
// DBからタスクデータを取得
$dbTasksData = getTasksList($currentMinNum, $group);
// DBからチームデータを取得
$dbGroupData = getGroup();
//debug('DBデータ：'.print_r($dbFormData,true));
//debug('チームデータ：'.print_r($dbGroupData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php
$siteTitle = 'メインページ';
require('head.php');
?>

<body class="page-logined page-main">

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

<section id="main" class="site-width">

  <section id="sidebar">
          <form name="" method="get">
            <h1 class="title">チーム</h1>
            <div class="selectbox">
              <span class="icn_select"></span>
              <select name="g_id" id="">
                <option value="0" <?php if(getFormData('g_id') == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <?php
                  foreach($dbGroupData as $key => $val){
                ?>
                  <option value="<?php echo $val['id'] ?>" <?php if(getFormData('g_id') == $val['id'] ){ echo 'selected'; } ?> >
                    <?php echo $val['name']; ?>
                  </option>
                  <?php
                }
                ?>
              </select>
            </div>
            <input type="submit" value="検索">
          </form>

        </section>

        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($dbTasksData['total']); ?></span>件のタスクがあります
          </div>
          <div class="search-right">
            <span class="num"><?php echo (!empty($dbTasksData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbTasksData['data']); ?></span>件 /
            <span class="num"><?php echo sanitize($dbTasksData['total']); ?></span>件中
          </div>
        </div>

        <div class="panel-list">
         <?php
            foreach($dbTasksData['data'] as $key => $val):
          ?>
            <a href="taskDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="panel">
              <div class="panel-body">
                <p class="panel-title">タスク名:<?php echo sanitize($val['name']); ?></p>
              </div>
            </a>
          <?php
            endforeach;
          ?>
        </div>


        <?php pagination($currentPageNum,$dbTasksData['total_page']); ?>


</section>

<!-- フッター -->
<?php
require('footer.php');
?>
