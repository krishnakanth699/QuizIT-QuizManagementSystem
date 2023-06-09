<?php

session_start();
require('checksession.php');
if (isset($_REQUEST['id'])) {
    mysqli_query($con, "delete from class_room where cid='" . $_REQUEST['id'] . "'");
    header("Location: setting.php");
} else if (isset($_REQUEST['uid'])) {
    $sid = "";
    $result = mysqli_query($con, "select * from class_room where sid like '%" . $_REQUEST['uid'] . "%' and cid like '%" . $_REQUEST['cid'] . "%'");
    while ($rows = mysqli_fetch_assoc($result)) {
        $sid = $rows["sid"];
    }
    if (strpos($sid, ",") !== false) {
        $sid = str_replace("," . $_REQUEST['uid'], "", $sid);
        $temp = $_REQUEST['uid'];
        $result22 = mysqli_query($con, "DELETE FROM qattempt WHERE sid='$temp'");
    } else {
        $sid = null;
        $temp1 = $_REQUEST['uid'];
        $result222 = mysqli_query($con, "DELETE FROM qattempt WHERE sid='$temp1'");
    }
    mysqli_query($con, "update class_room set sid='$sid' where cid='" . $_REQUEST['cid'] . "'");
    header("Location: class_room.php?no=" . $_REQUEST['cid']);
}
?>