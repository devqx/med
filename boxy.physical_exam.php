<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 4:09 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PhysicalExaminationCategoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PhysicalExaminationDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BodyPartDAO.php";
$body = (new BodyPartDAO())->all();
$cats = (new PhysicalExaminationCategoryDAO())->all();
?>
<select id="exam_view" class="" data-placeholder="-- Select an examination category --">
	<option></option>
	<?php foreach ($cats as $category) { ?>
		<option value="<?= $category->getId() ?>"><?= $category->getName() ?></option>
	<?php } ?>
</select>

<select style="align-items: center; margin-top: 5px;" id="body_part_view" class="hide" data-placeholder="-- Select body part --" >
	<option></option>
	<option value="Eye">Eye </option>
</select>



<?php foreach ($cats as $category) { ?>
	<dl class="physical_examination hide category<?= $category->getId() ?>">
		<dt><?= $category->getName() ?></dt>
		<?php foreach ((new PhysicalExaminationDAO())->byCat($category->getId()) as $sys) { ?>
			<dd>
				<label><input name="physical_exam[]" class="phy_check_summary" data-text="<?= $sys->getName() ?>" data-category="<?= $category->getId() ?>" value="<?= $sys->getId() ?>" type="checkbox"> <?= $sys->getName() ?>
				</label>
			</dd>
		<?php } ?>
		<textarea name="physical_exam_summary[<?= $category->getId() ?>]" data-category="<?= $category->getId() ?>" rows="3" class="wide" placeholder="Category Summary"></textarea>
	</dl>
<?php } ?>

<div id="body_part_section" style="margin-top: 20px;" class="hidden">
	<?php require_once('boxy.eye_part.php') ?>
</div>

<div class="clear"></div>

<script type="text/javascript">
	$(document).on('change', "#body_part_view", function () {
		var  body = $(this).val();
		if (body === "Eye"){
			$('div[id="body_part_section"]').removeClass("hidden");
		}else{
			$('div[id="body_part_section"]').addClass("hidden");
		}
	});

	$(document).on('change', '#exam_view', function (e) {
		var id = $(this).val();
		if (!e.handled) {
			$("dl.physical_examination").removeClass("hide").addClass("hide");
			$("dl.physical_examination.category" + id).removeClass("hide");
			e.handled = true;
		}
	}).on('change', '.phy_check_summary[data-text]', function (e) {
		var summary_area = $(this).parents('dl').find('textarea');
		var categoryId = $(summary_area).data("category");
		if (!e.handled) {
			// texts[categoryId] = [];
			if($(this).data("category") === categoryId && $(this).is(":checked")){
				if(summary_area.val().trim() !== ''){
					summary_area.val( summary_area.val() + "\n" + $(this).data("text"));
				} else {
					summary_area.val($(this).data("text"));
				}
			}
			e.handled = true;
		}
	});

	function getAttr(el) {
		var id = $(el).data('rel-id');
		$('#exam_view').select2('val', id).trigger('change');
	}
</script>

