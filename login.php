<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログインページ」');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();


// ログイン認証
require('auth.php');


//================================
// ログイン画面処理
//================================

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');

  // 各変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; //if文の略記


   // 未入力チェック
   validRequired($email,'email');
   validRequired($pass,'pass');


   // Emailの形式チェック
   validEmail($email,'email');
   // Emailの最大文字数チェック
   validMaxLen($email,'email');


   // パスワードの半角英数字チェック
   validHalf($pass,'pass');
   // パスワードの最大文字数チェック
   validMaxLen($pass,'pass');
   // パスワードの最小文字数チェック
   validMinLen($pass,'pass');


   //$err_msgが空の場合
   if(empty($err_msg)){
     debug('バリデーションOKです');

     // 例外処理
     try {
       // DBへ接続
       $dbh = dbConnect();
       // SQL文作成
       $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
       $data = array(':email' => $email);
       // クエリ実行
       $stmt = queryPost($dbh,$sql,$data);
       // クエリ結果の値を取得
       $result = $stmt->fetch(PDO::FETCH_ASSOC);

       debug('クエリ結果の中身:'.print_r($result,true));


       // パスワードの照合
       if(!empty($result) && password_verify($pass,array_shift($result))){
         debug('パスワードがマッチしました');

         // ログイン有効期限 (1時間)
         $sesLimit = 60*60;
         // 最終ログイン日時を現在日時に
         $_SESSION['login_date'] = time();


         // ログイン保持にチェックがある場合
         if($pass_save){
           debug('ログイン保持にチェックがあります');
           // ログイン有効期限を30日にセット
           $_SESSION['login_limit'] = $sesLimit * 24 * 30;

         }else{
           debug('ログイン保持にチェックはありません');
           // 次回からログイン保持しないので、ログイン有効期限を１時間後にセット
           $_SESSION['login_limit'] = $sesLimit;
         }

         // ユーザーIDを格納
         $_SESSION['user_id'] = $result['id'];

         $_SESSION['msg_success'] = SUC06;

         debug('セッション変数の中身:'.print_r($_SESSION,true));
         debug('マイページへ遷移します');
         header("Location:mypage.php");

       }else{
         debug('パスワードがアンマッチです');
         $err_msg['common'] = MSG09;
       }

     } catch(Exception $e){
       error_log('エラーが発生しました:'.$e->getMessage());
       $err_msg['common'] = MSG07;
     }
   }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'ログインページ';
require('head.php');
?>

<body>

    <!-- ヘッダー -->
<?php
require('header.php');
?>

    <!-- メインコンテンツ -->

      <!-- Main -->
      <section id="main" class="site-width">

       <div class="form-container">

         <form action="" method="post" class="form">
           <h2 class="title">ログイン</h2>
           <div class="area-msg">
             <?php
              if(!empty($err_msg['common'])) echo $err_msg['common'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
             <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
           </label>
           <div class="area-msg">
             <?php
             if(!empty($err_msg['email'])) echo $err_msg['email'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
             パスワード
             <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
           </label>
           <div class="area-msg">
             <?php
             if(!empty($err_msg['pass'])) echo $err_msg['pass'];
             ?>
           </div>
           <label>次回ログインを省略する
             <input type="checkbox" name="pass_save" class="checkbox">
           </label>
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="ログイン">
            </div>
            パスワードを忘れた方は<a href="passRemindSend.php">コチラ</a>
         </form>
       </div>

      </section>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>
