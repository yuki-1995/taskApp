<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
// DBからチームデータを取得
$dbGroupData = getGroup();

debug('取得したユーザー情報:'.print_r($dbFormData,true));
debug('取得したチーム情報'.print_r($dbGroupData,true));

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報:'.print_r($_POST,true));
  // debug('FILE情報:'.print_r($_FILE,true));

  // 各変数にユーザー情報を代入
  $username = $_POST['username'];
  $group = $_POST['g_id'];
  $tel = $_POST['tel'];
  $age = $_POST['age'];
  $email = $_POST['email'];
  // 画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;

  //DBの情報と入力情報が異なる場合にバリデーションを行う
  if($dbFormData['username'] !== $username){
    // 名前の最大文字数チェック
    validMaxLen($username,'username');
  }

  if($dbFormData['tel'] !== $tel){
    // TEL形式チェック
    validTel($tel,'tel');
  }

  if($dbFormData['age'] !== $age){
    // 最大文字数チェック
    validMaxLen($age,'age');
    // 半角数字チェック
    validNumber($age,'age');
  }

  if($dbFormData['email'] !== $email){
    // email最大文字数チェック
    validMaxLen($email,'email');
    if(empty($err_msg['email'])){
      // email重複チェック
      validEmailDup($email);
    }
    // email形式チェック
    validEmail($email,'email');
    // email未入力チェック
    validRequired($email,'email');
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');

    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :u_name, group_id = :group_id, tel = :tel, age = :age, email = :email, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $username, ':group_id' => $group, ':tel' => $tel, ':age' => $age, ':email' => $email, ':pic' => $pic, ':u_id' => $dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

      // クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }

    } catch(Exception $e){
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'プロフィール編集ページ';
require('head.php');
?>

<body class="page-logined profEdit">
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
          <form action="" method="post" class="form" enctype="multipart/form-data">

            <h2 class="title">プロフィール編集</h1>

            <div class="area-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></div>
            <label class="<?php if(!empty($err_msg['username'])) echo 'err';?>">
              名前
              <input type="text" name="username" value="<?php echo getFormData('username');?>">
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['username'])) echo $err_msg['username'];?></div>

            <label class="<?php if(!empty($err_msg['g_id'])) echo 'err'; ?>">
              チーム名
              <select name="g_id" id="">
                <option value="0" <?php if(getFormData('g_id') == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <?php
                  foreach($dbGroupData as $key => $val){
                ?>
                  <option value="<?php echo $val['id'] ?>" <?php if(getFormData('group_id') == $val['id'] ){ echo 'selected'; } ?> >
                    <?php echo $val['name']; ?>
                  </option>
                <?php
                  }
                ?>
              </select>
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['g_id'])) echo $err_msg['g_id']; ?></div>

            <label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
              TEL<span style="font-size:12px;margin-left:5px;">※ハイフン無しでご入力ください</span>
              <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['tel'])) echo $err_msg['tel']; ?></div>

            <label style="text-align:left;" class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
              年齢
              <input type="number" name="age" value="<?php echo getFormData('age'); ?>">
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?></div>

            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></div>

            <!-- <div class="area-msg"></div> -->
            <!-- プロフィール画像 -->
            <label class="area-drop <?php  if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file" style="height:370px;line-height:370px;">
              <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php  if(empty(getFormData('pic'))) echo 'display:none;'?>">
                ドラッグ＆ドロップ
            </label>
            <div class="area-msg"><?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; ?></div>

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

    <?php
      require('footer.php');
    ?>
