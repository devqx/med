<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/14
 * Time: 2:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
$cat = (new DrugCategoryDAO())->getCategory($_GET['id']);
if ($_POST) {
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugCategory.php';
	$category = new DrugCategory();
	if (!empty($_POST['drugcategory'])) {
		$category->setName($_POST['drugcategory']);
		$category->setId($_POST['cat_id']);
	} else {
		exit("error:Invalid Category name");
	}
	
	$new_category = (new DrugCategoryDAO())->updateCategory($category);
	if ($new_category !== null) {
		echo "success:Category edited";
	} else {
		echo "error:Cannot edit category";
	}
} ?>
<div><span class="error"></span>
	<form id="formDrugCategory" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : CategoryEdited})">
		<label for="drugcategory">Drug Category<input type="text" name="drugcategory" id="drugcategory" value="<?= $cat->getName() ?>"></label>
		<div><input type="hidden" value="<?= $cat->getId() ?>" name="cat_id">
			<button class="btn" name="drugcatbtn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function CategoryEdited(s) {
		var s1 = s.split(":");
		if (s1[0] === "success") {
			$('span.error').html('<span class="alert alert-info">' + s1[1] + '</span>');
			$("#formDrugCategory").get(0).reset();
			$('.close').click();
			hideAll(7);

		} else {
			if (s1[0] === "error") {
				$('span.error').html('<span class="alert alert-error">' + s1[1] + '</span>');
			}
		}
	}
</script>
