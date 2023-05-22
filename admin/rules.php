<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>ルール一覧</title>
</head>

<body class="menu">
    <header>
        <h1 class="font-weight-normal">ルール一覧</h1>
    </header>

    <?php
    session_start();
    $save = $_SESSION['admin_id'];
    $_SESSION = array();
    $_SESSION['admin_id'] = $save;

    if (isset($_SESSION['admin_id'])) { //ログインしているとき

        //ページングに対応し、DBから情報を持ってくる処理
        if ($_GET['id'] == NULL) {
            $nowp = 1;
        } else {
            $nowp = $_GET['id'];
        }

        $fline = ($nowp - 1) * 10;  //ページ番号に準じた最初の行

        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT * FROM rules ORDER BY id ASC LIMIT :fline,10;";
            $sql = $db->prepare($dptable);
            //first-lineからid昇順で10行のみ抽出
            $sql->bindParam(':fline', $fline, PDO::PARAM_INT);
            $sql->execute();
            //DB全体のレコード数を$countに入れる処理
            $dball = "SELECT * FROM rules;";
            $sth = $db->prepare($dball);
            $sth->execute();
            $count = $sth->rowCount();
        } catch (PDOException $e) {
            echo 'DB接続エラー！: ' . $e->getMessage();
        }

    ?>
        <br>
        <div>勤怠ルール一覧</div>

        <table border="1">
            <tr class="head">
                <td></td>
                <td>ルール名</td>
                <td>始業時間</td>
                <td>終業時間</td>
                <td>時給</td>
                <td>1年の給与上限</td>
                <td>デフォルト</td>
                <td>編集</td>
                <td>削除</td>
            </tr>

            <?php foreach ($sql as $row) { ?>
                <tr>
                    <?php $i++;
                    $defch_url = "'/admin/setting_default.php?id=" . $row['id'] . "'";
                    $money_perhour = number_format($row['perhour']);
                    $max_income = number_format($row['peryear']);
                    ?>
                    <td><?php echo $i; ?></td>
                    <td class="time"><?php echo $row['rule_name']; ?></td>
                    <td class="time"><?php echo $row['start']; ?></td>
                    <td class="time"><?php echo $row['end']; ?></td>
                    <td class="money_h"><?php echo $money_perhour; ?></td>
                    <td class="money_y"><?php echo $max_income; ?></td>
                    <?php if ($row["status"] == 1) {
                        echo '<td class="def">Default-rule</td>';
                    } else {
                        echo "<td class=\"def\"><button type='button' onclick=\"location.href=" . $defch_url . "\">デフォルトに変更</a></td>";
                    } ?>
                    <td><button type="button" onclick="location.href='/admin/rule_edit.php?id=<?php echo $row['id']; ?>'">編集</a></td>
                    <td><button type="button" onclick="location.href='/admin/rule_delete.php?id=<?php echo $row['id']; ?>'">削除</a></td>
                </tr>
            <?php } ?>

        </table>

        <div id="paging">
            <?php
            if (ceil($count / 10) > 1) {
                for ($j = 1; $j <= ceil($count / 10); $j++) {  //ページ遷移ボタンの実装
                    if ($j == $nowp) {  //表示ページのみボタンではなくページ番号出力
                        echo $j;
                    } else { ?>
                        <button type="button" onclick="location.href='/admin/rules.php?id=<?php echo $j; ?>'"><?php echo "{$j}" ?></button>
            <?php }
                }
                echo "<br>";
            } ?>
        </div>
        <footer id="footer">
            <button type="button" onclick="location.href='/admin/rule_form.php'">ルール追加</button>
            <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>
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