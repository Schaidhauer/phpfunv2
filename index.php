<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//echo phpversion();

require_once("app/core/class.core.php");
//$core = new Core(false);
$core = new Core();
$core->redir();
?>