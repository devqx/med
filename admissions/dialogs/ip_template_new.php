<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/2/16
 * Time: 8:53 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NursingTemplate.php';

if($_POST){
	if(is_blank($_POST['template_name'])){exit("error:Template title is required");}
	if(is_blank($_POST['content'])){exit("error:Template Content is required");}

	$note = (new NursingTemplate())->setTitle($_POST['template_name'])->setContent($_POST['content'])->add();

	if($note != null){
		exit("success:Template saved");
	}
	exit("error:Template failed to save");
}
?>
<section STYLE="width: 700px">
	<p>Configure new Nursing Template</p>
	<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: savied__})">
		<label>Title <input type="text" name="template_name"></label>
		<label>Content <textarea name="content" class="wide" id="content_area"></textarea> </label>
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	$(document).ready(function(){
		$('#content_area').summernote(SUMMERNOTE_CONFIG);
	});

	function savied__(s) {
		var data = s.split(":");
		if (data[0]==="error"){
			Boxy.warn(data[1]);
		} else if(data[0]==="success") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>

