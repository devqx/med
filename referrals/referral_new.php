<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/17
 * Time: 2:44 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ReferralCompany.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Referral.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
$specialties = (new StaffSpecializationDAO())->getSpecializations();
$companies = (new ReferralCompanyDAO())->all(0, 5000);
if ($_POST['type'] == "ref1") {
	if (is_blank($_POST['company_id'])) {
		exit('error:Company is required');
	}
	if (is_blank($_POST['name'])) {
		exit('error:Name is required');
	}
	if (is_blank($_POST['phone'])) {
	}
	
	if (is_blank($_POST['specialization_id'])) {
		exit('error:Specialty is required');
	}
	if (is_blank($_POST['bank_name'])) {
		exit('error:Bank name is required');
	}
	if (is_blank($_POST['account_number'])) {
	}
	$referral = (new Referral())->setCompany(new ReferralCompany($_POST['company_id']))->setName($_POST['name'])->setPhone($_POST['phone'])->setEmail($_POST['email'])->setSpecialization(new StaffSpecialization($_POST['specialization_id']))->setBankName($_POST['bank_name'])->setAccountNumber($_POST['account_number']);
	
	$newRef = (new ReferralDAO())->add($referral);
	if ($newRef !== null) {
		exit('success:Added Referral successfully');
	}
	exit('error:Failed to add referral');
}
?>
<section>
	<h4>New Referral Details</h4>
	<form autocomplete="off" method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: starting, onComplete: savedReferral})">
		<label>Company
			<select name="company_id" data-placeholder="Select a referring Company"><?php foreach ($companies->data as $c) { ?>
					<option value="<?= $c->getId() ?>"><?= $c->getName() ?></option><?php } ?></select></label>
		<label>Name <input name="name" type="text"></label>
		<label>Phone <input name="phone" type="text"> </label>
		<label>Email <input name="email" type="email"> </label>
		<label>Specialization
			<select name="specialization_id" data-placeholder="Select a specialty field"><?php foreach ($specialties as $s) { ?>
					<option value="<?= $s->getId() ?>"><?= $s->getName() ?></option><?php } ?>
			</select></label>
		<label>Bank Name <input name="bank_name" type="text"> </label>
		<label>Account Number
			<input name="account_number" type="text" pattern="[0-9]{10}" title="10-Digit NUBAN number?"></label>
		<div>
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="type" value="ref1">
	</form>
</section>
