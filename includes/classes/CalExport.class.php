<?php
class CalendarExportImport{
	//member variables
	
	var $ical; // calendar in ical format
	var $atutor; // calendar in atutor format
	//TODO add more formats
	var $op_mode;
	
	
	/**
     * Constructor : Set the mode of operation(import/export)           
     * @param string $mode - Operation Mode ['export'|'import']      			  	     
     * @author Anurup Raveendran
     */	
	function CalendarExportImport($mode){
		$this->op_mode=$mode;			
	}
	
	/**
	 * Export to the specified format
	 * @param array	$input	-	ATutor dates to be exported
	 * @param int $mode	-	Mode of operation of export [ 0 | 1 ] 
	 *							*	mode 0 - returns the exported dates as a string
	 *							* 	mode 1 - returns the name of the file that has the exported dates
	 * @param string $format - Export Format	DEFAULT 'ical'
	 * @return string
     * @author Anurup Raveendran
	 *
	 */
	function cal_export($input=null,$mode,$format='ical'){
		$cal=""; //output
		$user="";
		if($input==null){
			$time_zone="";
		}
		else{
			$time_zone=$input['timezone'];
		}
		global $db;	
		
		require_once('iCalcreator.class.php');
		require_once('html_parser.inc.php');
		
		$v = new vcalendar( array( 'unique_id' => 'Inclusive Design Insitute' )); //create an instance of the ical obj
		
		// ADD EXTRA DETAILS

		if(isset($_SESSION['member_id'])){
			// GET USER DETAILS			
			$m_id = $_SESSION['member_id'];
			// GET TIME ZONE
			// CHECK USER TABLE FOR PREF_TIMEZONE		
			$sql = "SELECT first_name,last_name,preferences FROM ".TABLE_PREFIX."members WHERE member_id='".$m_id."'";
			$result = mysql_query($sql,$db) or die(mysql_error());
			$row = mysql_fetch_assoc($result);
			$user = $row['first_name'].' '.$row['last_name'];
			
			if($time_zone==""){
			
			$prefs = unserialize($row['preferences']);
			$time_zone_offset=$prefs['PREF_TIMEZONE'];	
					
			if($time_zone_offset==""){
			// CHECK CONFIG TABLE SET BY ADMIN
			$sql = "SELECT value 
					FROM ".TABLE_PREFIX."config
					WHERE name='pref_defaults'";
			$result = mysql_query($sql,$db) or die(mysql_error());
			
				if(mysql_num_rows($result)>0){//pref_defaults is set
					$row = mysql_fetch_row($result);
					$prefs = unserialize($row[0]);
					$time_zone_offset = $prefs['PREF_TIMEZONE'];
				}	
			}
			if($time_zone_offset!=""){
				
				$sql = "SELECT time_zone 
						FROM ".TABLE_PREFIX."calendar_timezone_offset 
						WHERE offset='".$time_zone_offset."'";
						
				$result = mysql_query($sql,$db) or die(mysql_error());
				$row = mysql_fetch_row($result);
				$time_zone = $row[0];
				
			}
			else{
				// GET TIMEZONE FROM SERVER
				$version = phpversion();
				$version = str_replace('Current PHP version: ','',$version);
				$version = explode('.',$version);
				if($version[0]>4){
					$time_zone = date_default_timezone_get();
				}
				else{
					// get from ini file
					$time_zone = ini_get('date.timezone');					
				}
					$sql = "SELECT offset 
							FROM ".TABLE_PREFIX."calendar_timezone_offset 
							WHERE time_zone='".$time_zone."'";
						
					$result = mysql_query($sql,$db) or die(mysql_error());
					$row = mysql_fetch_row($result);
					$time_zone_offset = $row[0];
			}
		}	
		else{
		$sql = "SELECT offset 
							FROM ".TABLE_PREFIX."calendar_timezone_offset 
							WHERE time_zone='".$time_zone."'";
						
					$result = mysql_query($sql,$db) or die(mysql_error());
					$row = mysql_fetch_row($result);
					$time_zone_offset = $row[0];
		}
		}	
		/** HEADER **/
		$v->setProperty( 'X-WR-CALNAME'
               , $user );         
		$v->setProperty( 'X-WR-CALDESC'
               , 'ATutor Dates' );
		$v->setProperty( 'X-WR-TIMEZONE'
               , $time_zone );
		
		// set the ical timezone component
		$e = & $v->newComponent( 'vtimezone' );           
		$e->setProperty( 'TZID', $time_zone );             
		$e->setProperty( 'X-LIC-LOCATION',  $time_zone );  
		
		$t = & $e->newComponent( 'standard' );
		
		$t->setProperty( 'tzoffsetfrom',$this->get_tz_offset($time_zone_offset) );
		$t->setProperty( 'tzoffsetto',$this->get_tz_offset($time_zone_offset) );	             
		$t->setProperty( 'dtstart', 1970, 1, 1, 0, 0, 00 );
		/** HEADER **/
		
		// NOW LOAD THE EVENTS
		global $moduleFactory;
		$coursesmod = $moduleFactory->getModule("_core/courses");
		$assignmentsmod = $moduleFactory->getModule("_standard/assignments");
		$testsmod = $moduleFactory->getModule("_standard/tests");
		
		// GET ALL THE COURSES THE USER IS ENROLLED IN
		$sql = "SELECT course_id
            	FROM ".TABLE_PREFIX."course_enrollment 
            	WHERE member_id = '".$_SESSION['member_id']."'";
		
        $course_result = mysql_query($sql,$db) or die(mysql_error());
        while($row=mysql_fetch_row($course_result)){
        	$course=$coursesmod->extend_date($row[0]);        	
        	$course=$course[0];
        	// get the dates
			$course_start_unix_ts	= $course[0]['unixts'];
			$course_end_unix_ts		= $course[1]['unixts'];			
        	
        	$cs = & $v->newComponent( 'vevent' );           			// initiate COURSE_START event
			
			$cs->setProperty('dtstart', 
							date("Y",$course_start_unix_ts),
							date("n",$course_start_unix_ts),
							date("j",$course_start_unix_ts),
							date("G",$course_start_unix_ts),
							date("m",$course_start_unix_ts),
							date("i",$course_start_unix_ts)							
							);											// COURSE_START DTSTART
			
			$cs->setProperty('dtend', 
							date("Y",$course_start_unix_ts),
							date("n",$course_start_unix_ts),
							date("j",$course_start_unix_ts),
							date("G",$course_start_unix_ts),
							date("m",$course_start_unix_ts),
							date("i",$course_start_unix_ts)
							
							);											// COURSE_START DTEND
							
			$cs->setProperty('dtstamp', 
							date("Y"),
							date("n"),
							date("j"),
							date("G"),
							date("m"),
							date("i")							
							);											// CALENDAR TS
							
			
			//parse the html content to get the summary			
			$html = str_get_html($course[0]['content']);
			
			foreach($html->find('div.content') as $d)
				    $div = $d;
				    
			foreach($div->find('span.module') as $m)
					$module = $m->innertext;
					
			foreach($div->find('span.title') as $tl)
					$title = $tl->innertext;
					
			foreach($div->find('span.content') as $c)
					$content = $c->innertext;				
			
			unset($html);
			unset($div);
			
			$summary =	$module.$title.$content;
			
			$cs->setProperty('summary',	 $summary );					// SUMMARY
			
			
			$cs->setProperty( 'sequence', 0);							// SEQUENCE NUMBER
			$cs->setProperty( 'status', 'CONFIRMED' );					// STATUS			
			$cs->setProperty( 'transp', 'TRANSPARENT' );				// TRANSPARENCY
			
			
			$ce = & $v->newComponent( 'vevent' );           			// initiate COURSE_END event
			
			$ce->setProperty('dtstart', 
							date("Y",$course_end_unix_ts),
							date("n",$course_end_unix_ts),
							date("j",$course_end_unix_ts),
							date("G",$course_end_unix_ts),
							date("m",$course_end_unix_ts),
							date("i",$course_end_unix_ts)							
							);											// COURSE_END DTSTART
			
			$ce->setProperty('dtend', 
							date("Y",$course_end_unix_ts),
							date("n",$course_end_unix_ts),
							date("j",$course_end_unix_ts),
							date("G",$course_end_unix_ts),
							date("m",$course_end_unix_ts),
							date("i",$course_end_unix_ts)							
							);											// COURSE_END DTEND
							
			$ce->setProperty('dtstamp', 
							date("Y"),
							date("n"),
							date("j"),
							date("G"),
							date("m"),
							date("i")							
							);											// CALENDAR TS
							
			
			//parse the html content to get the summary			
			$html = str_get_html($course[1]['content']);
			
			foreach($html->find('div.content') as $d)
				    $div = $d;
				    
			foreach($div->find('span.module') as $m)
					$module = $m->innertext;
					
			foreach($div->find('span.title') as $tl)
					$title = $tl->innertext;
					
			foreach($div->find('span.content') as $c)
					$content = $c->innertext;				
			
			unset($html);
			unset($div);
			
			$summary = $module.$title.$content;		
			$ce->setProperty('summary',	 $summary );					// SUMMARY
			
			
			$ce->setProperty( 'sequence', 0);							// SEQUENCE NUMBER
			$ce->setProperty( 'status', 'CONFIRMED' );					// STATUS			
			$ce->setProperty( 'transp', 'TRANSPARENT' );				// TRANSPARENCY
			
			
			//GET THE ASSIGNMENTS FOR THIS COURSE
			$assignments=$assignmentsmod->extend_date($row[0]);					
			
			foreach($assignments as $key=>$assignment){
			
				// get the dates
				$assignment_due_unix_ts		= $assignment[0]['unixts'];
				$assignment_cutoff_unix_ts	= $assignment[1]['unixts'];
				
				$ad = & $v->newComponent( 'vevent' );         			// initiate ASSIGNMENT_DUE event
			
				$ad->setProperty('dtstart', 
								date("Y",$assignment_due_unix_ts),
								date("n",$assignment_due_unix_ts),
								date("j",$assignment_due_unix_ts),
								date("G",$assignment_due_unix_ts),
								date("m",$assignment_due_unix_ts),
								date("i",$assignment_due_unix_ts)								
								);										// ASSIGNMENT_DUE DTSTART
			
			
				$ad->setProperty( 'dtend', 
								date("Y",$assignment_due_unix_ts),
								date("n",$assignment_due_unix_ts),
								date("j",$assignment_due_unix_ts),
								date("G",$assignment_due_unix_ts),
								date("m",$assignment_due_unix_ts),
								date("i",$assignment_due_unix_ts)								
								); 										// ASSIGNMENT_DUE DTEND
							                   
				$ad->setProperty('dtstamp', 
								date("Y"),
								date("n"),
								date("j"),
								date("G"),
								date("m"),
								date("i")								
								);										// WHEN THE CAL WAS EXPORTED
							
			
				//parse the html content to get the summary
				
				$html = str_get_html($assignment[0]['content']);
			
				foreach($html->find('div.content') as $d)
				    	$div = $d;
				    
				foreach($div->find('span.module') as $m)
						$module = $m->innertext;
					
				foreach($div->find('span.title') as $tl)
						$title = $tl->innertext;
					
				foreach($div->find('span.content') as $c)
						$content = $c->innertext;				
			
				unset($html);
				unset($div);

				$summary =	$module.$title.$content;
				$ad->setProperty( 'summary', 
									$summary	);						// SUMMARY
								
				$ad->setProperty( 'sequence', 0);
				$ad->setProperty( 'status', 'CONFIRMED' );				// STATUS		
				$ad->setProperty( 'transp', 'TRANSPARENT' );			// TRANSPARENCY
				
				
				$ac = & $v->newComponent( 'vevent' );           		// initiate ASSIGNMENT_CUTOFF event
				
				$ac->setProperty('dtstart', 
								date("Y",$assignment_cutoff_unix_ts),
								date("n",$assignment_cutoff_unix_ts),
								date("j",$assignment_cutoff_unix_ts),
								date("G",$assignment_cutoff_unix_ts),
								date("m",$assignment_cutoff_unix_ts),
								date("i",$assignment_cutoff_unix_ts)								
								);										// ASSIGNMENT_CUTOFF DTSTART
			
			
				$ac->setProperty( 'dtend', 
								date("Y",$assignment_cutoff_unix_ts),
								date("n",$assignment_cutoff_unix_ts),
								date("j",$assignment_cutoff_unix_ts),
								date("G",$assignment_cutoff_unix_ts),
								date("m",$assignment_cutoff_unix_ts),
								date("i",$assignment_cutoff_unix_ts)								
								);										// ASSIGNMENT_CUTOFF DTEND
							                  
				$ac->setProperty('dtstamp', 
								date("Y"),
								date("n"),
								date("j"),
								date("G"),
								date("m"),
								date("i")								
								);										// WHEN THE CAL WAS EXPORTED
							
			
				//parse the html content to get the summary
				
				$html = str_get_html($assignment[1]['content']);
			
				foreach($html->find('div.content') as $d)
				   		$div = $d;
				    
				foreach($div->find('span.module') as $m)
						$module = $m->innertext;
					
				foreach($div->find('span.title') as $tl)
						$title = $tl->innertext;
					
				foreach($div->find('span.content') as $c)
						$content = $c->innertext;				
			
				unset($html);
				unset($div);

				$summary =	$module.$title.$content;
				
				$ac->setProperty( 'summary',	$summary	);			// SUMMARY
								
				$ac->setProperty( 'sequence', 0);
				$ac->setProperty( 'status', 'CONFIRMED' );				// STATUS			
				$ac->setProperty( 'transp', 'TRANSPARENT' );			// TRANSPARENCY			
			}
				
				
				
			//GET THE TESTS FOR THIS COURSE
    		$tests=$testsmod->extend_date($row[0]);
    		
    		foreach($tests as $test){
    			// get the dates
			
				$test_start_unix_ts		= $test[0]['unixts'];
				$test_end_unix_ts		= $test[1]['unixts'];
			
			
        		$ts = & $v->newComponent( 'vevent' );           		// initiate TEST_START event
			
				$ts->setProperty('dtstart', 
							date("Y",$test_start_unix_ts),
							date("n",$test_start_unix_ts),
							date("j",$test_start_unix_ts),
							date("G",$test_start_unix_ts),
							date("m",$test_start_unix_ts),
							date("i",$test_start_unix_ts)							
							);											// TEST_START DTSTART
			
				$ts->setProperty('dtend', 
							date("Y",$test_start_unix_ts),
							date("n",$test_start_unix_ts),
							date("j",$test_start_unix_ts),
							date("G",$test_start_unix_ts),
							date("m",$test_start_unix_ts),
							date("i",$test_start_unix_ts)
							);											// TEST_START DTEND
							
				$ts->setProperty('dtstamp', 
							date("Y"),
							date("n"),
							date("j"),
							date("G"),
							date("m"),
							date("i")
							);											// CALENDAR TS
							
			
				//parse the html content to get the summary
				$html = str_get_html($test[0]['content']);
			
				foreach($html->find('div.content') as $d)
				   		$div = $d;
				    
				foreach($div->find('span.module') as $m)
						$module = $m->innertext;
					
				foreach($div->find('span.title') as $tl)
						$title = $tl->innertext;
					
				foreach($div->find('span.content') as $c)
						$content = $c->innertext;				
			
				unset($html);
				unset($div);
			
				$summary =	$module.$title.$content;
				$ts->setProperty('summary',
							 	$summary );								// SUMMARY
			
			
				$ts->setProperty( 'sequence', 0);						// SEQUENCE NUMBER
				$ts->setProperty( 'status', 'CONFIRMED' );				// STATUS			
				$ts->setProperty( 'transp', 'TRANSPARENT' );			// TRANSPARENCY
			
			
				$te = & $v->newComponent( 'vevent' );           		// initiate TEST_END EVENT
			
				$te->setProperty('dtstart', 
							date("Y",$test_end_unix_ts),
							date("n",$test_end_unix_ts),
							date("j",$test_end_unix_ts),
							date("G",$test_end_unix_ts),
							date("m",$test_end_unix_ts),
							date("i",$test_end_unix_ts)							
							);											// TEST_END DTSTART
			
				$te->setProperty('dtend', 
							date("Y",$test_end_unix_ts),
							date("n",$test_end_unix_ts),
							date("j",$test_end_unix_ts),
							date("G",$test_end_unix_ts),
							date("m",$test_end_unix_ts),
							date("i",$test_end_unix_ts)							
							);											// TEST_END DTEND
							
				$te->setProperty('dtstamp', 
							date("Y"),
							date("n"),
							date("j"),
							date("G"),
							date("m"),
							date("i")
							);											// CALENDAR TS
						
			
				//parse the html content to get the summary
				$html = str_get_html($test[1]['content']);
			
				foreach($html->find('div.content') as $d)
				   		$div = $d;
				    
				foreach($div->find('span.module') as $m)
						$module = $m->innertext;
					
				foreach($div->find('span.title') as $tl)
						$title = $tl->innertext;
					
				foreach($div->find('span.content') as $c)
						$content = $c->innertext;				
			
				unset($html);
				unset($div);
			
				$summary =	$module.$title.$content;
				
				$te->setProperty('summary',
							 	$summary );								// SUMMARY
			
			
				$te->setProperty( 'sequence', 0);						// SEQUENCE NUMBER
				$te->setProperty( 'status', 'CONFIRMED' );				// STATUS			
				$te->setProperty( 'transp', 'TRANSPARENT' );			// TRANSPARENCY
			
        	}	
        }
       		$str = $v->createCalendar();  
		
		return $str;
	} 
	
	/**
	 * Import to the ATutor DB
	 * @param string $input	-	Depends on mode
	 * @param int $mode	-	Mode of operation of import [ 0 | 1 ] 
	 *							*	mode 0 - accepts the dates to be imported as a string
	 *							* 	mode 1 - accepts the name of the file containing the dates to be imported
	 * @param string $format - Import Format	DEFAULT 'ical'
	 * @return void
     * @author Anurup Raveendran
	 *
	 */
	function cal_import($input,$mode,$format='ical'){
		$ics_file_name="";		
				
		include('classes/parser_'.$format.'.class.php'); // include the format-based parser	
		
		if(!$mode){// string input | rare case but good for code re-use
			$ics_file_name=AT_CONTENT_DIR.(uniqid()).'ics';
			$fhandle = @fopen($ics_file_name,'w');
			fwrite($fhandle, $input); 
    		fseek($fhandle, 0);
    		fclose($fhandle);
    	}
    	else if($mode){// filename input
    		$ics_file_name=$input;
    	}
		
		$cal = parse_ical($ics_file_name);
		
		//parse $cal
	}
	/**
	 * to get the timezone_offset in ical format
	 * @param string $offset
	 * @return string timezone offset in ical format
     * @author Anurup Raveendran
	 *
	 */
	
	function get_tz_offset($offset){
		if($offset=="") return "";
		
		//make sure the sign of the offset is correct
		$sign="+";
		
		if(strpos($offset,'-')!==FALSE) $sign="-";
		
		if(is_array(explode('.',$offset))){
			
		    
			$temp = explode('.',$offset);
			$hours = intval($temp[0]);
			$minutes = $offset - $hours;
			$minutes = intval($minutes*60);
			
			return $sign.(($hours>9)?$hours:("0".$hours)).(($minutes>9)?$minutes:("0".$minutes));
		}
		else{
		
			$hours = intval(substr($offset,1));
			
			return $sign.(($hours>9)?$hours:("0".$hours));
			
		}			
	}
}
?>
