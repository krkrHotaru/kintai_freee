<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>list</title>
</head>

<body class="menu">

    <header>
        <h1 class="font-weight-normal">勤怠情報一覧</h1>
    </header>

    <?php
    session_start();
    if (isset($_POST['id']) && isset($_POST['ad'])) {
        $_SESSION['staff_id'] = $_POST['id'];
        $_SESSION['admin_id'] = $_POST['ad'];
    }
    $staff_id = $_SESSION['staff_id'];

    if (isset($_SESSION['admin_id'])) { //管理者がログインしているとき
        //ページングに対応し、DBから情報を持ってくる処理
        if ($_GET['id'] == NULL) {
            $nowp = 1;
        } else {
            $nowp = $_GET['id'];
        }

        $fline = ($nowp - 1) * 10;  //ページ番号に準じた最初の行

        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT timecard.id 'id',staff.name 'name',staff.rule 'rule',timecard.start 'shukkin',rules.start,timecard.end 'taikin',rules.end,timecard.date,
                        TIMEDIFF(TIME(timecard.start),rules.start) 'late_time',
                        TIMEDIFF(rules.end,TIME(timecard.end)) 'early_time',
                        TIMEDIFF(timecard.end,timecard.start) 'work_time'
                        FROM staff
                        INNER JOIN rules ON staff.rule = rules.id
                        INNER JOIN timecard ON staff.login_id = timecard.login_id
                        WHERE staff.login_id=" . $staff_id . " ORDER BY timecard.date DESC LIMIT :fline,10";
            $sql = $db->prepare($dptable);
            //first-lineからid昇順で10行のみ抽出
            $sql->bindParam(':fline', $fline, PDO::PARAM_INT);
            $sql->execute();
            //DB全体のレコード数を$countに入れる処理
            $dball = "SELECT timecard.id 'id',staff.name 'name',staff.rule 'rule',timecard.start,rules.start,timecard.end,rules.end,timecard.date,
                      TIMEDIFF(TIME(timecard.start),rules.start) 'late_time',
                      TIMEDIFF(rules.end,TIME(timecard.end)) 'early_time',
                      TIMEDIFF(timecard.end,timecard.start) 'work_time'
                      FROM staff
                      INNER JOIN rules ON staff.rule = rules.id
                      INNER JOIN timecard ON staff.login_id = timecard.login_id
                      WHERE staff.login_id=" . $staff_id;
            $sth = $db->prepare($dball);
            $sth->execute();
            $count = $sth->rowCount();
        } catch (PDOException $e) {
            echo 'DB接続エラー！: ' . $e->getMessage();
        }
    ?>
        <br>
        <div>社員一覧表<?php echo "　[ID：" . $staff_id . "]"; ?></div>

        <table border="1">
            <tr class="head">
                <td></td>
                <td>名前</td>
                <td>規定の業務時間</td>
                <td>勤務した時間帯</td>
                <td>遅刻</td>
                <td>早退</td>
                <td>総労働時間</td>
                <td>編集</td>
                <td>削除</td>
            </tr>

            <?php foreach ($sql as $row) { ?>
                <tr>
                    <?php $i++;
                    $shukkin = date("m/d H:i", strtotime($row['shukkin']));
                    $taikin = date("H:i", strtotime($row['taikin']));
                    $sigyo = date("H:i", strtotime($row['start']));
                    $shugyo = date("H:i", strtotime($row['end']));
                    $late = strtotime($row['late_time']) > strtotime("00:00:00") ? " ☑️ " : " ";
                    $early = strtotime($row['early_time']) > strtotime("00:00:00") ? " ☑️ " : " ";
                    ?>
                    <td><?php echo $i; ?></td>
                    <td class="name"><?php echo $row['name']; ?></td>
                    <td class="date"><?php echo $sigyo . "~" . $shugyo; ?></td>
                    <td class="date"><?php echo $shukkin . "~" . $taikin; ?></td>
                    <td class="rule_num"><?php echo $late; ?></td>
                    <td class="rule_num"><?php echo $early; ?></td>
                    <td class="date"><?php echo $row['work_time']; ?></td>
                    <td><button type="button" onclick="location.href='/admin/stf/time_edit.php?id=<?php echo $row['id']; ?>'">編集</a></td>
                    <td><button type="button" onclick="location.href='/admin/stf/time_delete.php?id=<?php echo $row['id']; ?>'">削除</a></td>
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
                        <button type="button" onclick="location.href='/admin/mykintai_list.php?id=<?php echo $j; ?>'"><?php echo "{$j}" ?></button>
            <?php }
                }
                echo "<br>";
            } ?>
        </div>
        <footer id="footer">
            <button type="button" onclick="location.href='/admin/stf/time_insert.php'">新規追加</button>
            <button type="button" onclick="location.href='/admin/list_staff.php'">一覧へ戻る</button>
            <button type="button" onclick="location.href='/admin/menu.php'">管理者メニュー</button>
            <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
            <br>
            <a>Copyright (C)gumin</a>
        </footer>
    <?php } else { //ログインしていない時
        header("Location:../authorize/logout.php");
    } ?>

</body>

</html>