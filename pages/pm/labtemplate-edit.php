<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 8/21/15
 * Time: 11:00 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabTemplateData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDataDAO.php';

$methods = (new LabMethodDAO())->all();
$template = (new LabTemplateDAO())->getLabTemplate($_REQUEST['id']);
if ($_POST) {
	if (is_blank($_POST['labTemplate'])) {
		exit("error:Lab Template name is required");
	}
	$data = [];
	if(isset($_POST['method_id'])){
		if (count($_POST['method_id']) !== count($_POST['reference'])) {
			exit("error:Lab Template data is incomplete");
		} else {
			foreach (@$_POST['method_id'] as $i => $d) {
				if (!is_blank(@$_POST['method_id'][$i])) {
					$data[] = (new LabTemplateData())->setMethod((new LabMethod(@$_POST['method_id'][$i])))->setReference($_POST['reference'][$i]);
				}
			}
		}
	}
	
	if (count($_POST['method_id_']) !== count($_POST['reference_'])) {
		exit("error:Lab Template data is incomplete.");
	} else {
		foreach ($_POST['method_id_'] as $i => $d) {
			if (!is_blank($_POST['method_id_'][$i])) {
				$data[] = (new LabTemplateDataDAO())->getLabTemplateDatum($i)->setMethod((new LabMethod($_POST['method_id_'][$i])))->setReference($_POST['reference_'][$i]);
			}
		}
	}
	$temp = (new LabTemplateDAO())->getLabTemplate($_POST['id'])->setLabel($_POST['labTemplate'])->setData($data)->update();
	if ($temp !== null) {
		exit("success:Updated");
	} else {
		exit("error:Oops! Something went wrong");
	}
}
?>
<div style="width: 600px;">
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': _start, 'onComplete': _done});">
		<input type="hidden" name="id" value="<?= $template->getId() ?>">
		<label>Edit Lab Template
			<input type="text" name="labTemplate" id='labTemplateLabel' placeholder="eg. Malaria Template" value="<?= $template->getLabel() ?>"/></label>
		<label>Template Fields
			<span class="pull-right">
			<a href="javascript:" id="addTemplateDataBtn_" class="drop-btn"> <i class="icon-plus-sign"></i> </a>
		</span>
		</label>
		<table class="table" id="template_data_config_">
			<thead>
			<tr>
				<th>Method <a href="javascript:" id="newLabMethod" class="pull-right normal-text">New Method</a></th>
				<th>Reference</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($template->getData() as $datum){//$datum=new LabTemplateData();?>
			<tr>
				<td width="45%"><label>
						<select name="method_id_[<?=$datum->getId()?>]" data-placeholder="Select Lab Method">
							<?php foreach ($methods as $method) { ?>
								<option value="<?= $method->getId() ?>"<?= $method->getId()==$datum->getMethod()->getId() ? ' selected':'' ?>><?= $method->getName() ?></option><?php } ?>
						</select></label>
				</td>
				<td width="45%">
					<label><input title="Reference" type="text" name="reference_[<?=$datum->getId()?>]" value="<?= $datum->getReference() ?>" placeholder="eg. [3.6 - 5.5 mmol/l]"></label>
				</td>
				<td>
					<!--<a href="javascript:" class="action removeTemplate_" data-id="<?=$datum->getId() ?>"> <i class="icon-minus-sign"></i> </a>-->
				</td>
			</tr>
			<?php }?>
			</tbody>
		</table>

		<div class="btn-block">
			<button type="submit" class="btn">Update Template</button>
			<button type="button" data-name="cancel" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel &raquo;</button>
		</div>

	</form>
</div>

<template id="tpData_">
	<tr>
		<td width="45%"><label>
				<select name="method_id[]" data-placeholder="Select Lab Method">
					<?php foreach ($methods as $method) { ?>
						<option value="<?= $method->getId() ?>"><?= $method->getName() ?></option><?php } ?>
				</select></label>
		</td>
		<td width="45%">
			<label><input title="Reference" type="text" name="reference[]" placeholder="eg. [3.6 - 5.5 mmol/l]"></label></td>
		<td>
			<a href="javascript:" class="action removeTemplate_"> <i class="icon-minus-sign"></i> </a>
		</td>
	</tr>
</template>

<script>
	$(document).on('click', '#addTemplateDataBtn_', function (e) {
		if (!e.handled) {
			$('#template_data_config_ tbody tr:last').after($('#tpData_').html());
			$('#template_data_config_ tbody tr:last select[name="method_id[]"]').select2({width: '100%'});
			e.handled = true;
		}
	}).on('click', '.removeTemplate_', function (e) {
		var $this = this;
		if (!e.handled) {
			if($($this).parents('tbody').find('tr').length > 1){
				if($($this).data('id')){
					Boxy.ask('You\'re unbinding an already bound method. \nThis action is irreversible. Continue?', ['Yes', 'No'], function (answer) {
						if(answer==='Yes'){
							//alert($($this).data('id'));
							Boxy.alert('Sorry, unbind action failed');
							//$($this).parentsUntil('tbody').remove();
						}
					});
				} else {
					$($this).parentsUntil('tbody').remove();
				}
			}
			e.handled = true;
		}
	}).on('click', '#newLabMethod', function (e) {
		if (!e.handled) {
			Boxy.load('/labs/addLabMethod.php');
			e.handled = true;
		}
	});
	function _start() {
		$('.loading_place').html('Please wait...');
	}

	function _done(s) {
		var data = s.split(':');
		if (data[0]==='success') {
			Boxy.info("Changes have been saved", function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}	else {
			Boxy.alert('An error occurred, please try again');
		}
	}
</script>