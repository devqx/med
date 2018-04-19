<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/31/14
 * Time: 3:23 PM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Procedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureSpecialtyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureResourceTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$process = (new ProcedureDAO())->getProcedures();
$specialties = (new ProcedureSpecialtyDAO())->all();
$cats = (new ProcedureCategoryDAO())->all();
$_ = new Procedure();
$desc = $_::$desc;

$procResTypes = (new ProcedureResourceTypeDAO())->all();
if ($_POST) {
	if (isset($_POST['procedure'])) {
		$p = new Procedure();
		if (!empty($_POST['name'])) {
			$p->setName($_POST['name']);
		} else {
			exit("error:Name is required");
		}
		if (is_blank($_POST['category_id'])) {
			exit("error: Category is required");
		} else {
			$p->setCategory(new ProcedureCategory($_POST['category_id']));
		}
		
		if (is_blank($_POST['base_price'])) {
			exit("error: " . str_replace(":", "", $desc[0]) . " is required");
		} else {
			$p->setBasePrice(parseNumber($_POST['base_price']));
		}
		if (is_blank($_POST['theatre_price'])) {
			exit("error: " . str_replace(":", "", $desc[3]) . " is required");
		} else {
			$p->setPriceTheatre(parseNumber($_POST['theatre_price']));
		}
		
		if (is_blank($_POST['surgeon_price'])) {
			exit("error: " . str_replace(":", "", $desc[1]) . " is required");
		} else {
			$p->setPriceSurgeon(parseNumber($_POST['surgeon_price']));
		}
		
		if (is_blank($_POST['anaesthesia_price'])) {
			exit("error: " . str_replace(":", "", $desc[2]) . " is required");
		} else {
			$p->setPriceAnaesthesia(parseNumber($_POST['anaesthesia_price']));
		}
		
		if (is_blank($_POST['icd_code'])) {
			exit("error: ICD Code is required");
		} else {
			$p->setIcdCode($_POST['icd_code']);
		}
		if (is_blank($_POST['description'])) {
			exit("error: Description is required");
		} else {
			$p->setDescription($_POST['description']);
		}
		$newProc = (new ProcedureDAO())->addProcedure($p);
		if ($newProc !== null) {
			exit("success:Procedure " . $newProc->getName() . " added");
		}
	}
}
?>
<section>
	<div id="existingCenters"></div>
	<hr class="border">
	<div data-block="procedure">
		<h6><a href="javascript:" onclick="toggleMe('procs')">Available procedures</a></h6>
		<div class="three-column procs" style="display: none">
			<?php foreach ($process as $s) { ?>
				<div class="column tag"><?= $s->getName() ?>
					<span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?= $s->getId() ?>" class="editProcdLink">edit</a></span>
				</div>
			<?php } ?>
		</div>
	</div>
	<hr class="border">
	Procedure Resource Types <span class="pull-right"><a href="javascript:" id="newResourceType">New Resource Type</a></span>
	<div class="three-column resource_types" id="resource_types">
		<?php foreach ($procResTypes as $s) { ?>
			<div class="column tag"><?= $s->getName() ?>
				<span class="pull-right"><i class="icon-edit"></i><a href="javascript:" data-id="<?= $s->getId() ?>" class="editResTypeLink">edit</a></span>
			</div>
		<?php } ?>
	</div>
	
	<hr class="border">
	Procedure Specialties <span class="pull-right"><a href="javascript:" id="newProcSpecialty">New Procedure Specialty</a></span>
	<div class="three-column id_specialties" id="id_specialties">
		<?php foreach ($specialties as $s) { ?>
			<div class="column tag"><?= $s->getName() ?>
				<span class="pull-right"><i class="icon-edit"></i><a href="javascript:" data-id="<?= $s->getId() ?>" class="editSpecialty">Edit</a></span>
			</div>
		<?php } ?>
	</div>
	<hr class="border">
	
	<h6>Add New Procedure</h6>
	<form method="post" name="newProcedForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: addProcdStart, onComplete: addProcdStop})">
		<label>Name <input type="text" name="name"></label>
		<label>Category
			<span class="pull-right"><a href="javascript:;" id="catLink">Add</a></span>
			<select name="category_id" data-placeholder="select a category">
				<option></option>
				<?php foreach ($cats as $cat) { ?>
					<option value="<?= $cat->getId() ?>"><?= $cat->getName() ?></option><?php } ?>
			</select></label>
		<div class="row-fluid">
			<label class="span6">ICD10 PCS <input type="text" name="icd_code">
			</label>
			<label class="span6">Description <input type="text" name="description">
			</label>
		</div>

		<div class="row-fluid">
			<label class="span3"><?= $desc[3] ?>
				<input name="theatre_price" type="number" value="0" min="0"></label>
			<label class="span3"><?= $desc[1] ?>
				<input name="surgeon_price" type="number" value="0" min="0"></label>
			<label class="span3"><?= $desc[2] ?>
				<input name="anaesthesia_price" type="number" value="0" min="0"></label>
			<label class="span3"><?= $desc[0] ?>
				<input name="base_price" type="number" value="0" min="0"></label>
		</div>

		<div class="btn-block" style="margin-top: 10px;">
			<button type="submit" class="btn" name="procedure">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Close
			</button>
		</div>
	</form>

	<h6><a href="javascript:" onclick="toggleMe('temps')">CheckList Templates</a></h6>
	<div class="temps" style="display: none;">
		Templates List
		<span class="pull-right">Add</span>

	</div>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#existingCenters').load("/pages/pm/procedure-centres.php", function () {
			$('table.table').dataTable();
		});
		$('a.editProcdLink').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pm/procedure/edit_procedure.php?id=' + $(this).data('id'), {
					title: 'Edit Procedure',
					afterHide: reloadProceds
				});
				e.handled = true;
			}
		});
		
		$('a.editSpecialty').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/procedures/dialogs/config.specialty.edit.php?id=' + $(this).data('id'), {
					title: 'Edit Procedure Specialty',
					afterHide: populateSpecialties
				});
				e.handled = true;
			}
		});

		$('a#catLink').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pm/procedure/add_procedure_category.php', {
					title: 'New Procedure Category',
					afterHide: reloadProCats
				});
				e.handled = true;
			}
		});
		
		$('a#newResourceType').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/pm/procedure/add_resource_type.php', {
					title: 'New Procedure Resource'
					//afterHide: reloadProResourceTypes
				});
				e.handled = true;
			}
		});
		$('a#newProcSpecialty').live('click', function (e) {
			if(!e.handled){
				Boxy.load('/procedures/dialogs/config.specialty.new.php', {title:'New Procedure Specialty',afterHide: function () {
					populateSpecialties();
				}});
				e.handled = true;
			}
		});
		$('a.editResTypeLink').live('click', function (e) {
			var id = this.dataset.id;
			if (!e.handled) {
				Boxy.load('/pm/procedure/edit_resource_type.php?id='+id, {
					title: 'Edit Procedure Resource'
					//afterHide: reloadProResourceTypes
				});
				e.handled = true;
			}
		});
	});
	function addProcdStart() {
	}
	function addProcdStop(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else if (data[0] === "success") {
			//Boxy.info(data[1], function () {
				reloadProceds();
			//});
			$('form[name="newProcedForm"]').get(0).reset();
		}
	}
	
	function reloadProResourceTypes() {
		$.getJSON('/api/get_procedure_resource_types.php', function(response){
			//console.log(response);
			var html = [];
			_.each(response, function(resType){
				html.push('<div class="column tag">'+resType.name+'<span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="'+resType.id+'" class="editResTypeLink">edit</a></span> </div>');
			});
			$('#resource_types').html(html.join(''));
		});
	}

	var populateSpecialties = function () {
		var str = [];
		$.getJSON('/api/get_procedure_specialties.php', function (data) {
			_.each(data, function (obj) {
				str.push('<div class="column tag">'+obj.name+'<span class="pull-right"><i class="icon-edit"></i><a href="javascript:" data-id="'+obj.id+'" class="editSpecialty">Edit</a></span> </div>');
			});
			$('#id_specialties').html(str.join(''));
		})
	};

	function reloadProceds() {
		$.ajax({
			url: '/api/get_procedures.php',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, p) {
					html += '<div class="column tag">' + p.name + ' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="' + p.id + '" class="editProcdLink">edit</a></span></div>';
				});
				$('div.three-column.procs').html(html);
			},
			error: function () {
				alert("procedure error");
			}
		});
	}
	function reloadProCats() {
		$.ajax({
			url: '/api/get_procedure_categories.php',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, p) {
					html += '<option value="' + p.id + '">' + p.name + '</option>';
				});
				$('select[name="category_id"]').html(html);
			},
			error: function () {
				Boxy.alert("Error refreshing categories");
			}
		});
	}

	function toggleMe(ele) {
		$("div[class*='" + ele + "']").toggle('fast');
		$(".boxy-content").animate({scrollTop: '1000px'}, "slow");
	}
</script>