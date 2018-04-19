<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 12:52 PM
 */
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFAnalysisTemplate.php';
 
	if (!is_blank($_POST['name']) && !is_blank($_POST['content']) && !is_blank($_POST['t_type'])) {
		  $ivfsperm = (new IVFAnalysisTemplate())->setName($_POST['name'])->setType($_POST['t_type'])->setNote($_POST['content'])->add();
		if (!is_null($ivfsperm)) {
			exit("success:Analysis Template saved");
		} else {
			exit("error:Failed to save Analysis Template");
		}
	} else {
		exit("error: Analysis Template required");
	}
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Template Name
			<input type="text" name="name">
		</label>
		<label>
			Template Type
			<select name="t_type">
				<option value="Pre Sperm Analysis">Sperm Pre Analysis </option>
				<option value="Post Sperm Analysis">Sperm Post Analysis </option>
			</select>
		</label>
		<label>
			Note
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
