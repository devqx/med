<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/5/16
 * Time: 12:20 PM
 */
$id = filter_var($_GET['instance'], FILTER_VALIDATE_INT);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
$instance = (new AntenatalEnrollmentDAO())->get($id, FALSE);
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/antenatal_vars.php";
if ($_POST) {
	if (is_blank($_POST['gravida'])) {
		exit("error:Gravida is blank");
	}
	if (is_blank($_POST['para'])) {
		exit("error:Para is blank");
	}
	if (is_blank($_POST['alive'])) {
		exit("error:Alive is blank");
	}
	if (is_blank($_POST['abortions'])) {
		exit("error:Miscarriages is blank");
	}
	$validate = validatePregnancies($_POST['gravida'], $_POST['para'], $_POST['alive'], $_POST['abortions']);
	if($validate !== true){
		exit($validate);
	}

	$ae = (new AntenatalEnrollmentDAO())->get($_POST['instance'], TRUE)->setGravida($_POST['gravida'])->setPara($_POST['para'])->setAlive($_POST['alive'])->setAbortions($_POST['abortions'])->update();
	if($ae !== null){
		exit('success:GPAM updated successfully');
	}
	exit('error:Failed to update details');

}
?>
<section style="width:500px">
	<form method="post" id="editGPAMForm" name="editGPAMForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: pot1, onComplete: saved})">
		<label>Gravida <span class="help-block pull-right">Number of Pregnancies (including current)</span>
			<select name="gravida" data-placeholder="--  Select gravida  --">
				<option></option>
				<?php foreach ($gravida as $k => $G) { ?>
					<option value="<?= $k ?>" <?= $instance->getGravida() == $k ? 'selected':'' ?>><?= $G ?></option><?php } ?>
			</select>
		</label>
		<label>Para <span class="help-block pull-right">Number of deliveries</span>
			<Select name="para" data-placeholder="--  Select para  --">
				<option></option>
				<?php foreach ($parity as $k => $P) { ?>
					<option value="<?= $k ?>" <?= $instance->getPara()==$k ? 'selected':'' ?>><?= $P ?></option><?php } ?>
			</Select>
		</label>

		<div class="row-fluid">
			<label class="span6">Alive <Select name="alive" data-placeholder="--  Select live births  --">
					<?php foreach ($general_ as $k => $A) { ?>
						<option value="<?= $k ?>" <?= $instance->getAlive()==$k ? 'selected':'' ?>><?= $A ?></option><?php } ?>
				</Select>
			</label>
			<label class="span6">Miscarriages <Select name="abortions" data-placeholder="--  Select abortions  --">
					<?php foreach ($general_ as $k => $A) { ?>
						<option value="<?= $k ?>" <?= $instance->getAbortions()==$k ? 'selected':'' ?>><?= $A ?></option><?php } ?>
				</Select>
			</label>
		</div>
		<p class="clear clearBoth"></p>
		<input type="hidden" name="instance" value="<?= $id ?>">
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>

</section>
<script type="text/javascript">
	var pot1 = function () {
		$('#editGPAMForm').parent().parent().block({
			message: '<div class="ball"></div>',
			css: {
				borderWidth: '0',
				backgroundColor: 'transparent'
			}
		});
	};

	function saved(s) {
		$('#editGPAMForm').parent().parent().unblock();
		var returnData = s.split(":");
		//console.log(returnData);
		if (returnData[0] == "error") {
			Boxy.alert(returnData[1]);
		} else {
			location.reload();
		}
	}
</script>
