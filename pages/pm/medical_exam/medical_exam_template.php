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
?>
<section style="width:1000px">
	<div class="row-fluid">
		<label class="span12">Title
			<input type="text" name="title" id="title" placeholder="Template Title" value="<?= $report->getTitle() ?>"></label>
	</div>
	<label><textarea readonly placeholder="Template Content..." id="_tpl_content" name="report_tpl"><?= $report->getBodyPart() ?></textarea></label>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#_tpl_content').summernote(SUMMERNOTE_CONFIG);
	});
</script>
