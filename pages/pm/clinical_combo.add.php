<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/27/17
 * Time: 11:13 AM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskCombo.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskComboData.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
//$options = getTypeOptions('type', 'vital_sign');
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$options = array_col($vitalTypes, 'name');
$options[] = "Others";

if ($_POST) {
	@session_start();
	if(!isset($_SESSION['staffID'])){exit('error:Please login again');}
	if (is_blank($_POST['combo_name'])){exit('error:Combo name is required');}
	if (!sameSizeArray(array_filter($_POST['type_id']), array_filter($_POST['frequency']), array_filter($_POST['interval']), array_filter($_POST['task_count']))) {
		exit('error:All inputs are required');
	}
	$cData = [];
	foreach ($_POST['type_id'] as $i=>$type){
		$cData[] = (new ClinicalTaskComboData())->setType($type)->setDescription($type != "Others" ? "" : $_POST['description'][$i])->setFrequency($_POST['frequency'][$i])->setInterval($_POST['interval'][$i])->setTaskCount($_POST['task_count'][$i]);
	}
	$combo = (new ClinicalTaskCombo())->setName($_POST['combo_name'])->setData($cData)->add();
	if($combo != null){
		exit('success:Combo saved');
	}
	exit('error:Failed to save combo');
}
?>
<section style="width: 550px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: sdskf_, onComplete: sdsfk_})">
		<label>Name/Description
			<input type="text" name="combo_name">
		</label>
		<label>Component Tasks <span class="pull-right"><button class="btn btn-mini" type="button" id="addComboData">Add</button></span> </label>

		<div class="data">
			<label>Type <select name="type_id[]" data-placeholder="-- Task Type --">
					<?php foreach ($options as $option) { ?>
						<option value="<?= $option ?>"><?= $option ?></option><?php } ?>
				</select> </label>
			<label class="others_task_container hide">Description
				<input type="text" name="description[]" placeholder="Describe Task here">
			</label>
			<div class="row-fluid">
				<label class="span3">Every <input type="number" data-decimals="0" name="frequency[]"> </label>
				<label class="span4 no-label">
					<select name="interval[]">
						<option value="1">minutes</option>
						<option value="60">hours</option>
						<option value="<?= 60 * 24 ?>">days</option>
						<option value="<?= 60 * 24 * 7 ?>">weeks</option>
						<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>">months</option><!-- // should we use 30 or 31  -->
					</select>
				</label>
				<label class="span3">For
					<input type="number" data-decimals="0" name="task_count[]">
				</label>
				<label class="span2 no-label">
					<input type="text" value="times" disabled style="background: transparent !important;border:none !important;box-shadow: none !important;">
				</label>
			</div>
		</div>

		<div style="margin-top:20px">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<template id="options">
	<div class="data">
		<div class="pull-right">
			<svg class="svgcloser" onclick="remove(this)" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 10 10" focusable="false">
				<polygon fill="#000000" points="10,1.01 8.99,0 5,3.99 1.01,0 0,1.01 3.99,5 0,8.99 1.01,10 5,6.01 8.99,10 10,8.99 6.01,5 "></polygon>
			</svg>
		</div>
		<label>Type <select name="type_id[]" data-placeholder="-- Task Type --">
				<?php foreach ($options as $option) { ?>
					<option value="<?= $option ?>"><?= $option ?></option><?php } ?>
			</select> </label>
		<label class="others_task_container hide">Description
			<input type="text" name="description[]" placeholder="Describe Task here">
		</label>
		<div class="row-fluid">
			<label class="span3">Every <input type="number" data-decimals="0" name="frequency[]"> </label>
			<label class="span4 no-label">
				<select name="interval[]">
					<option value="1">minutes</option>
					<option value="60">hours</option>
					<option value="<?= 60 * 24 ?>">days</option>
					<option value="<?= 60 * 24 * 7 ?>">weeks</option>
					<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>">months</option><!-- // should we use 30 or 31  -->
				</select>
			</label>
			<label class="span3">For
				<input type="number" data-decimals="0" name="task_count[]">
			</label>
			<label class="span2 no-label">
				<input type="text" value="times" disabled style="background: transparent !important;border:none !important;box-shadow: none !important;">
			</label>
		</div>
	</div>
</template>

<script type="text/javascript">
	$(document).ready(function () {
		$('#addComboData').click(function (e) {
			if (!e.handled) {
				$('form > .data:last').after($('#options').html());
				$('form > .data:last select').select2({width: '100%'});
				$('form > .data:last input[type="number"]').each(function () {
					$(this).attr('type', 'text').number(true, 2);
				});
				e.handled = true;
			}
		});
		
		$('select[name="type_id[]"]').live('change',function(e){
			var $target = $(e.currentTarget).parent('label').next().find('input');
			if(e.val==="Others"){
				$target.val('').parent().removeClass('hide');
			} else {
				$target.val('').parent().addClass('hide');
			}
		});
	});

	var remove = function (element) {
		$(element).parents('.data').remove();
	};

	var sdskf_ = function () {
		$(document).trigger('ajaxSend');
	};

	var sdsfk_ = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.warn(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload(function () {
				setTimeout(function () {
					Boxy.get($('.close')).hideAndUnload(function () {
						Boxy.load('/pages/pm/clinicalTasksCombos.php');
					});
				}, 4);
			});
		}
	};
</script>

<style type="text/css" style="display:none">
	form > .data {
		display: inline-block;
		background: #FEFEFE;
		border: 2px solid #FAFAFA;
		box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
		margin: 5px 0 !important;
		/*-webkit-column-break-inside: avoid;*/
		/*-moz-column-break-inside: avoid;*/
		/*column-break-inside: avoid;*/
		padding: 5px;
		background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
		opacity: 1;

		-webkit-transition: all .2s ease;
		-moz-transition: all .2s ease;
		-o-transition: all .2s ease;
		transition: all .2s ease;
	}

	.svgcloser {
		cursor: pointer;
		opacity: 0.5;
	}

	.svgcloser:hover {
		opacity: 1;
	}
</style>
