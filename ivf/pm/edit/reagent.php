<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 9:51 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ReagentDAO.php';
$reagent = (new ReagentDAO())->get($_GET['id']);
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/Reagent.php';

	$reagent = (new ReagentDAO())->get($_POST['id']);
	if (!is_blank($_POST['name'])) {
		$reagent_ = $reagent->setName($_POST['name'])->update();
		if (!is_null($reagent_)) {
			exit("success:Reagent saved");
		} else {
			exit("error:Failed to save reagent");
		}
	} else {
		exit("error:Reagent Name required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Reagent Name/Description
			<input type="text" name="name" value="<?= $reagent->getName()?>">
		</label>
		<button type="submit" class="btn">Update Reagent</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<input type="hidden" name="id" value="<?= $reagent->getId() ?>">
		<span name="messageBox"></span>
	</form>
</section>
<script type="text/javascript">
	function finish(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>');
			$('button[reset]').click();
		}

	}
</script>
