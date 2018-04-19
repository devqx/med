<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 6:47 PM
 */
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ExamReportingTemplate.php';
	if(is_blank($_POST['title'])){
		exit("error:Template Title is required");
	}
	if(is_blank($_POST['report_tpl'])){
		exit("error:Template content is required");
	}
	$tpl = (new ExamReportingTemplate())->setTitle($_POST['title'])->setBodyPart($_POST['report_tpl'])->add();
	if($tpl !== null){
		exit("success:Template Added");
	}
	exit("error:An error occurred");
}
?>
<form method="post" name="newExamReportTplForm" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: __addTplStop})">
	<div class="row-fluid">
		<label class="span12">Title <input type="text" name="title" id="title" placeholder="Template Title"></label>

	</div>
	<label><textarea placeholder="Template Content..." id="_tpl_content" name="report_tpl"></textarea></label>

	<div class="clear"></div>
	<div class="btn-block">
		<button type="submit" class="btn">Save</button>
		<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
	</div>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$('#_tpl_content').summernote(SUMMERNOTE_CONFIG);
	});

	function __addTplStop(s){
		var answer = s.split(":");
		if(answer[0]==="error"){
			Boxy.alert(answer[1])
		} else {
			reloadExamTemplates();
			$('form[method="post"][name="newExamReportTplForm"]').get(0).reset();
			$("#_tpl_content").val('').code('');
			Boxy.info(answer[1]);
		}
	}
</script>
