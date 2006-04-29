<?php
class umls {

    function umls() {
    }

    function connect() {

        $fp = fsockopen("umlsks.nlm.nih.gov", 8042, $errno, $errstr, 1);
        //$fp = fopen("umlsks.nlm.nih.gov:8042");
        if (!$fp) {
            echo "$errstr ($errno)<br/>\n";
        } else {
            $out = '<?xml version="1.0"?><getCurrentUMLSVersion version="1.0"/>%%\r\n\r\n\r\n';

            fputs($fp, $out);
            while (!feof($fp)) {
                echo fgets($fp, 128);
            }
            fclose($fp);
        }

    }
function socket_raw_connect ($server, $port, $timeout,$request)
{
 if (!is_numeric($port) or !is_numeric($timeout)) {return false;}
 $socket = fsockopen($server, $port, $errno, $errstr, $timeout);
 fputs($socket, $request);
 $ret = '';
 while (!feof($socket))
 {
  $ret .= fgets($socket, 4096);
 }
 return $ret;
 fclose($socket);
}

}

$umls = new umls;
$umls->socket_raw_connect("umlsks.nlm.nih.gov", 8042, 5, '<?xml version="1.0"?><getCurrentUMLSVersion version="1.0"/>%%\r\n\r\n\r\n');
?>

