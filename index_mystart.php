<?php 
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
$_custom_css = $_base_path . 'mods/calendar/calendar.css'; // use a custom stylesheet
require (AT_INCLUDE_PATH.'header.inc.php');

echo '<script type="text/javascript">var all=true;</script>';

include("calendar.php");

require (AT_INCLUDE_PATH.'footer.inc.php'); 


?>    


