<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/Hx_Template_CategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/Hx_TemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$templateCategories = (new Hx_Template_CategoryDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/HxTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Hx_Template_Category.php';
	$new = new HxTemplate();
	if (!is_blank($_POST['name'])) {
		$new->setName($_POST['name']);
	} else {
		exit("error:Template Name is required");
	}
	
	if (!is_blank($_POST['hx_category_id'])) {
		$new->setCategory(new Hx_Template_Category($_POST['hx_category_id']));
	} else {
		exit("error:Category is required");
	}
	
	if (!is_blank($_POST['content'])) {
		$new->setContent($_POST['content']);
	} else {
		exit("error:Template Content Text is required");
	}
	
	$added = $new->add();
	if ($added !== null) {
		exit("success:Template saved");
	}
	exit("error:Save failed for new template");
}
?>
<section style="width: 850px">
	<p>Save a new history template <a href="javascript:;" data-href="template_help.php" class="pull-right" id="hx_template_link_child"><i class="icon-question-sign"></i> help</a></p>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete:done})">
		<label>Title <input type="text" name="name"> </label>
		<label>Category
			<span class="pull-right"><a href="javascript:;" class="newCategoryLink">add</a> </span>
			<select name="hx_category_id" id="hx_category_id" placeholder="Template Category/Specialty">
				<option></option>
				<?php foreach ($templateCategories as $t) { ?>
					<option value="<?= $t->getId() ?>"><?= $t->getName() ?></option>
				<?php } ?>
			</select></label>
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

	$('#hx_template_link_child').click(function () {
		Boxy.load("/consulting/" + $(this).data("href"));
	});

	$('.newCategoryLink').click(function () {
		Boxy.load('/consulting/hx_template_category_new.php');
	});

	function done(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else {
			refreshSHXTemplates();
			Boxy.get($(".close")).hideAndUnload();
		}
	}

	function refreshHxCats() {
		$.ajax({
			url: "/api/get_hx_template_categories.php",
			dataType: 'json',
			complete: function (s) {
				var data = s.responseJSON;
				var str = '<option></option>';
				for (var i = 0; i < data.length; i++) {
					str += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';
				}
				$('#hx_category_id').html(str);
			}
		});
	}
</script>