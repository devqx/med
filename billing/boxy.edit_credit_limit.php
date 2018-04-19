<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/29/16
 * Time: 12:51 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
@session_start();
$limit = (new CreditLimitDAO())->getPatientLimit($_GET['pid']);

if($_POST){
	if(is_blank($_POST['amount'])){exit("error:Amount is required");}
	if(is_blank($_POST['expiration'])){exit("error:Expiration date is required");}
	if(is_blank($_POST['reason'])){exit("error:Reason is required");}
	$limit = (new CreditLimitDAO())->getCreditLimit($_POST['id'])->setPatient( new PatientDemograph($_GET['pid']) )->setReason($_POST['reason'])->setAmount($_POST['amount'])->setExpiration($_POST['expiration'])->setSetBy(new StaffDirectory($_SESSION['staffID']))->update();
	if($limit != null){
		exit("success:Updated Credit Limit");
	}
	exit("error:Failed to update patient's credit limit");
}
?>
<section style="width: 500px;">
	<form method="post" id="form101" action="<?= $_SERVER['REQUEST_URI']?>">
		<input type="hidden" name="id" value="<?=$limit->getId()?>">
		<label>Amount <input type="text" name="amount" class="price-input_"></label>
		<label>Valid till <input type="text" name="expiration" required value="" pattern="^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$"></label>
		<label>Reason <textarea rows="2" required name="reason"></textarea></label>
		<div class="clear"></div>
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>

</section>
<script type="text/javascript">
	$(document).on('submit', '#form101', function(e){
		var form = this;
		if(!e.handled){
			showPinBox( function () {
				$.ajax({
					url: form.action,
					data: $(form).serialize(),
					type: "POST",
					complete: function (xhr, status) {
						console.log(xhr.responseText);
						var s = (xhr.responseText);
						var data = s.split(":");
						if (data[0] === "success") {
							Boxy.info(data[1], function () {
								Boxy.get($(".close")).hideAndUnload();
							});
						} else {
							Boxy.warn(data[1]);
						}
					}
				});
			} );
			e.handled = true;
			return false;
		}
	}).ready(function(){
		$('[name="amount"]').number(true, 2);
		$('input[name="expiration"]').datetimepicker({
			format:'Y/m/d',
			formatDate:'Y-m-d',
			timepicker:false
		});
	});
</script>