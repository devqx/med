<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 5:41 PM
 */
$package_id = $_GET['package_id'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PackageItem.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PackageDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PackageItemDAO.php";

//require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
//the different items to be covered [consultancy, lab, imaging, drugs, procedures]
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';

//$iic = new InsuranceItemsCostDAO;
$package = (new PackageDAO())->get($package_id, true);
$pItems = $package->getItems();
$pdo = (new MyDBConnector())->getPDO();

?>
<link rel="stylesheet" href="/style/insurance_items.css?i=<?= rand(9, 10000000) ?>">
<section style="width: 800px;">
	<h6>Available Items</h6>
	<div id="packageItems">
		<ul class="service_list_box">
			<li class="ui-bar-c menu-head">
				<h5>Labs
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/packages/boxy_addItem.php?show=LA&pid=<?= $package->getId() ?>', {title:'Edit Lab Items'})" title="Add a lab to this package">Add Lab services</a>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="-webkit-column-count: 3;-moz-column-count: 3;column-count: 3;">
					<?php foreach ($pItems as $item) {//$item = new PackageItem();
						if (substr($item->getItemCode(), 0, 2) === "LA") { ?>
							<li class="tag"><?= getItem($item->getItemCode(), $pdo)->getName() ?>
								<span class="pull-right"> (<em id="item_quantity_<?= $item->getId()?>"><?= $item->getQuantity() ?></em>) <a href="javascript:" data-name="editItem" data-id="<?= $item->getId() ?>" data-code="<?=$item->getItemCode()?>" data-quantity="<?=$item->getQuantity()?>"><i class='icon-edit'></i></a>
								<a href="javascript:" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a></span>
							</li>
						<?php }
					} ?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head">
				<h5>Consultation services
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/packages/boxy_addItem.php?show=CO&pid=<?= $package->getId() ?>', {title:'Edit Lab Items'})" title="Add consultation to this package">Add Consultation</a>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="-webkit-column-count: 3;-moz-column-count: 3;column-count: 3;">
					<?php foreach ($pItems as $item) {//$item = new PackageItem();
						if (substr($item->getItemCode(), 0, 2) === "CO") { ?>
							<li class="tag"><?= getItem($item->getItemCode(), $pdo)->getName() ?>
								<span class="pull-right">(<em id="item_quantity_<?= $item->getId()?>"><?= $item->getQuantity() ?></em>)
								<a href="javascript:" data-name="editItem" data-id="<?= $item->getId() ?>" data-code="<?=$item->getItemCode()?>" data-quantity="<?=$item->getQuantity()?>"><i class='icon-edit'></i></a>
								<a href="javascript:" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a>
									</span>
							</li>
						<?php }
					} ?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head">
				<h5>Radiology services
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/packages/boxy_addItem.php?show=SC&pid=<?= $package->getId() ?>', {title:'Edit Radiology'})" title="Add a scan to this package">Add Radiology services</a>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="-webkit-column-count: 3;-moz-column-count: 3;column-count: 3;">
					<?php foreach ($pItems as $item) {//$item = new PackageItem();
						if (substr($item->getItemCode(), 0, 2) === "SC") { ?>
							<li class="tag"><?= getItem($item->getItemCode(), $pdo)->getName() ?>
								<span class="pull-right">(<em id="item_quantity_<?= $item->getId()?>"><?= $item->getQuantity() ?></em>)
									<a href="javascript:" data-name="editItem" data-id="<?= $item->getId() ?>" data-code="<?=$item->getItemCode()?>" data-quantity="<?=$item->getQuantity()?>"><i class='icon-edit'></i></a>
								<a href="javascript:" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a></span>
							</li>
						<?php }
					} ?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head">
				<h5>Procedures
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/packages/boxy_addItem.php?show=PR&pid=<?= $package->getId() ?>', {title:'Edit Procedure Items'})" title="Add a procedure to this package">Add Procedure</a>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="-webkit-column-count: 3;-moz-column-count: 3;column-count: 3;">
					<?php foreach ($pItems as $item) {//$item = new PackageItem();
						if (substr($item->getItemCode(), 0, 2) === "PR") { ?>
							<li class="tag"><?= getItem($item->getItemCode(), $pdo)->getName() ?>
								<span class="pull-right"> (<em id="item_quantity_<?= $item->getId()?>"><?= $item->getQuantity() ?></em>)
									<a href="javascript:" data-name="editItem" data-id="<?= $item->getId() ?>" data-code="<?=$item->getItemCode()?>" data-quantity="<?=$item->getQuantity()?>"><i class='icon-edit'></i></a>
								<a href="javascript:" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a></span>
							</li>
						<?php }
					} ?>
				</ul>
			</li>
			<li class="ui-bar-c menu-head">
				<h5>Drugs
					<a class="pull-right" href="javascript:void(0)" onClick="Boxy.load('/pm/packages/boxy_addItem.php?show=DR&pid=<?= $package->getId() ?>', {title:'Edit Drug Items'})" title="Add a drug to this package">Add Drug</a>
				</h5></li>
			<li class="contained_items">
				<ul class="service_list_box" style="-webkit-column-count: 3;-moz-column-count: 3;column-count: 3;">
					<?php foreach ($pItems as $item) {//$item = new PackageItem();
						if (substr($item->getItemCode(), 0, 2) === "DR") { ?>
							<li class="tag"><?= getItem($item->getItemCode(), $pdo)->getName() ?>
								<span class="pull-right">(<em id="item_quantity_<?= $item->getId()?>"><?= $item->getQuantity() ?></em>)
									<a href="javascript:" data-name="editItem" data-id="<?= $item->getId() ?>" data-code="<?=$item->getItemCode()?>" data-quantity="<?=$item->getQuantity()?>"><i class='icon-edit'></i></a>
								<a href="javascript:" data-class="removeItem" data-id="<?= $item->getId() ?>"><i class="icon-remove-sign"></i></a></span>
							</li>
						<?php }
					} ?>
				</ul>
			</li>
		</ul>
	</div>
</section>
<script>
	$(document).ready(function () {
		$('a[data-name="editItem"]').click(function (e) {
			var itemId = $(this).data('id');
			var itemCode = $(this).data('code');
			var itemQty = $(this).data('quantity');
			var $this = $(this);
			vex.dialog.prompt({
				message: 'Please enter the New quantity',
				placeholder: 'Quantity',
				value: itemQty,
				overlayClosesOnClick: false,
				beforeClose: function (e) {
					e.preventDefault();
				},
				callback: function (value) {
					if (value !== false && value !== '') {
						//do the auth and proceed the action
						$.post('/api/update_package_quantity.php', {itemCode: itemCode, quantity: value, id: itemId, package_id: '<?=$package_id?>'}, function (response) {
							var data = response;
							if(data.status=='error'){
								Boxy.warn(data.message);
							} else if(data.status=='success'){
								Boxy.info(data.message);
								$('#item_quantity_'+itemId).html(value);
								$this.data('quantity', value);
							}
						}, 'json');
					}
				}, afterOpen: function ($vexContent) {
					$submit = $($vexContent).find('[type="submit"]');
					$submit.attr('disabled', true);
					$vexContent.find('input.vex-dialog-prompt-input').on('input', function () {
						if ($(this).val() != '') {
							$submit.removeAttr('disabled');
						} else {
							$submit.attr('disabled', true);
						}
					}).trigger('input');
				}
			});
		});
		$('a[data-class="removeItem"][data-id]').live('click', function (e) {
			var $this = $(this);
			if (e.handled !== true) {
				Boxy.ask('Are you sure to remove this item from the package?', ['Yes', 'No'], function (answer) {
					if (answer === "Yes") {
						$.post('/api/package_remove_item.php', {id: $this.data("id")}, function (s) {
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
	});
</script>