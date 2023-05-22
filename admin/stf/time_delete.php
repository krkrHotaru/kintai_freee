<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/admin/style.css">
    <title>delete</title>
</head>

<body>
    <?php
    session_start();

    if (isset($_SESSION['admin_id'])) { //従業員のログインIDがセッションにある時
        $id = $_GET['id'];
        $check_id = !(preg_match('/^[0-9]+$/', $id));

        if ($check_id) {
            echo 'Error！<br/>不適格なidが入力されました★' . "<br/>";
        } else {
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("DELETE FROM timecard WHERE id=:id");
                $sql->bindParam(':id', $id, PDO::PARAM_INT);
                $sql->execute();
                $result = true;
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }
        }

        if ($result) {
            header("Location:../admin/mykintai_list.php");
        }
    } else {
        header("Location:../../authorize/logout.php");
    }
    ?>
    <footer id="footer">
        <button type="button" onclick="location.href='../mykintai_list.php'">一覧に戻る</button>
        <button type="button" onclick="location.href='../../authorize/logout.php'">ログアウト</button>
        <button type="button" onclick="location.href='../menu.php'">管理者メニューへ</button>
        <br>
        <a>Copyright (C)gumin</a>
    </footer>
</body>

</html>