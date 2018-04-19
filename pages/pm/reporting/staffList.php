<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'fname';
if ($_GET['param']) {
	$params = "patient_ID, legacy_patient_id, fname, lname, mname, date_of_birth, sex, email, address, lga_id, state_id, state_res_id, lga_res_id, registered_by, phonenumber, bloodgroup, bloodtype, enrollment_date";
	$staffs = (new StaffDirectoryDAO())->getStaffs();
} else {
	$ASC = $_GET['order'] ? $_GET['order'] : "ASC";
	$staffs = (new StaffDirectoryDAO())->getStaffs();
}
?>
<h3>List of all staff (<?= number_format(sizeof($staffs)) ?>)</h3>
<table id="inventoryreport" width="100%" border="0" cellspacing="0" cellpadding="5" class="display">
	<thead>
	<tr>
		<th>SN</th>
		<th class="drop"><a id="a" href="javascript:void(0)">Staff Name</a></th>
		<th class="drop"><a href="javascript:;">Profession</a></th>
		<th class="drop"><a href="javascript:;">Phone</a></th>
		<th>Status</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (sizeof($staffs) > 0) {
		foreach ($staffs as $key => $staff) { ?>
			<tr>
				<td><?= ($key + 1) ?>.</td>
				<td align="left"><?php echo $staff->getLastName() . " " . $staff->getFirstName() ?></td>
				<td><?= $staff->getProfession() ?></td>
				<td> <?= $staff->getPhone() ?></td>
				<td> <?= $staff->getStatus() ?></td>
			</tr>
		<?php }
	} else { ?>
		<tr>
			<td colspan='5' align='center'><em>No staff found!</em></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<script>
	var lastSort = sort = "<?= $sort?>";
	$(document).ready(function () {
		$("th a").click(function (d) {
			sort = "";
			switch ($(this).html()) {
				case("lastname"): {
					sort = "lastname";
					break;
				}
				case("Staff Name"): {
					sort = "lname";
					break;
				}
				case("Sex"): {
					sort = "sex";
					break;
				}
				case("Phone"): {
					sort = "phone";
					break;
				}
				default: {
					sort = "lastname";
				}
			}

			reload(sort, ((lastSort == sort) ? "DESC" : "ASC"));
		});
	});
	function reload(sort, order) {
//   window.location.href="/pm/reporting/patientList.php?sort="+sort+"&order="+order;
	}
</script>