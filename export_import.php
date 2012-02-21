<?php
	
	define('AT_INCLUDE_PATH', '../../include/');

	require(AT_INCLUDE_PATH.'vitals.inc.php');
	require (AT_INCLUDE_PATH.'header.inc.php');	
	global $db;
	global $timezones;
	$timezones = array();
	$regions = array();		
	
	
	$sql = "SELECT * FROM ".TABLE_PREFIX."calendar_timezone_offset";
	$result = mysql_query($sql,$db) or die(mysql_error());
	while($row=mysql_fetch_assoc($result)){
		$timezones[]= array('tz'=>$row['time_zone'],'offset'=>$row['offset']);	
	}
	foreach($timezones as $timezone){ 
		$temp= explode('/',$timezone['tz']);
		if(array_search($temp[0],$regions)===FALSE){
			$regions[]=$temp[0];
		}
	}
	
	@session_start();
	$m_id = $_SESSION['member_id'];	
	
	$sql = "SELECT preferences FROM ".TABLE_PREFIX."members WHERE member_id='".$m_id."'";
			$result = mysql_query($sql,$db) or die(mysql_error());
			$row = mysql_fetch_assoc($result);					
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
	
	
print "<form action='' method='POST'>
			<label class='note' style='font-size:18px;'><b>Note:</b> If you're <b>importing</b> into <b>Google Calendar</b>, make sure the timezone set here matches with the one in Google Calendar. To find the <b>timezone</b> in Google Calendar , please follow these instructions. :-<br>
			1. In the \"<b>My Calendars</b>\" option in <b>Google Calendar</b> , there is a drop-down box next to each calendar. You will get a menu which has the \"<b>Calendar Settings</b>\" option. Clicking on it loads the <b>Calendar Settings</b>.<br>
			2. Here, you can find the \"<b>Calendar Timezone</b>\" option where you can find the <b>timezone</b> set in your <b>Google Calendar</b>. Please make sure that this <b>timezone</b> is same as the one you are setting here in this page.<br>
			&nbsp;&nbsp;- <b>ATutor Team</b>
			</label><br><br>
			<label for='region'>Choose your region</label>
			<select name='region' id='inpregion'>";
			
		$temp= explode('/',$time_zone);	
		$break=FALSE;
		$selected=FALSE;
	foreach($regions as $region){ 
		$selected=FALSE;
		if((array_search($temp[0],$regions)!==FALSE)&&(!$break)){
			$selected=TRUE;
			$break=TRUE;
		}
		
		echo "<option ".(($selected==TRUE)?"selected":"")." >"
		.$region
		."</option>";		
	}
		print "</select><br>			
			<label class='labeltimezone' for='timezone'>Choose your timezone</label>";
			echo "<select name='timezone' id='inptimezone'>";
			$break=FALSE;
		    $selected=FALSE;
			foreach($timezones as $timezone){ 
				$temp = explode('/',$timezone['tz']);
				//get the corresponding region;
				$index = array_search($temp[0],$regions);
				$selected=FALSE;
				if(($temp[0]==$timezone['tz'])&&(!$break)){
					$selected=TRUE;
					$break=TRUE;
				}
								
		echo "<option class='".$regions[$index]."' value='".$timezone['tz']."' ".(($selected==TRUE)?"selected":"")." >"
		.$timezone['tz']." (UTC".(($timezone['offset']!=0)?((strpos($timezone['offset'],'-')!==FALSE)?$timezone['offset']:"+".$timezone['offset']):"")
		.")</option>";
		
	}
		print "</select>
			<br>
			<br>		
			<input type='submit' value='Export' />		
		</form>";		
	
	if(isset($_POST['timezone'])){
		echo "<br>";
		echo "<br>";
		include_once('includes/classes/CalExport.class.php');
		$cei = new CalendarExportImport('export');	
		$ical = $cei->cal_export(array('timezone'=>$_POST['timezone']),0,'ical');
		$fhandle = 	fopen('atutor.ics','w');
		fwrite($fhandle,$ical);
		echo "Calendar successfully exported !<br>";
		echo "<style>a{text-decoration:none;}</style>";
		echo "<a href='".AT_BASE_HREF."/mods/calendar/download.php?file=atutor.ics' target='_blank'>Download</a>";
		echo "<br>";
	}		
	echo "<br><br>";
	echo "<a href='".AT_BASE_HREF."/mods/calendar/index.php'>Go Back To Calendar</a>";
	require (AT_INCLUDE_PATH.'footer.inc.php'); 
