<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/1/16
 * Time: 11:00 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/EyeReviewDAO.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BodyPartDAO.php';
$body = (new BodyPartDAO())->all(null);

$eye_rev = (new EyeReviewDAO())->getAll(null);
?>
<select id="view_body"  placeholder="-- Select body part --" >
	<option></option>
	<?php foreach ($body as $bd) { ?>
	  <option  value="<?= $bd->getName() ?>" ><?= $bd->getName() ?></option>
	<?php } ?>
</select>
<div class="clear"></div>

<dl>

<ul><?php foreach ($eye_rev as $eye){?><li><label>
		<input type="checkbox" value="" class="check_eye_summary" data-text="<?= $eye->getName() ?>">
			<?= $eye->getName() ?></label></li><?php } ?>
	</ul>
	<textarea name="eye_review_summary" data-category="" rows="3" class="wide"  placeholder="eye review Summary"></textarea>
</dl>

<style>
	dl ul li{
		width:12em;
		float:left;
	}
</style>

<script type="text/javascript">
	$(document).on('change', '#view_body', function (e) {
		var eye_summary_review = $('textarea[name="eye_review_summary"]');
		eye_summary_review.val($(this).val() +"\:");
		e.handled = true;
	});
	$(document).on('change', '.check_eye_summary[data-text]', function (e) {
		if($('#view_body').val()){
			if ($(this).is(':checked')) {
				var x = $(this).data('text');
				var eye_summary_review = $(this).parents('dl').find('textarea');
					eye_summary_review.val(eye_summary_review.val() + "\," + x);
			}
			}else {
			$(this).prop('checked', false).iCheck('update');
			alert("Select the body part");

		}

	e.handled = true;
	});
</script>