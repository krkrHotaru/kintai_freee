<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>setting_default change</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    session_start();

    if (isset($_SESSION['admin_id'])) { //ログインしているとき
        $id = $_GET['id'];
        $check_id = !(preg_match('/^[0-9]+$/', $id));

        if ($check_id) {
            echo 'Error！<br/>不適格なidが入力されました★' . "<br/>";
        } else {

            //旧デフォルールのstatusをNULLに変更
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("UPDATE `rules` SET `status`=NULL WHERE `status`=1;");
                $sql->execute();
                echo '編集内容を保存しました！';
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }

            //新デフォルールのstatusを1に変更
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("UPDATE `rules` SET `status`=1 WHERE `id`=:id;");
                $sql->bindParam(':id', $id, PDO::PARAM_INT);
                $sql->execute();
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }
            echo "デフォルトルールを変更しました";
            $result = true;
        }
        if ($result) {
            header("Location:/admin/rules.php");
        }
    } else {
        header("Location:../authorize/logout.php");
    }
    ?>

</body>

</html>