<?php
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugManufacturer.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
	$drug_manufacturer = new DrugManufacturer();
	
	if (!empty($_POST['drugmanufacturer'])) {
		$drug_manufacturer->setName($_POST['drugmanufacturer']);
		$ret = (new DrugManufacturerDAO())->addManufacturer($drug_manufacturer);
		
		if ($ret !== null) {
			exit("success:Manufacturer added successfully");
		}
	}
	exit("error:Can't Add Manufacturer");
}
?>

<div><span class="error"></span>
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" id="formDrugMan"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finish})">
		<label>Drug Manufacturer
			<input type="text" name="drugmanufacturer" required="required"></label>

		<div>
			<button class="btn" name="drugmanubtn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
	}
	function finish(s) {
		var s1 = s.split(":");
		if (s1[0] === "success") {
			$('span.error').html('<span class="alert alert-info">' + s1[1] + '</span>');
			$('#formDrugMan').get(0).reset();
		} else {
			if (s1[0] === "error") {
				$('span.error').html('<span class="alert alert-error">' + s1[1] + '</span>');
			}
		}
	}
</script>
