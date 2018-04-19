<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 10:24 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlTypeDAO.php';
$qc = (new QualityControlTypeDAO())->get($_GET['id']);

if ($_POST) {
	$qc = (new QualityControlTypeDAO())->get($_POST['id']);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/QualityControlType.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlTypeDAO.php';

	if (!is_blank($_POST['name'])) {
		$reagent = $qc->setName($_POST['name'])->update();
		if (!is_null($reagent)) {
			exit("success:QualityControlType saved");
		} else {
			exit("error:Failed to save QualityControlType");
		}
	} else {
		exit("error:QualityControlType Name required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Quality Control Type
			<input type="text" name="name" value="<?= $qc->getName() ?>">
		</label>
		<input type="hidden" name="id" value="<?= $qc->getId()?>">
		<button type="submit" class="btn">Update</button>
		<button type="button" class="btn-link" reset>Cancel</button>
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
