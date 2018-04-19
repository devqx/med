<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 7:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PackageDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/StaffSpecializationDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/DrugDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/ProcedureDAO.php";

$package = (new PackageDAO())->get($_REQUEST['pid'], TRUE);
$show = explode(",", $_REQUEST['show']);
//todo get the items that are not included

$itemsCoveredAlready = [];
foreach ($package->getItems() as $item){
	//$item = new PackageItem();
	$itemsCoveredAlready[] = $item->getItemCode();
}

if($_POST){
	unset($package);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageItem.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$package = (new PackageDAO())->get($_POST['package_id'], FALSE, $pdo);
	
	$itemsSelected = $_POST['item'];
	foreach ($itemsSelected as $item=>$code){
		$x = (new PackageItem())->setItemCode($code)->setQuantity($_POST['quantity'][$code])->setPackage($package)->add($pdo);
		if($x == null){
			$pdo->rollBack();
			exit('error:Process failed;<br> maybe you are trying to add an item that is already in the package');
		}
	}
	
	$pdo->commit();
	exit('success:Services added to package successfully');
}
?>
<section style="width: 800px;">
	<div class="alert-box notice">You are editing Services for <?= $package->getName() ?></div>
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {onStart: k34239, onComplete: k893487})">
		<?php if (in_array("LA", $show)) {
			$labs = (new LabDAO())->getLabs(); ?>
			<table class="table table-striped table-hover" data-block="lab">
				<thead>
				<tr>
					<th><label><input type="checkbox" id="check_all" title="Check/Uncheck all"></label></th>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($labs as $item) { //$item=new Lab();
					if(!in_array($item->getCode(),$itemsCoveredAlready)){ ?>
					<tr>
						<td><input type="checkbox" value="<?= $item->getCode() ?>" id="item_<?= $item->getId() ?>" data-id="<?= $item->getId() ?>" name="item[<?= $item->getCode() ?>]"></td>
						<td><?= (++$key) ?></td>
						<td><label for="item_<?= $item->getId() ?>"><?= $item->getName() ?></label></td>
						<td><input type="number" min="1" disabled="disabled" value="1" data-id="<?= $item->getId() ?>" name="quantity[<?= $item->getCode() ?>]" title="Quantity" step="1"></td>
					</tr>
				<?php } }?>
			</table>
		<?php } ?>
		
		<?php if (in_array("PR", $show)) {
			$services = (new ProcedureDAO())->getProcedures(); ?>
			<table class="table table-striped table-hover" data-block="lab">
				<thead>
				<tr>
					<th><label><input type="checkbox" id="check_all" title="Check/Uncheck all"></label></th>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($services as $item) { //$item=new Lab();
				if(!in_array($item->getCode(),$itemsCoveredAlready)){ ?>
					<tr>
						<td><input type="checkbox" value="<?= $item->getCode() ?>" id="item_<?= $item->getId() ?>" data-id="<?= $item->getId() ?>" name="item[<?= $item->getCode() ?>]"></td>
						<td><?= (++$key) ?></td>
						<td><label for="item_<?= $item->getId() ?>"><?= $item->getName() ?></label></td>
						<td><input type="number" min="1" disabled="disabled" value="1" data-id="<?= $item->getId() ?>" name="quantity[<?= $item->getCode() ?>]" title="Quantity" step="1"></td>
					</tr>
				<?php } }?>
			</table>
		<?php } ?>

		<?php if (in_array("SC", $show)) {
			$services = (new ScanDAO())->getScans(); ?>
			<table class="table table-striped table-hover" data-block="lab">
				<thead>
				<tr>
					<th><label><input type="checkbox" id="check_all" title="Check/Uncheck all"></label></th>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($services as $item) { //$item=new Lab();
				if(!in_array($item->getCode(),$itemsCoveredAlready)){ ?>
					<tr>
						<td><input type="checkbox" value="<?= $item->getCode() ?>" id="item_<?= $item->getId() ?>" data-id="<?= $item->getId() ?>" name="item[<?= $item->getCode() ?>]"></td>
						<td><?= (++$key) ?></td>
						<td><label for="item_<?= $item->getId() ?>"><?= $item->getName() ?></label></td>
						<td><input type="number" min="1" disabled="disabled" value="1" data-id="<?= $item->getId() ?>" name="quantity[<?= $item->getCode() ?>]" title="Quantity" step="1"></td>
					</tr>
				<?php } }?>
			</table>
		<?php } ?>

		<?php if (in_array("DR", $show)) {
			$services = (new DrugDAO())->getDrugs(); ?>
			<table class="table table-striped table-hover" data-block="lab">
				<thead>
				<tr>
					<th><label><input type="checkbox" id="check_all" title="Check/Uncheck all"></label></th>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($services as $item) { //$item=new Lab();
				if(!in_array($item->getCode(),$itemsCoveredAlready)){ ?>
					<tr>
						<td><input type="checkbox" value="<?= $item->getCode() ?>" id="item_<?= $item->getId() ?>" data-id="<?= $item->getId() ?>" name="item[<?= $item->getCode() ?>]"></td>
						<td><?= (++$key) ?></td>
						<td><label for="item_<?= $item->getId() ?>"><?= $item->getName() ?></label></td>
						<td><input type="number" min="1" disabled="disabled" value="1" data-id="<?= $item->getId() ?>" name="quantity[<?= $item->getCode() ?>]" title="Quantity" step="1"></td>
					</tr>
				<?php } }?>
			</table>
		<?php } ?>
		
		<?php if (in_array("CO", $show)) {
			$services = (new StaffSpecializationDAO())->getSpecializations(); ?>
			<table class="table table-striped table-hover" data-block="lab">
				<thead>
				<tr>
					<th><label><input type="checkbox" id="check_all" title="Check/Uncheck all"></label></th>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Quantity</th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($services as $item) { //$item=new Lab();
				if(!in_array($item->getCode(),$itemsCoveredAlready)){ ?>
					<tr>
						<td><input type="checkbox" value="<?= $item->getCode() ?>" id="item_<?= $item->getId() ?>" data-id="<?= $item->getId() ?>" name="item[<?= $item->getCode() ?>]"></td>
						<td><?= (++$key) ?></td>
						<td><label for="item_<?= $item->getId() ?>"><?= $item->getName() ?></label></td>
						<td><input type="number" min="1" disabled="disabled" value="1" data-id="<?= $item->getId() ?>" name="quantity[<?= $item->getCode() ?>]" title="Quantity" step="1"></td>
					</tr>
				<?php } }?>
			</table>
		<?php } ?>

		<div>
			<input name="package_id" type="hidden" value="<?= $_GET['pid'] ?>">
			<button type="submit" class="btn">Save</button>
			<button type="reset" class="btn-link" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
</section>
<script>
	$('#check_all').live('change', function () {
		if ($(this).is(':checked')) {
			$("input[type='checkbox'][name^='item']").prop("checked", true).iCheck('update').trigger('change');
		} else {
			$("input[type='checkbox'][name^='item']").prop("checked", false).iCheck('update').trigger('change');
		}
	});
	
	$("input[type='checkbox'][name^='item']").live('change', function (e) {
		if (!e.handled) {
			var id = $(e.target).data('id');
			if($(e.target).is(':checked')){
				$('[name^="quantity"][data-id="'+id+'"]').prop('disabled', false);
			} else {
				$('[name^="quantity"][data-id="'+id+'"]').prop('disabled', true);
			}
			e.handled = true;
		}
	});
	
	var k34239 = function () {
		$(document).trigger('ajaxSend');
	};
	
	var k893487 = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0]=='error'){
			Boxy.warn(data[1]);
		} else if(data[0]=='success'){
			Boxy.get($('.close')).hideAndUnload();
			
		}
	}
</script>