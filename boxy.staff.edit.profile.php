<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/25/14
 * Time: 9:40 AM
 */
if (!class_exists('StaffManager')) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
}

$STAFF = new StaffManager();
if ($_POST) {
	exit($STAFF->updateStaffProfile($_POST));
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();

$dao = new StaffDirectoryDAO();
$staffObj = $dao->getStaff($_GET['id']);

$this_user = $dao->getStaff($_SESSION['staffID']);
$depts = (new DepartmentDAO())->getDepartments();

?>
<section style="width: 650px;">
	<div class="well output"></div>
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : stop})">
		<label>First Name <input type="text" name="fname" value="<?= $staffObj->getFirstName() ?>"></label>
		<input type="hidden" name="id" value="<?= $staffObj->getId() ?>">
		<label>Last Name <input type="text" name="lname" value="<?= $staffObj->getLastName() ?>"></label>
		<label>Phone Number <input type="text" name="phonenumber" value="<?= $staffObj->getPhone() ?>"></label>
		<label>Email address <input type="email" name="email" value="<?= $staffObj->getEmail() ?>"></label>
		<label>Profession
			<select name="profession" data-placeholder="Select Profession" <?= !$this_user->hasRole($protect->user_management) ? 'disabled' : '' ?>><?php
				$profns = (new StaffDirectoryDAO())->getProfessions();
				foreach ($profns as $p) { ?>
					<option <?= ($staffObj->getProfession() == $p ? ' selected="selected"' : '') ?> value="<?= $p ?>"><?= ucwords(str_replace("_", " ", $p)) ?></option>
				<?php } ?></select>
		</label>
		<label>Department
			<select name="department_id"<?= !$this_user->hasRole($protect->user_management) ? ' disabled' : '' ?>>
				<option value="">-- Select Department --</option>
				<?php foreach ($depts as $d) { ?>
					<option value="<?= $d->getId() ?>"<?= (($staffObj->getDepartment() != null && $d->getId() == $staffObj->getDepartment()->getId()) ? ' selected="selected"' : '') ?>><?= $d->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Specialization <select name="specialization" data-placeholder="Select Specialization"<?= !$this_user->hasRole($protect->user_management) ? ' disabled' : '' ?>>
				<option value="">-- specialization --</option>
				<?php
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
				$specs = (new StaffSpecializationDAO())->getSpecializations();
				foreach ($specs as $data) { ?>
					<option value="<?= $data->getId() ?>"<?= (($staffObj->getSpecialization()->getId() == $data->getId()) ? ' selected="selected"' : '') ?>><?= $data->getName() ?></option>
				<?php } ?></select></label>
		<label>Folio Number <input type="text" name="folio_number" value="<?= $staffObj->getFolioNumber()?>"> </label>

        <?php if( $staffObj->getProfession() == "Doctor"){ ;?>
        <label> <input type="checkbox" name="is_consultant" value="0" onclick="isConsultant(event)" <?php if($staffObj->getisConsultant() == 1){?> checked="checked" <?php }?>  id="is_consultant"  /> Consultant </label>
        <?php } ;?>


		<label><input type="checkbox" id="enable_sip" class="enable_sip">Enable Sip Technology for this Staff<i class="icon-phone icon-phone-sign"></i></label>
		<div class="row-fluid" id="sip_info">
			<label class="span4" for="sip_username">Sip Username
				<input class="sip_username" id="sip_username" type="text"  name="sip_username" value="<?= $staffObj->getSipUserName() ? $staffObj->getSipUserName() : '' ?>">
			</label>
			<label class="span4" for="sip_extension">Sip Extension
				<input class="sip_extension" id="sip_extension" type="text"  onkeypress='validateExtension(event)' name="sip_extension"  value="<?= $staffObj->getSipExtension() ? $staffObj->getSipExtension() : ' ' ?>">
			</label>
			<label class="span4" for="sip_password">Sip Password
				<input type="password"  name="sip_password" class="sip_password"  onkeypress='validateSipPassword(event)' id="sip_password" value="<?= $staffObj->getSipPassword() ? $staffObj->getSipPassword() : ' ' ?>">
			</label>

		</div>

		<div>

			<button type="submit" class="btn btn-primary">Update</button>
			<button type="reset" class="btn">Reset</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function start() {
		$(document).trigger('ajaxSend');
		$('div.output').html('<img src="/img/loading.gif"> updating');
	}
	function stop(e) {
		$(document).trigger('ajaxStop');
		var data = e.split(":");
		var loading = $('div.output');
		loading.html(data[1]);
		if (data[0] === "success") {
			location.reload();
		}
	}
	$(document).ready(function () {
		$('#sip_info').hide();

		$('#enable_sip').change(function () {
			if($(this).is(':checked')){
				$('#sip_info').show();
			}else{
				$('#sip_info').hide();

			}
		});

		$("#sip_extension").keyup(function (e) {
			var text = $(this).val();
			if(text.length > 4){
				Boxy.alert("You have exceeded the max only 4 digits are required!");
			}
			e.preventDefault();
		});
	});

	function validateExtension(evt) {
		var input = evt || window.event;
		var key = input.keyCode || input.which;
		key = String.fromCharCode( key );
		var regex = /[1-9]|\./;
		if( !regex.test(key) ) {
			Boxy.alert("Not allowed!");
			if(input.preventDefault) input.preventDefault();
		}

	}

	function validateSipPassword(evt){
		var input = evt || window.event;
		var key = input.keyCode || input.which;
		key = String.fromCharCode(key);
		var regex = /[0-9]|\./;
		if(!regex.test(key)){
			Boxy.alert("Wrong entry!");
			if(input.preventDefault) input.preventDefault();
		}



	}

    function isConsultant(e){

        this.onchange = function(e){

            e.target.checked ? e.target.value = 1 : e.target.value = 0;
            console.log(e.target.value);
        }
    }

</script>