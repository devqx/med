<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/7/16
 * Time: 2:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureActionList.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureChecklistTemplateDAO.php';

$templates = (new ProcedureChecklistTemplateDAO)->all();
if($_POST){
	if(is_blank($_POST['description'])){
		exit("error:Task description is required");
	}
	$done = (isset($_POST['done'])) ? TRUE : FALSE;
	$pp = new PatientProcedure($_POST['patient_procedure_id']);

	$task = (new ProcedureActionList())->setDescription($_POST['description'])->setDone($done)->setPatientProcedure($pp)->add();

	if($task !== null){
		exit("success:Procedure Task added");
	}
	exit("error:Failed to add procedure task");
}
?>
<section style="width: 600px;">
	 <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="preprocedure_template_link" data-href="template_help.php">help</a>
					<i	class="icon-plus-sign"></i><a href="javascript:;" class="add_preprocedure_template_link" data-href="template_new_check_list.php">add to list</a></span>
	<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {'onStart':__start_, 'onComplete':__done_})">
		<label>Task Template <!--<span class="pull-right"><a href="javascript:;" id="new_ip_tpl">New Template</a></span>-->
			<select id="pre_pro_template_id" class="template_id" placeholder="Select Template">
				<option></option>
				<?php foreach($templates as $tpl => $t){?>
					<option value="<?= $t->getId() ?>" data-text="<?= $t->getContent()?>"><?= $t->getTitle()?></option><?php }?>
			</select> </label>
		<label>Task Description
			<textarea name="description" rows="2"></textarea>
		</label>

		<!--<label>
			<input type="checkbox" name="done" > Mark Already Done
		</label>-->
		<input type="hidden" name="patient_procedure_id" value="<?= $_GET['id']?>">

		<div class="clear" style="height: 40px;"></div>


		<div class="btn-block">
			<button type="submit" class="btn">Add</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		
		$('.boxy-content #pre_pro_template_id').select2().change(function(data){
			if(data.added != null){
				var content = $(data.added.element).data("text");
				$('textarea[name="description"]').code(content).focus();
			}else{
				$('textarea[name="description"]').code('').focus();
			}
		}).trigger('change');
	});

	$('.boxy-content a.preprocedure_template_link').click(function () {
		Boxy.load("/consulting/" + $(this).data("href"));
	});

	$('.boxy-content a.add_preprocedure_template_link').click(function () {
		Boxy.load("/admissions/dialogs/" + $(this).data("href"));
	});

	$('.boxy-content textarea[name="description"]').summernote(SUMMERNOTE_CONFIG);

	function __start_(){
		$(document).trigger('ajaxSend');
	}

	function __done_(s){
		$(document).trigger('ajaxStop');
		var data = s.split(":");
		if(data[0]==="error"){
			Boxy.alert(data[1]);
		}else if(data[0]==="success"){
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	}

</script>
