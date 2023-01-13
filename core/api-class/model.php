<?php
 
require_once ("db.php");

class model{

    public $mysqli = NULL;

    public function __construct() { 
        $DB = new db();
        $this->mysqli = $DB->getMysqlObject();
    }

    public function esc($str) {

        return $this->mysqli->real_escape_string($str);
    }

    public function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }
}

;
?>