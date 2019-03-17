<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「タスク登録ページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// ===============================
// 画面処理
// ===============================

// GETデータを格納
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
// DBから商品データを取得
$dbFormData = (!empty($t_id)) ? getTasks($_SESSION['user_id'], $t_id) : '';
// DBからチームデータを取得
$dbGroupData = getGroup();
debug('タスクID：'.$t_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));
debug('チームデータ：'.print_r($dbGroupData,true));

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでメインページへ遷移させる
if(!empty($t_id) && empty($dbFormData)){
  debug('GETパラメータのタスクIDが違います。メインページへ遷移します。');
  header("Location:main.php"); //マイページへ
}




// POST送信された場合
if(!empty($_POST)){
  debug('POSTがあります');
  debug('POST情報:'.print_r($_POST,true));

  // 各変数にユーザー情報を代入
  $name = $_POST['name'];
  $group = $_POST['g_id'];
  $comment = $_POST['comment'];



  // 変更の場合はDBの情報と入力情報に違いがある場合にバリデーションチェックを行う
  if(empty($dbFormData)){
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //セレクトボックスチェック
    validSelect($group, 'g_id');
    //最大文字数チェック
    validMaxLen($comment, 'comment', 500);

  }else{
    if($dbFormData['name'] !== $name){
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name');
    }
    if($dbFormData['group_id'] !== $group){
      //セレクトボックスチェック
      validSelect($group, 'group_id');
    }
    if($dbFormData['comment'] !== $comment){
      //最大文字数チェック
      validMaxLen($comment, 'comment', 500);
    }
  }


  if(empty($err_msg)){
    debug('バリデーションOKです');

    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      // DBの更新はUPDATE、新規登録はINSERT
      if($edit_flg){
        debug('DB更新です');
        $sql = 'UPDATE tasks SET name = :name, group_id = :group, comment = :comment WHERE user_id = :u_id AND id = :t_id';
        $data = array(':name' => $name , ':group' => $group, ':comment' => $comment, ':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
      }else{
        debug('DB新規登録です。');
        $sql = 'INSERT INTO tasks (name,group_id,comment,user_id,create_date) VALUES (:name,:group,:comment,:u_id,:date)';
        $data = array(':name' => $name, ':group' => $group, ':comment' => $comment, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:'.$sql);
      debug('流し込みのデータ:'.print_r($data,true));
      // クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

      // クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUC03;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }

    } catch(Exception $e){
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }

  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'タスクを登録する';
require('head.php');
?>

  <body>

    <!-- ヘッダー -->
    <?php
    require('header.php');
    ?>


      <section id="main" class="site-width">

        <div class="form-container">
          <form action="" method="post" class="form">
            <h2 class="title"><?php echo $siteTitle; ?></h1>


            <!-- タスク名 -->
            <div class="area-msg">
              <?php
              if(!empty($err_msg['common'])) echo $err_msg['common'];
              ?>
            </div>
            <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
              タスク名
              <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['name'])) echo $err_msg['name'];
              ?>
            </div>


            <!-- チームカテゴリー -->
            <label>
              チーム名
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
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['group_id'])) echo $err_msg['group_id'];
              ?>
            </div>


            <!-- タスク内容 -->
            <label>
              内容
              <textarea name="comment" id="js-count" cols="30" rows="10" style="height:150px;"></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['comment'])) echo $err_msg['comment'];
              ?>
            </div>



            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="登録する">
            </div>


          </form>
        </div>

        <div class="page-back">
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
        </div>

      </section>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>
