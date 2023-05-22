<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>list</title>
</head>

<body class="menu">

    <header>
        <h1 class="font-weight-normal">タイムカード一覧</h1>
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
            $dptable = "SELECT staff.login_id,staff.name 'name',staff.rule 'rule',timecard.date 'date',timecard.update_time 'update',
                        TIMEDIFF(TIME(timecard.start), rules.start) 'late_time',
                        TIMEDIFF(rules.end,TIME(timecard.end)) 'early_time',
                        TIMEDIFF(timecard.end,timecard.start) 'work_time',
                        TIMEDIFF(TIME(timecard.end),rules.end) 'over_time',
                        TIMEDIFF(timecard.end,timecard.start)>TIMEDIFF('08:00:00','00:00:00') =1 'exceed_time'
                        FROM staff
                        INNER JOIN rules ON staff.rule = rules.id 
                        INNER JOIN timecard ON staff.login_id = timecard.login_id
                        ORDER BY timecard.update_time DESC LIMIT :fline,10;";
            $sql = $db->prepare($dptable);
            //first-lineからid昇順で10行のみ抽出
            $sql->bindParam(':fline', $fline, PDO::PARAM_INT);
            $sql->execute();
            //DB全体のレコード数を$countに入れる処理
            $dball = "SELECT staff.login_id,staff.name 'name',staff.rule 'rule',timecard.date 'date',timecard.update_time 'update',
                        TIMEDIFF(TIME(timecard.start), rules.start) 'late_time',
                        TIMEDIFF(rules.end,TIME(timecard.end)) 'early_time',
                        TIMEDIFF(timecard.end,timecard.start) 'work_time',
                        TIMEDIFF(TIME(timecard.end),rules.end) 'over_time',
                        TIMEDIFF(timecard.end,timecard.start)>TIMEDIFF('08:00:00','00:00:00') =1 'exceed_time'
                        FROM staff
                        INNER JOIN rules ON staff.rule = rules.id 
                        INNER JOIN timecard ON staff.login_id = timecard.login_id";
            $sth = $db->prepare($dball);
            $sth->execute();
            $count = $sth->rowCount();
        } catch (PDOException $e) {
            echo 'DB接続エラー！: ' . $e->getMessage();
        }

    ?>
        <br>
        <div>履歴一覧表</div>

        <table border="1">
            <tr class="head">
                <!--１行目の出力部分-->
                <td></td>
                <td>名前</td>
                <td>日付</td>
                <td>実働時間</td>
                <td>遅刻or早退</td>
                <td>所定外残業</td>
                <td>超過時間</td>
                <td>更新時間</td>
            </tr>

            <?php foreach ($sql as $row) { ?>
                <tr>
                    <?php $i++;
                    $_SESSION['login_id' . $i] = $row['login_id'];
                    //超過時間の計算(所定外時間は終業時間後の勤務時間、超過時間は８時間以上の勤務時間)
                    //所定外時間：over_time　超過時間：exceed_time
                    /*$Date = date("Y-m-d");
                    $InTime = $Date . "08:00:00";
                    $OutTime = $Date . $row['work_time'];
                    $exceed_time = (strtotime($OutTime) - strtotime($InTime));*/
                    $exceed_time = (strtotime($row['work_time']) - strtotime("08:00:00"));

                    //表示形式のフォーマット
                    $work_time = date("G:i", strtotime($row['work_time'])); //実働時間は必須のため三項演算子なし
                    $exceed_time = date("G:i", ($exceed_time));
                    $exceed_time = $row['exceed_time'] > 0 ? $exceed_time : "";
                    $over_time = date("G:i", strtotime($row['over_time']));
                    $over_time = $row['over_time'] > 0 ? $over_time : "";
                    $update = date("y/m/d G:i", strtotime($row['update']));

                    //備考欄に遅刻・早退を１文で収める
                    $late = strtotime($row['late_time']) > strtotime("00:00:00") ? " [遅刻] " : " [ーー] ";
                    $early = strtotime($row['early_time']) > strtotime("00:00:00") ? " [早退] " : " [ーー] ";
                    ?>

                    <!--２行目以降の出力部分-->
                    <td class="int"><?php echo $i; ?></td>
                    <td class="name"><?php echo $row['name']; ?></td>
                    <td class="date"><?php echo $row['date']; ?></td>
                    <td class="time"><?php echo $work_time; ?></td>
                    <td class="str"><?php echo $late . $early; ?></td>
                    <td class="time"><?php echo $over_time; ?></td>
                    <td class="time"><?php echo $exceed_time; ?></td>
                    <td><?php echo $update; ?></td>
                </tr>
            <?php } ?>

        </table>
        <br><br>
        <a href="/admin/list_staff.php">→修正は「従業員データ」より可能です</a>

        <div id="paging">
            <?php
            if (ceil($count / 10) > 1) {
                for ($j = 1; $j <= ceil($count / 10); $j++) {  //ページ遷移ボタンの実装
                    if ($j == $nowp) {  //表示ページのみボタンではなくページ番号出力
                        echo $j;
                    } else { ?>
                        <button type="button" onclick="location.href='/admin/list_history.php?id=<?php echo $j; ?>'"><?php echo "{$j}" ?></button>
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