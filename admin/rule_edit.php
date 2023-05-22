<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>rule_edit</title>
    <link rel="stylesheet" href="style.css">
</head>

<?php
session_start();

//ログインIDが存在しない場合は新規登録画面へ
if (isset($_SESSION['admin_id'])) { //ログインしているとき

    //編集中のルールのid
    $renewRule_id = $_GET['id'];

    //結果の表示・DB操作部分
    if ($renewRule_id == "formdoc") {

        $renewRule_id = $_POST['id'];
        $name = $_POST['name'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $perhour = $_POST['perhour'];
        $peryear = $_POST['peryear'];

        //入力内容と全く同じルールがあれば取得
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql = $db->prepare("SELECT * FROM `rules` WHERE `rule_name`=:rule_name AND `start`=:start AND `end`=:end AND `perhour`=:perhour AND `peryear`=:peryear;");
            $sql->bindParam(':rule_name', $name, PDO::PARAM_STR);
            $sql->bindParam(':start', $start, PDO::PARAM_STR);
            $sql->bindParam(':end', $end, PDO::PARAM_STR);
            $sql->bindParam(':perhour', $perhour, PDO::PARAM_INT);
            $sql->bindParam(':peryear', $peryear, PDO::PARAM_INT);
            $sql->execute();
            $same_rule = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'sameruleDB接続エラー！<br/>' . $e->getMessage();
        }

        //同じルールが存在しなければUPADTE
        if ($same_rule['id'] <> NULL) {
            echo "既存ルールと同じ条件には編集できません。<br>";
        } else {
            try {
                $sql = $db->prepare("UPDATE `rules` SET `rule_name`=:name,`start`=:start,`end`=:end, `perhour`=:perhour, `peryear`=:peryear WHERE `id`=:id;");
                $sql->bindParam(':id', $renewRule_id, PDO::PARAM_INT);
                $sql->bindParam(':name', $name, PDO::PARAM_STR);
                $sql->bindParam(':start', $start, PDO::PARAM_STR);
                $sql->bindParam(':end', $end, PDO::PARAM_STR);
                $sql->bindParam(':perhour', $perhour, PDO::PARAM_INT);
                $sql->bindParam(':peryear', $peryear, PDO::PARAM_INT);
                $sql->execute();
                echo '編集内容を保存しました！<br>';
            } catch (PDOException $e) {
                echo 'saveDB接続エラー！<br>' . $e->getMessage();
            }
            echo $renewRule_id . $start . $end . $perhour . $peryear . "<br>";  //フォーム内容確認
        }

        //以下form部分
    } else {

        //編集ルールの取得
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql = $db->prepare("SELECT * FROM rules WHERE `id`=:id");
            $sql->bindParam(':id', $renewRule_id, PDO::PARAM_INT);
            $sql->execute();
            $rules = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'DB接続エラー！' . $e->getMessage();
        }

        //各従業員に適用中のルールidとrenew中のルールがfocusか,そのルールはdefaultとして設定中か,それぞれ取得
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql2 = $db->prepare('SELECT staff.login_id "id",staff.name "name",rules.rule_name "rule_name",
                                  SUM(staff.rule=:focus_rule) AS "focus_rule?",
                                  SUM(rules.status IS NOT NULL)AS "default?"
                                  FROM staff 
                                  INNER JOIN rules
                                  ON staff.rule = rules.id
                                  GROUP BY staff.login_id;');
            $sql2->bindParam(':focus_rule', $renewRule_id, PDO::PARAM_INT);
            $sql2->execute();
        } catch (PDOException $e) {
            echo 'DB接続エラー！' . $e->getMessage();
        }

?>

        <body class="menu">
            <header>
                <h1 class="font-weight-normal">ルールの設定内容</h1>
            </header>
            <form method="post" action="/admin/rule_edit.php?id=formdoc">
                <input type="hidden" name="id" value=<?php echo $renewRule_id; ?>>
                <table border="1">

                    <tr>
                        <td>ルール名称：<input type="text" name="name" value=<?php echo $rules['rule_name']; ?> required></td><br>
                        <td>始業時間：<input type="time" name="start" value=<?php echo $rules['start']; ?> required></td>
                        <td>終業時間：<input type="time" name="end" value=<?php echo $rules['end']; ?> required></td><br>
                        <td>時給：<input type="text" name="perhour" pattern="[0-9]{1,11}" value=<?php echo $rules['perhour']; ?> required></td>
                        <td>1年の給与上限：<input type="text" name="peryear" pattern="[0-9]{1,11}" value=<?php echo $rules['peryear']; ?> required></td>
                    </tr>

                </table>

                <button type="submit">保存</button>

            </form>

            <br>
            <div>ルール適用者</div>

            <table border="1" style="border-collapse: collapse">
                <tr class="head">
                    <td></td>
                    <td>ログインID</td>
                    <td>名前</td>
                    <td>適用</td>
                </tr>
                <?php foreach ($sql2 as $row) { ?>
                    <tr>
                        <?php $i++;
                        $rule_update_url = "'/admin/setting_rule.php?id=" . $i . "'";
                        $_SESSION['staff_id' . $i] = $row['id'];
                        $_SESSION['focus_rule' . $i] = $rules['id'];
                        ?>
                        <td><?php echo $i; ?></td>
                        <td class="logid"><?php echo $row['id']; ?></td>
                        <td class="name"><?php echo $row['name']; ?></td>
                        <?php if ($row['focus_rule?'] == 0 && $row['default?'] == 0) { //特殊ルール編集中かつ別の特殊ルールに設定中の編集行
                            echo "<td class=\"def\"><button type='button' onclick=\"location.href=" . $rule_update_url . "\">" . $row['rule_name'] . "から変更する</a></td>";
                        } else if ($row['focus_rule?'] == 1 && $row['default?'] == 0) { //特殊ルール編集中かつその特殊ルールに設定中の編集行
                            echo '<td class="def">適用中</td>';
                        } else if ($row['focus_rule?'] == 1 && $row['default?'] == 1) { //特殊ルール編集中かつデフォに設定中の編集行
                            if ($renewRule_id == $row['rule']) { //デフォルトルール編集中かつデフォに設定中の編集行
                                echo '<td class="def">デフォルト</td>';
                            } else { //デフォルトルール編集中かつデフォに設定していない編集行
                                echo '<td class="def">変更可</td>';
                            }
                        } else if ($row['focus_rule?'] == 0 && $row['default?'] == 1) { //デフォルトルール編集中かつデフォに設定していない編集行
                            echo "<td class=\"def\"><button type='button' onclick=\"location.href=" . $rule_update_url . "\">" . $row['rule_name'] . "から変更する</a></td>";
                        } else if ($row['focus_rule?'] == 1 && $row['default?'] == 1) { //特殊ルール編集中かつデフォに設定中の編集行
                            echo "<td class=\"def\"><button type='button' onclick=\"location.href=" . $rule_update_url . "\">" . $row['rule_name'] . "から変更する</a></td>";
                        } ?>
                    </tr>
                <?php } ?>

            </table>


        <?php
    } ?>
        <br>
        <footer id="footer">
            <button type="button" onclick="location.href='/admin/rules.php'">一覧に戻る</button>
            <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
            <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>
            <br>
            <a>Copyright (C)gumin</a>
        </footer>
        </body>
    <?php
} else {
    header("Location:../authorize/logout.php");
} ?>

</html>