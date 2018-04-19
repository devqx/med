<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/22/18
 * Time: 10:33 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
sessionExpired();
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsurerDAO.php';
$schemes_owners = (new InsurerDAO())->getInsurers(TRUE);
$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes(TRUE);

?>
<form id="params">
	<div class="row-fluid">
		<label class="span6 class="btn-block_"">
		<select name="SchemeOwner" id="SchemeOwner">
			<option value="">-- select a scheme owner to view bill --</option>
			<?php foreach ($schemes_owners as   $owner ) { ?>
			<option value="<?= $owner->getId() ?>"><?= $owner->getName() ?></option>
			
		<?php	} ?>
		</select>
		</label>
		
		<label class="span6">
			<input type="text" name="iSchemes" id="iSchemes" placeholder="-- select a scheme to view bill --">
		</label>
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function () {
		getSchemes_();
		
		$("select[name='SchemeOwner']").select2({
			allowClear: true,
			width: '100%',
		}).change(function (e) {
			if(e.val !== ""){
				loadDoc( $('#billDoc'), '/billing/insurance/insuranceaccount.php?sid='+e.val + '&provider_id=' + e.val);
				getSchemes(e.val);
			}else {
				getSchemes_();
			}
			
		})
		
	});
	
	function  getSchemes_() {
			$.ajax({
				url: '/billing/insurance/bill_doc.php',
				type: 'GET',
				dataType: 'json',
				success: function (result) {
					setSchemes(result);
				}
			});
	}
	
	function getSchemes(iid) {
		$.ajax({
			url: '/billing/insurance/bill_doc.php?insurer='+ iid,
			type: 'GET',
			dataType: 'json',
			success: function (result) {
				setSchemes(result);
			}
		});
	
	}
	
	function setSchemes(data) {
		$('input[name="iSchemes"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: "-- select a scheme to view bill --",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		}).change(function (s) {
			var owner_id = $('select[name="SchemeOwner"]').val();
			if(s.val !== "") {
				console.log(owner_id);
				if(owner_id === "") {
					loadDoc($('#billDoc'), '/billing/insurance/insuranceaccount.php?sid=' + s.added.id + '&schemeid=' + s.added.id);
				}else{
					loadDoc($('#billDoc'), '/billing/insurance/insuranceaccount.php?sid=' + s.added.id + '&schemeid=' + s.added.id+ '&provider_id=' + owner_id);
				}
			}else {
				if(owner_id !== ""){
					loadDoc( $('#billDoc'), '/billing/insurance/insuranceaccount.php?sid='+owner_id + '&provider_id=' + owner_id);
				}else {
					getSchemes_();
					//$('#billDoc').html('');
				}
			}
		})
	}
</script>
