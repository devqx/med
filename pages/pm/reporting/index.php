<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>
<script type="text/javascript">
	$(document).ready(function () {
		$("ul li ul").hide();
		$("ul li").click(function () {
			$(this).find("ul").toggle("fast");
		});
	});

</script>

<style>
	#content ul li {
		background-color: #fafafa;
		border: 1px solid #ddd;
		cursor: pointer;
		padding: 0 20px;
	}

	#content ul li ul {
		background-color: #fff;
	}

	#content ul li ul li {
		padding: 5px 25px;
		border: 1px solid #ddd;
		border-width: 0 0 1px 0;
		background-color: #fff;
		margin-left: -20px;
		margin-right: -20px;
	}

	#content ul li ul li:before {
		font-family: FontAwesome;
		content: "\f101";
		padding-right: 5px;
	}

	#content ul li ul li:hover {
		background-color: #e8f8fd;
	}

	#content a:hover {
		text-decoration: none;
	}

</style>
<div id='content'>
	<ul>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>Admissions Report</h3>
				<ul>
					<li><a href='/pm/reporting/inPatient.php?view=current'>Current Admission</a></li>
					<li><a href='/pm/reporting/inPatient.php?view=discharged'>Discharged Patients</a></li>
					<li><a href='/pm/reporting/inPatient.php?view=admissions'>Admitted Patients</a></li>
					<li><a href='/pm/reporting/mortality.php'>Mortality Report</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->accounts) || $this_user->hasRole($protect->bill_auditor) || $this_user->hasRole($protect->cashier))) { ?>
			<li><h3>Bills Report</h3>
				<ul>
					<?php if ($this_user->hasRole($protect->bill_auditor)) { ?>
						<li><a href='/pm/reporting/bill.php'>Transactions</a></li><?php } ?>
					<?php if ($this_user->hasRole($protect->cashier) || $this_user->hasRole($protect->bill_auditor)) { ?>
						<li><a href='/pm/reporting/transactions.php'>Cash Transactions</a></li><?php } ?>
					<?php if ($this_user->hasRole($protect->bill_auditor)) { ?>
						<li><a href='/pm/reporting/discounts.php'>Discounts</a></li><?php } ?>
					<?php if ($this_user->hasRole($protect->bill_auditor)) { ?>
						<li><a href='/pm/reporting/outstanding_bills.php'>Outstanding Bills</a></li><?php } ?>

					<?php if ($this_user->hasRole($protect->bill_auditor)) { ?>
						<li><a href='/pm/reporting/credit_limits.php'>Credit Limits</a></li><?php } ?>
                    <?php if ($this_user->hasRole($protect->bill_auditor)) {?>
                        <li><a href="/pm/reporting/claims.php">Claims Report</a> </li>
                    <?php } ?>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->records))) { ?>
			<li><h3>Daily Report</h3>
				<ul>
					<li><a href='/pm/reporting/report.visit.php?view=visits'>Visits/Registrations</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>Doctors Report</h3>
				<ul>
					<li><a href="/pm/reporting/report.consultant.php">Consultant</a></li>
				</ul>
			</li>
		<?php } ?><?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>Diagnoses Report</h3>
				<ul>
					<li><a href="/pm/reporting/report.diagnoses.php">Diagnoses Requests by date</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->hmo_officer))) { ?>
			<li><h3>Insurance/Free Health Care</h3>
				<ul>
					<li><a href='/pm/reporting/insuranceScheme.php'>Insurance Bills</a></li>
					<li><a href='/pm/reporting/insuranceExpiration.php'>Insurance Expiration</a></li>
					<li><a href='/pm/reporting/insurancePatients.php'>Patients List</a></li>
					<!--<li><a href='/pm/reporting/generatespreadsheet.php'>Generate HMO Spreadsheet</a></li>-->
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->radiology))) { ?>
			<li><h3>Imaging/Scans Reports</h3>
				<ul>
					<li><a href='/pm/reporting/reports.scans.php'>Scans</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->lab))) { ?>
			<li><h3>Lab Reports</h3>
				<ul>
					<li><a href="/pm/reporting/reports.labs.php">Labs</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if (is_dir("antenatal") &&  $this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>MDG Reports</h3>
				<ul>
					<li><a href='/pm/reporting/antenatalReport.php'>Antenatal Enrollments</a></li>
					<li><a href='/pm/reporting/immunisation.php'>Immunization</a></li>
					<li><a href='/pm/reporting/patientVisit.php'>Patient Visit</a></li>
					<!--<li><a href='/pm/reporting/vaccineShot.php'>Vaccine Shot</a></li>-->
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>Patients</h3>
				<ul>
					<li><a href='/pm/reporting/patientList.php'>Patient List</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->pharmacy))) { ?>
			<li><h3>Pharmacy/Drugs Reports</h3>
				<ul>
					<li><a href="/pm/reporting/reports.pharmacysales.php">Pharmaceutical Sales Report</a></li>
					<li><a href='/pm/reporting/dispensedDrugs.php'>Dispensed Drugs Report</a></li>
					<!-- --><li><a href='/pm/reporting/stockMove.php'>Stock Moves</a></li>
					<!--<li><a href='/pm/reporting/alldruginventory.php'>Available Drugs</a></li>-->
					<li><a href='/pm/reporting/drugprescriptions.php'>Drug Prescriptions</a></li>
					<li><a href="/pm/reporting/drugreport.php">Drug Expiration Report</a></li>
					<li><a href="/pm/reporting/refills.php">Refill Report</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role))) { ?>
			<li><h3>Procedure Report</h3>
				<ul>
					<li><a href="/pm/reporting/procedures.php">Procedure Report</a></li>
					<li><a href="/pm/reporting/procedure_started.php">Started Procedure</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->records) || $this_user->hasRole($protect->referral_mgt))) { ?>
			<li><h3>Referral Report</h3>
				<ul>
					<li><a href='/pm/reporting/referrals.php'>Referrals</a></li>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->records))) { ?>
			<li><h3>Staff Directory</h3>
				<ul>
					<li><a href='/pm/reporting/staffList.php'>Staff List</a></li>
					<?php if($this_user->hasRole($protect->user_management)){?><li><a href='/excel.php?filename=Staff.Roles&dataSource=staffRoles'>Staff Roles/Permissions (Direct export)</a></li><?php }?>
				</ul>
			</li>
		<?php } ?>
		<?php if ($this_user && ($this_user->hasRole($protect->reporter) || $this_user->hasRole($protect->records))) { ?>
			<li><h3>Unfulfilled Requests</h3>
				<ul>
					<li><a href='/pm/reporting/unfulfilledLabs.php'>Lab Requests</a></li>
					<li><a href='/pm/reporting/unfulfilledRadiology.php'>Radiology Requests</a></li>
					<li><a href='/pm/reporting/unfulfilledPrescriptions.php'>Prescriptions</a></li>
					<li><a href='/pm/reporting/unfulfilledProcedures.php'>Procedures</a></li>
				</ul>
			</li>
		<?php } ?>

	</ul>
</div>

