<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBodySystemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$service = (new ServiceCenterDAO())->all();
$MainConfig = new MainConfig();
$cats = (new DrugCategoryDAO())->getCategories();
$systems = (new DrugBodySystemDAO())->getBodySystems();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
	$ge = new DrugGeneric();

	if (!empty($_POST['generic_name'])) {
		$ge->setName($_POST['generic_name']);
	} else {
		exit("error:Generic Name of drug is required");
	}
	if (!empty($_POST['b_systems'])) {
		$ge->setBodySystems($_POST['b_systems']);
	} else {
		$ge->setBodySystems([]);
	}

	if (!empty($_POST['categories'])) {
		$ge->setCategories($_POST['categories']);
	} else {
		exit("error:Category is required");
	}
	if (!empty($_POST['service_centre'])) {
		$ge->setServiceCentreId($_POST['service_centre']);
	} else {
		//exit("error:Business unit/service centre is required");
	}

	if (!empty($_POST['weight'])) {
		$ge->setWeight($_POST['weight']);
	} else {
		exit("error:Drug Weight is required");
	}

	if (!empty($_POST['_form'])) {
		$ge->setForm($_POST['_form']);
	} else {
		exit("error:Drug Form is reqired");
	}
	if (!is_numeric(parseNumber($_POST['low_level']))) {
		exit("error:Invalid low stock level");
	} else {
		$ge->setLowStockLevel(parseNumber($_POST['low_level']));
	}
	if (!empty($_POST['description'])) {
		$ge->setDescription($_POST['description']);
	} else {
		exit("error:Drug description is reqired");
	}


	$data = (new DrugGenericDAO())->addGeneric($ge);
	if ($data !== null) {
		exit("success:Generic Drug added");
	}
	exit("error:Cannot add generic drug");
}
?>
<div style="width: 600px;">
	<span class="error"></span>
	<form id="formGeneric" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : endGenericAdd})">
		<label>Generic Name <input type="text" name="generic_name" id="generic_name"></label>
		<label>Category <span class="pull-right"><a href="javascript:;" id="addCatLink">add category</a></span></label>
		<label><select name="categories[]" style="width: 100% !important;" multiple="multiple">
				<?php foreach ($cats as $cat) {
					echo '<option value="' . $cat->getId() . '">' . $cat->getName() . '</option>';
				} ?>
			</select></label>
		<label>Body Systems <span class="pull-right"><em>select all that apply</em></span></label>
		<label><select multiple="multiple" style="width: 100% !important;" name="b_systems[]">
				<?php foreach ($systems as $ds) {
					echo '<option value="' . $ds->getId() . '">' . $ds->getName() . '</option>';
				} ?>
			</select></label>
		<label>Weight <input type="text" name="weight" placeholder="example: 200mg or 100ml depending on form"></label>
		<label>Form
			<span class="pull-right"><em>presentation</em></span>
			<select name="_form">
				<?php foreach ($MainConfig::$drug_presentations as $form) {
					echo '<option value="' . $form . '">' . ucwords($form) . '</option>';
				} ?>
			</select>
		</label>
		<label>Low Stock Level <input name="low_level" type="number" min="1"></label>
		<label>Description <input type="text" name="description"></label>
		<?php
		   /*<label>Business unit/service centre <span class="pull-right"><em>select all that apply</em></span></label>
		    <label><select name="service_centre[]" style="width: 100% !important;" multiple="multiple">
				<?php foreach ($service as $serv) {
					if ($serv->getType() === "Pharmacy") {
						echo '<option value="' . $serv->getId() . '">' . $serv->getName() . '</option>';
					}
				} ?>
			</select></label> */
		?>
		<div>
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function endGenericAdd(s) {
		var s1 = s.split(":");
		if (s1[0] === "success") {
			$('span.error').html('<span class="alert alert-info">' + s1[1] + '</span>');
			$('#formGeneric').get(0).reset();
			//The select2 elements dont reset
			$('.close').click();
			hideAll(7);
		} else {
			if (s1[0] === "error") {
				$('span.error').html('<span class="alert alert-error">' + s1[1] + '</span>');
			}
		}
	}
	$("#addCatLink").on('click', function () {
		Boxy.load('/pages/pm/pharmacy/drugcat.php', {
			afterHide: function () {
				$.ajax({
					url: "/api/get_drug_categories.php",
					type: "POST",
					dataType: "json",
					success: function (d) {
						console.log(d)
						drugCats = d;
						refreshCat();
					}
				});
			}
		});
	});
	function refreshCat() {
		$("#category").select2({
			width: '100%',
			allowClear: true,
			placeholder: "select drug category",
			data: {results: drugCats, text: 'name'},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		});
	}
</script>
