<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>delete</title>
</head>

<body>
    <?php
    session_start();

    //ログインIDが存在しない場合は新規登録画面へ
    if (isset($_SESSION['admin_id'])) { //ログインしているとき
        $id = $_GET['id'];
        $check_id = !(preg_match('/^[0-9]+$/', $id));

        if ($check_id) {
            echo 'Error！<br/>不適格なidが入力されました★' . "<br/>";
        } else {
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("SELECT * FROM `rules` WHERE `id`=:id;");
                $sql->bindParam(':id', $id, PDO::PARAM_INT);
                $sql->execute();
                $result = $sql->fetch(PDO::FETCH_ASSOC);
                if ($result['status'] == NULL) {
                    $sql = $db->prepare("DELETE FROM rules WHERE id=:id");
                    $sql->bindParam(':id', $id, PDO::PARAM_INT);
                    $sql->execute();
                    echo '削除成功！<br>';
                } else {
                    echo 'デフォルトルールは削除できません。<br>';
                }
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }
        }
    } else {
        header("Location:../authorize/logout.php");
    }
    ?>
    <footer id="footer">
        <button type="button" onclick="location.href='/admin/rules.php'">一覧に戻る</button>
        <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
        <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>
        <br>
        <a>Copyright (C)gumin</a>
    </footer>
</body>

</html>