<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/29/16
 * Time: 2:33 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$activeGenericsOnly = true;
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugRequisition.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugRequisitionLine.php';

	$items = [];
	foreach ($_POST['drug_id'] as $index => $item) {
		$drug = (new Drug($_POST['drug_id'][$index]));
		$items[] = (new DrugRequisitionLine())->setDrug($drug)->setItemCode($_POST['item_code'][$index])->setQuantity(parseNumber($_POST['quantity'][$index]))->setBatchName($_POST['batch_name'][$index])->setExpiration($_POST['expiration_date'][$index]);
	}
	unset($item);
	
	$request = (new DrugRequisition())->setLastAction('Create')->setLastActionUser($staff)->setCreateUser($staff)->setStatus('Draft')->setItems($items)->add();

	if($request != null){
		exit("success:Requisition saved");
	}
	exit("error:Failed to save requisition");
}
?>
<section style="width: auto; margin-left: 0" >
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: add1called})">
		<div class="row-fluid">
			<label class="span8">Request By <select><option><?= $staff->getFullname()?></option></select></label>
			<label class="span4">Request Date<input type="text" name="create_date" value="<?= date(MainConfig::$dateTimeFormat) ?>"> </label>
		</div>
		<label class="menu-head">Request Items <span class="pull-right"><a class="btn btn-mini add_request_line">Add Item</a> </span> </label>
		<div class="row-fluid request_items">
			<label class="span3">Drug<input type="hidden" name="drug_id[]" required> </label>
			<label class="span2">Item Code <span class="fadedText">(if available)</span><input readonly type="text" name="item_code[]" required> </label>
			<label class="span2">Request Quantity<input type="number" name="quantity[]" required> </label>
			<label class="span2">Name this batch<input type="text" name="batch_name[]" required> </label>
			<label class="span2">Batch expiration date <input type="text" name="expiration_date[]" required> </label>
			<label class="span1" style="margin-top:25px"><a class="btn remove_request_line">&minus;</a> </label>
		</div>
		<div class="clear"></div>
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="reset(this)">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	var now;

	function setExpirationDate() {
		$('.request_items > label > input[name="expiration_date[]"]:last').datetimepicker({
			format: 'Y/m/d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					minDate: now
				});
			}
		});
	}

	function setDrugSelect() {
		$('.request_items > label > input[name="drug_id[]"]:last').select2({
			allowClear: true,
			placeholder: "---Select Drug---",
			width: '100%',
			minimumInputLength: 3,
			ajax: {
				url: "/api/get_drugs.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (source) {
				return source.name + ' ('+source.generic.weight+' '+source.generic.form+')';
			},
			formatSelection: function (source) {
				return source.name+ ' ('+source.generic.weight+' '+source.generic.form+')';
			}
		}).change(function (evt) {
			if(!evt.handled){
				if(evt.added != undefined){
					$(evt.target).parents('label').next('label.span2').find('input').val(evt.added.code);
				}
				evt.handled = true;
			}
		});
	}

	$(document).ready(function () {
		now = new Date().toISOString().split('T')[0];
		setExpirationDate();
		setDrugSelect();
	}).on('click', '.add_request_line', function (e) {
		if(!e.handled){
			var template = '<div class="row-fluid request_items"><label class="span3">Drug<input type="hidden" name="drug_id[]" required> </label><label class="span2">Item Code <span class="fadedText">(if available)</span><input readonly type="text" name="item_code[]" required> </label><label class="span2">Request Quantity<input type="number" name="quantity[]" required> </label><label class="span2">Name this batch<input type="text" name="batch_name[]" required> </label><label class="span2">Batch expiration date <input type="text" name="expiration_date[]" required> </label><label class="span1" style="margin-top:25px"><a class="btn remove_request_line">&minus;</a> </label></div>';
			$('.request_items:last').after(template);
			setExpirationDate();
			setDrugSelect();
			$(document).trigger('ajaxStop');
			e.handled = true;
		}
	}).on('click', '.remove_request_line', function (e) {
		if(!e.handled){
			if($('.request_items').length > 1){
				$(this).parents('.request_items').remove();
			}
			e.handled = true;
		}
	});

	function add1called(s) {
		var data = s.split(":");
		if(data[0]=="success"){
			Boxy.info(data[1]);
			$('form:last').get(0).reset();
			$('.request_items > label > input[name="drug_id[]"]').select2("val","");
			$('.mini-tab > a.tab:first').click();
		} else if(data[0]=="error"){
			Boxy.alert(data[1]);
		}
	}

	function reset(e) {
		$(e).parents('form').get(0).reset();
		$('.request_items > label > input[name="drug_id[]"]').select2("val","");
		$('.mini-tab > a.tab:first').click();
	}
</script>
