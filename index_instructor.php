<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
print_r($_SESSION);
if(authenticate(AT_PRIV_CALENDAR)){


}
require (AT_INCLUDE_PATH.'header.inc.php');

global $moduleFactory;
    $coursesmod = $moduleFactory->getModule("_core/courses");
    $courses=$coursesmod->extend_date();
    print_r($courses);
    
require (AT_INCLUDE_PATH.'footer.inc.php');
?>
