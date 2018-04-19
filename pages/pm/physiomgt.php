<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
if (isset($_POST['testtype'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Physiotherapy.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyDAO.php';
	
	$consult = new Physiotherapy();
	if (!empty($_POST['testtype'])) {
		$consult->setName($_POST['testtype']);
	} else {
		exit("error:Test Name is required");
	}
	
	if (!empty($_POST['cost'])) {
		
	} else {
		exit("error:Base Price is required");
	}
	
	if (isset($_SESSION['staffID'])) {
		$consult->setHospital((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic());
	} else {
		exit("error:Sorry, You need to sign in again");
	}
	
	$newConsult = (new PhysiotherapyDAO())->add($consult, parseNumber($_POST['cost']));
	
	if ($newConsult !== null) {
		exit("ok");
	}
	exit("error:Error adding Physiotherapy object");
}
if (isset($_POST['itemtype'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysiotherapyItem.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if (is_blank($_POST['itemtype'])) {
		exit("error:Item Name is required");
	}
	if (is_blank($_POST['base_price'])) {
		exit("error:Item Base Price is required");
	}
	$item = new PhysiotherapyItem();
	$item->setName($_POST['itemtype']);
	$item->setBasePrice(parseNumber($_POST['base_price']));
	
	$newItem = (new PhysiotherapyItemDAO())->add($item, $_POST['cost']);
	
	if ($newItem !== null) {
		exit("ok");
	}
	exit("error:Error adding Physiotherapy Item");
}
?>
<script type="text/javascript">
	function startItem() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$('#output_item').html('<img src="/img/loading.gif"> Please wait');
	}
	function doneItem(s) {
		if (s.indexOf('ok') != -1) {
			$('#output_item').html('<span class="alert alert-info">Physiotherapy Item updated !</span>');
			setTimeout(function () {
				$('#output_item').html('');
				$('#fwdij_item').get(0).reset();
			}, 3000);

			$('#physio_items').load('/pages/pm/physio-items.php');
		} else {
			var dat = s.split(":"); //dat[0] will always be error then
			$('#output_item').html('<span class="alert alert-error">' + dat[1] + '</span>');
		}
	}
	function collapseAll() {
		$(".hide").hide();
	}
</script>
<div>
	<div id="existingLabCenters"></div>
	<hr>
	<h4><a href="javascript:" onclick="$('#physio_items').toggleClass('hide')">Physiotherapy
			Items</a></h4>
	<div class="hide" id="physio_items">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemDAO.php';
		$items = (new PhysiotherapyItemDAO())->all(); ?>
		<ul class="list-blocks">
			<?php foreach ($items as $item) { ?>
				<li class="tag"><?= $item->getName() ?>
					<span><a class="editItem" href="javascript:;" data-href="/pages/pm/physioItemEdit.php?id=<?= $item->getId() ?>">Edit Item</a> | <a class="manageBatch" data-href="/pages/pm/physioItemBatch.php?id=<?= $item->getId() ?>" href="javascript:">Manage Batches</a></span>
				</li>
			<?php } ?>
		</ul>
	</div>
	<hr>
	<div><h4><a href="javascript:" onclick="$('#fwdij_item').toggleClass('hide')">New
				Physiotherapy Item</a></h4></div>
	<form class="hide" action="/pages/pm/physiomgt.php" method="post" id="fwdij_item"
	      onSubmit="return AIM.submit(this, {'onStart': startItem, 'onComplete': doneItem})">
		<span id="output_item"></span>
		<div class="row-fluid">
			<label class="span12">Item Name/Description
				<input type="text" name="itemtype" id="itemtype"/></label>
		</div>
		<div class="row-fluid">
			<label class="span12">Base Price
				<input type="number" step="any" name="base_price"> </label>
		</div>

		<div class="row-fluid">
			<button name="btn2" type="submit" class="btn span12 pull-right">Add
				Physiotherapy Item
			</button>
		</div>
		<br><span id="msg_item"></span>
	</form>

</div>
<script>
	var newlyAdded = "";
	$(document).ready(function () {
		$('#existingLabCenters').load("/pages/pm/physio-centers.php", function () {
			$('table.table').dataTable();
		});
		$('#ophth_items').load("/pages/pm/physio-items.php", function () {
		});

		$('a.editItem').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Edit Physiotherapy Item'});
				e.handled = true;
			}
		});
		$('a.manageBatch').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Physiotherapy Item Batches'});
				e.handled = true;
			}
		});

	});
</script>