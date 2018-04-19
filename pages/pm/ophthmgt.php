<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
if (isset($_POST['testtype'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Ophthalmology.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	
	$lab = new Ophthalmology();
	if (!empty($_POST['testtype'])) {
		$lab->setName($_POST['testtype']);
	} else {
		exit("error:Test Name is required");
	}
	if (!empty($_POST['description'])) {
		$lab->setDescription($_POST['description']);
	} else {
		exit("error:Test description is required");
	}
	
	if (!empty($_POST['cost'])) {
		
	} else {
		exit("error:Test Base Price is required");
	}
	if (!empty($_POST['testCategory'])) {
		$lab->setCategory((new OphthalmologyCategoryDAO())->getCategory($_POST['testCategory']));
	} else {
		exit("error:Test Category is required");
	}
	if (!empty($_POST['labTemplate'])) {
		$lab->setTemplate(new OphthalmologyTemplate($_POST['labTemplate']));
	} else {
		exit("error:Ophthalmology Template is required");
	}
	if (isset($_SESSION['staffID'])) {
		$lab->setHospital((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic());
	} else {
		exit("error:Sorry, You need to sign in again");
	}
	
	$newLab = (new OphthalmologyDAO())->add($lab, parseNumber($_POST['cost']));
	
	if ($newLab !== null) {
		exit("ok");
	}
	exit("error:Error adding Ophthalmology object");
}
if (isset($_POST['itemtype'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyItem.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if (is_blank($_POST['itemtype'])) {
		exit("error:Item Name is required");
	}
	if (is_blank($_POST['base_price'])) {
		exit("error:Item Base Price is required");
	}
	$item = new OphthalmologyItem();
	$item->setName($_POST['itemtype']);
	$item->setBasePrice(parseNumber($_POST['base_price']));
	
	
	$newItem = (new OphthalmologyItemDAO())->add($item);
	
	if ($newItem !== null) {
		exit("ok");
	}
	exit("error:Error adding Ophthalmology Item");
}
?>
<script type="text/javascript">
	function start() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$('#output').html('<img src="/img/loading.gif"> Please wait');
	}
	function done(s) {
		if (s.indexOf('ok') !== -1) {
			setTimeout(function () {
				$('#output').html('<span class="alert alert-info">Ophthalmology updated !</span>');
			}, 2000);
			$('#existingLabs').load('/pages/pm/ophth_labs.php');
		} else {
			var dat = s.split(":"); //dat[0] will always be error then
			$('#output').html('<span class="alert alert-error">' + dat[1] + '</span>');
		}
	}
	function startItem() {
		$('#output_item').html('<img src="/img/loading.gif"> Please wait');
	}
	function doneItem(s) {
		if (s.indexOf('ok') !== -1) {
			setTimeout(function () {
				$('#output_item').html('<span class="alert alert-info">Ophthalmology Item updated !</span>');
			}, 2000);

			$('#ophth_items').load('/pages/pm/ophth-items.php');
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

	<div><h4>
			<a href="javascript:" onclick="$('#existingLabs').toggleClass('hide')">Available
				Ophthalmology Services</a></h4></div>
	<div class="hide" id="existingLabs">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
		$labs = (new OphthalmologyDAO())->all(); ?>
		<ul class="list-blocks">
			<?php foreach ($labs as $lab) { ?>
				<li class="tag"><?= $lab->getName() ?>
					<span><a class="editLab" href="javascript:;" data-href="/pages/pm/ophthedit.php?id=<?= $lab->getId() ?>">Edit</a> | <a class="editLabTemplate" href="javascript:;" data-href="/pages/pm/ophthtemplate-edit.php?id=<?= $lab->getTemplate()->getId() ?>">Edit Template</a></span>
				</li>
			<?php } ?>
		</ul>
	</div>
	<hr>
	<div><h4><a href="javascript:" onclick="$('#fwdij').toggleClass('hide')">New
				Ophthalmology Service</a></h4></div>

	<form class="hide" action="/pages/pm/ophthmgt.php" method="post" id="fwdij"
	      onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done})">
		<span id="output"></span>
		<div class="row-fluid">
			<label class="span6">Service Name
				<input type="text" name="testtype" id="testtype"/></label>
			<label class="span6">Base Price
				<input type="number" step="0.10" min="0" name="cost" id="cost"></label>
		</div>

		<div class="row-fluid">
			<label class="span6">Category
				<span class="pull-right">
                <a href="javascript:void(0)" data-url="/ophthalmology/boxy.addTestCategory.php" onclick="Boxy.load($(this).attr('data-url'), {title: 'Add Ophthalmology Test Category', afterHide: function () {
                                refreshCategories();
                            }})">Add Test Category</a></span>
				<input type="hidden" id="testCategory" name="testCategory"/>
			</label>
			<label class="span6">Template
				<span class="pull-right">
                <a href="javascript:void(0)" onclick="Boxy.load('/ophthalmology/addTemplate.php?label=' + $('#testtype').val(), {title: 'Add Ophthalmology Template', afterHide: function () {
                                refreshTemplates();
                            }})">Add Ophthalmology Template</a></span>
				<input type="hidden" id="labTemplate" name="labTemplate"/>
			</label>
		</div>
		<div class="row-fluid">
			<label class="span12">Description
				<textarea class="wide" rows="1" name="description"></textarea></label>
		</div>
		<div class="row-fluid">
			<button name="btn1" type="submit" class="span3 offset9 btn pull-right">Add
				Ophthalmology Test
			</button>
		</div>

		<br><span id="msg"></span>
		<div class="btn-block">

		</div>

	</form>
	<hr>
	<h4><a href="javascript:" onclick="$('#ophth_items').toggleClass('hide')">Ophthalmology
			Items (Frames, Lenses, etc)</a></h4>
	<div class="hide" id="ophth_items">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemDAO.php';
		$items = (new OphthalmologyItemDAO())->all(); ?>
		<ul class="list-blocks">
			<?php foreach ($items as $item) { ?>
				<li class="tag"><?= $item->getName() ?>
					<span><a class="editItem" href="javascript:;" data-href="/pages/pm/ophthItemEdit.php?id=<?= $item->getId() ?>">Edit Item</a></span>
				</li>
			<?php } ?>
		</ul>
	</div>
	<hr>
	<div><h4><a href="javascript:" onclick="$('#fwdij_item').toggleClass('hide')">New
				Ophthalmology Item</a></h4></div>
	<form class="hide" action="/pages/pm/ophthmgt.php" method="post" id="fwdij_item"
	      onSubmit="return AIM.submit(this, {'onStart': startItem, 'onComplete': doneItem})">
		<span id="output_item"></span>
		<div class="row-fluid">
			<label class="span8">Item Name/Description
				<input type="text" name="itemtype" id="itemtype"/></label>
			<label class="span4">Base Price
				<input type="number" step="any" name="base_price"> </label>
		</div>

		<div class="row-fluid">
			<button name="btn2" type="submit" class="btn span3 offset9 pull-right">Add
				Ophthalmology Item
			</button>
		</div>
		<br><span id="msg_item"></span>
	</form>

</div>
<script>
	var newlyAdded = "";
	var templateData = [];
	var categoryData = [];
	$(document).ready(function () {
		$('#existingLabCenters').load("/pages/pm/ophth-centers.php", function () {
			$('table.table').dataTable();
		});
		$('#ophth_items').load("/pages/pm/ophth-items.php", function () {
		});
		$('a.editLab').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Edit Ophthalmology'});
				e.handled = true;
			}
		});
		$('a.editItem').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Edit Ophthalmology Item'});
				e.handled = true;
			}
		});
		$('a.manageBatch').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Ophthalmology Item Batches'});
				e.handled = true;
			}
		});

		$('a.editLabTemplate').live('click', function (e) {
			if (e.handled != true) {
				Boxy.load($(this).data("href"), {title: 'Edit Ophthalmology Templates'});
				e.handled = true;
			}
		});

		$.ajax({
			url: '/api/ophth_category.php',
			dataType: 'json',
			success: function (a) {
				categoryData = a;
				$('#testCategory').select2({
					placeholder: "Select a Category",
					width: '100%',
					formatResult: function (data) {
						return data.name;
					},
					formatSelection: function (data) {
						return data.name;
					},
					data: function () {
						return {results: categoryData, text: 'name'};
					}
				});
			}
		});

		$.ajax({
			url: '/api/ophth_template.php',
			dataType: 'json',
			success: function (a) {
				templateData = a;
				$('#labTemplate').select2({
					placeholder: "Select a Template",
					width: '100%',
					formatResult: function (data) {
						return data.label;
					},
					formatSelection: function (data) {
						return data.label;
					},
					data: function () {
						return {results: templateData, text: 'label'};
					}
				});
			}
		});
	});

	function refreshTemplates() {
		$.ajax({
			url: '/api/ophth_template.php',
			dataType: 'json',
			success: function (a) {
				templateData = a;
				var selectedVal = "";
				for (var i = 0; i < a.length; i++) {
					if (a[i].label === newlyAdded) {
						selectedVal = a[i].id;
						break;
					}
				}
				Str = '';
				for (var i = 0; i < a.length; i++) {
					Str.concat('<option value="' + a[i].id + '">' + a[i].label + '</option>');
				}
				$('select[name="labTemplate"]').html(Str).select2("val", selectedVal, true);
				$('#labTemplate').select2("val", selectedVal, true);
			}
		});
	}
	function refreshCategories() {
		$.ajax({
			url: '/api/ophth_category.php',
			dataType: 'json',
			success: function (a) {
				categoryData = a;
				var selectedVal = "";
				for (var i = 0; i < a.length; i++) {
					if (a[i].name === newlyAdded) {
						selectedVal = a[i].id;
						break;
					}
				}
				var Str = '';
				for (var i = 0; i < a.length; i++) {
					Str.concat('<option value="' + a[i].id + '">' + a[i].name + '</option>');
				}
				$('#testCategory').select2("val", selectedVal, true);
				$('select[name="testCategory"]').html(Str).select2("val", selectedVal, true);
			}
		});
	}

</script>