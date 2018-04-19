<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/19/17
 * Time: 10:19 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$QTypes = getTypeOptions('type', 'patient_queue');
$clinic = (new AptClinicDAO())->get($_GET['id']);

if($_POST){
	if(is_blank($_POST['name'])){exit('error:Clinic name is required');}
	if(is_blank($_POST['a_limit'])){exit('error:appointment daily limit/maximum is required');}
	
	if((new AptClinicDAO())->get($_POST['id'])->setName($_POST['name'])->setALimit(parseNumber($_POST['a_limit']))->setQueueType($_POST['queue_type'])->update()) {
		exit('success:clinic updated');
	}
	exit('error:Failed to update clinic details');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:_92934, onComplete: __sdkjri092})">
		<label>Clinic Name <input type="text" name="name" value="<?= $clinic->getName()?>"> </label>
		<label>Daily Appointment Limit <input type="number" name="a_limit" min="0" value="<?= $clinic->getALimit()?>" data-decimals="0"> </label>
		<label>Queue Type <select name="queue_type" data-placeholder=" Select Option " required>
				<option></option>
				<?php foreach ($QTypes as $type){?>
					<option value="<?= $type ?>" <?= $type==$clinic->getQueueType() ? 'selected':'' ?>><?= ucwords($type) ?></option>
				<?php }?>
			</select> </label>
		<input type="hidden" name="id" value="<?= $clinic->getId()?>">
		
		<p style="margin-bottom: 20px;"></p>
		<div class="btn-block">
			<button type="submit" class="btn">Update</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var _92934 = function () {
		$(document).trigger('ajaxSend');
	};
	
	var __sdkjri092  = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if (data[0]==='success'){
			Boxy.get($('.close')).hideAndUnload();
		}
	}
</script>
