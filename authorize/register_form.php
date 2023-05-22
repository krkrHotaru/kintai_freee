<html>

<head>
  <meta charset="UTF-8">
  <title>従業員用ログインID新規登録</title>
  <link rel="stylesheet" href="style.css">
</head>
<?php
session_start();

if (isset($_SESSION['admin_id'])) { //ログインしているとき
?>

  <body class="menu">
    <header>
      <h1 class="font-weight-normal">従業員用ログインID新規登録画面</h1>
    </header>

    <form action="/authorize/register.php" method="post">
      ログインID<input type="text" name="login_id" value="" placeholder="数字8~12桁" pattern="[0-9]{8,12}" title="ログインIdは8~12桁の数字で入力してください。"><br />
      名前<input type="text" name="name" value=""><br />
      パスワード<input type="password" name="pass" value="" placeholder="英数字8~20字" pattern="[0-9A-Za-z]{8,20}" title="パスワードは8~20字の英数字で入力してください。"><br />
      パスワード(確認)<input type="password" name="confirm" value=""><br />
      <button type="submit">新規登録</button>
    </form>

    <button type="button" onclick="location.href='/authorize/login_form.php'">従業員用ログイン画面へ</button>
    <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>

  </body>
<?php
} else { //ログアウト状態
  header("Location:/authorize/logout.php");
} ?>

</html>