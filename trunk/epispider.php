<?php
/**

    Authors:
        Herman Tolentino MD (herman.tolentino@gmail.com)

    Date: 2006 April 1
    License: GNU GPL (open source)

    EpiSPIDER Project

    IMPORTANT: Implementation of this code requires that you ask permission from and make
    reference to the work of ProMEDMail.org

    USE:
    1. crontab epispider.cron (runs hourly downloads)
    2. see MySQL tables for data stream

**/

require("class.mysql.php");
require("class.promed.php");

$mysqldb = new MysqlDB;
//$conn = $mysqldb->connect("epispider", "epispider", "db.berlios.de", "epispider");
$mysqldb->connect("root", "", "localhost", "epispider");
//$mysqldb->connect("hi4devo_epispide", "epispider", "localhost", "hi4devo_epispider");

$promed = new Promed;
$promed->dbconnect($mysqldb);

$fp = $promed->connect("http://www.promedmail.org");
$promed->extract_links($fp);
$promed->disconnect($fp);

$promed->warehouse();
?>
