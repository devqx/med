<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/17
 * Time: 2:33 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/ReferralCompany.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ReferralCompanyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';

if ($_POST['type'] == "ref_c") {
	if (is_blank($_POST['name'])) {
		exit("error:Company Name is required");
	}
	if (is_blank($_POST['address'])) {
		exit("error:Address is required");
	}
	if (is_blank($_POST['phone'])) {
		exit("error:Contact phone number is required");
	}
	if (is_blank($_POST['email'])) {
		exit("error:Email address is required");
	}
	if (is_blank($_POST['bank_name'])) {
		exit("error:Bank name is required");
	}
	if (is_blank($_POST['account_number'])) {
		exit("error:Account Number is required");
	}
	
	$referral = (new ReferralCompany())->setName($_POST['name'])->setAddress($_POST['address'])->setContactPhone($_POST['phone'])->setEmail($_POST['email'])->setBankName($_POST['bank_name'])->setAccountNumber($_POST['account_number']);
	
	$newRef = (new ReferralCompanyDAO())->add($referral);
	
	if ($newRef !== null) {
		exit('success:Added Referring Company successfully');
	}
	exit('error:Failed to add Referring Company');
}
?>
<section id="newReferralComp">
	<h4>New Referral Company</h4>
	<form autocomplete="off" method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: starting, onComplete: savedReferral})">
		<label>Name <input name="name" type="text"></label>
		<label>Address <textarea name="address"></textarea></label>
		<label>Phone <input name="phone" type="text"> </label>
		<label>Email <input name="email" type="text"> </label>
		<label>Bank Name <input name="bank_name" type="text"> </label>
		<label>Account Number
			<input name="account_number" type="text" pattern="[0-9]{10}" title="10-Digit NUBAN number?"></label>
		<div>
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="type" value="ref_c">
	</form>
</section>

