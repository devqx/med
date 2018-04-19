<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/21/16
 * Time: 10:30 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AptClinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if($_POST){
	if(is_blank($_POST['name'])){exit("error:Clinic Name is required");}
	if(is_blank($_POST['app_limit'])){exit("error:Daily Appointment Limit is required");}

	$cl = (new AptClinic())->setName($_POST['name'])->setALimit($_POST['app_limit'])->add();
	if($cl!==null){
		exit("success:Added");
	}
	exit("error:Failed to add clinic");
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: added_apt_clinic})">
		<label>Clinic Name <input type="text" name="name" placeholder="Eg. Antenatal"> </label>
		<label>Daily Appointment Limit <input type="number" name="app_limit" step="1" min="0"> </label>
		<p class="clear"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function added_apt_clinic(s) {
		var data = s.split(":");
		if(data[0]=="error"){
			Boxy.alert(data[1]);}
		else if(data[0]=="success"){
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>
