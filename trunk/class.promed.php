<?php
/**

  promed is the class that lookups up the content of http://www.promedmail.org
  and extracts information about outbreaks and epidemics.

**/
class promed {

    var $fp; // connection
    var $status;
    var $promed;
    var $linklimit;
    var $mysqldb;

    function promed() {
        //
        // init function
        //
        $this->linklimit = 4;
    }

    function dbconnect($mysqldb) {
        $this->mysqldb = $mysqldb;
    }

    function set_linklimit($limit) {

        $this->linklimit = $limit;

    }

    function connect($uri) {

        // connect to socket (port 80 URL)
        $this->status = $fp = @fopen ($uri, "r");
        if ($this->status) {
            // you can comment the line below in actual implementation
            // this is good for testing connection
            return $fp;
        } else {
            print "socket connection failed";
            return false;
        }
    }

    /**
    *   FUNCTION extract_links()
    *
    *   Author(s): Herman Tolentino MD
    *   Purpose: This function extracts links from the front page of the ProMEDMail web site and passes
    *   these to extract_info().
    *
    **/
    function extract_links($fp) {

        while (!feof ($fp)) {
            $buffer .= fgets($fp, 4096);
        }

        if (ereg ("(<B>)(.*)(<b>Postings from last 30 days...</b></A>)", $buffer, $matches)) {
            $this->promed = str_replace("<TD><A HREF=\"","<TD>--</TD><TD><A target='_blank' class='boxlink2' HREF=\"http://www.promedmail.org/pls/askus/", $matches[0]);
            $this->promed = str_replace("<B>","<br><U>", $this->promed);
            $this->promed = str_replace("</B>","</U>", $this->promed);
            $this->promed = str_replace("<TABLE","<TABLE cellpadding='1'", $this->promed);
            $this->promed = ereg_replace("(<A HREF=\"f)(.*)(<b>Postings from last 30 days...</b></A>)", "", $this->promed);
        } else {
            print("No document retrieved.<br>");
        }
        //print $this->promed."<br>";
        $pattern = "/(HREF=\")(.*)(\"\>PRO)/";
        $subject = $this->promed;
        preg_match_all($pattern, $subject, $matches);
        for ($i=0; $i < $this->linklimit; $i++) {
            $link = $matches[0][$i];
            // clean up link
            $link = ereg_replace("(HREF=\")", "", $link);
            $link = ereg_replace("(\"\>PRO)", "", $link);
            $link = preg_replace("/[\n]/", "", $link);
            // spider this link
            $fp2 = $this->connect($link);
            $this->extract_info($fp2, $link);
            $this->disconnect($fp2);
        }

    }

    /**
    *   FUNCTION extract_info()
    *
    *   Author(s): Herman
    *
    *   This function saves basic information in the raw table, where each record is one email:
    *   1. link
    *   2. archiveno
    *   3. publishdate
    *   4. subject
    *
    **/
    function extract_info($fp, $link) {
        while (!feof ($fp)) {
            $buffer .= fgets($fp, 4096);
        }

        if (ereg ("(<!-- start: main content -->)(.*)(<!-- end: main content -->)", $buffer, $matches1)) {
            if (ereg("(<!-- start: main content -->)(.*)(<PRE>)", $matches1[0], $header)) {
                //$processed1 = ereg_replace("(<!-- start: main content -->)", "", $header[0]);
                //$processed1 = ereg_replace("(<PRE>)", "", $processed1);
                //print $processed1;
                if (ereg("(</table><table summary=\"\" >)(.*)(</table>)", $header[0], $matches2)) {
                    //print $matches2[0];
                    // archive number example: 20060328.0942

                    $core = $matches[0];

                    $pattern[0] = "/(<\/table><table)(.*)(<\/B><\/td>)/";
                    $pattern[1] = "/(<\/td>)/";
                    $pattern[2] = "/(<\/table>)/";
                    $pattern[3] = "/(<td nowrap)(.*)(left\">)/";
                    $pattern[4] = "/(<\/tr>)(<tr>)(.*)(Date<\/B>)/";
                    $pattern[5] = "/(<\/tr>)(<tr>)(.*)(Subject<\/B>)/";
                    $pattern[6] = "/(<\/tr>)/";
                    $pattern[7] = "/(<br>)/";
                    $replacement[0] = "";
                    $replacement[1] = "";
                    $replacement[2] = "";
                    $replacement[3] = "";
                    $replacement[4] = "";
                    $replacement[5] = "";
                    $replacement[6] = "";
                    $replacement[7] = "";
                    $core = preg_replace($pattern, $replacement, $matches2[0]);
                    list($temp, $archiveno_temp, $publishdate_temp, $subject_temp) = preg_split("/[\n]+/",$core);
                    $array = preg_split("/[\n]+/",$core);
                    // archive number example: 20060328.0942
                    if (preg_match("/^([0-9]{8}\.[0-9]{4})/", $archiveno_temp, $match)) {
                        $archiveno = $match[0];
                    }
                    // published date example: 28-MAR-2006
                    if (preg_match("/([0-9]){1,2}(\-)([A-Z]{3})(\-)([0-9]{4})/", $publishdate_temp, $match)) {
                        $publishdate = $match[0];
                    }
                    // subject example: starts with PRO/
                    if (preg_match("/^(PRO\/)(.*)/", $subject_temp, $match)) {
                        $subject = $match[0];
                        $structured_subject = $this->parse_subject($subject);
                    }

                }
            }
            if (ereg("(<PRE>)(.*)(</PRE>)", $buffer, $matches3)) {
                $freetext = $matches3[0];
                $structuredtext = $this->parse_body($freetext);
                if (strlen(trim($structuredtext["COUNTRY"]))==0) {
                    $structuredtext["COUNTRY"] = $structured_subject["COUNTRY"];
                }
            }
            print "LINK: <a href='$link'>click</a><br>";
            print "ARCHIVE NUMBER: $archiveno<br>";
            print "PUBLISH DATE: $publishdate<br>";
            print "SUBJECT: $subject<br><br>";
            print "TOPIC: ".$structured_subject["TOPIC"]."<br>";
            print "EMAIL TYPE: ".$structured_subject["EMAILTYPE"]."<br>";
            print "SERIES: ".$structured_subject["SERIES"]."<br>";
            print "COUNTRIES: ".$structuredtext["COUNTRY"]."<br><br>";

            $ts = $this->convert_date($publishdate);
            $month = date("n", $ts);
            if ($month<=3) {
                $quarter = 1;
            } elseif ($month<=6) {
                $quarter = 2;
            } elseif ($month<=9) {
                $quarter = 3;
            } elseif ($month<=12) {
                $quarter = 4;
            }
            $pdate = date("Y-m-d", $ts);
            srand();
            $train = rand(0,1);
            $sql = "insert into raw (raw_date, email_url, archive_num, publish_date, email_subject, country_list, series_num, topic, ".
                   "week, month, dayofweek, quarter, train) values ".
                   "('".date("Y-m-d H:i:s")."', '".$link."', '".$archiveno."', '".$pdate."', '".$subject."', ".
                   "'".$structuredtext["COUNTRY"]."', '".$structured_subject["SERIES"]."', ".
                   "'".$structured_subject["TOPIC"]."', '".date("W",$ts)."', '".date("n",$ts)."', '".strtoupper(date("D",$ts))."', ".
                   "'".$quarter."', '$train')";
            $this->mysqldb->execsql($sql);
        }
    }

    function convert_date($publishdate) {

        $month_haystack = array("1"=>"JAN", "2"=>"FEB", "3"=>"MAR", "4"=>"APR", "5"=>"MAY", "6"=>"JUN",
                             "7"=>"JUL", "8"=>"AUG", "9"=>"SEP", "10"=>"OCT", "11"=>"NOV", "12"=>"DEC");
        list($day, $month_needle, $year) = explode("-", $publishdate);
        //$day = int ($day);
        $month = array_search($month_needle, $month_haystack);
        return mktime(0,0,0,$month,$day,$year);
    }

    /**
    *   FUNCTION parse_body()
    *   Author: Herman Tolentino MD
    *   Last update: 04/01/2006
    *   This function is a finite state machine (FSM) to extract structured
    *   information from the email body. It aims to extract the following:
    *
    *   1. countries
    *   2. diseases
    *   3. cases and deaths
    *
    **/
    function parse_body($freetext) {

        // define states
        define(NOSTATE,0);
        define(UPDATESECTION,1);

        // define empty local country_array
        $country_array = array();

        if ($lines = preg_split("/(\n+)/", $freetext, $matches4)) {

            $state = NOSTATE;
            // loop through each line
            for ($i=0; $i<count($lines); $i++) {

                //if (ereg("In this update", $lines[$i])) {
                //    $state = UPDATESECTION;
                //}
                if (preg_match("/(In this report|In this update|In this issue)/", $lines[$i])) {
                    $state = UPDATESECTION;
                } elseif (preg_match("/(A ProMED-mail post)/", $lines[$i])) {
                    $state = UPDATESECTION;
                }
                if ($state<>NOSTATE) {
                    // uncomment line below for debugging
                    //print "<br>STATE: $state ";
                }
                switch ($state) {
                case UPDATESECTION:
                    //print $lines[$i]."<BR>";
                    // PATTERNS:
                    // Pattern 1:
                    //      In this update:
                    //      [1] Germany
                    //      [2] Jordan Valley
                    // Pattern 2:
                    //      In this update:
                    //      [1] Germany - swans, geese, falcons and ducks
                    //      [2] Denmark - tufted duck and whooper swan
                    //      [3] Georgia, swans
                    //      [4] Greece, mute swan
                    // Pattern 3
                    //      In this update:
                    //      [1], [2] Indonesia
                    //      [3] Argentina
                    // Pattern 4
                    //      [1] UK: Department of Health monthly CJD statistics, Mon 3 Apr 2006
                    //      [2] Japan: Update on first Japanese case
                    //      [3] Japan: Proposal for amendment of case description
                    //      [4] UK: Risk assessment
                    // Pattern 5
                    //      [1] Thailand, final report.
                    //      [2] Nigeria, poultry and ornamental birds.
                    //      [3] Israel, poultry.
                    if (ereg("([*]{4,6})", $lines[$i])) {
                        $state = NOSTATE;
                        $country_array = array_unique($country_array);
                        $structuredtext["COUNTRY"] = implode("|", $country_array);
                        $structuredtext["COUNTRY"] = preg_replace("/(\|\|)/", "|", $structuredtext["COUNTRY"]);
                        $structuredtext["COUNTRY"] = preg_replace("/^(\|)/", "", $structuredtext["COUNTRY"]);
                        if (is_array($topic_array)) {
                            $topic_array = array_unique($topic_array);
                            $structuredtext["TOPIC"] = implode("|", $topic_array);
                        }
                        return $structuredtext;
                    }
                    if (preg_match("/(\[)([0-9]{1,3})(.*)/", $lines[$i])) {
                        // clean up line
                        $lines[$i] = preg_replace("/(\[)([0-9]{1,2})(\])(.*)/", "$4", $lines[$i]);
                        $lines[$i] = preg_replace("/(\[)([0-9]{1,2})(\])(.*)/", "$4", trim($lines[$i]));
                        $lines[$i] = preg_replace("/([,])(.*)/", "$2", trim($lines[$i]));
                        $lines[$i] = preg_replace("/([:])(.*)/", "$2", trim($lines[$i]));
                        $lines[$i] = preg_replace("/([.])(.*)/", "$2", trim($lines[$i]));
                        // tokenize the line
                        // assume country name is a word or two
                        $tokens = preg_split("/([\s])/", $lines[$i]);
                        for($j=0; $j<count($tokens); $j++) {
                            if (strlen(trim($tokens[$j]))>0) {
                                // make sure these countries are spelled correctly
                                $phrase = "";
                                // look up one token ahead and see if it makes sense
                                for ($k=0; $k<2; $k++) {
                                    $phrase = strtoupper(trim($phrase." ".$tokens[$j+$k]));
                                    //print "PHRASE: $phrase<br>";
                                    $country_array[] = $this->lookup_country_id($phrase);
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                    break;
                case NUMBERS:
                    break;
                case DISEASE:
                    break;
                } // end switch
                $prev_state = $state;
            } // end for

        }
    }

    function lookup_country_id($phrase) {

        $sql = "select country_id from country_codes where country_name like '$phrase%'";
        if ($result = mysql_query($sql)) {
            if (mysql_num_rows($result)) {
                list($country_id) = mysql_fetch_array($result);
                return $country_id;
            }
        }
        return false;
    }


    /**
    *   FUNCTION parse_subject()
    *   Author: Herman Tolentino MD
    *   Last update: 04/01/2006
    *   This function is a finite state machine (FSM) to extract structured
    *   information from the email subject. It aims to extract the following:
    *
    *   1. topic (contains disease information)
    *   2. number in series
    *   3. country
    *
    **/
    function parse_subject($subject) {

        define(NOSTATE,0);
        define(EMAILTYPE,1);
        define(TOPIC,2);
        define(COUNTRY,3);
        define(SERIES,4);

        // split the subject into tokens
        $tokens = preg_split("/([\s,:])/",$subject);
        $country_keys = array();
        $state = NOSTATE;

        for ($i=0; $i<count($tokens); $i++) {

            // convert tokens to upper case
            $tokens[$i] = strtoupper(stripslashes($tokens[$i]));

            // flag different transition states
            if (preg_match("/^(PRO\/)(.*)(>)/",$tokens[$i])) {
                $state = EMAILTYPE;
            }
            if (preg_match("/^(PRO\/)(.*)(>)/",$tokens[$i-1]) && $i<>0) {
                $state = TOPIC;
            }
            if (preg_match("/(\-)/", $tokens[$i])) {
                $state = COUNTRY;
            }
            if (preg_match("/(\()([0-9]{1,3})(\))/",$tokens[$i])) {
                $state = SERIES;
            }
            if (preg_match("/(\()([A-Z,\s]+)(\))/",$tokens[$i])) {
                //print "<font color='red'>hello</font>";
                //$state = NOSTATE;
            }
            //if ($state <> NOSTATE) {
            //    print "STATE: $state [".$tokens[$i]."]<br>";
            //}

            // execute actions for each state
            switch ($state) {

            case EMAILTYPE:
                $emailtype = preg_replace("/^(PRO\/)(.*)(>)/", "$2", $tokens[$i]);
                break;

            case TOPIC:
                $topic[] .= $tokens[$i];

                break;

            case COUNTRY:
                // expand ProMED abbreviations

                $phrase = "";
                // check next token
                if (strlen(trim($tokens[$i]))>0) {
                    for ($j=0; $j<2; $j++) {
                        $phrase = strtoupper(trim($phrase." ".$tokens[$i+$j]));
                        //print "PHRASE: $phrase<br>";
                        if ($country_id = $this->lookup_country_id($phrase)) {
                            $country_keys[] .= $country_id;
                        }
                    }
                }
                break;

            case SERIES:
                $pattern = "/(\()([0-9]{1,3})(\))/";
                $replacement = "$2";
                $series = preg_replace($pattern, $replacement, $tokens[$i]);
                $state = COUNTRY;
                break;

            default:
                //$topic[] .= $tokens[$i];
                $state = NOSTATE;
            }
            //print "TOKEN ".$tokens[$i]."<BR>";
            /*
            if ($key = array_search($tokens[$i], $this->country_array)) {
                $country_keys[] .= $key;
            }
            */
        } // for $i

        // assemble return value
        //print_r($topic);
        // process topic
        for($i=0; $i<count($topic); $i++) {
            $phrase = "";
            // check next token
            for ($j=0; $j<2; $j++) {
                $phrase = strtoupper(trim($phrase." ".$topic[$i+$j]));
                //print "PHRASE: $phrase<br>";
                $sql = "select diagnosis_code from m_lib_icd10_en where upper(description) like '$phrase%'";
                if ($result = mysql_query($sql)) {
                    if (mysql_num_rows($result)) {
                        list($diagnosis_code) = mysql_fetch_array($result);
                        $newtopic[] .= " [$diagnosis_code]";
                        $i = $i+$j;
                    }
                }
            }
        }
        $country_keys = array_unique($country_keys);
        $structured_subject["TOPIC"] = implode(" ", $topic);
        $structured_subject["COUNTRY"] = implode("|", $country_keys);
        $structured_subject["SERIES"] = $series;
        $structured_subject["EMAILTYPE"] = $emailtype;

        return $structured_subject;
    }

    function disconnect($fp) {

        return fclose ($fp);

    }

    function warehouse() {

        // warehouse weekly data
        $sql = "select week, publish_date, archive_num, country_list from raw where week >= week(now())-1;";
        if ($result = mysql_query($sql)) {
            if (mysql_num_rows($result)) {
                while (list($week, $publishdate, $archive_num, $country_id) = mysql_fetch_array($result)) {
                    if (strlen(trim($country_id))>0) {
                        $country_list = explode("|", $country_id);
                        if (count($country_list)>0) {
                            foreach ($country_list as $country_code) {
                                if (strlen(trim($country_code))>0) {
                                    $sql_insert = "insert into weeklydata values ('$week', '$archive_num', '$country_code', '$publishdate')";
                                    $result_insert = mysql_query($sql_insert);
                                }
                            }
                        } else {
                            if (strlen(trim($country_id))>0) {
                                $sql_insert = "insert into weeklydata values ('$week', '$archive_num', '$country_id', '$publishdate')";
                                $result_insert = mysql_query($sql_insert);
                            }
                        }
                    }
                }
            }
        }

    }
}

?>
