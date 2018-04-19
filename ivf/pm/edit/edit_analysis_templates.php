<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 1:11 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFAnalysisTemplatesDAO.php';
$template = (new IVFAnalysisTemplatesDAO())->get($_GET['id']);


if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFAnalysisTemplate.php';
	
	if (!is_blank($_POST['name']) && !is_blank($_POST['content'])) {
		
		
		$template = (new IVFAnalysisTemplate())->setId($template->getId())->setName($_POST['name'])->setNote($_POST['content'])->update();
		if (!is_null($template)) {
			exit("success:Template saved");
		} else {
			exit("error:Failed to save Template");
		}
	} else {
		exit("error:Template Name required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Template Name/Description
			<input type="text" name="name" value="<?=$template->getName() ?>">
		</label>
		<label>
			Template Type
			<input type="text" readonly value="<?= $template->getType() ?>">
		</label>
		<label>
			Content
			<textarea name="content" placeholder="Type/Paste the template content here"><?= $template->getNote() ?></textarea>
		</label>
		<input type="hidden" name="id" value="<?= $template->getId()?>">
		<button type="submit" class="btn">Update Template</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<span name="messageBox"></span>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
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