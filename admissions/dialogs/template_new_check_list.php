<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/4/16
 * Time: 1:23 PM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureChecklistTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureChecklistTemplate.php';

    $new = new ProcedureChecklistTemplate();

	if(!is_blank($_POST['title'])){
		$new->setTitle($_POST['title']);
	}else{
		exit("Error: Title is required");
	}
	if(!is_blank($_POST['content'])){
		$new->setContent($_POST['content']);
	}else{
		exit("Error: Content is required");
	}

	$added = (new ProcedureChecklistTemplateDAO())->add($new);
	if($added !== NULL){
		exit("Success: Template saved");
	}else{
		exit("Error: Fail to save the new template");
	}
}
?>

<section style="width: auto;">
	<p>Save a new Procedure check List template
		        <a href="javascript:;" data-href="template_help.php" class="pull-right" id="procedure_template_link_child"><i class="icon-question-sign"></i> help</a>
	</p>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete:done})">
		<label>Title
			<input name="title" id="title_id" type="text" placeholder="Type the template title here">
				</label>
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

//	$("#procedure_template_link_child").

	function done(s){
		var data = s.split(":");
		if(data[0]==="error"){
			Boxy.alert(data[1]);
		} else {
			refreshPreProcedureTemplates();
			Boxy.get($(".close")).hideAndUnload();
		}
	}


	$('.boxy-content a#procedure_template_link_child').click(function () {
		Boxy.load("consulting/" + $(this).data("href"));
	});


	function refreshPreProcedureTemplates() {
		$.ajax({
			url: "/api/get_procedure_checklist_templates.php",
			dataType: 'json',
			complete: function (s) {
				var data = s.responseJSON;
				// console.log(data);
				var str = '<option></option>';
				for (var i = 0; i < data.length; i++) {
					str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">'+ data[i].title + '</option>';
				}
				$('#pre_pro_template_id').html(str);
			}
		});
	}

</script>
