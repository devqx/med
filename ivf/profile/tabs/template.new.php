<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 4:31 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFNoteTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if($_POST){
	if(is_blank($_POST['title'])){exit('error:Template title is required');}
	if(is_blank($_POST['content'])){exit('error:Template content is required');}

	if( (new IVFNoteTemplate())->setTitle($_POST['title'])->setContent($_POST['content'])->add() ){
		exit('success:Template saved');
	}
	exit('error:Failed to create template');
}
?>
<section style="width: 900px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: __wrap8e3d__, onComplete: __impl4cf__})">
		<label>Title <input type="text" name="title"> </label>
		<label>Content <textarea name="content"></textarea></label>
		<p></p>
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('section > form > label:nth-child(2) > textarea').summernote(SUMMERNOTE_CONFIG);
	});

	var __wrap8e3d__ = function () {
		$(document).trigger('ajaxSend');
	};

	var __impl4cf__ = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] == 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
		}
	};
</script>
