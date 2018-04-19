<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$services = (new DentistryDAO())->getServices();
$categories = (new DentistryCategoryDAO())->all();
if ($_POST) {
	$service = new Dentistry();
	if (!empty($_POST['name'])) {
		$service->setName($_POST['name']);
	} else {
		exit("error:Dentistry reference name is required");
	}
	//for the category, the browser makes sure it is selected
	$service->setCategory((new DentistryCategoryDAO())->get($_POST['category_id']));
	
	if (is_blank($_POST['base_price'])) {
		exit("error: Base Price is required");
	}
	
	$newDentistry = (new DentistryDAO())->add($service, parseNumber($_POST['base_price']));
	
	if ($newDentistry !== null) {
		exit("success:Dentistry Service - " . $newDentistry->getName() . " added");
	}
	exit("error:Update failed");
}

?>
<div>
	<h6>Available Services</h6>
	<div class="three-column dentistry">
		<?php foreach ($services as $s) { ?>
			<div class="column tag"><?= $s->getName() ?>
				<span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?= $s->getId() ?>" class="editDentistryLink">Edit</a></span>
			</div>
		<?php } ?>
	</div>
	<h6>Add New Dentistry</h6>
	<form method="post" name="newDentistryForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:addDentistryStart, onComplete: addDentistryStop})">
		<div class="row-fluid">
			<label class="span4">Name <input type="text" name="name"></label>
			<label class="span4">Category
				<a class="pull-right" onclick="addCategory()">New Category</a>
				<select id="category_id" name="category_id" required="required" placeholder="--- select category ---" style="width:100%;">
					<option></option>
					<?php foreach ($categories as $c) { ?>
						<option value="<?= $c->getId() ?>"><?= $c->getName() ?></option>
					<?php } ?>
				</select></label>
			<label class="span4">Base Price
				<input name="base_price" type="number" value="0" min="0"></label>
		</div>
		<div class="btn-block" style="margin-top: 10px;">
			<button type="submit" class="btn">Add</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Close
			</button>
		</div>
	</form>
</div>
<script>
	$(document).ready(function () {
		$("#category_id").select2();
		$('a.editDentistryLink').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pm/dentistry/edit_service.php?id=' + $(this).data('id'), {
					title: 'Edit Dentistry',
					afterHide: reloadServices
				});
				e.handled = true;
			}
		})
	});
	function reloadServices() {
		$.ajax({
			url: '/api/get_dentistry_services.php',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, dentistry) {
					html += '<div class="column tag">' + dentistry.name + ' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="' + dentistry.id + '" class="editDentistryLink">Edit</a></span></div>';
				});
				$('div.three-column.dentistry').html(html);
			},
			error: function () {

			}
		});
	}
	function reloadDentistryCategory() {
		$.ajax({
			url: '/api/dent_category.php',
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
	function addCategory() {
		Boxy.load('/pm/dentistry/add_dentistry_category.php', {
			title: 'New Dentistry Category',
			afterHide: reloadDentistryCategory
		})
	}
	function addDentistryStart() {
	}
	function addDentistryStop(s) {
		var answer = s.split(":");
		if (answer[0] === "error") {
			Boxy.alert(answer[1])
		} else {
			reloadServices();
			$('form[method="post"][name="newDentistryForm"]').get(0).reset();
			$("#category_id").select2('val', '', true);
			Boxy.info(answer[1]);
		}
	}
</script>