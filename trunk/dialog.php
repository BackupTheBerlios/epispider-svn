<?php
require("class.mysql.php");
$mysql = new mysqldb;
$is_connected = false;
if ($mysql->connect("root", "", "localhost", "epispider")) {
    $is_connected = true;
} else {
    if ($mysql->connect("epispider", "epispider", "db.berlios.de", "epispider")) {
        $is_connected = true;
    } else {
        header("location: error.html");
    }
}

$sql = "select w.week, w.country_id, w.archive_num, r.email_subject, r.email_url from weeklydata w, raw r where r.archive_num = w.archive_num and w.country_id = '".$_GET["country_id"]."' and w.week = '".$_GET["week"]."'";

if ($result = mysql_query($sql) or die(mysql_error())) {
    if (mysql_num_rows($result)) {
        $message = "Clicking on the links will bring you to the article on the ProMED web site.<br>";
        $firstpass = false;
        while (list($week, $country, $archive, $subject, $url) = mysql_fetch_array($result)) {
            if (!isset($display)) {
                $display = "WEEK $week REPORTS<br><br>$message";
            }
            $display .= "<a href='$url' target='_blank'>$subject</a><br>";
        }
        print $display;
    }  
}
?>