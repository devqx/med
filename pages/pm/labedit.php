<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$lab = (new LabDAO())->getLab($_REQUEST['id']);


if ($_POST) {
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
		exit("error:Lab Category is required");
	}
	if (isset($_SESSION['staffID'])) {
		$lab->setHospital((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic());
	} else {
		exit("error:Sorry, You need to sign in again");
	}
	$update = (new LabDAO())->updateLab($lab, parseNumber($_POST['cost']));
	exit(json_encode($update));
} ?>
<div>
	<div class="loading_place"></div>
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, {onStart: updating, onComplete: updated})">
		<input type="hidden" name="id" value="<?= $lab->getId() ?>">
		<label>Test Name
			<input type="text" name="testtype" id="testtype" value="<?= $lab->getName() ?>"/></label>
		<label>Base Price
			<input type="number" step="0.10" min="0" name="cost" id="cost" value="<?= (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($lab->getCode()) ?>"></label>
		<label>Description
			<textarea rows="2" cols="6" name="description"><?= $lab->getDescription() ?></textarea></label>
		<label>Category
			<span class="pull-right">
                <a href="javascript:void(0)" data-url="/labs/boxy.addLabTestClasses.php" onclick="Boxy.load($(this).attr('data-url'),{title:'Add Lab Test Category'})">Add
                    Test Category</a></span>
			<select name="testCategory">
				<?php
				$data = (new LabCategoryDAO())->getLabCategories();
				
				foreach ($data as $category) {
					echo '<option value="' . $category->getId() . '"' . ($category->getId() == $lab->getCategory()->getId() ? ' selected' : '') . '>' . ucwords($category->getName()) . '</option>';
				}
				?>
			</select>
		</label>

		<label>Template
			<span class="pull-right">
                <a href="javascript:void(0)" data-url="/labs/addLabTemplate.php" onclick="Boxy.load($(this).attr('data-url'),{title:'Add Lab Template'})">Add
                    Lab Template</a></span>
			<select name="labTemplate">
				<?php
				$temps = (new LabTemplateDAO())->getLabTemplates();
				foreach ($temps as $temp) {
					echo '<option value="' . $temp->getId() . '"' . ($temp->getId() == $lab->getLabTemplate()->getId() ? ' selected' : '') . '>' . ucwords($temp->getLabel()) . '</option>';
				}
				?>
			</select>
		</label>

		<div class="button-block">
			<button class="btn">Save Changes</button>
		</div>
	</form>

</div>

<script type="text/javascript">
	function updating() {
		$('.loading_place').html('Please wait...');
	}
	function updated(s) {
		try {
			var data = JSON.parse(s);
			if (data !== null) {
				refreshLabList();
				Boxy.info("Changes have been saved", function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			} else {
				Boxy.alert("Nothing was changed", function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			}
		} catch (exception) {
			console.error(s);
			var data = s.split(":");
			if (data[0] == "error") {
				Boxy.alert(data[1]);
			}
		}

	}
	function refreshLabList() {
		$.ajax({
			url: '/api/get_labs.php',
			success: function (a, b, c) {
				var data = JSON.parse(a);
				var str = '<ul class="list-blocks">';
				for (var i = 0; i < data.length; i++) {
					str += '<li class="tag">' + data[i].name + '<span><a class="editLab" href="javascript:;" data-href="/pages/pm/labedit.php?id=' + data[i].id + '">edit test</a> | <a class="editLabTemplate" href="javascript:;" data-href="/pages/pm/labtemplate-edit.php?id=' + data[i].labTemplate.id + '">edit template</a></span> </li>';
					//str += '<li class="tag"><a class="editLab" href="javascript:;" data-href="/pages/pm/labedit.php?id=' + data[i].id + '"><i class="icon-edit"></i></a> &nbsp;' + data[i].name + '</li>';
				}
				str += '</ul>';
				$('#existingLabs').html(str);
			}
		});
	}
</script>