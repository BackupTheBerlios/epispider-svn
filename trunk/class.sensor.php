<?php
class sensor () {

    var $pattern;
    var $input;
    var $output;

    function sensor() {

        $this->pattern = array();
    }

    function add_pattern($pattern) {

        $this->pattern[] .= $pattern;
    }

    function input($text) {
        $this->input = $text;
    }

    function output() {
        foreach()
    }


}

?>
