<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>list</title>
</head>

<body class="menu">

    <header>
        <h1 class="font-weight-normal">従業員リスト</h1>
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

        //月間表示か年間表示かの判定（デフォルトは年間表示）
        $term = '%Y';
        if (isset($_POST['term']) || isset($_SESSION['term'])) {
            if ($_POST['term'] == 'month') {
                $term = '%Y-%m';
                $_SESSION['term'] = 'month';
            } else if ($_POST['term'] == 'year') {
                $term = '%Y';
                $_SESSION['term'] = 'year';
            } else if (isset($_GET['id']) && $_SESSION['term'] == 'month') {
                $term = '%Y-%m';
            } else if (isset($_GET['id']) && $_SESSION['term'] == 'year') {
                $term = '%Y';
            }
        }

        $fline = ($nowp - 1) * 10;  //ページ番号に準じた最初の行

        //ログインID,スタッフ氏名,勤務日,実働時間の取得(勤務ルールid)
        try {
            $db = new PDO('mysql:dbname=gumin_kintai;host=mysql57.gumin.sakura.ne.jp;charset=utf8', 'root', 'root');
            $dptable = "SELECT staff.login_id 'id',staff.name 'name',staff.rule 'rule',DATE_FORMAT(timecard.date, :term) AS 'period',
                        SUM(TIMEDIFF(TIME(timecard.start),TIME(rules.start))>0)AS 'late_num',
                        SUM(TIMEDIFF(TIME(rules.end),TIME(timecard.start))>0)AS 'cut_num',
                        TIME(SUM(TIMEDIFF(timecard.end,timecard.start)))AS 'work_total',
                        FORMAT(TIME_TO_SEC(SUM(TIMEDIFF(timecard.end,timecard.start)))*rules.perhour/3600,0) AS'income'
                        FROM staff 
                        INNER JOIN rules ON staff.rule = rules.id
                        INNER JOIN timecard ON staff.login_id = timecard.login_id
                        GROUP BY staff.login_id,DATE_FORMAT(timecard.date, :term)
                        ORDER BY staff.login_id ASC LIMIT :fline,10;";
            $sql = $db->prepare($dptable);
            $sql->bindParam(':term', $term, PDO::PARAM_STR);
            //first-lineからid昇順で10行のみ抽出
            $sql->bindParam(':fline', $fline, PDO::PARAM_INT);
            $sql->execute();
            //DB全体のレコード数を$countに入れる処理
            $dball = "SELECT staff.login_id 'id',staff.name 'name',staff.rule 'rule',DATE_FORMAT(timecard.date, :term) AS 'period',
                      SUM(TIMEDIFF(TIME(timecard.start),TIME(rules.start))>0)AS 'late_num',
                      SUM(TIMEDIFF(TIME(rules.end),TIME(timecard.start))>0)AS 'cut_num',
                      TIME(SUM(TIMEDIFF(timecard.end,timecard.start)))AS 'work_total',
                      FORMAT(TIME_TO_SEC(SUM(TIMEDIFF(timecard.end,timecard.start)))*rules.perhour/3600,0) AS'income'
                      FROM staff 
                      INNER JOIN rules ON staff.rule = rules.id
                      INNER JOIN timecard ON staff.login_id = timecard.login_id
                      GROUP BY staff.login_id,DATE_FORMAT(timecard.date, :term);";
            $sth = $db->prepare($dball);
            $sth->bindParam(':term', $term, PDO::PARAM_STR);
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
                <td>期間</td>
                <td>遅刻総数</td>
                <td>早退総数</td>
                <td>総労働時間</td>
                <td>給与予測</td>
            </tr>

            <?php foreach ($sql as $row) { ?>
                <tr>
                    <?php $i++;
                    $_SESSION['login_id' . $i] = $row['id'];
                    ?>
                    <td><?php echo $i; ?></td>
                    <td class="logid"><?php echo $row['id']; ?></td>
                    <td class="name">
                        <a href="../trans.php?id=<?php echo $i; ?>"><?php echo $row['name']; ?></a>
                    </td>
                    <td class="date"><?php echo $row['period']; ?></td>
                    <td class="num"><?php echo $row['late_num']; ?></td>
                    <td class="num"><?php echo $row['cut_num']; ?></td>
                    <td class="time_full"><?php echo $row['work_total']; ?></td>
                    <td class="money_y"><?php echo $row['income'] . "円"; ?></td>
                </tr>
            <?php } ?>

        </table>

        <!—月間表示か年間表示かの選択・送信—>
            <form method="post" action="/admin/list_staff.php">
                <table border="1">

                    <tr>
                        <td>集計期間</td>
                        <td>
                            <?php
                            if ($term == '%Y-%m') {
                                echo '<input type="radio" name="term" value="year" required>年間<br>
                            <input type="radio" name="term" value="month" checked required>月間<br>';
                            } else if ($term == '%Y') {
                                echo '<input type="radio" name="term" value="year" checked required>年間<br>
                            <input type="radio" name="term" value="month" required>月間<br>';
                            } else {
                                echo '想定外の$term';
                            } ?>
                        </td>
                    </tr>

                </table>

                <button type="submit">適用</button>
            </form>

            <div id="paging">
                <?php

                if (ceil($count / 10) > 1) {
                    for ($j = 1; $j <= ceil($count / 10); $j++) {  //ページ遷移ボタンの実装
                        if ($j == $nowp) {  //表示ページのみボタンではなくページ番号出力
                            echo $j;
                        } else { ?>
                            <button type="button" onclick="location.href='/admin/list_staff.php?id=<?php echo $j; ?>'"><?php echo "{$j}" ?></button>
                <?php }
                    }
                    echo "<br>";
                } ?>
            </div>
            <footer id="footer">
                <button type="button" onclick="location.href='../authorize/register_form.php'">従業員の新規登録</button>
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