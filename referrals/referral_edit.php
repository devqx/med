<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/15
 * Time: 1:22 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ReferralCompany.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$companies = (new ReferralCompanyDAO())->all();
$specialties = (new StaffSpecializationDAO())->getSpecializations();

if ($_POST) {
	if ($_POST['type'] == "personal") {
		$referral = (new ReferralDAO())->get($_POST['id']);
		if (!is_blank($_POST['company_id'])) {
			$referral->setCompany(new ReferralCompany($_POST['company_id']));
		} else {
			exit('error:Company is required');
		}
		if (!is_blank($_POST['name'])) {
			$referral->setName($_POST['name']);
		} else {
			exit('error:Name is required');
		}
		if (!is_blank($_POST['phone'])) {
			$referral->setPhone($_POST['phone']);
		} else {
			exit('error:Phone is required');
		}
		if (!is_blank($_POST['specialization_id'])) {
			$referral->setSpecialization(new StaffSpecialization($_POST['specialization_id']));
		} else {
			exit('error:Specialty is required');
		}
		if (!is_blank($_POST['bank_name'])) {
			$referral->setBankName($_POST['bank_name']);
		} else {
			exit('error:Bank name is required');
		}
		if (!is_blank($_POST['account_number'])) {
			$referral->setAccountNumber($_POST['account_number']);
		} else {
			exit('error:Account Number is required');
		}
		if(!is_blank($_POST['email'])){
		    $referral->setEmail($_POST['email']);
        }else{
		    exit('error:Email Address Required');
        }
		
		if ((new ReferralDAO())->update($referral) !== null) {
			exit("success:Referral Updated");
		}
		exit("error:Failed to update the referral details");
		
	}
	if ($_POST['type'] == "company") {
		$referral = (new ReferralCompanyDAO())->get($_POST['id']);
		if (!is_blank($_POST['name'])) {
			$referral->setName($_POST['name']);
		} else {
			exit("error:Name is invalid");
		}
		if (!is_blank($_POST['address'])) {
			$referral->setAddress($_POST['address']);
		} else {
			exit("error:Address is invalid");
		}
		if (!is_blank($_POST['phone'])) {
			$referral->setContactPhone($_POST['phone']);
		} else {
			exit("error:Contact Phone is invalid");
		}
		if (!is_blank($_POST['bank_name'])) {
			$referral->setBankName($_POST['bank_name']);
		} else {
			exit("error:Bank Name is invalid");
		}
		if (!is_blank($_POST['account_number'])) {
			$referral->setAccountNumber($_POST['account_number']);
		} else {
			exit("error:Account number is invalid");
		}
		
		if (!is_blank($_POST['email'])){
			$referral->setEmail($_POST['email']);
		}
		
		if ((new ReferralCompanyDAO())->update($referral) !== null) {
			exit("success:Referring Company details updated successfully");
		}
		exit("error:Failed to update company details");
		
	}
	exit("error:View wrongly configured");
}

if ($_GET['type'] == "personal") {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
	$referral = (new ReferralDAO())->get($_GET['id']);
	?>
	<section>
		<h4>Edit Referral Details</h4>
		<form autocomplete="off" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: starting, onComplete: savedReferral})">
			<label>Company <!--<a href="javascript:" id="newRefComp" class="pull-right">New</a>--><select name="company_id" data-placeholder="Select a referring Company"><?php foreach ($companies->data as $c) { ?>
						<option value="<?= $c->getId() ?>" <?= ($c->getId() === $referral->getCompany()->getId()) ? ' selected' : '' ?>><?= $c->getName() ?></option><?php } ?></select></label>
			<label>Name <input name="name" value="<?= $referral->getName() ?>" type="text"></label>
			<label>Phone <input name="phone" value="<?= $referral->getPhone() ?>" type="text"> </label>
			<label>Email Address <input name="email" value="<?= $referral->getEmail() ?>" type="email"> </label>
			<label>Specialization <select name="specialization_id" data-placeholder="Select a specialty field"><?php foreach ($specialties as $s) { ?>
						<option value="<?= $s->getId() ?>" <?= ($referral->getSpecialization()->getId() === $s->getId() ? 'selected="selected"' : '') ?>><?= $s->getName() ?></option><?php } ?>
				</select></label>
			<label>Bank Name <input name="bank_name" value="<?= $referral->getBankName() ?>" type="text"> </label>
			<label>Account Number <input name="account_number" value="<?= $referral->getAccountNumber() ?>" type="text" pattern="[0-9]{10}" title="10-Digit NUBAN number?"></label>
			<div>
				<button class="btn" type="submit">Save</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
			</div>
			<input type="hidden" name="id" value="<?= $referral->getId() ?>">
			<input type="hidden" name="type" value="<?= $_GET['type'] ?>">
		</form>
	</section>
	<?php
} else if ($_GET['type'] == "company") {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
	$referral = (new ReferralCompanyDAO())->get($_GET['id']);
	?>
	<section>
		<h4>Edit Referring Company Details</h4>
		<form autocomplete="off" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: starting, onComplete: savedReferral})">
			<label>Name <input name="name" type="text" value="<?= $referral->getName() ?>"></label>
			<label>Address <textarea name="address"><?= $referral->getAddress() ?></textarea></label>
			<label>Contact Phone <input name="phone" type="text" value="<?= $referral->getContactPhone() ?>"> </label>
			<label>Bank Name <input name="bank_name" type="text" value="<?= $referral->getBankName() ?>"> </label>
			<label>Account Number <input name="account_number" value="<?= $referral->getAccountNumber() ?>" type="text" pattern="[0-9]{10}" title="10-Digit NUBAN number?"></label>
			<div>
				<button class="btn" type="submit">Save</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
			</div>
			<input type="hidden" name="id" value="<?= $referral->getId() ?>">
			<input type="hidden" name="type" value="<?= $_GET['type'] ?>">
		</form>
	</section>
<?php } ?>

<script>
	function starting() {
	}
	function savedReferral(s) {
		var status = s.split(":")[0];
		var response = s.split(":")[1];
		if (status === "error") {
			Boxy.alert(response);
		} else {
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(response, function () {
			});
		}
	}
</script>