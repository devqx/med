<div style="width:650px">
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	$pat = (new PatientDemographDAO())->getPatient(($_GET['id']), true);
	sessionExpired();
	?>
	<table class="patient_more_details table">
		<tr>
			<th>Primary HealthCare Center</th>
			<td><?= $pat->getBaseClinic() ? $pat->getBaseClinic()->getName() : 'N/A' ?></td>
		</tr>
		<tr>
			<th>Address</th>
			<td><?= $pat->getAddress() ?></td>
		</tr>
		<tr>
			<th>Local Government Area</th>
			<td><?= $pat->getLga() ? $pat->getLga()->getName() : 'N/A' ?></td>
		</tr>
		<tr>
			<th>Phone Numbers</th>
			<td><ul><?php foreach ($pat->getContacts() as $contact){?><li><?=$contact?></li><?php }?></ul></td>
		</tr>
		<tr>
			<th>Email</th>
			<td><?= !is_blank($pat->getEmail()) ? $pat->getEmail() : '- -' ?></td>
		</tr>
		<tr>
			<th>International/Other Number</th>
			<td><?= $pat->getForeignNumber() ?></td>
		</tr>
		<tr>
			<th>Blood Group / Type</th>
			<td><?= $pat->getBloodGroup() ?> / <?= $pat->getBloodType() ?></td>
		</tr>
		<tr>
			<th>Occupation/Work Address</th>
			<td><?= $pat->getOccupation() ?>/<?= $pat->getWorkAddress() ?></td>
		</tr>
		<tr>
			<th>Industry</th>
			<td><?= $pat->getIndustry() ? $pat->getIndustry()->getName() : '- -' ?></td>
		</tr>
		<tr>
			<th>Religion</th>
			<td><?= ($pat->getReligion() != null) ? $pat->getReligion()->getName() : '- -' ?></td>
		</tr>
		<tr>
			<th colspan="2" style="background-color: #eee">Next of Kin details</th>
		</tr>
		<tr>
			<th>Name</th>
			<td><?= $pat->getKinsLastName() ?>, <?= $pat->getKinsFirstName() ?></td>
		</tr>
		<tr>
			<th>Relationship</th>
			<td><?= $pat->getKinRelationship() != null ? $pat->getKinRelationship()->getName() : 'Not Specified' ?></td>
		</tr>
		<tr>
			<th>Phone/Address</th>
			<td><?= $pat->getKinsPhone() ? $pat->getKinsPhone() : '' ?>, <?= $pat->getKinsAddress() ?></td>
		</tr>
		<tr>
			<th colspan="2" style="background-color: #eee">Insurance</th>
		</tr>
		<tr>
			<th>Type</th>
			<td><?= (($pat->getInsurance() == null) ? "SELF PAY" : ($pat->getScheme() == null) ? "SELF PAY" : $pat->getScheme()->getType() == 'self' ? 'SELF-PAY' : 'INSURED') ?></td>
		</tr>
		<tr>
			<th>Insurance Scheme</th>
			<td><span<?= ((bool)!$pat->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= strtoupper(($pat->getInsurance() == null) ? "SELF PAY" : ($pat->getScheme() == null) ? "SELF PAY" : $pat->getScheme()->getName()) ?></span></td>
		</tr>
		<tr>
			<th>Expiration</th>
			<td><span<?= ((bool)!$pat->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= (($pat->getInsurance()->getExpirationDate() == 0) ? '- -' : date("Y M, d", strtotime($pat->getInsurance()->getExpirationDate()))) ?></span></td>
		</tr>
		<tr>
			<th>Policy #/Enrollee ID</th>
			<td><?= ($pat->getInsurance()->getPolicyDetails()) ?></td>
		</tr>
		<tr>
			<th>Referrer</th>
			<td>
				<?= $pat->getReferral() ? $pat->getReferral()->getName() : ''?>
				<?= $pat->getReferralCompany() ? $pat->getReferralCompany()->getName() : ''?>
				<?= !$pat->getReferral() && !$pat->getReferralCompany() ? 'N/A' : ''?>
			</td>
		</tr>
		<tr>
			<th style="background-color: #eee">Account Status</th>
			<td style="background-color: #eee"><?php if ($pat->isActive()) { ?>
					<a href="javascript:;" onclick="changeState('Deactivate')" title="Deactivate this patient's Account">Deactivate
						Account</a>
				<?php } else { ?>
					<a href="javascript:;" onclick="changeState('Activate')">Activate
						Account</a>
				<?php } ?>
				|
				<?php if(!$pat->getDeceased()){?>
				<a href="javascript:" onclick="deceasePatient()">Mark as deceased</a>
				<?php } else {?>
					<span class="alert alert-danger">Patient is deceased</span>
				<?php }?>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	var deceasePatient = function () {
		Boxy.ask("This patient will be marked as deceased", ["OK", "Cancel"], function (answer) {
			if(answer === "OK"){
				$.ajax({
					url: "/changeDeceasedStatus.php",
					type: "post",
					data: {
						pid: "<?= $pat->getId() ?>"
					},
					success: function (d) {
						if ((d.split(":")[0]).trim() === "ok") {
							Boxy.get($(".close")).hideAndUnload();
							$.notify2("Action Completed", "notice");
							//location.href = "/patient_find-or-create.php";
						} else {
							$.notify2(d.split(":")[1], "error");
						}
					},
					error: function (d) {
						$.notify2("Sorry action failed", "error");
					}
				});
			}
		});
	};
	function changeState(changeTo) {
		Boxy.ask("Are you sure you want " + changeTo + " this patient's account?", ["Yes", "No"],
			function (d) {
				if (d === "Yes") {
					$.ajax({
						url: "/changeAccountStatus.php",
						type: "post",
						data: {
							pid: "<?= $pat->getId() ?>",
							status: "<?= ($pat->isActive()) ? 0 : 1 ?>"
						},
						success: function (d) {
							if ((d.split(":")[0]).trim() === "ok") {
								location.href = "/patient_find-or-create.php";
							} else {
								$.notify2(d.split(":")[1], "error");
							}
						},
						error: function (d) {
							$.notify2("Sorry action failed", "error");
						}
					});
				}
			}
		);
	}
</script>