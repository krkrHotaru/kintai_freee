<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>rule_edit</title>
    <link rel="stylesheet" href="style.css">
</head>

<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

if (isset($_SESSION['login_id'])) { //従業員のログインIDがセッションにある時

    //編集中のルールのid
    $renewTime_id = $_GET['id'];

    //結果の表示・DB操作部分
    if ($renewTime_id == "formdoc") {

        $renewTime_id = $_POST['id'];
        $date = $_POST['start_day'];
        $start = $_POST['start_day'] . " " . $_POST['start'] . ":00";
        $end = $_POST['end_day'] . " " . $_POST['end'] . ":00";

        if (isset($start) || isset($end)) {
            try {
                $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                $sql = $db->prepare("SELECT * FROM timecard WHERE `date`=:date AND login_id=:login_id;");
                $sql->bindParam(':login_id', $_SESSION['login_id'], PDO::PARAM_INT);
                $sql->bindParam(':date', $date, PDO::PARAM_STR);
                $sql->execute();
                $samedate = $sql->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo 'DB接続エラー！' . $e->getMessage();
            }
            if (isset($samedate['id']) && $renewTime_id <> $samedate['id']) {
                echo "指定の日付には別の勤怠情報が入っています。<br>";
            } else {
                try {
                    $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
                    $sql = $db->prepare("UPDATE `timecard` SET `date`=:date,`start`=:start,`end`=:end WHERE `id`=:id;");
                    $sql->bindParam(':id', $renewTime_id, PDO::PARAM_INT);
                    $sql->bindParam(':date', $date, PDO::PARAM_STR);
                    $sql->bindParam(':start', $start, PDO::PARAM_STR);
                    $sql->bindParam(':end', $end, PDO::PARAM_STR);
                    $sql->execute();
                    echo '編集内容を保存しました！<br>';
                } catch (PDOException $e) {
                    echo 'DB接続エラー！<br>' . $e->getMessage();
                }
                $result = true;
            }
        } else {
            echo "未入力の項目があります。<br>";
        }

        //以下form部分
    } else {

        //編集するタイムカード行の取得
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $sql = $db->prepare("SELECT * FROM timecard WHERE `id`=:id");
            $sql->bindParam(':id', $renewTime_id, PDO::PARAM_INT);
            $sql->execute();
            $timecard = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'DB接続エラー！' . $e->getMessage();
        }

        $day1 = $timecard['start'];
        $start = date("H:i", strtotime($timecard['start']));
        $day2 = $timecard['end'];
        $end = date("H:i", strtotime($timecard['end']));
?>

        <body class="menu">
            <header>
                <h1 class="font-weight-normal">修正内容</h1>
            </header>
            <form method="post" action="/staff/time_edit.php?id=formdoc">
                <input type="hidden" name="id" value=<?php echo $renewTime_id; ?>>
                <table border="1">

                    <tr>
                        <td>修正内容の入力　</td>
                        <td>出勤：<input type="date" name="start_day" value=<?php echo $day1; ?> required>
                            <input type="time" name="start" value=<?php echo $start; ?> required>
                        </td>
                        <td>退勤：<input type="date" name="end_day" value=<?php echo $day2; ?> required>
                            <input type="time" name="end" value=<?php echo $end; ?> required>
                        </td>
                    </tr>

                </table>

                <button type="submit">保存</button>

            </form>

        <?php
    } ?>
        <br>
        <footer id="footer">
            <button type="button" onclick="location.href='/staff/mykintai_list.php'">一覧に戻る</button>
            <button type="button" onclick="location.href='../authorize/logout.php'">ログアウト</button>
            <button type="button" onclick="location.href='../admin/menu.php'">管理者メニューへ</button>
            <br>
            <a>Copyright (C)gumin</a>
        </footer>
        </body>
    <?php

    if ($result) {
        header("Location:/staff/mykintai_list.php");
    }
} else {
    header("Location:../authorize/logout.php");
} ?>

</html>