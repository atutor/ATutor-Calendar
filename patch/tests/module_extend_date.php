<?php 
@session_start();
if (!defined('AT_INCLUDE_PATH')) { exit; }

	/**
     * Extending the test dates to make them accessible to Calendar Module
     * @param	:	none | course_id(if this function is going to be used for multiple courses)
     * @return	:	array (test dates)
     * @author	:	Anurup Raveendran
     */


function tests_extend_date($course_id=null){

    global $db;
    $tests = array();
    
    if($course_id==null) $course_id = $_SESSION['course_id'];
    
     // get course title
    $sql = "SELECT title 
            FROM ".TABLE_PREFIX."courses 
            WHERE course_id = '".$course_id."'";
            
    $result = mysql_query($sql,$db) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $course_title= $row['title'];
    
    $sql = "SELECT title,test_id,start_date,end_date
            FROM ".TABLE_PREFIX."tests
            WHERE course_id = '".$course_id."'";

    $result = mysql_query($sql,$db) or die(mysql_error());
    $row_count  = mysql_num_rows($result);
    

    if($row_count > 0){
    	$index=0;
        while($row = mysql_fetch_assoc($result)){
            
            
            $unix_ts = strtotime($row['start_date']);
			$time = date("h:i A",$unix_ts);
			$tests[$index][0] = array(
						"date"=>date("D M j Y",$unix_ts),
                        "content"=>strip_tags(
                        	"<div class='content'>".
                            "<span class='day'> <b>".
                        	 date('l',$unix_ts).
                        	"</b> </span>".
                            "<span class='time'>".
                            $time.
                            "</span> - ".                            
                            "<span class='content'>".
                            "<b>Start Date</b> for".			                
			                "</span>".
			                "<span class='module'> <b>Test</b> on </span>".
			                "<span class='title'> '"
                            .$row['title'].
                            "' </span>".
			                "</div>","<b>,<div>,<span>,<br>"),
			            "uuid"=>uniqid(),
			            "unixts"=>$unix_ts,
			            "class"=>'tests'			            
			             ) ;
	
	$unix_ts = strtotime($row['end_date']);		
	$time = date("h:i A",$unix_ts);
	
	                               
			$tests[$index][1] = array(
						"date"=>date("D M j Y",$unix_ts),
                        "content"=>strip_tags(
                        	"<div class='content'>".
                        	 "<span class='day'> <b>".
                        	 date('l',$unix_ts).
                        	"</b> </span>".
                        	"<span class='time'>".
                            $time.
                            "</span> - ".                            
                            "<span class='content'>".
                            "<b>End Date</b> for".			                
			                "</span>".
			                "<span class='module'> <b>Test</b> on</span>".
			                "<span class='title'> '"
                            .$row['title'].
                            "' </span>".
			                "</div>","<b>,<div>,<span>,<br>"),
			            "uuid"=>uniqid(),
			            "unixts"=>$unix_ts,
			            "class"=>'tests' 			             
			            ) ;
		$index++;
        }
    }
    return $tests;
}
?>
