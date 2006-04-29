<?php
/**
* CLASS SVGMAPPER
* SVGMapper is a PHP class that assembles a dynamic SVG map
* of the world and plots it in relation to ProMED email reports.
*
* @Authors: Raoul Kamadjeu, Herman Tolentino
* @Created: 2006-April-06
*
**/
class svgmapper {

    var $template;
    var $default_condition;
    var $weekly_data;
    var $case_circles;
    var $graph_rectangles;
    var $x_axis_labels;

    function svgmapper() {
    }

    function load_template($template) {

        if ($this->template = $this->read_file($template)) {
        } else {
            print "Error loading file";
        }
    }

    function read_file($file) {

        if ($fp = fopen("$file", 'r')) {
            $buffer = "";
            while (!feof($fp)) {
                $buffer .= fread($fp, 4096);
            }
            if (fclose($fp)) {
                return $buffer;
            }
        } else {
            return false;
        }
    }

    function get_info($type) {

        switch ($type) {

        case "defaultcondition":
            $sql = "select week, country_id from weeklydata where week = week(now()) group by week, country_id";
            if ($result = mysql_query($sql)) {
                if (mysql_num_rows($result)) {
                    $default_condition = "<g id=\"DefaultCondition\">\n";
                    $i = 0;
                    while (list($week, $country_id) = mysql_fetch_array($result)) {
                        $i++;
                        $default_condition .= "<default id=\"$i\" infotext=\"".strtolower($country_id)."\" />\n";
                    }
                    $default_condition .= "</g>\n";
                }
                return $default_condition;
            }
            break;

        case "weeklydata":
            $sql = "select week, country_id, count(country_id) value from weeklydata group by week, country_id";
            if ($result = mysql_query($sql)) {
                if (mysql_num_rows($result)) {
                    $weekly_data = "<g id=\"WeeklyData\">\n";
                    $i = 0;
                    while (list($week, $country_id, $value) = mysql_fetch_array($result)) {

                        $weekly_data .= "<week".$week." id=\"$week\" infotext=\"".strtolower($country_id)."\" value=\"".$value."\"/>\n";

                    }
                    $weekly_data .= "</g>\n";
                }
                return $weekly_data;
            }
            break;

        case "graphdata":
            $sql = "select week, count(week) count from weeklydata group by week";
            if ($result = mysql_query($sql)) {
                if (mysql_num_rows($result)) {
                    $i = 0;
                    $x = 20;
                    $inc = 5;
                    $graph_rectangles = "<g id=\"WeeklyGraph\" style=\"fill:#8B0000; stroke:black; stroke-width:1\" shape-rendering=\"crispEdges\">\n";
                    while (list($week, $count) = mysql_fetch_array($result)) {
                        $i++;
                        //$x = $x + 15;
                        $y = 600-$count;
                        $graph_rectangles .= "<text id=\"txtwk$week\" x=\"".($x+6)."\" y=\"".($y-3)."\" style=\"font-weight:normal;forecolor:red;text-anchor:middle;font-size:7\"> $count </text>";
                        if ($count>0) {
                            $graph_rectangles .= "<rect id=\"week".$week."\" x=\"$x\" y=\"$y\" width=\"12 pt\" height=\"$count pt\"  pos=\"$i\"/>\n";
                        }
                        $graph_rectangles .= "<text id=\"txtwk$week\" x=\"".($x+6)."\" y=\"610\" style=\"font-weight:normal;text-anchor:middle;font-size:7\"> $week </text>";
                        $x = $x+15;
                    }
                    $graph_rectangles .= "</g>\n";
                }
                return $graph_rectangles;
            }
            break;

        }
    }

    function render_map() {

        $javascript = $this->read_file("svg/javascript.svg");
        $this->template = preg_replace("/(<!-- JAVASCRIPT -->)/", $javascript, $this->template);

        $this->weekly_data = $this->get_info("weeklydata");
        $this->template = preg_replace("/(<!-- WEEKLY DATA -->)/", $this->weekly_data, $this->template);

        $this->graph_rectangles = $this->get_info("graphdata");
        $this->template = preg_replace("/(<!-- GRAPH RECTANGLES -->)/", $this->graph_rectangles, $this->template);

        $this->template = preg_replace("/(<!-- CASES AS CIRCLES -->)/", "", $this->template);

        $mapdata = $this->read_file("svg/mapdata.svg");
        $this->template = preg_replace("/(<!-- MAP DATA -->)/", $mapdata, $this->template);

        $this->template = preg_replace("/(<!-- X and Year is dynamic for axes title -->)/", "", $this->template);

        $this->template = preg_replace("/(<!-- X AXIS LABELS -->)/", "", $this->template);

    }

    function write($svgmap) {
        if ($fp = fopen("$svgmap", "w+")) {
            if (fwrite($fp, $this->template)) {
                fclose($fp);
            } else {
                print "Cannot write file. Please check directory permissions.";
            }
        } else {
            print "Cannot open file. Please check if dump directory is present.";
        }
    }

    function display_buffer() {
        print $this->template;
    }
}


?>