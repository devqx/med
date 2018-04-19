<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 10:40 AM
 */
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticTemplateDAO.php';

	if (!is_blank($_POST['name']) && !is_blank($_POST['content'])) {
		$reagent = (new GeneticTemplateDAO())->add((new GeneticTemplate())->setName($_POST['name'])->setContent($_POST['content']));
		if (!is_null($reagent)) {
			exit("success:GeneticTemplate saved");
		} else {
			exit("error:Failed to save GeneticTemplate");
		}
	} else {
		exit("error:GeneticTemplate Name required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			GeneticTemplate Name/Description
			<input type="text" name="name">
		</label>
		<label>
			Content <!--(*this property is bogus and will be deprecated right now! :()-->
			<textarea name="content" placeholder="Type/Paste the template content here"></textarea>
		</label>
		<button type="submit" class="btn">Save Template</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<span name="messageBox"></span>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		console.log("ready page");
		$('textarea[name="content"]').summernote(SUMMERNOTE_CONFIG);
	});
	function finish(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>')
			$('button[reset]').click();
		}

	}
</script>
