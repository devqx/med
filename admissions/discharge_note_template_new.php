<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/2/16
 * Time: 11:15 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DischargedNoteTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DischargedNoteTemplate.php';
	$note = new DischargedNoteTemplate();
	if (!is_blank($_POST['title'])) {
		$note->setTitle($_POST['title']);
	} else {
		exit("Error: Template Title is required");
	}
	
	if (!is_blank($_POST['content'])) {
		$note->setContent($_POST['content']);
	} else {
		exit("Error: Template Content is required");
	}
	$added = (new DischargedNoteTemplateDAO())->add($note);
	
	if ($added !== null) {
		exit("success:Template saved");
	} else {
		exit("error:Template not saved");
	}
}
?>
<section style="width: auto;">
	<p>Save a new discharge template <a href="javascript:;" data-href="template_help.php" class="pull-right" id="discharge_template_link_child"><i class="icon-question-sign">help</i></a></p>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete:done})">
		<label>Title <input type="text" name="title"></label>
		<label>Content <textarea name="content"></textarea></label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>

		</div>
	</form>
</section>
<script>
	$(document).ready(function () {
		$('textarea[name="content"]').summernote(SUMMERNOTE_CONFIG);
	});
	$('#discharge_template_link_child').click(function () {
		Boxy.load("/admissions/" + $(this).data("href"));
	});


	function done(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else {
			refreshTemplates();
			Boxy.get($(".close")).hideAndUnload();
		}
	}


</script>
