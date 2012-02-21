<?php
@session_start();
if (!defined('AT_INCLUDE_PATH')) { exit; }

	/**
     * Extending the assignment dates to make them accessible to Calendar Module
     * @param	:	none | course_id(if this function is going to be used for multiple courses)
     * @return	:	array (assignment dates)
     * @author	:	Anurup Raveendran
     */


function assignments_extend_date($course_id=null){

    global $db;
    $assignments = array();
    
    if($course_id==null) $course_id = $_SESSION['course_id'];
    
    // get course title
    $sql = "SELECT title 
            FROM ".TABLE_PREFIX."courses 
            WHERE course_id = '".$course_id."'";
            
    $result = mysql_query($sql,$db) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $course_title= $row['title'];
    
    $sql = "SELECT assignment_id,title,date_due,date_cutoff
            FROM ".TABLE_PREFIX."assignments
            WHERE course_id = '".$course_id."'";

    $result = mysql_query($sql,$db) or die(mysql_error());
    $row_count  = mysql_num_rows($result);

    if($row_count > 0){
    $index=0;
        while($row = mysql_fetch_assoc($result)){

            $assignment_id = $row['assignment_id'];
            $unix_ts = strtotime($row['date_due']);
            $time = date("h:i A",$unix_ts);
			$assignments[$index][0] =  array(
            					"date"=>date("D M j Y",$unix_ts),
                            	"content"=>strip_tags(
                            	"<div class='content'>".
                             		"<span class='day'> ".
                        	 		date('l',$unix_ts).
                        			" </span>".
                            		"<span class='time'>".
                             		$time.
                             		"</span> - ".
                             		"<span class='content'>".
                             		"<b>Due Date</b> for".			                 		
			                		"</span>".
			                		"<span class='module'> <b>Assignment</b> on</span>".
			                		"<span class='title'> '"
                             		.$row['title'].
                             		"' </span>".
			                 		"</div>","<b>,<div>,<span>,<br>"),
			                	 "uuid"=>uniqid(),
			                 	"unixts"=>$unix_ts,
			                 	"class"=>'assignments' 
			                 	) ;
			                 
			$unix_ts = strtotime($row['date_cutoff']);                  
			$time = date("h:i A",$unix_ts);
			                               
			$assignments[$index][1] =  array(
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
                                "<b>Cutoff Date</b> for".		                    
			                    "</span>".
			                    "<span class='module'> <b>Assignment</b> on</span>".
			                     "<span class='title'> '"
                                .$row['title'].
                                "' </span>".
			                    "</div>","<b>,<div>,<span>,<br>"),
			                "uuid"=>uniqid(),
			                "unixts"=>$unix_ts,
			                "class"=>'assignments' 
			                 ) ;            
		$index++;
        }
    }    
    return $assignments;
}
?>
