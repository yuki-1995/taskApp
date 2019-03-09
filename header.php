<header>
    <!-- 未ログイン時 -->
    <?php if(empty($_SESSION['user_id'])){ ?>

      <h1><a href="index.php">Hello task</a></h1>

    <!-- ログイン時 -->
    <?php }else{?>

      <h1><a href="main.php">Hello task</a></h1>

    <?php } ?>

      <nav id="top-nav">
        <ul>
    <!-- 未ログイン時 -->
      <?php
        if(empty($_SESSION['user_id'])){
      ?>

          <li><a href="login.php">ログイン</a></li>
          <li><a href="signup.php">ユーザー登録</a></li>

      <!-- ログイン時 -->
      <?php
        }else{
      ?>

          <li><a href="main.php">ホーム</a></li>
          <li><a href="mypage.php">マイページ</a></li>
          <li><a href="logout.php">ログアウト</a></li>

      <?php
        }
      ?>
        </ul>
      </nav>
</header>
