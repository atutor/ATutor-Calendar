<?php
echo "<script language='javascript'>calendar=true;</script>";
?>
<a href='<?php echo AT_BASE_HREF."mods/calendar/"; ?>export_import.php' style='float:right;position:relative;top:0;'>Export Calendar in iCal Format</a>
<br>
<div align="center" class="date-pick dp-applied" id="dp"></div>
<div id="datecontent" style="display:none;"></div>

	<div id="status" align="center">
		<div class="ajaxloader"></div>
		<span class="ajaxtext">Loading...</span>
	</div>
	
<div id="offset"></div>
<div id="accord">
    <h3><a name='month' id='month' class='accordhref' href="#month">Month View</a></h3>
	<div>
        <h5>Events</h5>
		<ul id="monthul"></ul>
	</div>
	<h3><a name='week' id='week' class='accordhref' href="#week">Week View</a></h3>
	<div>
    	<h5>Events</h5>
		<ul id="weekul"></ul>
	</div>
	<h3><a name='day' id='day' class='accordhref' href="#day">Day View</a></h3>
	<div>
    	<h5>Events</h5>
		<ul id="dayul">        
        </ul>
    </div>
</div>
<div id="tooltips" class="tooltip"></div>
<div id="legend">
	<ul style='list-style-type:none;'>
		<h4 style='font-size:1em;'> Legend </h4>
		<li class='course'>
			<div class='color'></div><span class='module'> Course </span>
		</li>
		<li class='assignments'>
			<div class='color'></div><span class='module'> Assignments </span>
		</li>
		<li class='tests'>
			<div class='color'></div><span class='module'> Tests </span>
		</li>
	</ul>
</div>
