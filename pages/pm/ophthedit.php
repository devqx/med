<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$lab = (new OphthalmologyDAO())->get($_REQUEST['id']);


if ($_POST) {
	if (!empty($_POST['testtype'])) {
		$lab->setName($_POST['testtype']);
	} else {
		exit("error:Ophthalmology Name is required");
	}
	
	if (!empty($_POST['description'])) {
		$lab->setDescription($_POST['description']);
	} else {
		exit("error:Ophthalmology description is required");
	}
	
	if (!empty($_POST['cost'])) {
	} else {
		exit("error:Ophthalmology Base Price is required");
	}
	if (!empty($_POST['testCategory'])) {
		$lab->setCategory((new OphthalmologyCategoryDAO())->getCategory($_POST['testCategory']));
	} else {
		exit("error:Ophthalmology Category is required");
	}
	if (!empty($_POST['labTemplate'])) {
		$lab->setTemplate(new OphthalmologyTemplate($_POST['labTemplate']));
	} else {
		exit("error:Ophthalmology Category is required");
	}
	if (isset($_SESSION['staffID'])) {
		$lab->setHospital((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic());
	} else {
		exit("error:Sorry, You need to sign in again");
	}
	$update = (new OphthalmologyDAO())->update($lab, parseNumber($_POST['cost']));
	exit(json_encode($update));
} ?>
<div style="width: 500px">
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
                <a href="javascript:void(0)" data-url="/ophthalmology/boxy.addTestCategory.php" onclick="Boxy.load($(this).attr('data-url'),{title:'Add Ophthalmology Category', afterHide: function(){refreshCategories();}})">Add
                    Category</a></span>
			<input type="hidden" id="testCategory2" name="testCategory" value="<?= $lab->getCategory()->getId() ?>"/>
			<!--<select name="testCategory">
                <?php
			//$data = (new OphthalmologyCategoryDAO())->all();
			
			//foreach($data as $category) {
			//    echo '<option value="' . $category->getId() . '"'.($category->getId()==$lab->getCategory()->getId()? ' selected':'').'>' . ucwords($category->getName()) . '</option>';
			//}
			?>
            </select>-->
		</label>

		<label>Template
			<span class="pull-right">
                <a href="javascript:void(0)" data-url="/ophthalmology/addTemplate.php" onclick="Boxy.load($(this).attr('data-url'),{title:'Add Ophthalmology Template', afterHide: function(){refreshTemplates();}})">Add
                    Ophthalmology Template</a></span>
			<input type="hidden" id="labTemplate2" name="labTemplate" value="<?= $lab->getTemplate()->getId() ?>">
			<!--<select name="labTemplate">
                <?php
			//$temps = (new OphthalmologyTemplateDAO())->getTemplates();
			//foreach($temps as $temp) {
			//    echo '<option value="' . $temp->getId() . '"'.($temp->getId()==$lab->getTemplate()->getId()? ' selected':'').'>' . ucwords($temp->getLabel()) . '</option>';
			//}
			?>
            </select>-->
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
				refreshOphthalmologyList();
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
	function refreshOphthalmologyList() {
		$('#existingLabs').load('/pages/pm/ophth_labs.php');
	}

	$(document).ready(function () {
		$.ajax({
			url: '/api/ophth_category.php',
			dataType: 'json',
			success: function (a) {
				categoryData = a;
				$('#testCategory2').select2({
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
					},
					initSelection: function (element, callback) {
						var id = $(element).val();
						if (id !== "") {
							$.ajax("/api/ophth_category.php?id=" + id, {
								dataType: "json"
							}).done(function (data) {
								callback(data);
							});
						}
					}
				});
			}
		});

		$.ajax({
			url: '/api/ophth_template.php',
			dataType: 'json',
			success: function (a) {
				templateData = a;
				$('#labTemplate2').select2({
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
					},
					initSelection: function (element, callback) {
						var id = $(element).val();
						if (id !== "") {
							$.ajax("/api/ophth_template.php?id=" + id, {
								dataType: "json"
							}).done(function (data) {
								callback(data);
							});
						}
					}
				});
			}
		});
	})
</script>