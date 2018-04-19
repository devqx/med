<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/22/16
 * Time: 10:28 AM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$departments = (new DepartmentDAO())->getDepartments();
$cost_centres = (new CostCenterDAO())->all();
$center__ = (new ServiceCenterDAO())->get($_GET['id']);
if($_POST){
	if(is_blank($_POST['centre_name'])){exit('error:What of the centre Name?');}
	if(is_blank($_POST['department_id_'])){exit('error:Select Department');}
	if(is_blank($_POST['cost_centre_id'])){exit('error:Select Cost Centre');}

	$centre = (new ServiceCenterDAO())->get($_POST['id'])->setDepartment( new Department($_POST['department_id_']) )->setCostCentre( new CostCenter($_POST['cost_centre_id']) )->setName($_POST['centre_name'])->setType('Imaging')->update();
	if($centre !== null){
		exit('success:Centre Updated!');
	}
	exit('error:Failed to update centre');
}

?>
<section style="width: 500px;">
	<form method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: $sd, onStart: $sct})">
		<label>Business Unit/Service Centre Name <input type="text" name="centre_name" value="<?= $center__->getName() ?>"> </label>
		<label>Department <select name="department_id_" data-placeholder="- Select Department -">
				<?php foreach ($departments as $k => $dept) { ?>
					<option <?= $center__->getDepartment() != null && $dept->getId() == $center__->getDepartment()->getId() ? ' selected': ''?> value="<?= $dept->getId() ?>"><?= $dept->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Cost Centre <select name="cost_centre_id">
				<?php foreach ($cost_centres as $k => $cs) { ?>
					<option <?= $center__->getCostCentre() != null && $cs->getId() == $center__->getCostCentre()->getId()? 'selected':''?> value="<?= $cs->getId() ?>"><?= $cs->getName() ?></option>
				<?php } ?>
			</select> </label>
		<div class="clear" style="margin-bottom: 25px;">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="id" value="<?= $center__->getId()?>">
	</form>
</section>
<script type="text/javascript">
	 $sct = function() {
		$(document).trigger('ajaxSend');
	};

	 $sd = function (s) {
		 $(document).trigger('ajaxStop');
		 var data = s.split(":");
		 if(data[0]==='success'){
		 	Boxy.info(data[1]);
			 Boxy.get($(".close")).hideAndUnload();
		 } else {
		 	Boxy.warn(data[1]);
		 }
	 }

</script>
