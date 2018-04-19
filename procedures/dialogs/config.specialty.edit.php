<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/18/17
 * Time: 12:02 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureSpecialty.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureSpecialtyDAO.php';

$data = (new ProcedureSpecialtyDAO())->get($_GET['id']);
if ($_POST) {
	if (!is_blank($_POST['name'])) {
		$ps = (new ProcedureSpecialtyDAO())->get($_POST['iid'])->setName($_POST['name'])->update();
		if ($ps != null) {
			exit('success:Specialty updated');
		} else {
			exit('error:Action failed');
		}
	} else {
		exit('error:Specialty Name is required');
	}
}
?>
<section style="width: 500px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, addSpHandler)">
		<label>Specialty Name <input type="text" name="name" required value="<?= $data->getName() ?>"> </label>
		<p><input type="hidden" name="iid" value="<?= $data->getId()?>"> </p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var addSpHandler = {
		onStart: function () {
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if (data[0] === 'error') {
				Boxy.alert(data[1]);
			} else if (data[0] === 'success') {
				Boxy.get($(".close")).hideAndUnload();
			}
		}
	};
</script>
