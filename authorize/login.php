<html>

<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
  <?php
  session_start();
  unset($_SESSION['admin_id']);

  $login_id = $_POST['login_id'];
  $pass = $_POST['pass'];
  $check_id = !(preg_match('/^[0-9]+$/', $login_id));
  $check_pass = !(preg_match('/^[a-zA-Z0-9]+$/', $pass));

  if (empty($login_id) || empty($pass)) {
    echo 'Error！<br/>ログインID欄とパスワード欄の両方を入力してください' . "<br/>";
    $flag = false;
  } elseif ($check_id) {
    echo 'Error！<br/>ログインID欄は半角数字を入力してください' . "<br/>";
  } elseif ($check_pass) {
    echo 'Error！<br/>パスワード欄は半角英数字を入力してください' . "<br/>";
  } else {

    //DBへの接続・SQLに処理命令
    try {
      $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
      $sql = $db->prepare("SELECT * FROM staff WHERE login_id=:login_id");
      $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
      $sql->execute();
    } catch (PDOException $e) {
      echo 'DB接続エラー！' . "<br/>" . $e->getMessage() . "<br/>";
    }
    $staff = $sql->fetch();
    //指定したハッシュがパスワードにマッチしているかチェック
    if (password_verify($pass, $staff['pass'])) {
      //DBのユーザー情報をセッションに保存
      $_SESSION['login_id'] = $staff['login_id'];
      $_SESSION['pass'] = $staff['pass'];
      echo 'ログイン成功！' . "<br/>";
      $flag = true;
    } else {
      echo 'ログインIDもしくはパスワードが間違っています。' . "<br/>";
      $flag = false;
    }
  }

  //新規登録が成功した場合以外は新規登録画面へ遷移するボタンを表示
  if ($flag) {
    header("Location:../staff/menu.php");
  } else { ?>
    <button type="button" onclick="location.href='/authorize/login_form.php'">ログイン画面へ</button>
  <?php } ?>

</body>

</html>