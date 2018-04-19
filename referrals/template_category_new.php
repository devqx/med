<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 9:22 AM
 */

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/RefererTemplateCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/RefererTemplateCategory.php';
	    require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
	$cat = new RefererTemplateCategory();
	if(!is_blank($_POST['category_name'])){
		$cat->setName($_POST['category_name']);
	} else {
		exit("error:Category name is required");
	}
	$add = (new RefererTemplateCategoryDAO())->add($cat);
	if ($add !== NULL){
		exit("success:Category added");
	}
	exit("error:Failed to add template category");
}
?>
<section>
	<form action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {onComplete:done_})">
		<label>Category Name: <input type="text" name="category_name"></label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function done_(s){
		var data = s.split(":");
		if(data[0]==="error"){
			Boxy.alert(data[1]);
		} else {
			refreshCats();
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>