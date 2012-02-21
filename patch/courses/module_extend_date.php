<?php
@session_start();
if (!defined('AT_INCLUDE_PATH')) { exit; }

	/**
     * Extending the course dates to make them accessible to Calendar Module
     * @param	:	none | course_id(if this function is going to be used for multiple courses)
     * @return	:	array (course dates)
     * @author	:	Anurup Raveendran
     */

function courses_extend_date($course_id=null){
    
	global $db;
    $course = array();      
    
    if($course_id==null) $course_id = $_SESSION['course_id'];
    
    // get the course details along with the relevant dates
	$sql = "SELECT M.first_name, M.last_name, C.title, C.release_date, C.end_date
            FROM ".TABLE_PREFIX."courses C , ".TABLE_PREFIX."members M , ".TABLE_PREFIX."course_enrollment E
            WHERE C.course_id = '".$course_id."' 
			AND M.member_id = '".$_SESSION['member_id']."' 
			AND E.member_id = M.member_id";   
	
	
    $result = mysql_query($sql,$db) or die(mysql_error());
    $row_count  = mysql_num_rows($result);     
	
    if($row_count > 0){
    $index=0; 
            $row = mysql_fetch_assoc($result);
            
			$unix_ts = strtotime($row['release_date']);
			$time = date("h:i A",$unix_ts);
			// release_date
			$course[$index][0] =  array(
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
                              "<b>Start Date</b> of".			                  
			                  "</span>  ".
                            "<span class='module'> <b>Course</b> </span>".                              
			                  "</div>","<b>,<div>,<span>,<br>"),
			            "uuid"=>uniqid(),
			            "unixts"=>$unix_ts,
			            "class"=>'course' 
			            ) ;
			                               
			//end date
			$unix_ts = strtotime($row['end_date']);
			$time = date("h:i A",$unix_ts);
			$course[$index][1] = array(
						"date"=>date("D M j Y",$unix_ts),
                        "content"=>strip_tags(
                        	"<div class='content'>".
                        	"<span class='day'> <b>".
                        	 date('l',$unix_ts).
                        	"</b> </span> ".
                        	"<span class='time'>".
                              	$time.
                              	"</span> - ". 
                              	"<span class='content'>".
                                "<b>End Date</b> of". 
			                    "</span>".
                            	"<span class='module'> <b>Course</b> </span>".
			                    "</div>","<b>,<div>,<span>,<br>"),
			            "uuid"=>uniqid(),
			            "unixts"=>$unix_ts,
			            "class"=>'course' 
			            ) ;
	$index++;
    }
    
return $course;
}
?>
