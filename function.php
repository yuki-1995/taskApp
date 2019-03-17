<?php
//================================
// ログ
//================================

// ログを取るか
// ini_set('log_errors','on');
// ログの出力先ファイル
// ini_set('error_log','php.log');


//================================
// デバック
//================================

// デバックフラグ
$debug_flg = false;

// デバックログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバック:'.$str);
  }
}


//================================
// セッション準備・セッション有効期限を延ばす
//================================

// セッションファイルの配置を変更する
session_save_path("/var/tmp/");
// ガーベージコレクションが削除するセッションの有効期限を変更
ini_set('session.gc_maxlifetime',60*60*24*30);
// cookieの有効期限を延ばす
ini_set('session.cookie_lifetime',60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();


//================================
// 画面表示処理の開始ログ吐き出し関数
//================================

function debugLogstart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  画面表示処理開始');
  debug('セッションID:'.session_id());
  debug('セッション変数の中身:'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ:'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時タイムスタンプ:'.($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}


//================================
// 定数
//================================

define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '256文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '現在のパスワードが違います');
define('MSG13', '現在のパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', '');
define('SUC04', 'ユーザー登録しました');
define('SUC05', 'タスク完了！');
define('SUC06', 'ログインしました');

//================================
// グローバル変数
//================================

// エラーメッセージ格納用の配列
$err_msg = array();


//================================
// バリデーション関数
//================================

// バリデーション関数 (未入力チェック)
function validRequired($str,$key){
  if($str === ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// バリデーション関数 (Email形式チェック)
function validEmail($str,$key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// バリデーション関数 (Email重複チェック)
function validEmailDup($email){
  global $err_msg;

  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // array_shiftで結果の一つ目だけを取り出して判定する
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }

  } catch (Exception $e){
    error_log('エラー発生:'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// バリデーション関数 (同値チェック)
function validMatch($str1,$str2,$key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// バリデーション関数 (最小文字数チェック)
function validMinLen($str,$key,$min = 6){
  if(mb_strlen($str) < 6){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// バリデーション関数 (最大文字数チェック)
function validMaxLen($str,$key,$max = 256){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

// バリデーション関数 (半角英数字チェック)
function validHalf($str,$key){
  if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}


// バリデーション関数 (TEL形式チェック)
function validTel($str,$key){
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}

//半角数字チェック
function validNumber($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}

//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}

//================================
// ログイン認証
//================================

function isLogin(){
  // ログインしている場合
  if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです');

      // セッションを削除する
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です');
      return true;
    }

  }else{
    debug('未ログインユーザーです');
    return false;
  }
}


//================================
// データベース
//================================

// DB接続関数
function dbConnect(){
  // DBへ接続準備
  $dbs = 'mysql:dbname=task;host=localhost;charset=utf8';
  $user = '????';
  $password = '????';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成
  $dbh = new PDO($dbs,$user,$password,$options);
  return $dbh;
}

// SQL実行関数
function queryPost($dbh,$sql,$data){
  // クエリー生成
  $stmt = $dbh->prepare($sql);
  // プレースホルダーに値をセットし、SQL文作成
  if(!$stmt->execute($data)){
    debug('クエリ失敗しました');
    debug('失敗したSQL:'.print_r($sql,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}

function getTasks($u_id, $t_id){
  debug('タスク情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('タスクID：'.$t_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM tasks WHERE user_id = :u_id AND id = :t_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':t_id' => $t_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


function getUser($u_id){
  debug('ユーザー情報を取得します');

  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    // クエリ結果の１レコードを返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}


function getGroup(){
  debug('チーム情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `group`';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData[$str]);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}


function getTasksOne($t_id){
  debug('タスク情報を取得します');
  debug('タスクID:'.$t_id);

  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT t.id, t.name,  t.comment, t.user_id, t.create_date, t.update_date, g.name AS `group`
            FROM tasks AS t LEFT JOIN `group` AS g ON t.group_id = g.id WHERE t.id = :t_id AND t.delete_flg = 0 AND g.delete_flg = 0';
    $data = array(':t_id' => $t_id);

    // クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      //クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}


function getMyTask($u_id){
  debug('自分のタスク情報を取得します');
  debug('ユーザーID:'.$u_id);

  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM tasks WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}


// メインページ用
function getTasksList($currentMinNum = 1, $group, $span = 5){
  debug('タスク情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM tasks';
    if(!empty($group)) $sql .= ' WHERE group_id = '.$group;
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM tasks';
    if(!empty($group)) $sql .= ' WHERE group_id = '.$group;
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

    } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    }
    }


// マイページ用
function getMyTasksList($currentMinNum = 1, $u_id, $span =5){
      debug('自分のタスク情報を取得します。');
      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // 件数用のSQL文作成
        $sql = 'SELECT * FROM tasks WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount(); //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
        if(!$stmt){
          return false;
        }


    // ページング用のSQL文作成
    $sql = 'SELECT * FROM tasks WHERE user_id = :u_id AND delete_flg = 0';
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array(':u_id' => $u_id);
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//================================
// メール送信
//================================
// function sendMail($from,$to,$subject,$comment){
//   if(!empty($to) && !empty($subject) && !empty($comment)){
//     // 文字化けしないように設定
//     mb_language("japanese");
//     mb_internal_encoding("UTF-8");
//
//     // メールを送信
//     $result = mb_send_mail($to,$subject,$comment,"From:".$from);
//     // 送信結果を判定
//     if($result){
//       debug('メールを送信しました');
//     }else{
//       debug('【エラー発生】メールの送信に失敗しました');
//     }
//   }
// }


// // 画像処理
// function uploadImg($file, $key){
//   debug('画像アップロード処理開始');
//   debug('FILE情報：'.print_r($file,true));
//
//   if (isset($file['error']) && is_int($file['error'])) {
//     try {
//       // バリデーション
//       // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
//       //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
//       switch ($file['error']) {
//           case UPLOAD_ERR_OK: // OK
//               break;
//           case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
//               throw new RuntimeException('ファイルが選択されていません');
//           case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
//           case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
//               throw new RuntimeException('ファイルサイズが大きすぎます');
//           default: // その他の場合
//               throw new RuntimeException('その他のエラーが発生しました');
//       }
//
//       // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
//       // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
//       $type = @exif_imagetype($file['tmp_name']);
//       if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
//           throw new RuntimeException('画像形式が未対応です');
//       }
//
//       // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
//       // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
//       // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
//       // image_type_to_extension関数はファイルの拡張子を取得するもの
//       $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
//       if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
//           throw new RuntimeException('ファイル保存時にエラーが発生しました');
//       }
//       // 保存したファイルパスのパーミッション（権限）を変更する
//       chmod($path, 0644);
//
//       debug('ファイルは正常にアップロードされました');
//       debug('ファイルパス：'.$path);
//       return $path;
//
//     } catch (RuntimeException $e) {
//
//       debug($e->getMessage());
//       global $err_msg;
//       $err_msg[$key] = $e->getMessage();
//
//     }
//   }
// }

//sessionを１回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}


//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}


//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}


// =====================
// サニタイズ
// =====================
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
