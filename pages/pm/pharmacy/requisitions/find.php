<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/16
 * Time: 9:50 AM
 */
if (isset($_GET['date']) && !is_blank($_GET['date'])){
	$date = explode(",", $_GET['date']);
	$start = $date[0];
	$stop  = $date[1];
}else {
	$start = null;
	$stop  = null;
}
?>
<div class="row-fluid ui-bar-c">
	<div class="span6">
		Filter by date:
		<div class="input-prepend">
			<span class="add-on">From</span>
			<input class="span2" type="text" name="date_start" value="<?=isset($start)?$start:''?>"  placeholder="Start Date">
			<span class="add-on">To</span>
			<input class="span2" type="text" name="date_stop" value="<?=isset($stop)?$stop:''?>" placeholder="Stop Date">
			<button class="btn" type="button" id="date_filter">Apply</button>
		</div>
	</div>
</div>
