<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$centres = (new ServiceCenterDAO())->all('Imaging');
$scans = (new ScanDAO())->getScans();
$cats = (new ScanCategoryDAO())->getCategories();
if ($_POST) {
	$scan = new Scan();
	if (!empty($_POST['name'])) {
		$scan->setName($_POST['name']);
	} else {
		exit("error:Scan reference name is required");
	}
	//for the category, the browser makes sure it is selected
	$scan->setCategory((new ScanCategoryDAO())->getCategory($_POST['category_id']));
	
	if (is_blank(parseNumber($_POST['base_price']))) {
		exit("error: Base Price is required");
	}
	$newScan = (new ScanDAO())->addScan($scan, parseNumber($_POST['base_price']));
	
	if ($newScan !== null) {
		exit("success:Scan - " . $newScan->getName() . " added");
	}
	exit("error:Update failed");
}
?>
<div>
	<h6 class="menu-head">Service Centres
		<span class="pull-right"><a class="scan_action_link" href="javascript:" data-href="imaging/add_centre.php" data-title="New Imaging Business/Service Centre" data-on-hide="reloadCentres()"> New Business Unit/Service Centre</a></span>
	</h6>
	<table class="table table-striped table-bordered" id="scanCentres">
		<thead>
		<tr>
			<th>Name</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($centres as $centre) { ?>
			<tr>
				<td><?= $centre->getName() ?></td>
				<td>
					<a href="javascript:" class="scan_action_link" data-href="imaging/edit_centre.php?id=<?= $centre->getId() ?>" data-title="Edit Business/Service Centre: <?= htmlentities($centre->getName()) ?>" data-on-hide="reloadCentres()">Edit</a>
				</td>
			</tr>
		<?php } ?>
	</table>
	<h6 class="menu-head">Available Scans</h6>
	<div class="three-column scans">
		<?php foreach ($scans as $s) { ?>
			<div class="column tag"><?= $s->getName() ?>
				<span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?= $s->getId() ?>" class="editScanLink">edit</a></span>
			</div>
		<?php } ?>
	</div>
	<h6 class="menu-head">Add New Scan</h6>
	<form method="post" name="newScanForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:addScanStart, onComplete: addScanStop})">
		<div class="row-fluid">
			<label class="span4">Name <input type="text" name="name"></label>
			<label class="span4">Category
				<a class="pull-right" onclick="addScanCategory()">New Category</a>
				<select id="category_id" name="category_id" required="required" placeholder="--- scan category ---" style="width:100%;">
					<option></option>
					<?php foreach ($cats as $c) { ?>
						<option value="<?= $c->getId() ?>"><?= $c->getName() ?></option>
					<?php } ?>
				</select></label>
			<label class="span4">Base Price
				<input name="base_price" type="number" value="0" min="0"></label>
		</div>
		<div class="btn-block" style="margin-top: 10px;">
			<button type="submit" class="btn"><i class="icon-save"></i>Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Close
			</button>
		</div>
	</form>
</div>
<script>
	$(document).ready(function () {
		$('#scanCentres').dataTable();
		$("#category_id").select2();
		$('a.editScanLink').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pm/imaging/edit_scan.php?id=' + $(this).data('id'), {
					title: 'Edit Scan',
					afterHide: reloadScans
				});
				e.handled = true;
			}
		});

		$('a.scan_action_link').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(e.target).data('href'), {
					title: $(e.target).data('title'), afterHide: function () {
						Function($(e.target).data('on-hide'))();
					}
				});
				e.handled = true;
			}
		})
	});
	function reloadScans() {
		$.ajax({
			url: '/api/get_scans.php',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, scan) {
					html += '<div class="column tag">' + scan.name + ' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="' + scan.id + '" class="editScanLink">edit</a></span></div>';
				});
				$('div.three-column.scans').html(html);
			},
			error: function () {

			}
		});
	}

	function reloadCentres() {
		$.get('<?= $_SERVER['REQUEST_URI'] ?>', function (data) {
			$('#scanCentres').html($(data).find('#scanCentres').html());
			//$('#scanCentres').html($(data).filter('#scanCentres').html()); // this does not work with `get`
		})
	}

	function reloadScanCategory() {
		$.ajax({
			url: '/api/get_scan_categories.php',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, cat) {
					html += '<option value="' + cat.id + '">' + cat.name + '</option>';
				});
				$('#category_id').html(html);
			},
			error: function () {

			}
		});
	}
	function addScanCategory() {
		Boxy.load('/pm/imaging/add_scan_category.php', {
			title: 'New Scan Category',
			afterHide: reloadScanCategory
		})
	}
	function addScanStart() {
	}
	function addScanStop(s) {
		var answer = s.split(":");
		if (answer[0] === "error") {
			Boxy.alert(answer[1])
		} else {
			reloadScans();
			$('form[method="post"][name="newScanForm"]').get(0).reset();
			$("#category_id").select2('val', '');
			Boxy.info(answer[1]);
		}
	}
</script>