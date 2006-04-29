<?php
/**

     This is a simple MySQL class that the other EpiSPIDER modules can use.

**/
class MysqlDB {

    var $connection;

    function mysqldb() {
    }

    function connect($user, $password, $host, $db) {

        if ($this->connection = mysql_connect($host,$user,$password)) {
            if (mysql_select_db($db) or die(mysql_error())) {
                return $this->connection;
            } else {
                return false;
            }

        } else {
            return false;
        }
        return false;
     }

     function execsql($sql) {

        if ($result = mysql_query($sql)) {
            return $result;
        } else {
            return mysql_error();
        }
     }

     function rows() {
     }
}
?>