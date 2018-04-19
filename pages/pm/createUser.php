<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

$clinics = (new ClinicDAO())->getClinics();
$depts = (new DepartmentDAO())->getDepartments();

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Department.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
	$s = new StaffDirectory();
	
	if (!empty($_POST['fname'])) {
		$s->setFirstName($_POST['fname']);
	} else {
		exit('error:First Name is required');
	}
	if (!empty($_POST['lname'])) {
		$s->setLastName($_POST['lname']);
	} else {
		exit('error:Last name is required');
	}
	
	if (!empty($_POST['hospital'])) {
		$clinic = new Clinic();
		$clinic->setId($_POST['hospital']);
		$s->setClinic($clinic);
	} else {
		exit('error:Clinic is required');
	}
	
	if (!empty($_POST['department_id'])) {
		$dept = new Department();
		$dept->setId($_POST['department_id']);
		$s->setDepartment($dept);
	} else {
		$s->setDepartment(null);
	}
	
	if (!empty($_POST['profession'])) {
		$s->setProfession($_POST['profession']);
	} else {
		exit('error:Select a profession');
	}
	if (!empty($_POST['specialty'])) {
		$spe = new StaffSpecialization();
		$spe->setId($_POST['specialty']);
		$s->setSpecialization($spe);
	} else {
		$s->setSpecialization(null);
	}
	if (!empty($_POST['phone'])) {
		$s->setPhone($_POST['phone']);
	} else {
		exit('error:Phone number is required');
	}
	if (!empty($_POST['email'])) {
		$s->setEmail($_POST['email']);
	} else {
		exit('error:Email is required');
	}
	if (!empty($_POST['username'])) {
		$s->setUsername($_POST['username']);
	} else {
		exit('error:Username is required');
	}
	if (empty($_POST['password'])) {
		exit('error:Password is required');
	} else if (empty($_POST['rpassword'])) {
		exit('error:Confirm password required');
	} else if ($_POST['rpassword'] == $_POST['password']) {
		$s->setPassword(password_hash($_POST['password'], PASSWORD_BCRYPT));
	} else {
		exit('error:Passwords do not match');
	}
	
	if (!empty($_POST['sip_username'])) {
		$s->setSipUserName($_POST['sip_username']);
	}
	if (!empty($_POST['sip_password'])) {
		$s->setSipPassword($_POST['sip_password']);
	}
	if (!empty($_POST['folio_number'])) {
		$s->setFolioNumber($_POST['folio_number']);
	}
	$s->setStatus('disabled');
	try {
		$DATA = (new StaffDirectoryDAO())->addStaff($s);
		if ($DATA != null) {
			exit('success:Staff Created');
		}
		exit('error:Failed to create staff');
	} catch (StaffDirectoryException $e) {
		exit('error:' . $e->getMessage());
	}
}
?>
<form action="<?= $_SERVER['REQUEST_URI'] ?>" id="createuser" method="post" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete': finishuSER})">
	<div class="row-fluid">
		<label class="span6">First name<span class="required-text">*</span>
			<input name="fname" type="text" id="fname"/></label>

		<label class="span6">Last name<span class="required-text">*</span>
			<input name="lname" type="text" id="lname"/></label>
	</div>
	
	<div class="row-fluid">
		<label class="span6">Hospital<span class="required-text">*</span>
			<select name="hospital">
				<option value="">Select your Hospital / Clinic</option>
				<?php
				foreach ($clinics as $cl) {?>
					<option value="<?=$cl->getId()?>"><?=$cl->getName()?></option>
				<?php }?>
			</select></label>
		<label class="span6">Department <a class="pull-right" href="javascript:;" id="newDept">New Department</a>
			<select name="department_id" id="department_id">
				<?php foreach ($depts as $d) { ?>
					<option value="<?= $d->getId() ?>"><?= $d->getName() ?></option>
				<?php } ?>
			</select></label>
	</div>
	
	
	<div class="row-fluid">
		<label class="span6">Profession<span class="required-text">*</span>
			<select name="profession" data-placeholder="-- profession --">
				<option></option>
				<?php
				$profns = (new StaffDirectoryDAO())->getProfessions();
				foreach ($profns as $p) { ?>
					<option value="<?= $p ?>"><?= ucwords(str_replace("_", " ", $p)) ?></option>
				<?php } ?>
			</select></label>
		<label class="span6">Specialization<span class="required-text">*</span>
			<select name="specialty" size="1">
				<option value="">-- specialization --</option>
				<?php
				$specs = (new StaffSpecializationDAO())->getSpecializations();
				foreach ($specs as $data) { ?>
					<option value="<?= $data->getId() ?>"><?= $data->getName() ?></option>
				<?php } ?>
			</select></label>
	</div>
	
	<div class="row-fluid">
		<label class="span6">Phone number<span class="required-text">*</span>
			<input name="phone" type="text" id="phone"/></label>
		<label class="span6">Email address<span class="required-text">*</span>
			<input name="email" type="email" id="email"/></label>
	</div>

	<label for="username">Username<span class="required-text">*</span>
		<input name="username" type="text" id="username"/></label>
	<label for="password">Password<span class="required-text">*</span>
		<input name="password" type="password" id="password"/></label>
	<label for="rpassword">Repeat password<span class="required-text">*</span>
		<input name="rpassword" type="password" id="rpassword"/></label>
	<label>Folio Number <input type="text" name="folio_number"> </label>
	<label><input type="checkbox" id="enable_sip" class="enable_sip"> Enable <abbr title="Session Initiation Protocol">SIP</abbr> for this Staff </label>
	<div class="row-fluid" id="sip_info">
		<label class="span4" for="sip_username">Sip Username
			<input class="sip_username" id="sip_username" type="text" name="sip_username" placeholder="Type sip username here">
		</label>
		<label class="span4" for="sip_extension">Sip Extension
			<input class="sip_extension" id="sip_extension" type="text" onkeypress='validateExtension(event)' name="sip_extension" placeholder="Type sip extension number[7-9] (eg 8888)">
		</label>
		<label class="span4" for="sip_password">Sip Password
			<input type="password" placeholder="Type sip password here (must be [1-9])" onkeypress='validateSipPassword(event)' name="sip_password" class="sip_password" id="sip_password">
		</label>

	</div>
	<div class="btn-block">
		<button name="newuser" class="btn" type="submit">Create User</button>
		<button class="btn-link" type="button" onclick="$('#creator').attr('data', 'none').html('');$('span.error').html('');">Cancel</button>
	</div>


</form>

<script type="text/javascript">
	$(document).ready(function () {
		$("#createuser select").select2({
			width: '100%'
		});
		$('#newDept').click(function () {
			loadCreateDepartment();
		});

		$("#sip_info").hide();

		$(".enable_sip").change(function () {
			if ($(this).is(':checked')) {
				$("#sip_info").show();
			} else {
				$("#sip_info").hide();
			}
		});

		$("#sip_extension").keyup(function (e) {
			var text = $(this).val();
			if (text.length > 4) {
				Boxy.alert("You have exceeded the max only 4 digits are required!");
			}
			e.preventDefault();
		});

	});

	function validateExtension(evt) {
		var input = evt || window.event;
		var key = input.keyCode || input.which;
		key = String.fromCharCode(key);
		var regex = /[1-9]|\./;
		if (!regex.test(key)) {
			Boxy.alert("Not allowed!");
			if (input.preventDefault) input.preventDefault();
		}
	}

	function validateSipPassword(evt) {
		var input = evt || window.event;
		var key = input.keyCode || input.which;
		key = String.fromCharCode(key);
		var regex = /[0-9]|\./;
		if (!regex.test(key)) {
			Boxy.alert("Wrong entry!");
			if (input.preventDefault) input.preventDefault();
		}
	}

	function finishuSER(s) {
		var data = s.split(":");
		if (data[0] === "success") {
			Boxy.info(data[1]);
			$('#existingUsers').load("/pages/pm/getUserList.php");
			$('#creator').hide();
		} else {
			if (data[1] !== "undefined") {
				Boxy.alert(data[1]);
			} else {
				Boxy.alert("An unknown error occurred in create staff");
			}
		}
		$('.error').html('');
	}
</script>