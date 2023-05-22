<html>

<head>
  <title>従業員用ログインID新規登録</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
  <?php
  session_start();

  if (isset($_SESSION['admin_id'])) { //ログインしているとき
    //フォームからの入力を受け取る
    $login_id = $_POST['login_id'];
    $name = $_POST['name'];
    $pass = $_POST['pass'];
    $check_id = !(preg_match('/^[0-9]+$/', $login_id));
    $check_pass = !(preg_match('/^[a-zA-Z0-9]+$/', $pass));

    if ($_POST['pass'] === $_POST['confirm']) { //パスワードが２回とも正しく入力されている場合
      $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT); //name="pass"の入力を暗号化して受け取る

      if (empty($login_id) || empty($pass) || empty($name)) {
        echo 'Error！<br/>ログインID欄やパスワード欄、名前は空欄にできません' . "<br/>";
        $flag = false;
      } else if ($check_id) {
        echo 'Error！<br/>ログインID欄は数字の8~12桁で入力してください' . "<br/>";
      } else if ($check_pass) {
        echo 'Error！<br/>パスワード欄は半角英数字を入力してください' . "<br/>";
      } else {
        //以上の条件を満たす場合のみ、DBへの接続・SQLに実行命令
        //入力内容と同じlogin_idがあれば取得
        try {
          $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
          $sql = $db->prepare("SELECT * FROM staff WHERE login_id=:login_id");
          $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
          $sql->execute();
          $staff = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
          echo 'DB接続エラー！' . "<br/>" . $e->getMessage() . "<br/>";
        }
        //現在のデフォルト勤怠ルールを取得
        try {
          $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
          $sql2 = $db->prepare("SELECT * FROM rules WHERE status=1;");
          $sql2->execute();
          $def_rule = $sql2->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
          echo 'DB接続エラー！: ' . $e->getMessage();
        }

        if ($staff['login_id'] === $login_id) {
          echo '新規登録に失敗しました…' . "<br/>";
          echo '同じログインIDが既に使われています' . "<br/>";
          $flag = false;
        } else {
          //登録されていなければinsert
          try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql = $db->prepare("INSERT INTO staff(login_id, name, pass, rule) VALUES (:login_id,:name,:pass,:rule)");
            $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
            $sql->bindParam(':name', $name, PDO::PARAM_STR);
            $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
            $sql->bindParam(':rule', $def_rule['id'], PDO::PARAM_INT);
            $sql->execute();
            echo '新規登録に成功しました！' . "<br/>";
            $flag = true;
          } catch (PDOException $e) {
            echo 'DB接続エラー！' . "<br/>" . $e->getMessage();
          }
        }
      }
      //新規登録が成功した場合以外は管理者メニューへ遷移するボタンを表示
      if ($flag) { ?>
        <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>
      <?php } else { ?>
        <button type="button" onclick="location.href='/authorize/register_form.php'">新規登録画面へ</button>
      <?php }
    } else { //パスワード入力に間違いがある場合
      echo "確認欄の内容とパスワードが一致していません" . "<br/>"; ?>
      <button type="button" onclick="location.href='/authorize/register_form.php'">新規登録画面へ</button>
  <?php }
  } else { //ログアウト状態
    header("Location:/authorize/logout.php");
  } ?>

</body>

</html>