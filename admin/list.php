<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>list</title>
</head>

<body>

    <header>
        <h1 class="font-weight-normal">従業員の勤怠状況一覧</h1>
    </header>

    <?php
    session_start();

    if (isset($_SESSION['admin_id'])) { //ログインしているとき
        //ページングに対応し、DBから情報を持ってくる処理
        if ($_GET['id'] == NULL) {
            $nowp = 1;
        } else {
            $nowp = $_GET['id'];
        }

        $fline = ($nowp - 1) * 10;  //ページ番号に準じた最初の行

        //ログインID,スタッフ氏名,勤務日,実働時間の取得(勤務ルールid)
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT staff.login_id, staff.name, timecard.date, TIMEDIFF(timecard.end, timecard.start) FROM staff JOIN timecard ON staff.`login_id` = timecard.`login_id` ORDER BY timecard.date ASC LIMIT :fline,10;";
            $sql = $db->prepare($dptable);
            //first-lineからid昇順で10行のみ抽出
            $sql->bindParam(':fline', $fline, PDO::PARAM_INT);
            $sql->execute();
            //DB全体のレコード数を$countに入れる処理
            $dball = "SELECT staff.login_id, staff.name, timecard.date, TIMEDIFF(timecard.end, timecard.start) FROM staff JOIN timecard ON staff.`login_id` = timecard.`login_id`;";
            $sth = $db->prepare($dball);
            $sth->execute();
            $count = $sth->rowCount();
        } catch (PDOException $e) {
            echo 'DB接続エラー！: ' . $e->getMessage();
        }

    ?>
        <br>
        <div>社員一覧表</div>

        <table border="1">
            <tr class="head">
                <td></td>
                <td>ログインID</td>
                <td>名前</td>
                <td>日付</td>
                <td>実働時間</td>
                <td>備考</td>
            </tr>

            <?php foreach ($sql as $row) { ?>
                <tr>
                    <?php $i++; ?>
                    <td><?php echo $i; ?></td>
                    <td class="logid"><?php echo $row['login_id']; ?></td>
                    <td class="name"><?php echo $row['name']; ?></td>
                    <td class="date"><?php echo $row['date']; ?></td>
                    <td class="time"><?php echo $row['TIMEDIFF(timecard.end, timecard.start)']; ?></td>
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
                        <button type="button" onclick="location.href='/admin/list.php?id=<?php echo $j; ?>'"><?php echo "{$j}" ?></button>
            <?php }
                }
                echo "<br>";
            } ?>
        </div>
        <footer id="footer">
            <button type="button" onclick="location.href='/admin/menu.php'">管理者メニューへ</button>
            <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
            <br>
            <a>Copyright (C)gumin</a>
        </footer>
    <?php } else { //ログインしていない時
        header("Location:../authorize/logout.php");
    } ?>

</body>

</html>