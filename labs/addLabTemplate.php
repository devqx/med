<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabTemplateData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';


$methods = (new LabMethodDAO())->all();
if ($_POST) {
	if (is_blank($_POST['labTemplate'])) {
		exit("error:Lab Template is name required");
	}
	$data = [];

	if (count(array_filter($_POST['method_id'])) !== count(array_filter($_POST['reference']))) {
		exit("error:Lab Template data is incomplete");
	} else {
		foreach ($_POST['method_id'] as $i => $d) {
			if (!is_blank($_POST['method_id'][$i])) {
				$data[] = (new LabTemplateData())->setMethod((new LabMethod($_POST['method_id'][$i])))->setReference($_POST['reference'][$i]);
			}
		}
	}
	$temp = (new LabTemplate())->setLabel($_POST['labTemplate'])->setData($data)->add();
	if ($temp !== null) {
		exit("success:Saved");
	} else {
		exit("error:Oops! Something went wrong");
	}
}
?>
<div style="width: 600px;">
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<label>New Lab Template
			<input type="text" name="labTemplate" id='labTemplateLabel' placeholder="eg. Malaria Template" value="<?= $_GET['label'] ?>"/></label>
		<label>Template Fields
			<span class="pull-right">
			<a href="javascript:" id="addTemplateDataBtn" class="drop-btn"> <i class="icon-plus-sign"></i> </a>
		</span>
		</label>
		<table class="table" id="template_data_config">
			<thead>
			<tr>
				<th>Method <a href="javascript:" id="newLabMethod" class="pull-right normal-text">New Method</a></th>
				<th>Reference</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td width="45%"><label>
						<select name="method_id[]" data-placeholder="Select Lab Method">
							<option></option>
							<?php foreach ($methods as $method) { ?>
								<option value="<?= $method->getId() ?>"><?= $method->getName() ?></option><?php } ?>
						</select></label>
				</td>
				<td width="45%">
					<label><input title="Reference" type="text" name="reference[]" placeholder="eg. [3.6 - 5.5 mmol/l]"></label></td>
				<td>*</td>
			</tr>
			</tbody>
		</table>

		<div class="btn-block">
			<button type="submit" onclick="setAddedValue()" class="btn">Add</button>
			<button type="button" data-name="cancel" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel &raquo;</button>
		</div>
		<div id="mgniu"></div>
	</form>
</div>
<template id="tpData">
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
			<a href="javascript:" class="action removeTemplate"> <i class="icon-minus-sign"></i> </a>
		</td>
	</tr>
</template>

<script type="text/javascript">
	$(document).on('click', '#addTemplateDataBtn', function (e) {
		if (!e.handled) {
			$('#template_data_config tbody tr:last').after($('#tpData').html());
			$('#template_data_config tbody tr:last select[name="method_id[]"]').select2({width: '100%'});
			e.handled = true;
		}
	}).on('click', '.removeTemplate', function (e) {
		if (!e.handled) {
			$(e.target).parentsUntil('tbody').remove();
			e.handled = true;
		}
	}).on('click', '#newLabMethod', function (e) {
		if (!e.handled) {
			Boxy.load('/labs/addLabMethod.php');
			e.handled = true;
		}
	});

	function start() {
		$('#mgniu').html('<img src="/img/loading.gif"> Please wait');
	}

	function done(s) {
		if (s.split(":")[0] === 'success') {
			$('#mgniu').html('<span class="alert alert-info">Saved</span>');
			Boxy.get($(".close")).hideAndUnload();
		} else {
			var data = s.split(":");
			$('#mgniu').html('<span style="color:#C00;font-weight:bold;">' + data[1] + '</span>');
		}
	}
	
	function setAddedValue() {
		newlyAdded = $('#labTemplateLabel').val();
	}
</script>