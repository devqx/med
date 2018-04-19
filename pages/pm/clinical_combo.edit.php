<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/27/17
 * Time: 5:51 PM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ClinicalTaskComboData.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
//$options = getTypeOptions('type', 'vital_sign');
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$options = array_col($vitalTypes, 'name');
$options[] = "Others";
$data = (new ClinicalTaskComboDAO())->get($_GET['id']);
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
	$combo = (new ClinicalTaskComboDAO())->get($_POST['id'])->setName($_POST['combo_name'])->setData($cData)->update();
	if($combo != null){
		exit('success:Combo updated');
	}
	exit('error:Failed to update combo');
}
?>
<section style="width: 500px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: e_sdskf_, onComplete: e_sdsfk_})">
		<label>Name/Description
			<input type="text" name="combo_name" value="<?= $data->getName() ?>">
		</label>
		<input type="hidden" name="id" value="<?=$_GET['id']?>">
		<label>Component Tasks <span class="pull-right"><button class="btn btn-mini" type="button" id="e_addComboData">Add</button></span> </label>

		<?php foreach ($data->getData() as $i=>$component) {//$component=new ClinicalTaskComboData();?>
			<div class="data">
				<?php if($i !== 0){?>
					<div class="pull-right">
						<svg class="svgcloser" data-id="<?= $component->getId()?>" onclick="e_remove(this)" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 10 10" focusable="false">
							<polygon fill="#000000" points="10,1.01 8.99,0 5,3.99 1.01,0 0,1.01 3.99,5 0,8.99 1.01,10 5,6.01 8.99,10 10,8.99 6.01,5 "></polygon>
						</svg>
					</div>
				<?php }?>
				<label>Type <select  name="type_id[]" data-placeholder="-- Task Type --">
						<?php foreach ($options as $option) { ?>
							<option value="<?= $option ?>" <?= $option == $component->getType() ? 'selected' : '' ?>><?= $option ?></option><?php } ?>
					</select> </label>
				<label class="others_task_container <?= $component->getType()!=='Others' ? 'hide':''?>">Description
					<input type="text" name="description[]" placeholder="Describe Task here" value="<?= $component->getDescription()?>">
				</label>
				<div class="row-fluid">
					<label class="span3">Every <input  type="number" data-decimals="0" name="frequency[]" value="<?= $component->getFrequency() ?>"> </label>
					<label class="span4 no-label">
						<select name="interval[]" >
							<option value="1"<?= $component->getInterval() == 1 ? ' selected' : '' ?>>minutes</option>
							<option value="60"<?= $component->getInterval() == 60 ? ' selected' : '' ?>>hours</option>
							<option value="<?= 60 * 24 ?>"<?= $component->getInterval() == (60 * 24) ? ' selected' : '' ?>>days</option>
							<option value="<?= 60 * 24 * 7 ?>"<?= $component->getInterval() == (60 * 24 * 7) ? ' selected' : '' ?>>weeks</option>
							<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>"<?= $component->getInterval() == intval(60 * 24 * 7 * 4.3453) ? 'selected' : '' ?>>months</option><!-- // should we use 30 or 31  -->
						</select>
					</label>
					<label class="span3">For
						<input type="number" data-decimals="0" name="task_count[]"  value="<?= $component->getTaskCount() ?>">
					</label>
					<label class="span2 no-label">
						<input type="text" value="times"  style="background: transparent !important;border:none !important;box-shadow: none !important;">
					</label>
				</div>
			</div>
		<?php } ?>
		<div style="margin-top:20px">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<template id="options">
	<div class="data">
		<div class="pull-right">
			<svg class="svgcloser" onclick="e_remove(this)" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 10 10" focusable="false">
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
				<input type="text" value="times" style="background: transparent !important;border:none !important;box-shadow: none !important;">
			</label>
		</div>
	</div>
</template>
<script type="text/javascript">
	$(document).ready(function () {
		$('#e_addComboData').click(function (e) {
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

	var e_remove = function (element) {
		if($(element).data('id') !== undefined){
			Boxy.ask('This action is undoable.<br>Are you sure to remove this component?', ['Yes', 'No'], function(answer){
				if(answer === 'Yes'){
					$.post('/api/remove_combo_data.php',{id: $(element).data('id')}, function (data) {
						if(data===true){
							$(element).parents('.data').remove();
						} else {
							Boxy.warn('Failed to remove component');
						}
					},'json');
				}
			});
		} else {
			$(element).parents('.data').remove();
		}
	};

	var e_sdskf_ = function () {
		$(document).trigger('ajaxSend');
	};

	var e_sdsfk_ = function (s) {
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
		margin: 10px 0 !important;
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
