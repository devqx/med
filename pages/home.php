<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
if (!isset($_SESSION)) {
	@session_start();
}
$this_user = null;
if (isset($_SESSION['staffID'])) {
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
}

if($this_user == null){
	//ob_start();
	//header('Location: /login.php');
}
?>
<div class="content">
	<div class="container">
		<div class="features-four">
			<div class="row">
				<?php if ($this_user && ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->pharmacy) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->lab) || $this_user->hasRole($protect->lab_super))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="patient_find-or-create.php"><i class="icon-search"></i></a>
							<a href="patient_find-or-create.php"><h4>Find/Create Patient</h4></a>

							<p>Create Patient Record or Search for existing patients</p>
						</div>
					</div><?php } ?>
				<?php if ($this_user && $this_user->hasRole($protect->records)) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/documents/"><i class="icon-book"></i></a>
							<a href="/documents/"><h4>Documents</h4></a>

							<p>Create/Attach Patient Document or Search/View existing documents</p>
						</div>
					</div><?php } ?>

				<?php if (is_dir("messaging")) { //the logged in person has access to sms feature ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/messaging/"><i class="icon-envelope"></i></a>
							<a href="/messaging/"><h4>Messages</h4></a>
							<p>Messages and Notifications to patients</p>
						</div>
					</div>
				<?php }//end if ?>

				<?php if (is_dir("appointments") && $this_user && ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role))) {//use role management to also apply who sees which app?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/appointments/"><i class="icon-calendar"></i></a>
							<a href="/appointments/"><h4>Appointments</h4></a>

							<p>Schedule patient for appointment and other events</p>
						</div>
					</div><?php } ?>

				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/labs") && $this_user && ($this_user->hasRole($protect->lab) || $this_user->hasRole($protect->lab_super))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/labs/"><i class="icon-beaker"></i></a>
							<a href="/labs/"><h4>Laboratory</h4></a>
							<p>Laboratory Requests/Results</p>
						</div>
					</div><?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/pharmaceuticals") && $this_user && ($this_user->hasRole($protect->pharmacy))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/pharmaceuticals/"><i class="icon-briefcase"></i></a>
							<a href="/pharmaceuticals/"><h4>Pharmacy</h4></a>

							<p>Drug Prescriptions for Patients</p>
						</div>
					</div><?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/imaging") && $this_user && ($this_user->hasRole($protect->radiology))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/imaging/"><i class="icon-picture"></i></a>
							<a href="/imaging/"><h4>Imaging</h4></a>
							<p>Patients' Radiological Documents</p>
						</div>
					</div>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/procedures") && $this_user && ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->procedures))) {//wrong role?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/procedures/"><i class="icon-medkit"></i></a>
							<a href="/procedures/"><h4>Procedures</h4></a>
							<p>Patients' Procedures ...</p>
						</div>
					</div>
				<?php } ?>

				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/admissions") && $this_user && ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/admissions/"><i class="icon-hospital"></i></a>
							<a href="/admissions/"><h4>In-Patients Care</h4></a>

							<p>Manage Patients admissions/discharge</p>
						</div>
					</div><?php } ?>

				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/outpatient_tasks") && $this_user && ($this_user->hasRole($protect->nurse))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/outpatient_tasks/"><i class="icon-paste"></i></a>
							<a href="/outpatient_tasks/"><h4>O.P Clinical Tasks</h4></a>

							<p>Clinical Tasks/Roundings for out-patients</p>
						</div>
					</div><?php } ?>

				<?php if (!$mobile) {
					if (is_dir($_SERVER['DOCUMENT_ROOT']."/immunization") && $this_user && ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records) || $this_user->hasRole($protect->doctor_role))) { ?>
						<div class="span4">
							<div class="f-block b-blue">
								<a href="/immunization/"><i class="icon-tint"></i></a>
								<a href="/immunization/"><h4>Vaccine &amp; Immunization</h4></a>

								<p>Patients vaccine details</p>
							</div>
						</div><?php
					}
				} ?>

				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/referrals") && $this_user && ($this_user->hasRole($protect->referral_mgt))) {//use role management to also apply who sees which app?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/referrals/"><i class="icon-resize-small"></i></a>
							<a href="/referrals/"><h4>Referrals</h4></a>

							<p>Manage entities that refer patients/requests to this clinic</p>
						</div>
					</div><?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/antenatal") && $this_user && ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records) || $this_user->hasRole($protect->doctor_role))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/antenatal/"><i class="antenatal-mother"></i></a>
							<a href="/antenatal/"><h4>Antenatal Care</h4></a>
							<p>Nursing mothers and infants</p>
						</div>
					</div>

				<?php } ?>

				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/labourMobile") && $this_user && ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records) || $this_user->hasRole($protect->doctor_role))) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/labourMobile/"><i class="icon-play-circle"></i></a>
							<a href="/labourMobile/"><h4>Labour Management</h4></a>
							<p>Puerperium, Labour Track events</p>
						</div>
					</div>
				<?php } ?>

					<div class="span4">
						<div class="f-block b-blue">
							<a href="/specialty_modules.php"><i class="icon-group"></i></a>
							<a href="/specialty_modules.php"><h4>Specialty Modules</h4></a>
							<p>Physiotherapy, IVF Clinic, Dentistry, ARV, Optometry</p>
						</div>
					</div>

				<?php if (!$mobile) { ?>
					<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/billing") && $this_user && ($this_user->hasRole($protect->accounts))) { ?>
						<div class="span4">
							<div class="f-block b-blue">
								<a href="/billing/"><i class="icon-shopping-cart"></i></a>
								<a href="/billing/"><h4>Billing</h4></a>
								<p>Patients medical bills</p>
							</div>
						</div>
					<?php } ?>
					<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/vouchers") && $this_user && ($this_user->hasRole($protect->voucher))) { ?>
						<div class="span4">
							<div class="f-block b-blue">
								<a href="/vouchers/"><i class="icon-gift"></i></a>
								<a href="/vouchers/"><h4>Vouchers</h4></a>
								<p>Vouchers for payments, discounts, refunds, ...</p>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/death_certificates")) { //the logged in person has access to sms feature ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/death_certificates/"><i class="fa "></i></a>
							<a href="/death_certificates/"><h4>Death Certificates</h4></a>
							<p>Create or View certificates of death of patients</p>
						</div>
					</div>
				<?php }//end if ?>

				<div class="span4">
					<div class="f-block b-blue">
						<a href="/staff_find.php"><i class="icon-user"></i></a>
						<a href="/staff_find.php"><h4>Medical Staff Directory</h4></a>
						<p>Find a doctor, nurse, medical staff</p>
					</div>
				</div>
				<?php if ($this_user) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/medical_exam/index.php"><i class="exam-test"></i></a>
							<a href="/medical_exam/index.php"><h4>Medical Reports</h4></a>
							<p>Medical Reports Requests</p>
						</div>
					</div>
				<?php } ?>

				<?php if ($this_user) { ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/pm/reporting/index.php"><i class="icon-bar-chart"></i></a>
							<a href="/pm/reporting/index.php"><h4>Reports</h4></a>
							<p>Reports and data</p>
						</div>
					</div>
				<?php } ?>
				
				<?php if(is_dir($_SERVER['DOCUMENT_ROOT'] . "/consumableItems") && ( $this_user && $this_user->hasRole($protect->consumables))){ ?>
					<div class="span4">
						<div class="f-block b-blue">
							<a href="/consumableItems/"><i class="icon-lemon"></i></a>
							<a href="/consumableItems/"><h4>Non-Drug Consumables</h4></a>
							<p>Patient Consumables Requests</p>
						</div>
					</div>
				<?php } ?>

				<?php if (!$mobile) { ?>
					<?php if (is_dir("pm") && $this_user && ($this_user->hasRole($protect->mgt))) { ?>
						<div class="span4">
							<div class="f-block b-blue">
								<a href="/pm/"><i class="icon-cog"></i></a>
								<a href="/pm/"><h4>Practice Management</h4></a>
								<p>Manage your hospital configuration</p>
							</div>
						</div>
					<?php }
				} ?>
			</div>
		</div>
	</div>
</div>