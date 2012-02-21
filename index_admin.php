<?php
// make sure user is allowed to see this page (admins only)
define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH . 'vitals.inc.php');



admin_authenticate(AT_ADMIN_PRIV_CALENDAR);
	
if (isset($_POST['submit'])) {
	// trim whitespace from the value submitted
	$_POST['uri'] = trim($_POST['uri']);

	// display an error message if the value is empty
	if (!$_POST['uri']){
		$msg->addError('CALENDAR_ADD_EMPTY');
	}
	
	// if no errors, insert the key "example_maker" and value "$_POST['uri']" into the config table	
	if (!$msg->containsErrors()) {
		$_POST['uri'] = $addslashes($_POST['uri']);
		$sql = "REPLACE INTO ".TABLE_PREFIX."config VALUES ('calendar', '$_POST[uri]')";
		mysql_query($sql, $db);
		$msg->addFeedback('CALENDAR_URL_SAVED');

		header('Location: '.$_SERVER['PHP_SELF']);
		exit;
	}
}

require (AT_INCLUDE_PATH.'header.inc.php');

/*******
 *  First check to see if there is a value for the example_maker key $_config['example_maker']
 *  If there isn't a value then a missing value message is displayed
 *  The form below that has a single field for submitting a value, in this case a URL
 *  If the value exists in the config table, then display it in the text field using  $_config['example_maker']
 */
	
?>

<?php 
   /*
    global $moduleFactory;
    $coursesmod = $moduleFactory->getModule("_core/courses");
    $courses=$coursesmod->extend_date();
    print_r($courses);
    */
    


?>


<?php if ($_config['calendar']): ?>
	<div class="input-form">
		<div class="row">
			<p><?php echo _AT('example_maker_text'); ?></p>
		</div>
	</div>
<?php else: ?>
	<div class="input-form">
		<div class="row">
			<p><?php echo _AT('example_maker_missing_url');  ?></p>
		</div>
	</div>
<?php endif; ?>

<form action="<?php  $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="input-form">
		<div class="row">
			<p><label for="uri"><?php echo _AT('example_maker_url'); ?></label></p>
	
			<input type="text" name="uri" value="<?php echo $_config['calendar']; ?>" id="uri" size="60" style="min-width: 65%;" />
		</div>
		<div class="row buttons">
			<input type="submit" name="submit" value="<?php echo _AT('save'); ?>"  />
		</div>
	</div>
</form>
