<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>管理者用 勤怠管理画面</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="home">
    <header>
        <h1 class="font-weight-normal">勤怠管理</h1>
    </header>
    <?php
    session_start();
    $save = $_SESSION['admin_id'];
    $_SESSION = array();
    $_SESSION['admin_id'] = $save;

    if (isset($_SESSION['admin_id'])) { //ログインしているとき
        date_default_timezone_set('Asia/Tokyo');
        $login_id = $_SESSION['admin_id'];
        echo "こんにちは、 " . $login_id . " さん。<br/>今日もしっかり従業員の勤怠状況を管理しましょう！<br>";

        echo "<br>";

        echo "<div><a>本日変更のあった勤怠情報：";
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT * FROM timecard WHERE DATE(update_time)=DATE(NOW());";
            $sql = $db->prepare($dptable);
            $sql->execute();
            $count = $sql->rowCount();
        } catch (PDOException $e) {
            echo 'DB接続エラー！: ' . $e->getMessage();
        }
        echo $count . '件</a><a href="/admin/list_history.php">→詳細確認/編集</a><br>';

        try {
            $dptable = "SELECT staff.name FROM timecard JOIN staff ON timecard.login_id=staff.login_id WHERE timecard.end IS NULL;";
            $sql = $db->prepare($dptable);
            $sql->execute();
        } catch (PDOException $e) {
            echo $dbmsg . 'エラー！: ' . $e->getMessage();
        }
        echo "<br>";
        echo "<div>勤務中のスタッフ<br>";
        foreach ($sql as $row) {
            echo $row['name'] . "　";
        }
        echo "</div><br>";
        echo "<br>";

        header("Refresh:20");

        echo "<br>";

    ?>

        <a class="push btn_green" href="/admin/rules.php">勤怠ルールの設定/変更</a><br>
        <a class="push btn_red" href="/admin/list_history.php">全ての勤怠履歴一覧</a><br>
        <a class="push btn_white" href="/admin/list_staff.php">従業員データ</a>
        <br><br>

    <?php
    } else { //ログインしていない時
        header("Location:../authorize/logout.php");
    } ?>

    <footer id="footer_home">
        <button type="button" onclick="location.href='/authorize/logout.php'">ログアウト</button>
        <button type="button" onclick="location.href='/authorize/login_form.php'">従業員用ログイン画面</button>
        <br>
        <a>Copyright (C)gumin</a>
    </footer>
</body>

</html>