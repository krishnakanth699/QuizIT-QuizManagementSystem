<?php

session_start();
require('checksession.php');
if (isset($_REQUEST["no"])) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($j = 0; $j < 3; $j++) {
        for ($i = 0; $i < 3; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        if ($j != 2) {
            $randomString .= '-';
        }
    }
    mysqli_query($con, "insert into class_room(cid,cname,tid) values('$randomString','" . $_REQUEST["no"] . "','$uid')");
    header("Location: dashboard.php");
} else if (isset($_REQUEST["no1"])) {
    $result = mysqli_query($con, "select * from class_room where cid='" . $_REQUEST["no1"] . "'");
    if ($rows = mysqli_fetch_array($result)) {
        if ($rows["sid"] === null || strlen($rows["sid"]) <= 0) {
            mysqli_query($con, "update class_room set sid='$uid' where cid = '" . $_REQUEST["no1"] . "'");
            $result3 = mysqli_query($con, "SELECT distinct(qlist.qid),questions,qno FROM qlist inner join qquestions on qquestions.qid=qlist.qid where cid='" . $_REQUEST["no1"] . "'");
            while ($rows = mysqli_fetch_assoc($result3)) {
                mysqli_query($con, "insert into qattempt values('" . $rows["qid"] . "','$uid','" . $rows["qno"] . "','','Not Started','" . $rows["qno"] . "')");
            }
            header("Location: dashboard.php");
        } else if (strpos($rows["sid"], $uid) !== false) {
        } else {
            $sid = $rows["sid"];
            $sid = $sid . "," . $uid;
            mysqli_query($con, "update class_room set sid='$sid' where cid = '" . $_REQUEST["no1"] . "'");
            $result3 = mysqli_query($con, "SELECT distinct(qlist.qid),questions,qno FROM qlist inner join qquestions on qquestions.qid=qlist.qid where cid='" . $_REQUEST["no1"] . "'");
            while ($rows = mysqli_fetch_assoc($result3)) {
                mysqli_query($con, "insert into qattempt values('" . $rows["qid"] . "','$uid','" . $rows["questions"] . "','','Not Started','" . $rows["qno"] . "')");
            }
            header("Location: dashboard.php");

        }
    }
} else if (isset($_REQUEST["add"])) {
    echo "yoyoy";
    $students = explode("\n", $_REQUEST["students"]);
    for ($i = 0; $i < count($students); $i++) {
        echo $students[$i];
        $students[$i] = trim($students[$i]);
        $result = mysqli_query($con, "select * from class_room where cid='" . $_REQUEST["cid"] . "' and sid like '%" . $students[$i] . "%'");
        if (mysqli_num_rows($result) == 0) {
            $result1 = mysqli_query($con, "select * from login where uid like '%" . $students[$i] . "%'");
            if (mysqli_num_rows($result1) > 0) {
                $result2 = mysqli_query($con, "select * from class_room where cid='" . $_REQUEST["cid"] . "'");
                if ($rows = mysqli_fetch_array($result2)) {
                    if ($rows["sid"] === null || strlen($rows["sid"]) <= 0) {
                        mysqli_query($con, "update class_room set sid='$students[$i]' where cid = '" . $_REQUEST["cid"] . "'");
                    } else {
                        $sid = $rows["sid"];
                        $sid = $sid . "," . $students[$i];
                        mysqli_query($con, "update class_room set sid='$sid' where cid = '" . $_REQUEST["cid"] . "'");

                    }
                }
                $result3 = mysqli_query($con, "SELECT distinct(qlist.qid),questions,qno FROM qlist inner join qquestions on qquestions.qid=qlist.qid where cid='" . $_REQUEST["cid"] . "' and status='UPCOMING'");
                while ($rows = mysqli_fetch_assoc($result3)) {
                    // echo $rows["qid"] . $students[$i] . $rows["questions"] . $rows["qno"];
                    mysqli_query($con, "insert into qattempt(qid,sid,questions,answer,status,qno) values('" . $rows["qid"] . "','$students[$i]','" . $rows["questions"] . "','','Not Started','" . $rows["qno"] . "')");
                }
            }
        }
    }
    echo "hello1";
    header("Location: class_room.php?no=" . $_REQUEST["cid"]);
} else if (isset($_REQUEST["qid1"])) {
    $qid = $_REQUEST["qid1"];
    $questions = $_REQUEST["question"];
    $option = $_REQUEST["option"];
    $type = $_REQUEST["type"];
    echo $_REQUEST["qid1"] . "<br>";
    echo $uid . "<br>";
    echo "COMPLETED<br>";
    $result = mysqli_query($con, "select * from qquestions where qid='$qid'");
    while ($rows = mysqli_fetch_assoc($result)) {
        for ($i = 1; $i <= count($questions); $i++) {
            if ($rows["questions"] == $questions[$i]) {
                $correctanswer = $option[$i][0];
                for ($j = 1; $j < count($option[$i]); $j++) {
                    $correctanswer .= "," . $option[$i][$j];
                }
                mysqli_query($con, "update qattempt set answer='$correctanswer',status='Completed' where qid='$qid' and sid='$uid' and questions='$questions[$i]'");
            }
        }
    }
    $marks = 0;
    $result1 = mysqli_query($con, "select * from qquestions where qid='$qid'");
    while ($rows = mysqli_fetch_assoc($result1)) {
        for ($i = 1; $i <= count($questions); $i++) {
            if ($rows["questions"] == $questions[$i]) {
                $correctanswer = $option[$i][0];
                for ($j = 1; $j < count($option[$i]); $j++) {
                    $correctanswer .= "," . $option[$i][$j];
                }
                if ($rows["answer"] == $correctanswer) {
                    $marks += 1;
                }
            }
        }
    }
    mysqli_query($con, "insert into qmarks values('$qid','$uid','$marks')");
    header("Location: cquiz_room.php?qid=" . $qid);
} else if (isset($_REQUEST["qid"])) {
    $qid = $_REQUEST["qid"];
    $qdate = $_REQUEST["qdate"];
    $stime = $_REQUEST["stime"];
    $etime = $_REQUEST["etime"];
    $shuffle = $_REQUEST["shuffle"];
    $qname = $_REQUEST["qname"];
    $question = $_REQUEST["question"];
    $answer = $_REQUEST["answer"];
    $option = $_REQUEST["option"];
    mysqli_query($con, "update qlist set qdate='$qdate',stime='$stime',etime='$etime',shuffle='$shuffle' where qid='$qid'");
    for ($i = 1; $i <= count($question); $i++) {
        $correctanswer = $option[$i][0];
        for ($j = 1; $j < count($option[$i]); $j++) {
            $correctanswer .= "," . $option[$i][$j];
        }
        mysqli_query($con, "update qquestions set questions='$question[$i]',option1='" . $answer[$i][0] . "',option2='" . $answer[$i][1] . "',option3='" . $answer[$i][2] . "',option4='" . $answer[$i][3] . "',answer='$correctanswer' where qid='$qid' and qno='$i'");
        mysqli_query($con, "update qattempt set questions='$question[$i]' where qid='$qid' and qno='$i'");
    }
    header("Location: quiz_room.php?qid=" . $qid);
} else if (isset($_REQUEST["cid"])) {
    $qid = rand(111111, 999999);
    echo $qid;
    $cid = $_REQUEST["cid"];
    $qdate = $_REQUEST["qdate"];
    $stime = $_REQUEST["stime"];
    $etime = $_REQUEST["etime"];
    $shuffle = $_REQUEST["shuffle"];
    $qname = $_REQUEST["qname"];
    $question = $_REQUEST["question"];
    $answer = $_REQUEST["answer"];
    $option = $_REQUEST["option"];
    $type = $_REQUEST["type"];
    $students = array();
    echo $cid;
    $result = mysqli_query($con, "select sid from class_room where cid='$cid'");
    while ($rows = mysqli_fetch_assoc($result)) {
        $students = explode(",", $rows["sid"]);
    }
    echo sizeof($students);
    mysqli_query($con, "insert into qlist(qid,cid,qname,qdate,stime,etime,shuffle,status) values('$qid','$cid','$qname','$qdate','$stime','$etime','$shuffle','UPCOMING')");
    for ($i = 1; $i <= count($question); $i++) {
        $correctanswer = $option[$i][0];
        for ($j = 1; $j < count($option[$i]); $j++) {
            $correctanswer .= "," . $option[$i][$j];
        }


        mysqli_query($con, "insert into qquestions(qid,questions,option1,option2,option3,option4,answer,type,qno) values('$qid','$question[$i]','" . $answer[$i][0] . "','" . $answer[$i][1] . "','" . $answer[$i][2] . "','" . $answer[$i][3] . "','$correctanswer','" . $type[$i] . "','$i')");
        echo "hello";
        for ($j = 0; $j < count($students); $j++) {
            echo "hello1";
            mysqli_query($con, "insert into qattempt(qid,questions,answer,sid,status,qno) values('$qid','" . $question[$i] . "','','" . $students[$j] . "','Not Started','$i')");
            echo "hello2";
        }
    }
    echo "hello";
    header("Location: class_room.php?no=" . $cid);
}

?>