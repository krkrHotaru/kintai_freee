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
        $login_id = $_SESSION['staff_id' . $id];
        $focus_rule = $_SESSION['focus_rule' . $id];
        $check_id = !(preg_match('/^[0-9]+$/', $login_id));

        if ($check_id) {
            echo 'Error！<br/>不適格なidが入力されました★' . "<br/>";
        } else {

            //選択行のスタッフidから検索・編集中のルールidにruleカラムを変更
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("UPDATE `staff` SET `rule`=:apply_rule WHERE `login_id`=:login_id;");
                $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
                $sql->bindParam(':apply_rule', $focus_rule, PDO::PARAM_INT);
                $sql->execute();
                echo '編集内容を保存しました！';
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }
            $result = true;
        }

        if ($result) {
            header("Location:/admin/rule_edit.php?id={$focus_rule}");
        }
    } else {
        header("Location:../authorize/logout.php");
    }
    ?>

</body>

</html>