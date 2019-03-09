<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード変更ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザー情報を取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報:'.print_r($userData,true));


// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報:'.print_r($_POST,true));

  // 各変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];


  // 未入力チェック
  validRequired($pass_old,'pass_old');
  validRequired($pass_new,'pass_new');
  validRequired($pass_new_re,'pass_new_re');


  // $err_msgが空の場合
  if(empty($err_msg)){
    debug('未入力チェックOKです');

    // 現在のパスワードのチェック
    validPass($pass_old,'pass_old');
    validPass($pass_new,'pass_new');


    // 現在のパスワードとDBパスワードを照合(DBに入っているデータと同じであればバリデーションチェックを行わなくてもよい)
    if(!password_verify($pass_old,$userData['password'])){
      $err_msg['pass_old'] = MSG12;
    }

    // 現在のパスワードと新しいパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG13;
    }

    // パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new,$pass_new_re,'pass_new_re');


    if(empty($err_msg)){
      debug('バリデーションOKです');

      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new,PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        // クエリ成功の場合
        if($stmt){
          $_SESSION['msg_success'] = SUC01;


          // メール送信
          $username = ($userData['username']) ? $userData['username'] : '名無し';
          $from = 'kohojoin15@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知 | Hello task';
          $comment = <<<EOT
{$username}さん
パスワードが変更されました。


////////////////////////////////////////
Hello task
URL  http://hellotask/
E-mail gmail.com
////////////////////////////////////////
EOT;
          sendMail($from,$to,$subject,$comment);

          header("Location:mypage.php");
        }

      } catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
?>


<?php
$siteTitle = 'パスワード変更ページ';
require('head.php');
?>

<body class="page-logined passEdit">


  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>

  <!-- メイン -->
    <section id="main" class="site-width">

      <section id="sidebar">

        <a href="task.php">タスクを登録する</a>
        <a href="profEdit.php">プロフィール編集</a>
        <a href="passEdit.php">パスワード変更</a>
        <a href="withdraw.php">退会</a>

      </section>

        <div class="form-container">
          <form action="" method="post" class="form">
            <h2 class="page-title">パスワード変更</h1>
           <div class="area-msg"></div>
            <label class="">
              現在のパスワード
              <input type="password" name="pass_old" value="">
            </label>
            <div class="area-msg"></div>
            <label class="">
              新しいパスワード
              <input type="password" name="pass_new" value="">
            </label>
            <div class="area-msg"></div>
            <label class="">
              新しいパスワード（再入力）
              <input type="password" name="pass_new_re" value="">
            </label>
            <div class="area-msg"></div>
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="変更する">
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


    <!-- フッター -->
    <?php
      require('footer.php');
    ?>
