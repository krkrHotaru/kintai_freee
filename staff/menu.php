<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>従業員用 勤怠管理画面</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="menu">
    <header>
        <h1 class="font-weight-normal">勤怠管理</h1>
    </header>
    <script src="clock.js"></script>
    <div class="clock">
        <p id="RealtimeClockArea2">現在時刻</p>
    </div>
    <?php
    session_start();

    date_default_timezone_set('Asia/Tokyo');
    $login_id = $_SESSION['login_id'];
    $now_date = date('Y-m-d');

    //ログインIDが存在しない場合はログアウト画面へ
    if (isset($_SESSION['login_id'])) { //ログインしているとき

        echo "こんにちは、 " . $login_id . " さん。<br/>今日も元気に勤怠を記録しましょう。<br>";

        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT * FROM timecard WHERE login_id=:login_id AND end IS NULL;";
            $sql = $db->prepare($dptable);
            $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
            $sql->execute();
            $onlystart_rec = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $dbmsg . 'エラー！: ' . $e->getMessage();
        }

        //常に表示する内容
        if (isset($onlystart_rec['id'])) { //これにより、日を跨ぐ勤務の場合でも未退勤のデータを表示可能
            $situ_msg = "[勤務中]";
            $start_msg = "出勤時刻：" . $onlystart_rec['start'] . "";
            $end_msg = "退勤時刻： --NoData-- ";
        } else {
            $dptable = "SELECT * FROM timecard WHERE login_id=:login_id AND date=:now_date;";
            $sql = $db->prepare($dptable);
            $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
            $sql->bindParam(':now_date', $now_date, PDO::PARAM_STR);
            $sql->execute();
            $today_rec = $sql->fetch(PDO::FETCH_ASSOC);

            if (isset($today_rec['id'])) { //今日の記録があるかどうか
                $situ_msg = "[本日の勤怠情報]";
                $start_msg = "出勤時刻：" . $today_rec['start'] . "";
                $end_msg = "退勤時刻：" . $today_rec['end'] . "";
            } else {
                $situ_msg = "[本日の勤怠情報]";
                $start_msg = "出勤時刻： --NoData-- ";
                $end_msg = "退勤時刻： --NoData-- ";
            }
        }
        $redirect = false;


        if ($_GET['status'] == 1) { //出勤ボタン

            if (isset($onlystart_rec['id'])) { //勤務中なのに出勤ボタンが押された
                $result_msg = "！勤務中なのに出勤ボタンが押されたよ！";
            } else {
                if (isset($today_rec['id'])) { //今日の記録があるかどうか
                    $result_msg = "！本日は既に上記の内容で登録されています！";
                } else { //今日はまだ記録が何もない
                    $dptable = "INSERT INTO timecard(login_id, date, start) VALUES (:login_id, DATE(NOW()), NOW());";
                    $sql = $db->prepare($dptable);
                    $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
                    $sql->execute(); //押した時間を出勤時刻として保存
                    $redirect = true;
                }
            }
        } else if ($_GET['status'] == 2) { //退勤ボタン
            if (isset($onlystart_rec['id'])) { //勤務中で、出勤ボタンが押された
                $dptable = "UPDATE timecard SET end=NOW() WHERE login_id=:login_id AND end IS NULL;";
                $sql = $db->prepare($dptable);
                $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
                $sql->execute(); //押した時間を退勤時刻として保存
                $redirect = true;
            } else { //勤務記録がないのに、退勤ボタンが押された
                if (isset($today_rec['id'])) { //出勤記録と退勤記録がある
                    $result_msg = "！既に退勤済みです！";
                } else { //勤務中データも当日データもない
                    $result_msg = "！出勤記録がありません！";
                }
            }
        } else if ($_GET['status'] == 3) { //削除ボタン
            if (isset($onlystart_rec['id'])) { //出勤記録のみ存在
                $dptable = "DELETE FROM timecard WHERE login_id=:login_id AND end IS NULL;";
                $sql = $db->prepare($dptable);
                $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
                $sql->execute(); //出勤記録のみのデータを削除(勤務中ステータスがなくなるのと同義)
                $redirect = true;
            } else {
                if (isset($today_rec['id'])) { //出勤記録と退勤記録がある
                    $dptable = "DELETE FROM timecard WHERE login_id=:login_id AND date=DATE(NOW());";
                    $sql = $db->prepare($dptable);
                    $sql->bindParam(':login_id', $login_id, PDO::PARAM_INT);
                    $sql->execute(); //押した日の出勤・退勤記録を削除
                    $redirect = true;
                } else { //勤務中データも当日データもない
                    $result_msg = "！本日はまだ勤怠記録がありません！";
                }
            }
        }
        if ($redirect) {
            header("Location:/staff/menu.php");
        }


    ?>
        <div>
            <br>
            <tr><?php echo $situ_msg; ?></tr><br>
            <tr><?php echo $start_msg; ?></tr><br>
            <tr><?php echo $end_msg; ?></tr><br>
            <tr><?php echo $result_msg; ?></tr><br>
            <br>
        </div>
        <a class="push btn_green" href="menu.php?status=1">出勤</a>
        <a class="push btn_red" href="menu.php?status=2">退勤</a>
        <a class="push btn_white" href="menu.php?status=3">出勤/退勤消去</a>
        <!--ここにhelpジャンプ　例：<a href="menu.php?status=3">→記録忘れや手動変更</a>-->
        <br><br>
        <a href="/staff/mykintai_list.php">→記録忘れや手動変更</a>

    <?php
    } else { //ログインしていない時
        header("Location:../authorize/logout.php");
    } ?>

    <footer id="footer">
        <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
        <br>
        <a>Copyright (C)gumin</a>
    </footer>
</body>

</html>