<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 10:20 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticSpecimenDAO.php';
$specimen = (new GeneticSpecimenDAO())->get($_GET['id']);
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticSpecimen.php';
	$specimen = (new GeneticSpecimenDAO())->get($_POST['id']);
	if (!is_blank($_POST['name'])) {
		$item = $specimen->setName($_POST['name'])->update();
		if (!is_null($item)) {
			exit("success:GeneticSpecimen saved");
		} else {
			exit("error:Failed to save GeneticSpecimen");
		}
	} else {
		exit("error:GeneticSpecimen Name required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			GeneticSpecimen Name/Description
			<input type="text" name="name" value="<?=$specimen->getName()?>">
		</label>

		<button type="submit" class="btn">Update GeneticSpecimen</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<span name="messageBox"></span>
		<input type="hidden" name="id" value="<?=$specimen->getId()?>">
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
