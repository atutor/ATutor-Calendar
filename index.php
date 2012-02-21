<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
$_custom_css = $_base_path . 'mods/calendar/css/calendar.css'; // use a custom stylesheet
require (AT_INCLUDE_PATH.'header.inc.php');
 
include("calendar.php");

require (AT_INCLUDE_PATH.'footer.inc.php'); 
?>