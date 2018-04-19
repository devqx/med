<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 6:47 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ExamReportingTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamReportingTemplateDAO.php';
$report = (new ExamReportingTemplateDAO())->get($_GET['id']);
if($_POST){
	$report_ = (new ExamReportingTemplateDAO())->get($_POST['id']);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if(is_blank($_POST['title'])){
		exit("error:Template Title is required");
	}
	if(is_blank($_POST['report_tpl'])){
		exit("error:Template content is required");
	}
	$tpl = $report_->setTitle($_POST['title'])->setBodyPart($_POST['report_tpl'])->update();
	if($tpl !== null){
		exit("success:Template Updated");
	}
	exit("error:An error occurred while updating report template");
}
?>
<section style="width:1000px">
	<form method="post" name="newExamReportTplForm" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: __addTplStop})">
		<div class="row-fluid">
			<label class="span12">Title <input type="text" name="title" id="title" placeholder="Template Title" value="<?=$report->getTitle()?>"></label>

		</div>
		<label><textarea placeholder="Template Content..." id="_tpl_content" name="report_tpl"><?=$report->getBodyPart()?></textarea></label>

		<div class="clear"></div>
		<div class="btn-block">
			<button type="submit" class="btn">Update</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
		<input type="hidden" name="id" value="<?=$report->getId()?>">
	</form>

</section>
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
			Boxy.info(answer[1], function () {
				Boxy.get($('.close')).hideAndUnload();
			});
		}
	}
</script>
