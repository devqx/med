<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 10:24 AM
 */
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/QualityControlType.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlTypeDAO.php';

	if (!is_blank($_POST['name'])) {
		$reagent = (new QualityControlTypeDAO())->add((new QualityControlType())->setName($_POST['name']));
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
			<input type="text" name="name">
		</label>
		<button type="submit" class="btn">Add</button>
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
