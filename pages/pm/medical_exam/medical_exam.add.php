<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 4:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MedicalExam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MedicalExamDAO.php';
if($_POST){
	if(is_blank($_POST['name'])){ exit("error:Report title is required"); }
	if(is_blank($_POST['price'])){ exit("error:Base Price is required"); }

	$exam = (new MedicalExam())->setName($_POST['name'])->setBasePrice(parseNumber($_POST['price']))->setLabs($_POST['labs'])->setImagings($_POST['scans'])->setProcedures($_POST['procedures'])->add();
	if($exam != null){
		exit("success:Exam added!");
	}
	exit("error:Failed to add exam");
}
?>
<section style="width:500px">
	<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: exam_added})">
		<label>Examination/Report Title <input name="name" type="text" required="required"></label>
		<label>Base Price <input name="price" type="number" min="0" required="required" value="0"></label>
		<label>Lab tests </label>
		<label><input type="hidden" id="labs" name="labs" placeholder="Search and select lab"></label>
		<label>Imagings </label>
		<label><input type="hidden" id="scans" name="scans" placeholder="Search and select scan"></label>
		<label>Procedures </label>
		<label><input type="hidden" id="procedures" name="procedures" placeholder="Search and select procedure"></label>
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function exam_added(s) {
		var data = s.split(":");
		if (data[0]==="error"){
			Boxy.warn(data[1]);
		} else if(data[0]==="success") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}

	$(document).ready(function () {
		$('.boxy-content #labs').select2({
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
			}
		});
		$(".boxy-content #scans").select2({
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
			}
		});

		$('.boxy-content #procedures').select2({
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
			}
		});
	})
</script>
