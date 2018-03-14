<?php
    class Db {
    private static $instance = NULL;

    private function __construct() {}

    private function __clone() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            $username 	= "FILTEREDOUT";
            $password	= "FILTEREDOUT!@#456";
            $server		= "FILTEREDOUT";
            $database	= "FILTEREDOUT";
            $fiestadb	= new mysqli($server, $username, $password, $database);
            $fiestadb->query("SET NAMES 'utf8'");
            self::$instance = $fiestadb;
        }
        return self::$instance;
        }
    }
?>
