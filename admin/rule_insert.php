<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>rule_insert</title>
</head>

<body>
    <header>
        <h1 class="font-weight-normal">ルール編集反映</h1>
    </header>

    <?php
    session_start();

    if (isset($_SESSION['admin_id'])) { //ログインしているとき
        $rule_name = $_POST['name'];
        $start_rule = $_POST['start'];
        $end_rule = $_POST['end'];
        $perhour = $_POST['perhour'];
        $peryear = $_POST['peryear'];
        $check_perhour = !(preg_match('/^[0-9]+$/', $perhour));
        $check_peryear = !(preg_match('/^[0-9]+$/', $peryear));


        //簡易judge
        if ($_POST['start'] == NULL || $_POST['end'] == NULL || $_POST['perhour'] == NULL || $_POST['peryear'] == NULL) {
            echo "未入力の項目があります。<br>";
            echo "<button type=\"button\" onclick=\"location.href='/admin/rule_form.php'\">ルールの入力に戻る</button>";
        } else if ($check_perhour || $check_peryear) {
            echo "時給と1年の給与上限は半角数字で入力してください。<br>";
            echo "<button type=\"button\" onclick=\"location.href='/admin/rule_form.php'\">ルールの入力に戻る</button>";
        }

        //デフォルトルールがあれば取得＆入力内容と全く同じルールがあれば取得
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql = $db->prepare("SELECT * FROM `rules` WHERE `status`=1;");
            $sql->execute();
            $default_rule = $sql->fetch(PDO::FETCH_ASSOC);
            $sql = $db->prepare("SELECT * FROM `rules` WHERE `start`=:start AND `end`=:end AND `perhour`=:perhour AND `peryear`=:peryear;");
            $sql->bindParam(':start', $start_rule, PDO::PARAM_STR);
            $sql->bindParam(':end', $end_rule, PDO::PARAM_STR);
            $sql->bindParam(':perhour', $perhour, PDO::PARAM_INT);
            $sql->bindParam(':peryear', $peryear, PDO::PARAM_INT);
            $sql->execute();
            $same_rule = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'DB接続エラー！' . "<br/>" . $e->getMessage();
        }

        /*ここまでで学んだこと…
        $変数名 = $sql->fetch(PDO::FETCH_ASSOC);
        この構文は複数データを入れようとするとうまく作動しないっぽい。
        */

        if ($default_rule['id'] == NULL) { //デフォが設定されていない場合のみstatus=1で強制INSERT
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("INSERT INTO rules(rule_name,start, end, perhour, peryear, status) VALUES (:rule_name,:start,:end,:perhour,:peryear,1)");
                $sql->bindParam(':rule_name', $rule_name, PDO::PARAM_STR);
                $sql->bindParam(':start', $start_rule, PDO::PARAM_STR);
                $sql->bindParam(':end', $end_rule, PDO::PARAM_STR);
                $sql->bindParam(':perhour', $perhour, PDO::PARAM_INT);
                $sql->bindParam(':peryear', $peryear, PDO::PARAM_INT);
                $sql->execute();
                echo "デフォルトルールとして保存されました。<br>";
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . "<br/>" . $e->getMessage();
            }
        } else if ($same_rule['id'] <> NULL) { //同じルールが存在する
            echo "全く同じルールが存在します。<br>ルール名：" . $same_rule['rule_name'];
        } else { //同じルールが存在しない
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("INSERT INTO rules(rule_name,start, end, perhour, peryear) VALUES (:rule_name,:start,:end,:perhour,:peryear)");
                $sql->bindParam(':rule_name', $rule_name, PDO::PARAM_STR);
                $sql->bindParam(':start', $start_rule, PDO::PARAM_STR);
                $sql->bindParam(':end', $end_rule, PDO::PARAM_STR);
                $sql->bindParam(':perhour', $perhour, PDO::PARAM_INT);
                $sql->bindParam(':peryear', $peryear, PDO::PARAM_INT);
                $sql->execute();
                echo "新規ルールとして保存されました。<br>";
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . "<br/>" . $e->getMessage();
            }
        }
    } else { //ログインしていない時
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