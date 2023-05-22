<html>

<head>
    <meta charset="UTF-8">
    <title>管理者専用ページ</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
    <header>
        <br>
        <h1 class="font-weight-normal">管理者専用ページ</h1>
    </header>
    <?php
    session_start();
    unset($_SESSION['login_id']);

    if (isset($_GET['status']) == false) {
        echo "ここは管理者専用画面へのアクセスページです。<br>管理者用のログインIDとパスワードをご入力ください。";
    ?>
        <form action="admin_login.php?status=99" method="post">
            ログインID<input type="text" name="admin_id" value=""><br>
            パスワード<input type="password" name="admin_pass" value=""><br>
            <br>
            <button type="button" onclick="location.href='/authorize/login_form.php'">従業員用のログイン画面へ</button>
            <button type="submit">ログイン</button>
        </form>


        <?php
    } elseif ($_GET['status'] == 99) {
        $admin_id = $_POST['admin_id'];
        $admin_pass = $_POST['admin_pass'];
        if ($admin_id == 21212121 && $admin_pass == "gumin") {
            //管理者画面へ遷移or管理者画面の表示
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_pass'] = $admin_pass;
            echo "adminページへようこそ！";
            $flag = true;
        ?>
            <br>
            <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
            <button type="button" onclick="location.href='/authorize/login_form.php'">従業員用のログイン画面へ</button>
        <?php
        } else {
            echo 'ログインIDもしくはパスワードが間違っています。' . "<br/>";
            $flag = false;
        ?>
            <br>
            <button type="button" onclick="location.href='/authorize/admin_login.php'">再試行</button>
            <button type="button" onclick="location.href='/authorize/login_form.php'">従業員用のログイン画面へ</button>
    <?php
        }
        //ログインに成功した場合はadmin/menu.phpへ
        if ($flag) {
            header("Location:../admin/menu.php");
        }
    } else {
        echo "予期しないstatusです。";
    } ?>


</body>

</html>