<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 5:41 PM
 */
$package_id = $_GET['package_id'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/AntenatalPackageItem.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackagesDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackageItemsDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';

$package = (new AntenatalPackagesDAO())->get($package_id);
$pItems = (new AntenatalPackageItemsDAO())->getItemsByPackage($package->getId());
if (isset($_POST['editUsage'])) {
	if (isset($_POST['usage']) && strlen(trim($_POST['usage'])) > 0) {
		$item_ = new AntenatalPackageItem();
		$item_->setId($_POST['itemid']);
		$item_->setUsage($_POST['usage']);
		if ((new AntenatalPackageItemsDAO)->updateItemUsages($item_)) {
			exit(json_encode("success:Changes committed successfully"));
		} else {
			exit(json_encode("error:Something went wrong. We are unable to save your changes"));
		}
	} else {
		exit(json_encode("error:Something went wrong. Make sure you entered the usage"));
	}
}
?>
<div id="addpackageitems" style="min-width: 800px; max-width:1000px">
	<h6>Available Items</h6>
	<div id="packageItems">
		<ul class="item_list_box" style="list-style-type: none; margin-left:0">
			<li class="ui-bar-c menu-head"><h5>Labs <a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/antenatal/boxy_addItem.php?show=LA&pid=<?= $package->getId() ?>', {title:'Edit Lab Items'})" title="Add a lab to this package"><i class='icon-plus-sign'></i></a></h5></li>
			<li>
				<ul class="item_list_box list-blocks" style="list-style-type: none;margin: 3px 0;">
					<?php $has = false;
					foreach ($pItems as $item) {
						if (strpos(strtoupper($item->getType()), "LA") !== false) {
							$has = true; ?>
							<li class='tag' style='display: inline-block;min-width: 110px;'>
								<?= (new LabDAO())->getLab($item->getName())->getName() ?> (<em id="la_u_<?= $item->getId() ?>"><?= $item->getUsage() ?></em>) <a href="javascript:void(0)" id="la_edit_<?= $item->getId() ?>" data-name="editItem"><i class='icon-edit'></i></a>
								<span style="display:none" id="la_edit_block_<?= $item->getId() ?>">
                                    <input type="number" data-decimals="0" value="<?= $item->getUsage() ?>" id="la_<?= $item->getId() ?>" class="inline-input">
                                    <span><button type="button" class="btn inline-btn" id="la_save_<?= $item->getId() ?>" data-usage="<?= $item->getUsage() ?>" data-name="usageSave">Ok</button></span>
                                </span>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head"><h5>Scans/Imagery <a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/antenatal/boxy_addItem.php?show=SC&pid=<?= $package->getId() ?>', {title:'Edit Imagery Items'})" title="Add imagery to this package"><i class='icon-plus-sign'></i></a>
				</h5></li>
			<li>
				<ul class="item_list_box list-blocks" style="list-style-type: none;margin: 3px 0;">
					<?php $has = false;
					foreach ($pItems as $item) {
						if (strpos(strtoupper($item->getType()), "SC") !== false) {
							$has = true; ?>
							<li class='tag' style='display: inline-block;min-width: 110px;'>
								<?= (new ScanDAO())->getScan($item->getName())->getName() ?> (<span id="img_u_<?= $item->getId() ?>"><?= $item->getUsage() ?></span>) <a href="javascript:void(0)" id="img_edit_<?= $item->getId() ?>" data-name="editItem"><i class='icon-edit'></i></a>
								<span style="display:none" id="img_edit_block_<?= $item->getId() ?>">
                                    <input type="number" data-decimals="0" value="<?= $item->getUsage() ?>" id="img_<?= $item->getId() ?>" class="inline-input">
                                    <span><button type="button" class="btn inline-btn" id="img_save_<?= $item->getId() ?>" data-usage="<?= $item->getUsage() ?>" data-name="usageSave">Ok</button></span>
                                </span>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head"><h5>Consultation
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/antenatal/boxy_addItem.php?show=CO&pid=<?= $package->getId() ?>', {title:'Edit Consultation Items'})" title="Add consultation to this package"><i class='icon-plus-sign'></i></a></h5></li>
			<li>
				<ul class="item_list_box list-blocks" style="list-style-type: none;margin: 3px 0;">
					<?php $has = false;
					foreach ($pItems as $item) {
						if (strpos(strtoupper($item->getType()), "CO") !== false) {
							$has = true; ?>
							<li class='tag' style='display: inline-block;min-width: 110px;'>
								<?= (new StaffSpecializationDAO())->get($item->getName())->getName() ?> (<span id="con_u_<?= $item->getId() ?>"><?= $item->getUsage() ?></span>) <a href="javascript:void(0)" id="con_edit_<?= $item->getId() ?>" data-name="editItem"><i class='icon-edit'></i></a>
								<span style="display:none" id="con_edit_block_<?= $item->getId() ?>">
                                    <input type="number" data-decimals="0" value="<?= $item->getUsage() ?>" id="con_<?= $item->getId() ?>" class="inline-input">
                                    <span><button type="button" class="btn inline-btn" id="con_save_<?= $item->getId() ?>" data-usage="<?= $item->getUsage() ?>" data-name="usageSave">Ok</button></span>
                                </span>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head"><h5>Drugs <a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/antenatal/boxy_addItem.php?show=DR&pid=<?= $package->getId() ?>', {title:'Edit Drug Items'})" title="Add drug to this package"><i class='icon-plus-sign'></i></a></h5></li>
			<li>
				<ul class="item_list_box list-blocks" style="list-style-type: none;margin: 3px 0;">
					<?php $has = false;
					foreach ($pItems as $item) {
						if (strpos(strtoupper($item->getType()), "DR") !== false) {
							$has = true; ?>
							<li class='tag' style='display: inline-block;min-width: 110px;'>
								<?= (new DrugDAO())->getDrug($item->getName())->getName() ?> (<span id="drug_u_<?= $item->getId() ?>"><?= $item->getUsage() ?></span>) <a href="javascript:void(0)" id="drug_edit_<?= $item->getId() ?>" data-name="editItem"><i class='icon-edit'></i></a>
								<span style="display:none" id="drug_edit_block_<?= $item->getId() ?>">
                                    <input type="number" data-decimals="0" value="<?= $item->getUsage() ?>" id="drug_<?= $item->getId() ?>" class="inline-input">
                                    <span><button type="button" class="btn inline-btn" id="drug_save_<?= $item->getId() ?>" data-usage="<?= $item->getUsage() ?>" data-name="usageSave">Ok</button></span>
                                </span>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head"><h5>Procedures
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/antenatal/boxy_addItem.php?show=PR&pid=<?= $package->getId() ?>', {title:'Edit Procedure Items'})" title="Add Procedure to this package"><i class='icon-plus-sign'></i></a></h5></li>
			<li>
				<ul class="item_list_box list-blocks" style="list-style-type: none;margin: 3px 0;">
					<?php $has = false;
					foreach ($pItems as $item) {
						if (strpos(strtoupper($item->getType()), "PR") !== false) {
							$has = true; ?>
							<li class='tag' style='display: inline-block;min-width: 110px;'>
								<?= (new ProcedureDAO())->getProcedure($item->getName())->getName() ?> (<span id="drug_u_<?= $item->getId() ?>"><?= $item->getUsage() ?></span>) <a href="javascript:void(0)" id="drug_edit_<?= $item->getId() ?>" data-name="editItem"><i class='icon-edit'></i></a>
								<span style="display:none" id="drug_edit_block_<?= $item->getId() ?>">
                                    <input type="number" data-decimals="0" value="<?= $item->getUsage() ?>" id="drug_<?= $item->getId() ?>" class="inline-input">
                                    <span><button type="button" class="btn inline-btn" id="drug_save_<?= $item->getId() ?>" data-usage="<?= $item->getUsage() ?>" data-name="usageSave">Ok</button></span>
                                </span>
								<a href="javascript:;" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</li>
		</ul>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('a[data-name="editItem"]').click(function () {
			block = $(this).prop("id").split("_")[0];
			idd = $(this).prop("id").split("_")[2];
			$("#" + block + "_edit_block_" + idd).toggle("fast");
		});
		$('a[data-class="removeItem"][data-id]').live('click', function (e) {
			var $this = $(this);
			item = $this;
			if (e.handled !== true) {
				Boxy.ask('Are you sure to remove this item from the antenatal package?', ['Yes', 'No'], function (answer) {
					if (answer === "Yes") {
						$.post('/api/antenatal_package_remove_item.php', {id: $this.data("id")}, function (s) {
							var data = s.split(":");
							if (data[0] === "error") {
								Boxy.alert(data[1]);
							} else {
								$this.parent().remove();
							}
						});
					}
				});
				e.handled = true;
			}
		});
		$('button[data-name="usageSave"]').click(function () {
			block = $(this).prop("id").split("_")[0];
			idd = $(this).prop("id").split("_")[2];
			Boxy.confirm("You are about to modify the usage for an item. <br>Click 'Ok' to proceed otherwise click 'Cancel'", function () {
				$.ajax({
					url: '/pm/antenatal/package_items.php?package_id=<?= $package_id ?>',
					type: 'POST',
					data: "editUsage=true&itemid=" + idd + "&usage=" + $("#" + block + "_" + idd).val(),
					dataType: 'json',
					beforeSend: function () {
					},
					success: function (d) {
						if (d.indexOf("success") !== -1) {
							$("#" + block + "_u_" + idd).html($("#" + block + "_" + idd).val());
							$("#" + block + "_edit_block_" + idd).toggle("fast");
							Boxy.info(d.split(":")[1]);
						} else {
							Boxy.alert(d.split(":")[1]);
						}
					},
					error: function (d) {
						Boxy.alert("Sorry, something went wrong!!!");
					}
				}, {title: 'Item Usage Update'});
			});
		});
	});
</script>