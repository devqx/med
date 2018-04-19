<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 7:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackagesDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackageItemsDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/StaffSpecializationDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/DrugDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/ProcedureDAO.php";

$package = (new AntenatalPackagesDAO())->get($_REQUEST['pid']);
$show = explode(",", $_REQUEST['show']);
if (isset($_POST['pid'])) {
	$items = array();
	
	//For Labs
	if (isset($_POST['lab_item'])) {
		foreach ($_POST['lab_item'] as $idCode) {
			if ($_POST['lab_per_usage_' . $idCode] > 0) {
				$itemCode = (new LabDAO())->getLab($idCode)->getCode();
				$item = (new AntenatalPackageItem())->setItemCode($itemCode)->setPackage(new AntenatalPackages($_POST['pid']))->setType('Lab')->setName($idCode)->setUsage($_POST['lab_per_usage_' . $idCode]);
				$items[] = $item;
			}
		}
	}
	
	//For Imagery
	if (isset($_POST['img_item'])) {
		foreach ($_POST['img_item'] as $idCode) {
			if ($_POST['img_per_usage_' . $idCode] > 0) {
				$itemCode = (new ScanDAO())->getScan($idCode)->getCode();
				$item = (new AntenatalPackageItem())->setItemCode($itemCode)->setPackage(new AntenatalPackages($_POST['pid']))->setType('Scan')->setName($idCode)->setUsage($_POST['img_per_usage_' . $idCode]);
				$items[] = $item;
			}
		}
	}
	
	//For Consultation
	if (isset($_POST['con_item'])) {
		foreach ($_POST['con_item'] as $idCode) {
			if ($_POST['con_per_usage_' . $idCode] > 0) {
				$itemCode = (new StaffSpecializationDAO())->get($idCode)->getCode();
				$item = (new AntenatalPackageItem())->setItemCode($itemCode)->setPackage(new AntenatalPackages($_POST['pid']))->setType('Consultation')->setName($idCode)->setUsage($_POST['con_per_usage_' . $idCode]);
				$items[] = $item;
			}
		}
	}
	
	//For Drugs
	if (isset($_POST['drug_item'])) {
		foreach ($_POST['drug_item'] as $idCode) {
			if ($_POST['drug_per_usage_' . $idCode] > 0) {
				$itemCode = (new DrugDAO())->getDrug($idCode)->getCode();
				$item = (new AntenatalPackageItem())->setItemCode($itemCode)->setPackage(new AntenatalPackages($_POST['pid']))->setType('Drug')->setName($idCode)->setUsage($_POST['drug_per_usage_' . $idCode]);
				$items[] = $item;
			}
		}
	}
	//For Procedures
	if (isset($_POST['proc_item'])) {
		foreach ($_POST['proc_item'] as $idCode) {
			if ($_POST['proc_per_usage_' . $idCode] > 0) {
				$itemCode = (new ProcedureDAO())->getProcedure($idCode)->getCode();
				$item = (new AntenatalPackageItem())->setItemCode($itemCode)->setPackage(new AntenatalPackages($_POST['pid']))->setType('Procedure')->setName($idCode)->setUsage($_POST['proc_per_usage_' . $idCode]);
				$items[] = $item;
			}
		}
	}
	
	if ((new AntenatalPackageItemsDAO())->addItems($items)) {
		$result = 'success:You have successfully added ' . sizeof($items) . " items to the current packages' list";
	} else {
		$result = 'error:Something went wrong';
	}
	exit(json_encode($result));
}
?>
<div style="width: 700px;">
	<?= $package->getName() ?>
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" id="addItemsForm">
		<?php if (in_array("LA", $show)) {
			$labs = (new LabDAO())->getLabs(); ?>
			<table class="table table-striped" data-block="lab">
				<thead>
				<tr>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Usage</th>
					<th><a href="javascript:void(0)" id="lab" title="Check/Uncheck all">All</a></th>
				</tr>
				</thead>
				<?php $key = 0;
				foreach ($labs as $l) { ?>
					<tr>
						<td><?= (++$key) ?></td>
						<td><label for="lab_item_<?= $l->getId() ?>"><?= $l->getName() ?></label></td>
						<td><input type="number" min="1" readonly="readonly" value="1" name="lab_per_usage_<?= $l->getId() ?>"></td>
						<td><input type="checkbox" value="<?= $l->getId() ?>" id="lab_item_<?= $l->getId() ?>" name="lab_item[]"
						           data-block="lab"></td>
					</tr>
				<?php } ?>

			</table>
		<?php } ?>
		
		<?php if (in_array("SC", $show)) { ?>
			<table class="table table-striped" data-block="img">
				<thead>
				<tr>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Usage</th>
					<th><a href="javascript:void(0)" id="img" title="Check/Uncheck all">All</a></th>
				</tr>
				</thead>
				<?php $key = 0;
				$scans = (new ScanDAO())->getScans();
				foreach ($scans as $scan) { ?>
					<tr>
						<td><?= (++$key) ?></td>
						<td><label for="img_item_<?= $scan->getId() ?>"><?= $scan->getName() ?></label></td>
						<td><input type="number" min="1" readonly="readonly" value="1" name="img_per_usage_<?= $scan->getId() ?>">
						</td>
						<td><input type="checkbox" value="<?= $scan->getId() ?>" id="img_item_<?= $scan->getId() ?>"
						           name="img_item[]" data-block="img"></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
		
		<?php if (in_array("CO", $show)) { ?>
			<table class="table table-striped" data-block="con">
				<thead>
				<tr>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Usage</th>
					<th><a href="javascript:void(0)" id="con" title="Check/Uncheck all">All</a></th>
				</tr>
				</thead>
				<?php $key = 0;
				$specialty = (new StaffSpecializationDAO())->getSpecializations();
				foreach ($specialty as $ss) { ?>
					<tr>
						<td><?= (++$key) ?></td>
						<td><label for="con_item_<?= $ss->getId() ?>"><?= $ss->getName() ?></label></td>
						<td><input type="number" min="1" readonly="readonly" value="1" name="con_per_usage_<?= $ss->getId() ?>">
						</td>
						<td><input type="checkbox" value="<?= $ss->getId() ?>" id="con_item_<?= $ss->getId() ?>" name="con_item[]"
						           data-block="con"></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
		
		<?php if (in_array("DR", $show)) { ?>
			<table class="table table-striped" data-block="drug">
				<thead>
				<tr>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Usage</th>
					<th><a href="javascript:void(0)" id="drug" title="Check/Uncheck all">All</a></th>
				</tr>
				</thead>
				<?php $key = 0;
				$drugs = (new DrugDAO())->getDrugs();
				foreach ($drugs as $drug) { ?>
					<tr>
						<td><?= (++$key) ?></td>
						<td><label for="drug_item_<?= $drug->getId() ?>"><?= $drug->getName() ?></label></td>
						<td><input type="number" min="1" readonly="readonly" value="1" name="drug_per_usage_<?= $drug->getId() ?>">
						</td>
						<td><input type="checkbox" value="<?= $drug->getId() ?>" id="drug_item_<?= $drug->getId() ?>"
						           name="drug_item[]" data-block="drug"></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
		
		<?php if (in_array("PR", $show)) { ?>
			<table class="table table-striped" data-block="proc">
				<thead>
				<tr>
					<th>SN</th>
					<th>Item/Service Name</th>
					<th>Usage</th>
					<th><a href="javascript:void(0)" id="proc" title="Check/Uncheck all">All</a></th>
				</tr>
				</thead>
				<?php $key = 0;
				$procs = (new ProcedureDAO())->getProcedures();
				foreach ($procs as $proc) { ?>
					<tr>
						<td><?= (++$key) ?></td>
						<td><label for="proc_item_<?= $proc->getId() ?>"><?= $proc->getName() ?></label></td>
						<td><input type="number" min="1" readonly="readonly" value="1" name="proc_per_usage_<?= $proc->getId() ?>">
						</td>
						<td><input type="checkbox" value="<?= $proc->getId() ?>" id="proc_item_<?= $proc->getId() ?>"
						           name="proc_item[]" data-block="proc"></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>

		<div>
			<input name="pid" type="hidden" value="<?= $_GET['pid'] ?>">
			<button name="items_save" id="saveItemsButton" type="button" class="btn">Save</button>
			<button name="cancel" type="reset" class="cancelBtn btn-link" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
</div>
<script>
	$(document).ready(function () {
		$("a[title='Check/Uncheck all']").click(function () {
			isAllChecked = true;
			block = $(this).prop('id');
			$.each($("input[type='checkbox'][id*='" + block + "']"), function (i, v) {
				if (!$(this).is(":checked")) {
					isAllChecked = false;
				}
			});

			if (isAllChecked) {
				$("input[type='checkbox'][id*='" + block + "']").prop("checked", false).iCheck('update');
				$("[name*='" + block + "_per_usage_']").prop("readonly", true).iCheck('update').val(1);
			} else {
				$("input[type='checkbox'][id*='" + block + "']").prop("checked", true).iCheck('update');
				$("[name*='" + block + "_per_usage_']").prop("readonly", false).iCheck('update');
			}
		});

		$("input[type='checkbox'][name*='_item[]']").change(function () {
			block = $(this).data("block");
			idd = $(this).prop("id").split("_")[2];

			if ($(this).is(":checked")) {
				$("[name='" + block + "_per_usage_" + idd + "']").prop("readonly", false);
			}
			else {
				$("[name='" + block + "_per_usage_" + idd + "']").prop("readonly", true).val(1);
			}
		});

		$("#saveItemsButton").click(function () {
			var $Form = $("#addItemsForm");
			$.ajax({
				url: $Form.prop("action"),
				type: 'post',
				data: $Form.serialize(),
				dataType: 'json',
				beforeSend: function () {
//                    alert(this.data)
				},
				success: function (d) {
					if (d.split(":")[0] === "success") {
						Boxy.get($(".close")).hideAndUnload();
						Boxy.info(d.split(":")[1], function () {
							//simulates a reload of the parent window
							Boxy.get($(".close")).hideAndUnload();
							Boxy.load('/pm/antenatal/package_items.php?package_id=<?=$_REQUEST['pid']?>');
						});

					} else {
						Boxy.alert(d.split(":")[1]);
					}
				}
			});
		});
	});
</script>