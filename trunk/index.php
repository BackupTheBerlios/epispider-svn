<?
ob_start();
ob_start("ob_gzhandler");
session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>EpiSPIDER</title>
<style type="text/css">
<!--
td { font-size: 10pt; font-family: verdana, sans-serif }
small { font-family: verdana, sans serif}
.textbox { font-family: verdana, arial, sans serif; font-size: 10pt; }
.whitetext { color: white; font-family: verdana, arial, sans serif; font-size: 10pt; }
.error { font-family: verdana, arial, sans serif; font-size: 12pt; color: red; }
.service { font-family: verdana, arial, sans serif; font-size: 12pt; font-weight: bold; color: #585858; }
.pt_menu { font-family: verdana, arial, sans serif; padding-top: 0px; padding-bottom: 0px; font-size: 8pt; font-weight: bold; color: black; }
.topmenu { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; padding-left: 4px; padding-right: 4px; }
.topmenu:hover { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; background-color: #FFFF00; border: 1px solid black; padding-left: 3px; padding-right: 3px;}
.groupmenu { font-family: verdana, arial, sans serif; font-size: 8pt; text-decoration: none; padding-left: 4px; padding-right: 4px; }
.groupmenu:hover { font-family: verdana, arial, sans serif; font-size: 8pt; text-decoration: none; background-color: #CCCCFF; border: 1px solid black; padding-left: 3px; padding-right: 3px; }
.complaintmenu { font-family: verdana, arial, sans serif; font-size: 8pt; text-decoration: none; padding-left: 4px; padding-right: 4px; }
.complaintmenu:hover { font-family: verdana, arial, sans serif; font-size: 8pt; text-decoration: none; background-color: #99FF99; border: 1px solid black; padding-left: 3px; padding-right: 3px;}
.sidemenu { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; padding-left: 3px; padding-right: 3px; }
.sidemenu:hover { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; background-color: #66FF33; border: 1px solid black; padding-left: 2px; padding-right: 2px;}
.catmenu { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; padding-left: 3px; padding-right: 3px; }
.catmenu:hover { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; background-color: #99FFFF; border: 1px solid black; padding-left: 2px; padding-right: 2px;}
.ptmenu { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; padding-left: 3px; padding-right: 3px; }
.ptmenu:hover { font-family: verdana, arial, sans serif; font-size: 10pt; text-decoration: none; background-color: #FFFF33; border: 1px solid black; padding-left: 2px; padding-right: 2px;}
.boxtitle { font-family: verdana, arial, sans serif; font-size: 8pt; font-weight: bold;}
.tiny { font-family: verdana, arial, sans serif; font-size: 7pt; font-weight: bold; color: black; }
.tinylight { font-family: verdana, arial, sans serif; font-size: 7pt; font-weight: normal; color: black; }
.copyright { font-family: verdana, arial, sans serif; font-size: 7pt; font-weight: normal; color: black; }
.admin { font-family: verdana, arial, sans serif; font-size: 14pt; font-weight: bold; color: #FF3300; }
.module { font-family: verdana, arial, sans serif; font-size: 14pt; font-weight: bold; color: #9999FF; }
.library { font-family: verdana, arial, sans serif; font-size: 14pt; font-weight: bold; color: #999999; }
.patient { font-family: verdana, arial, sans serif; font-size: 14pt; font-weight: bold; color: #99CC66; }
.newstitle { font-family: verdana, arial, sans serif; font-size: 12pt; font-weight: bold; color: #666699; }
.newsbody { font-family: Georgia, Times New Roman, Serif; font-size: 12pt; font-weight: normal; color: black; }
-->
</style></head>
<body>
<?
require("class.svgmapper.php");
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
if ($is_connected) {
    $map = new svgmapper;
    $map->load_template("./worldmap.svg.tpl");
    $map->render_map();
    $map->write("dump/worldmap.svg");  
}
?>
<p>
<br />
</p>
<table summary="" align="center">
<tr align="center"><h3>PROMED MAPPER</h3></tr>
<tr bordercolor="#4682B4" style="border-width:3" >
    <embed src="dump/worldmap.svg" width="1020" height="680" align="center" type="image/svg+xml" pluginspage="http://www.adobe.com/svg/viewer/install/">
    <noembed>For Map display the SVG-Plugin from <a href="http://www.adobe.com">http://www.adobe.com</a> is required!</noembed>
</tr>
<tr></tr>
</table>

</body>
</html>
<?
print ob_get_length();
ob_end_flush();
?>

