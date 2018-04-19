<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/2/17
 * Time: 3:24 PM
 */
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormCategory.php';
	if (!is_blank($_POST['name'])) {
		$sFormCategory = (new SFormCategory())->setName($_POST['name'])->add();
		if ($sFormCategory) {
			exit('success:Category added');
		}
		exit('error:Failed to save category');
	}
	exit('error:Cant\'t perform action');
}
?>
<section style="width: 200px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: categoryHandler.start, onComplete:categoryHandler.complete})">
		<label>Category Name <input type="text" name="name"> </label>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var categoryHandler = {
		start: function () {
			$(document).trigger('ajaxSend');
		},
		complete: function (s) {
			$(document).trigger('ajaxStop');
			$.getJSON('/api/get_sform_category.php').then(function (data) {
				var str = '<option></option>';
				_.each(data, function(obj){
					str += '<option value="'+obj.id+'">'+obj.name+'</option>';
				});
				$('select[name="category_id"]').html(str);
				Boxy.get($('.close')).hideAndUnload();
			})
		}
	};
</script>
