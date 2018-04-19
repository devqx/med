<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/23/17
 * Time: 7:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$cost_centres = (new CostCenterDAO())->all();
$departments = (new DepartmentDAO())->getDepartments();
$existing = (new ServiceCenterDAO())->get($_GET['c_id']);

if($_POST) {
	if (empty($_POST['name'])) {
		exit("error:Name is required");
	}
	 if(is_blank($_POST['cost_centre_id'])){
		 exit("error:Cost Center is required");
	 }
	if(is_blank($_POST['department_id_'])){
		exit("error:Department is required");
	}
	$existing_ = (new ServiceCenterDAO())->get($_GET['c_id']);
  $center_ = $existing_->setName($_POST['name'])->setCostCentre((new CostCenterDAO())->get($_POST['cost_centre_id']))->setDepartment((new DepartmentDAO())->get($_POST['department_id_']))->update();

	if ($center_ !== null) {
		exit("ok:Center updated successfully!");
	} else {
		exit("error:Failed to Update Category");
	}
}
?>
<div><span class="error"></span>
	<form id="hide-45" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {onStart: __ry, onComplete: __cm})">
		<label>Name/Code <input type="text" name="name" value="<?= $existing->getName() ?>"> </label>
		<label>Department <select name="department_id_">
				<?php foreach($departments as $k=>$dept){ ?>
					<option value="<?= $dept->getId() ?>"<?= $dept->getId() == $existing->getDepartment()->getId() ? 'selected="selected"' : ''?>><?= $dept->getName() ?></option>
				<?php }?>
			</select></label>
		<label>Cost Centre <select name="cost_centre_id">
				<?php foreach($cost_centres as $k=>$cs){ ?>
					<option value="<?= $cs->getId() ?>"<?= $cs->getId() == $existing->getCostCentre()->getId() ? 'selected="selected"' : '' ?>><?= $cs->getName() ?></option>
				<?php }?>
			</select> </label>
		<input type="hidden" name="id" value="<?= $_GET['c_id'] ?>">
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script>
	function __ry(){}
	function __cm(s){
		if(s.split(":")[0]=='error'){
			$('span.error').html('<span class="alert alert-error">' + s.split(":")[1] + '</span>');
		}
		else {
			Boxy.get($('.close')).hideAndUnload();
//			$('#existingItemCenters').load("/pm/items/item_centers.php", function () {
//				$('table.table').dataTable();
//			});
		}
	}
</script>