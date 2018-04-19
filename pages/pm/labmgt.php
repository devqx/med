<?php
@session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
if (isset($_POST['testtype'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Lab.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	
	$lab = new Lab();
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
		$lab->setCategory((new LabCategoryDAO())->getLabCategory($_POST['testCategory']));
	} else {
		exit("error:Test Category is required");
	}
	if (!empty($_POST['labTemplate'])) {
		$lab->setLabTemplate(new LabTemplate($_POST['labTemplate']));
	} else {
		exit("error:Lab Template is required");
	}
	if (isset($_SESSION['staffID'])) {
		$lab->setHospital((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic());
	} else {
		exit("error:Sorry, You need to sign in again");
	}
	
	$newLab = (new LabDAO())->addLab($lab, parseNumber($_POST['cost']));
	
	if ($newLab !== null) {
		exit("ok");
	}
	exit("error:Error adding lab object");
} else if (isset($_POST['newDiagnosis'])) {
	//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.diagnoses.php';
	//echo (new Diagnoses())->addDiagnosis($_POST['newDiagnosis']);
	
	exit;
}
?>
<script type="text/javascript">
	function start7() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$('#output').html('<img src="/img/loading.gif"> Please wait');
	}

	function done7(s) {
		console.log(s);
		if (s.indexOf('ok') !== -1) {
			$('#output').html('<span class="alert alert-info">Lab updated !</span>');
			//refreshLabList();
			setTimeout(function () {
				Boxy.get($('.close')).hideAndUnload()
			}, 1500);
		} else {
			var dat = s.split(":"); //dat[0] will always be error then
			$('#output').html('<span class="alert alert-error">' + dat[1] + '</span>');
		}
	}

	function adding() {
		$('#statusDIAG').html('<img src="/img/loading.gif">');
	}

	function added(s) {
		if (s === 'ok') {
			$('input[name="newDiagnosis"]').val('');
			//todo: use ajax to refresh the added diagnoses
			$('#statusDIAG').html('<span class="alert alert-info">Saved</span>');
			setTimeout(function () {
				Boxy.get($('.close')).hideAndUnload();
			}, 1500);
		} else if (s.indexOf('error') !== -1) {
			s = s.split(":");
			$('input[name="newDiagnosis"]').css({'border-color': 'pink'});
			$('#statusDIAG').html('<span class="alert alert-error">' + s[1] + '</span>');
		}
	}

	function collapseAll() {
		$(".hide").hide();
	}
</script>
<div>
	<div id="existingLabCenters"></div>

	<div class="menu-head"><h4>Available Lab tests</h4></div>
	<div id="existingLabs">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
		$labs = (new LabDAO())->getLabs(); ?>
		<ul class="list-blocks">
			<?php foreach ($labs as $lab) {//$lab = new Lab();?>
				<li class="tag"><?= $lab->getName() ?>
					<span><a class="editLab" href="javascript:;" data-href="/pages/pm/labedit.php?id=<?= $lab->getId() ?>">edit test</a> | <a class="editLabTemplate" href="javascript:;" data-href="/pages/pm/labtemplate-edit.php?id=<?= $lab->getLabTemplate()->getId() ?>">edit template</a></span>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="menu-head"><h4 class="pull-left">Available Lab Groups</h4>
			<span class="pull-right"><a href="javascript:" id="add_combo" data-href="/pages/pm/lab-combo-new.php">Add a Combo</a></span>
		</div>
	<div id="labCombos_block">
		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabComboDAO.php';
		$combos = (new LabComboDAO())->all(); ?>
		<ul class="list-blocks">
			<?php foreach ($combos as $combo) {//$combo=new LabCombo();?>
				<?php $cData = [];
				foreach ($combo->getCombos() as $cDatum) {//$cDatum=new LabComboData();
					$cData[] = $cDatum->getLab()->getName();
				}
				?>
				<li class="tag" title="<?= implode(" &middot; ", $cData) ?>">
					<?= $combo->getName() ?>
					<span class="pull-right"><a href="javascript:" class="combo-edit" data-id="<?= $combo->getId() ?>">Edit</a></span>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="menu-head"><h4>Add a new Lab test</h4></div>
	<span id="output"></span>
	<form action="/pages/pm/labmgt.php" method="post" id="fwdij"
	      onsubmit="return AIM.submit(this, {'onStart': start7, 'onComplete': done7})">
		<div class="row-fluid">
			<label class="span6">Test Name
				<input type="text" name="testtype" id="testtype"/></label>
			<label class="span6">Base Price
				<input type="number" step="0.10" min="0" name="cost" id="cost"></label>
		</div>

		<div class="row-fluid">
			<label class="span6">Category
				<span class="pull-right">
                <a href="javascript:void(0)" data-url="/labs/boxy.addLabTestClasses.php" onclick="Boxy.load($(this).attr('data-url'), {title: 'Add Lab Test Category', afterHide: function () {refreshCategories();}})">Add Test Category</a></span>
				<input type="hidden" id="testCategory" name="testCategory"/>
			</label>
			<label class="span6">Template
				<span class="pull-right">
					<a href="javascript:void(0)" onclick="Boxy.load('/labs/addLabTemplate.php?label=' + $('#testtype').val(), {title: 'Add Lab Template', afterHide: function () {refreshTemplates();}})">Add Lab Template</a></span>
				<input type="hidden" id="labTemplate" name="labTemplate"/>
			</label>
		</div>
		<label>Description
			<textarea class="wide" rows="1" cols="6" name="description"></textarea></label>
		<label><span id="msg"></span></label>
		<div class="btn-block">
			<button name="btn1" type="submit" class="btn wide">Add Lab Test</button>
		</div>

	</form>
	<div class="menu-head"><h4 class="pull-left">Available Lab Templates</h4>
		<span class="pull-right"><a href="javascript:" id="add_template" data-href="/labs/addLabTemplate.php">Add a Template</a></span>
	</div>
	<div id="labTemplates_block">
		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
		$data = (new LabTemplateDAO())->getLabTemplates(FALSE);
		?>
		<ul class="list-blocks">
			<?php foreach ($data as $datum){//$datum=new LabTemplate();?>
				<li class="tag">
					<?= $datum->getLabel() ?>
					<span class="pull-right"><a href="javascript:" class="editLabTemplate" data-href="/pages/pm/labtemplate-edit.php?id=<?= $datum->getId() ?>" data-id="<?= $datum->getId() ?>">Edit</a></span>
				</li>
			<?php }?>
		</ul>
	</div>
	<div class="menu-head"><h4 class="pull-left">Available Lab Methods</h4>
		<span class="pull-right"><a href="javascript:" id="add_method" data-href="/labs/addLabMethod.php">Add a Lab Method</a></span>
	</div>
	<div id="labMethods_block">
		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabMethodDAO.php';
		$data = (new LabMethodDAO())->all();
		?>
		<ul class="list-blocks">
			<?php foreach ($data as $datum){//$datum=new LabMethod();?>
				<li class="tag">
					<?= $datum->getName() ?>
					<span class="pull-right"><a href="javascript:" class="method-edit" data-href="/labs/editLabMethod.php?id=<?= $datum->getId() ?>" data-id="<?= $datum->getId() ?>">Edit</a></span>
				</li>
			<?php }?>
		</ul>
	</div>
	<form method="post" id="diagnosesForm" action="/pages/pm/labmgt.php" onsubmit="return AIM.submit(this, {'onStart': adding, 'onComplete': added})">
		<h4 class="menu-head" style="">Diagnoses</h4>
		<span id="statusDIAG" style="display: inline"></span>
		<br>
		<div id="load_diags"><a href="javascript:;" id="load_it">Show Available
				Diagnoses</a></div>
		<div class="row-fluid">
			<label class="span11"><input type="text" name="newDiagnosis"/><span></span></label>
			<button type="submit" class="btn span1">Add</button>
		</div>
	</form>
</div>
<script>
	var newlyAdded = "";
	var templateData = [];
	var categoryData = [];
	$(document).ready(function () {
		$('#existingLabCenters').load("/pages/pm/lab-centers.php", function () {
			$('table.table').dataTable();
		});

		$('.combo-edit').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pages/pm/lab-combo-edit.php?id=' + $(this).data('id'));
				e.handled = true;
			}
		});

		$('#load_it').click(function () {
			$.ajax({
				url: '/api/get_diagnoses.php',
				success: function (a, b, c) {
					var data = JSON.parse(a);
					var str = '<ul class="list-blocks">';
					for (var i = 0; i < data.length; i++) {
						str += '<li class="tag">' + data[i].name + '</li>';
					}
					str += '</ul>';
					$('#load_diags').html(data.length + " diagnoses available" + str);
				}
			});
		});
		$('a.editLab').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: 'Edit Lab'});
				e.handled = true;
			}
		});
		$('#add_method').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: 'Add Lab Method'});
				e.handled = true;
			}
		});
		$('a.method-edit').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: 'Edit Lab Method'});
				e.handled = true;
			}
		});
		
		$('a.editLabTemplate').live('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: 'Edit Lab Templates'});
				e.handled = true;
			}
		});

		$('#add_combo').on('click', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data("href"), {title: 'Add Lab Combo'});
				e.handled = true;
			}
		});


		$.ajax({
			url: '/api/lab_category.php',
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

		refreshTemplates();
	});

	function refreshTemplates() {
		$('#labTemplate').select2({
			placeholder: "Search and select lab template",
			minimumInputLength: 0,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/lab_template.php",
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
			formatResult: function (data) {
				return data.label;
			},
			formatSelection: function (data) {
				return data.label;
			}
		});
	}

	function refreshCategories() {
		$.ajax({
			url: '/api/lab_category.php',
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
				$('#testCategory').select2("val", selectedVal, true);
			}
		});
	}

	function reloadLabCombos() {
		$.ajax({
			url: '/api/get_lab_combos.php?fetch',
			success: function (a, b, c) {
				var data = JSON.parse(a);
				var str = '<ul class="list-blocks">';
				for (var i = 0; i < data.length; i++) {
					var cData = [];
					for (var j = 0; j < data[i].combos.length; j++) {
						cData.push(data[i].combos[j].lab.name);
					}
					var title = implode(" &middot; ", cData);
					str += '<li class="tag" title="' + title + '">' + data[i].name + '<span class="pull-right"><a href="javascript:" class="combo-edit" data-id="' + data[i].id + '">Edit</a></span></li>';
				}
				str += '</ul>';
				$('#labCombos_block').html(str);
			}
		});
	}

</script>