<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/22/15
 * Time: 3:03 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/History.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$typeOptions = getTypeOptions('datatype', 'history_template_data');

if ($_POST) {

	exit("error:Couldn't save data");
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {onComplete: submitted_new})">
		<div class="row-fluid">
			<label>Template Label <input type="text" name="t_label" value=""></label>
		</div>
		<div class="row-fluid">
			Data Elements <span class="pull-right add_line2">
				<a href="">New Data Line</a></span>
		</div>

		<div class="btn-block"></div>
		<button class="btn" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</form>

</section>
<script type="text/javascript">
	$newIdxs = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
	var chosen = -1;
	$(document).on('click', '.span1 > a[data-id]', function (e) {
		if (!e.handled) {
			// only delete those ones we have not saved [the ones with letter indices]
			if (!isNaN($(this).data("id") / 1)) {
				Boxy.alert("This template line might have been referenced in a patient data.");
			} else {
				// chosen = chosen == -1 ? -1: chosen - 1;
				$('.row-fluid[data-id="' + $(this).data('id') + '"]').remove();
			}
			e.handled = true;
		}
	});
	$(document).on('click', '.add_line2', function (e) {
		if (!e.handled) {
			++chosen;

			if (chosen in $newIdxs) {
				var $randIdx = $newIdxs[chosen];

				var sel = '<select name="tData_type[' + $randIdx + ']"><?php foreach ($typeOptions as $op) {?><option value="<?= $op?>"><?= ucwords($op)?></option><?php }?></select>';
				var containerDivParent = $('<div>');
				containerDivParent.attr('class', 'row-fluid');
				containerDivParent.attr("data-id", $randIdx);

				var span7 = $('<div>');
				span7.attr('class', 'span7');
				span7.append($('<label>').append($('<input type="text" name="tData_label[' + $randIdx + ']">')));

				var span4 = $('<div>');
				span4.attr('class', 'span4');
				span4.append($('<label>').append(sel));

				var span1 = $('<div>');
				span1.attr('class', 'span1');
				span1.append($('<a class="btn" href="javascript:;" data-id="' + $randIdx + '"><i class="fa fa-remove"></i></a>'));
				containerDivParent.append(span7);
				containerDivParent.append(span4);
				containerDivParent.append(span1);

				setTimeout(function () {
					$('select[name="tData_type[' + $randIdx + ']"]').select2({width: '100%'});
				}, 100);

				$('.row-fluid:last').after(
					containerDivParent
				);
			} else {
				alert("Oops! Options exhausted");
			}

			e.handled = true;
			e.preventDefault();
			return false
		}
	});

	var submitted_new = function (s) {
		if (s.split(":")[0] === "error") {
			Boxy.alert(s.split(":")[1]);
		} else if (s.split(":")[0] === "success") {
			Boxy.info(s.split(":")[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			})
		}
	}
</script>