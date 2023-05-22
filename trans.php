<?php
session_start();
$id = $_GET['id'];
$staff_id = $_SESSION['login_id' . $id];
$admin_id = $_SESSION['admin_id'];
session_destroy();
?>


<body onload="document.trans.submit();">
    <form Name="trans" method="post" action="/admin/mykintai_list.php">
        <input type="hidden" name="id" value=<?php echo $staff_id; ?>>
        <input type="hidden" name="ad" value=<?php echo $admin_id; ?>>
    </form>
</body>