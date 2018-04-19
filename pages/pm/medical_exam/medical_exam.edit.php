<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 4:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MedicalExam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MedicalExamDAO.php';
$exam = (new MedicalExamDAO())->get($_GET['id']);

$labs = $procedures = $imagings = [];
foreach ($exam->getLabs() as $lab) {
	$labs[] = $lab->getId();
}
unset($lab);
foreach ($exam->getProcedures() as $procedure) {
	$procedures[] = $procedure->getId();
}
unset($procedure);
foreach ($exam->getImagings() as $imaging) {
	$imagings[] = $imaging->getId();
}
unset($imaging);
if ($_POST) {
	if (is_blank($_POST['name'])) {
		exit("error:Report title is required");
	}
	if (is_blank($_POST['price'])) {
		exit("error:Report Price is required");
	}
	$exam_ = $exam->setName($_POST['name'])->setBasePrice(parseNumber($_POST['price']))->setLabs($_POST['labs'])->setImagings($_POST['scans'])->setProcedures($_POST['procedures'])->update();
	if ($exam_ != null) {
		exit("success:Exam updated!");
	}
	exit("error:Failed to update exam");
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: exam_added})">
		<label>Examination/Report Title <input name="name" type="text" required="required" value="<?= $exam->getName() ?>"></label>
		<label>Base Price
			<input name="price" type="number" min="0" required="required" value="<?= $exam->getBasePrice() ?>"></label>
		<label>Lab tests </label>
		<label><input type="hidden" id="labs_" name="labs" placeholder="Search and select lab" value="<?= implode(",", $labs) ?>"></label>
		<label>Imagings </label>
		<label><input type="hidden" id="scans_" name="scans" placeholder="Search and select scan" value="<?= implode(",", $imagings) ?>"></label>
		<label>Procedures </label>
		<label><input type="hidden" id="procedures_" name="procedures" placeholder="Search and select procedure" value="<?= implode(",", $procedures) ?>"></label>

		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function exam_added(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.warn(data[1]);
		} else if (data[0] === "success") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}

	$(document).ready(function () {
		$('.boxy-content #labs_').select2({
			placeholder: $(this).attr("placeholder"),
			minimumInputLength: 2,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_labs.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/get_labs.php?lab_ids=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		});
		$(".boxy-content #scans_").select2({
			placeholder: $(this).attr("placeholder"),
			minimumInputLength: 2,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_scans.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/get_scans.php?ids=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		});

		$('.boxy-content #procedures_').select2({
			placeholder: $(this).attr("placeholder"),
			minimumInputLength: 0,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_procedures.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/get_procedures.php?ids=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		});
	})
</script>