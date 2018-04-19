<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/3/17
 * Time: 12:58 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if (isset($_POST['name'])) {
	$cat = new ItemCategory($_POST['iid']);
	if (!empty($_POST['name'])) {
		$cat->setName($_POST['name']);
	} else {
		exit("error:Name is required");
	}


	$newCat = (new ItemCategoryDAO())->update($cat);

	if ($newCat !== null) {
		//            error_log(json_encode($_POST));
		exit("ok:Item " . $newCat->getName() . " updated successfully!");
	} else {
		exit("error:Failed to Update Category");
	}
} else {
	$cat_ = (new ItemCategoryDAO())->get($_GET['id']);
}
?>
<section>
	<div>
		<form method="post" name="editCategoryForm" action="<?= $_SERVER['REQUEST_URI'] ?>">
			<label>Name
				<input type="text" name="name" value="<?= $cat_->getName() ?>">
			</label>
			<div class="btn-block" style="margin-top: 10px;">
				<input type="hidden" name="iid" value="<?= $cat_->getId() ?>">
				<button type="button" class="btn" name="category"><i class="icon-save"></i>
					Save
				</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
					Close
				</button>
			</div>
		</form>
	</div>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('button[name="category"]').click(function () {
			$.ajax({
				url: "<?= $_SERVER['REQUEST_URI'] ?>",
				type: "post",
				data: $("form[name='editCategoryForm']").serialize(),
				success: function (d) {
					if (d.split(":")[0] === "ok") {
						Boxy.info("Category update successfully");
//						$("#notify").notify("create", {text: "Category updated successfully"}, {expires: 3000});
					} else {
						$("#notify").notify("create", {text: d.split(":")[1]}, {expires: 3000});
					}
					Boxy.get($(".close")).hideAndUnload();
				},
				error: function (d) {
					$("#notify").notify("create", {text: "Sorry action failed"}, {expires: 3000});
				}
			});
		});

	});
</script>