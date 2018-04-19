<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 9:21 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/RefererTemplateCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/RefererTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
$templateCategories = (new RefererTemplateCategoryDAO())->getCategories();

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/RefererTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/RefererTemplateCategory.php';
	$new = new RefererTemplate();
	if(!is_blank($_POST['title'])){
		$new->setTitle($_POST['title']);
	} else {
		exit("error:Template Title is required");
	}

	if(!is_blank($_POST['category_id'])){
		$new->setCategory(new RefererTemplateCategory($_POST['category_id']));
	} else {
		exit("error:Category is required");
	}

	if(!is_blank($_POST['content'])){
		$new->setContent($_POST['content']);
	} else {
		exit("error:Template Content Text is required");
	}

	$added = (new RefererTemplateDAO())->add($new);
	if($added !== NULL){
		exit("success:Template saved");
	}
	exit("error:Save failed for new template");
}
?>
<section style="width: 850px">
	<p>Save a new referral template <a href="javascript:;" data-href="refer_template_help.php" class="pull-right" id="referral_template_link_child"><i class="icon-question-sign"></i> help</a> </p>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete:done})">
		<label>Title <input type="text" name="title"> </label>
		<label>Category
			<span class="pull-right"><a href="javascript:;" class="newCategoryLink">add</a> </span>
			<select name="category_id" id="category_id" placeholder="Template Category/Specialty">
				<option></option>
				<?php foreach($templateCategories as $t){?>
					<option value="<?=$t->getId()?>"><?=$t->getName()?></option>
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
	$(document).ready(function(){
		$('textarea[name="content"]' ).summernote(SUMMERNOTE_CONFIG);
	});

	$('#referral_template_link_child').click(function(){
		Boxy.load("referrals/"+$(this).data("href"));
	});

	$('.newCategoryLink').click(function () {
		Boxy.load('referrals/template_category_new.php');
	});

	function done(s){
		var data = s.split(":");
		if(data[0]==="error"){
			Boxy.alert(data[1]);
		} else {
			refreshTemplates();
			Boxy.get($(".close")).hideAndUnload();
		}
	}

	function refreshCats(){
		$.ajax({
			url:"/api/get_referral_template_category.php",
			dataType:'json',
			complete: function(s){
				var data = s.responseJSON;
				var str = '<option></option>';
				for(var i=0;i< data.length;i++){
					str += '<option value="'+data[i].id+'" >'+data[i].name+'</option>';
				}
				$('#category_id').html(str);
			}
		});
	}
</script>