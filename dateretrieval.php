<?php

define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH . 'vitals.inc.php');


global $db;
$sql="";
$dates=array();
/*TODO : validate student using authenticate method from vitals.inc.php */

/* 
 *  Date Retrieval Function
 */
 function get_dates($all=null){
	 /* get status of the user 
	  * might not be necessary but could be useful later for the personalized calendar 
	  */
     /*       $sql = "SELECT status
                     FROM `".TABLE_PREFIX."members`
                     WHERE `login`='".$_SESSION['login']."'";
					
             $result = mysql_query($sql,$db) or die(mysql_error());
             $row_count = mysql_num_rows($result);
             if($row_count > 0){
                 $row = mysql_fetch_assoc($result);
             }
	   */
	global $moduleFactory;
	$dates=array();
    $coursesmod = $moduleFactory->getModule("_core/courses");
    $courses=$coursesmod->extend_date();    
	$assignmentsmod = $moduleFactory->getModule("_standard/assignments");
    $assignments=$assignmentsmod->extend_date();
	$testsmod = $moduleFactory->getModule("_standard/tests");
    $tests=$testsmod->extend_date();
	$dates['course']= $courses;
	$dates['assignments']=$assignments;
	$dates['tests']=	$tests;
	//$dates[$_SESSION['course_id']]['assignments']=	$assignments;
    die(json_encode($dates));
			 
 }

/*
 *  Verify the user
 */

// Check if the user is an admin
 if(isset($_SESSION['privileges'])){
    if($_SESSION['privileges']){
		if(admin_authenticate(AT_ADMIN_PRIV_CALENDAR)){
			// valid admin user -- do admin stuff
			
		}
		
	}
	
 }
 /* not admin */
 if(isset($_SESSION['valid_user'])){

     if($_SESSION['valid_user']){         
	 
	 /* check if the user is enrolled in the course */
	    $sql = "SELECT COUNT(*) FROM
               `".TABLE_PREFIX."course_enrollment`
                WHERE `member_id`='".$_SESSION['member_id']."'
				AND   `course_id`='".$_SESSION['course_id']."'";
		
		$result = mysql_query($sql,$db);
		$row = mysql_fetch_row($result);
		
		if($row[0]>0){
		$dates = get_dates();      
		}
		else{ /* not enrolled / hacker */
		}
	 }
 }


?>
