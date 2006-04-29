<?php
//require("class.promed.php");
if (mysql_connect("db.berlios.de", "epispider", "epispider")) {
    mysql_select_db("epispider");
    print "success";
}
$fp = fopen("./countries.csv", "r");
while (!feof($fp)) {
  $buffer .= fread($fp, 4096);
}
$lines = preg_split("/[\n]/", $buffer);
for ($i = 0; $i < count($lines); $i++) {
    list($country, $code) = preg_split("/(\|)/", $lines[$i]);
    $sql = "insert into country_codes (country_id, country_name) values ('".trim($code)."', '".trim($country)."')";
    $result = mysql_query($sql);
}

?>