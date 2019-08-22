<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_profile-sd-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();



?>
