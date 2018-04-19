<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/7/18
 * Time: 10:40 PM
 */

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/Hx_Template_CategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Hx_Template_Category.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
	$cat = new Hx_Template_Category();
	if(!is_blank($_POST['category_name'])){
		$cat->setName($_POST['category_name']);
	} else {
		exit("error:Category name is required");
	}
	$add = $cat->add();
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
			refreshHxCats();
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>