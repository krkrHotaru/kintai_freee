<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>従業員用ログイン画面</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
  <header>
    <br>
    <h1 class="font-weight-normal">従業員用ログイン画面</h1>
  </header>

  <form action="/authorize/login.php" method="post">
    ログインID<input type="text" name="login_id" value=""><br />
    パスワード<input type="password" name="pass" value=""><br />
    <br>
    <button type="button" onclick="location.href='/authorize/admin_login.php'">管理者用のログイン画面へ</button>
    <button type="submit">ログイン</button>
  </form>

</body>

</html>