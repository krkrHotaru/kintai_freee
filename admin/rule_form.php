<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>ルール追加</title>
</head>

<body>
    <header>
        <h1 class="font-weight-normal">勤怠ルールの追加</h1>
    </header>
    <?php
    session_start();

    if (isset($_SESSION['admin_id'])) { //ログインしているとき
    ?>
        <div>始業/終業時間の設定</div>
        <form method="post" action="/admin/rule_insert.php">

            <table border="1">

                <tr>
                    <td>ルール名</td>
                    <td><input type="text" name="name" value="大学生用"></td>
                </tr>
                <br>
                <tr>
                    <td>始業時間</td>
                    <td><input type="time" name="start" value="10:00"></td>
                </tr>

                <tr>
                    <td>終業時間</td>
                    <td><input type="time" name="end" value="18:00"></td>
                </tr>
                <tr>
                    <td>時給</td>
                    <td><input type="text" name="perhour" pattern="[0-9]{1,11}" value="1030"></td>
                </tr>

                <tr>
                    <td>１年の給与限度</td>
                    <td><input type="text" name="peryear" pattern="[0-9]{1,11}" value="1030000"></td>
                </tr>

            </table>
            <button type="submit">保存</button>
        </form>

        <footer id="footer">
            <button type="button" onclick="location.href='/admin/rules.php'">一覧に戻る</button>
            <button type="button" onclick="location.href='/admin/menu.php'">管理画面に戻る</button>
            <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
            <br>
            <a>Copyright (C)gumin</a>
        </footer>

    <?php
    } else { //ログインしていない時
        header("Location:../authorize/logout.php");
    } ?>
</body>

</html>