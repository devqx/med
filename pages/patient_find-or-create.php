<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SelfRegisterPatientDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->pharmacy) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->lab) || $this_user->hasRole($protect->lab_super)) {
	$f_patient = (new SelfRegisterPatientDAO())->getSelfRegisteredPatientAll();
	?>
	<form method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
		<label><h2>Find Patient <span style="margin-left: 20%; font-size: 20px;" class="new_patient_search">
		</span></h2>
			<input autocomplete="off" class="bigSearchField" autofocus="autofocus" type="text" name="searchfield" id="searchfield" placeholder="Search for a patient" style="border-width: 1px !important;">
		</label>

		<div style="text-align: right;">
			<!-- <a href="javascript:void(0)">Advanced Search</a> &nbsp;&nbsp;&nbsp; -->
			<button type="submit" style="width: 100%;" class="btn">Search &raquo;</button>
		</div>
	</form>

	<form method="post" action="/create-new-patient.php">
		<h2>Create Patient</h2>
		<label>Pre-registered Patient
		<select id="smallSearchField" name="smallSearchField" data-placeholder="-- search for self registered patient --" class="smallSearchField">
			<option value=""></option>
			<?php foreach ($f_patient as $fp) { ?>
				<option value="<?= $fp->getId()[0] ?>"><?= $fp->getTitle() . ' ' . $fp->getFname() . ' ' . $fp->getLname() . ' '. $fp->getContact() ?></option>
			<?php } ?>
		</select></label>
		<label class="sub">First Name<span class="required-text">*</span>
			<input type="text" name="fname" id="fname">
		</label>

		<label class="sub">Last Name<span class="required-text">*</span>
			<input type="text" name="lname" id="lname">
		</label>
		
		<label class="sub">Date of Birth<span class="required-text">*</span><input type="text" name="dob" id="dob" readonly="readonly" placeholder="Click/touch to select a date of birth"></label>
		<div style="text-align: right;">
			<button type="submit" style="width: 100%;" class="btn">Create &raquo;</button>
		</div>
	</form>

	<script type="text/javascript">
		$(document).ready(function () {
			
			$(".smallSearchField").select2({
				placeholder: 'Search for self registered patient',
				allowClear: true,
				width: '100%',
		}).
			change(function () {
				var id = $(this).val();
				window.location.replace("/create-new-patient.php?x=" + id);
			});


			$('#dob').datetimepicker({
				format: 'd/m/Y',
				formatDate: 'd/m/Y',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({maxDate: new Date().toISOString().split('T')[0]});
				}
			});
		});
	</script>
<?php } else {
	echo $protect->ACCESS_DENIED;
} ?>