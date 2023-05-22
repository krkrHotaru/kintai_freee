<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>勤怠管理サイト</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
  <header>
    <br>
    <h1 class="font-weight-normal">勤怠管理サイト</h1>
  </header>
  <?php
  session_start();
  $_SESSION = array(); //セッションの中身をすべて削除
  session_destroy(); //セッションを破壊
  ?>
  <p>ログアウト状態です。<br>ログインは以下のボタンから行ってください。</p>
  <br>
  <a class="push btn_white" href="/authorize/login_form.php">従業員用ログイン画面</a>
  <br>
  <br>
  <a class="push btn_white" href="/authorize/admin_login.php">管理者用ログイン画面</a>

</body>

</html>