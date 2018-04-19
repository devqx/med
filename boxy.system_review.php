<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 4:09 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewCategoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewDAO.php";
$cats = (new SystemsReviewCategoryDAO())->allByType(null);
?>
<select id="view" placeholder="-- Select a system category --">
	<option></option>
	<?php foreach ($cats as $category) { ?>
		<option value="<?= $category->getId() ?>"><?= $category->getName() ?></option><?php } ?>
</select>
	<?php foreach ($cats as $category) { ?>
		<dl class="systems_review hide category<?= $category->getId() ?>">
			<dt><?= $category->getName() ?></dt>
			<?php foreach ((new SystemsReviewDAO())->byCat($category->getId()) as $sys) { ?>
				<dd><label><input name="system_review[]" data-category="cat<?= $category->getId() ?>" class="check_summary" data-text="<?= $sys->getName() ?>" value="<?= $sys->getId() ?>" type="checkbox"> <?= $sys->getName() ?>
					</label></dd>
			<?php } ?>
			<textarea name="system_review_summary[<?= $category->getId() ?>]" data-category="cat<?= $category->getId() ?>" rows="3" class="wide" placeholder="Category Summary"></textarea>
		</dl>
	<?php } ?>

<div class="clear"></div>

<script type="text/javascript">
	var texts = [];

	$(document).on('change', '#view', function (e) {
		var id = $(this).val();
		if (!e.handled) {
			$("dl.systems_review").removeClass("hide").addClass("hide");
			$("dl.systems_review.category" + id).removeClass("hide");
			e.handled = true;
		}
	}).on('change', '.check_summary[data-text]', function (e) {
		var summary_area = $(this).parents('dl').find('textarea');
		var categoryId = $(summary_area).data("category");
		if (!e.handled) {
			//texts[categoryId] = [];

			if($(this).data("category") === categoryId && $(this).is(":checked")){
				if(summary_area.val().trim() !== ''){
					summary_area.val( summary_area.val() + "\n" + $(this).data("text"));
				} else {
					summary_area.val($(this).data("text"));
				}
			}


			/*$('.check_summary[data-text]:checked').each(function(i, f){
				if($(f).data("category") === categoryId){
					texts[categoryId][texts[categoryId].length] = $(f).data("text");
				}
			});
			summary_area.val( texts[categoryId].join(", ") );*/
			e.handled = true;
		}
	});
</script>

