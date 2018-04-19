<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InPatientDAO.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$ip = isset($_GET['aid']) ? (new InPatientDAO())->getInPatient($_GET['aid']) : null;


if (  /*($ip != null && $ip->getStatus()=='Active') && isset($_GET['aid']) && */ ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->pharmacy))) { ?>
	<div class="menu-head"><span id="newLink">
        <a href="javascript:void(0)" onClick="Boxy.load('/boxy.addRegimen.php?id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?><?= isset($_GET['ivf']) ? '&ivf=' . $_GET['ivf'] : '' ?>'  ,{title: 'New Regimen'})">New Regimen</a></span>
	</div>
<?php }

if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy)) exit ($protect->ACCESS_DENIED);
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDAO.php";

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$temp = (new PrescriptionDAO())->getPatientPrescriptions($_GET['id'], $page, $pageSize, TRUE);
$pps = $temp->data;
$totalSearch = $temp->total;
?>
<div class="dataTables_wrapper">
	<?php
	if (sizeof($pps) > 0) {
		?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>ID</th>
				<th>Drug/Generic</th>
				<th>By</th>
				<th>Note</th>
				<th nowrap>Action</th>
			</tr>
			</thead>
			<tbody>

			<?php
			foreach ($pps as $i => $pp ) { // $pp=new Prescription();
				?>
				<tr class="head-link" id="<?= $pp->group_code ?>">
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($pp->when)) ?></td>
					<td><a href="javascript:;" class="code1" data-note="<?= urlencode($pp->note) ?>"><?= $pp->group_code ?></a></td>
					<td><?= $pp->drug_name ?> (<?= trim($pp->generic)?> <?= $pp->weight?>)</td>
					<td><?= $pp->username ?></td>
					<td><span class="fadedText"><?= $pp->note ?></span></td>
					<td nowrap>
						<?php if (!$pp->external) { ?>
							<a class="_p_action btn btn-small1" href="javascript:void(0)" <?= (bool)$pp->refillable && $pp->status=='filled' && !is_null($pp->refill_date) ? '':' disabled' ?> data-action="refill" data-id="<?= $pp->id ?>" title="Refill this prescription">Refill <?=(bool)$pp->refillable ? '('. $pp->refill_number .')':''?></a>
							<?php if ($pp->service_centre_id != null) { ?>
								<a class="_p_action btn btn-small1" data-action="transfer" href="javascript:;" data-id="<?= $pp->group_code ?>" <?=  $pp->status!=='open' ? ' disabled':''?>>Transfer</a><?php } ?>
						<?php } ?>
						<a class="btn btn-small1" href="/pharmaceuticals/print.prescription.php?grouped=true&pcode=<?= $pp->group_code ?>&note=<?= urlencode($pp->note) ?>" target="_blank" title="Print this prescription">Print</a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } else {
		$pat = (new PatientDemographDAO())->getPatientMin($_REQUEST['id']);
		echo '<div class="notify-bar">' . $pat->getFullname() . ' does not have any recorded regimen</div>';
	} ?>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
		results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
	</div>
	<div class="resultsPager no-footer dataTables_paginate">
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
				records</a>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.resultsPager.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "/prescriptionDetails.php?id=<?=$_GET['id']?>&page=" + page;
			$('#contentPane').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	});
	$(document).ready(function () {
		$('a._p_action').click(function (e) {
			var action = $(this).data("action");
			var item_id = $(this).data("id");
			if (action == "refill") {
				refillPrescription(item_id);
			} else if (action == "transfer") {
				Boxy.load('/pharmaceuticals/boxy_transferPrescription.php?pCode=' + item_id, {title: 'Transfer Prescription'});
			}
			e.preventDefault();
		});

		$('.head-link .code1').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/boxy.prescriptionDetails.php?id=' + $(this).parent().parent().attr('id') + '&note=' + $(this).data("note"), {title: 'Prescription Details'});
				e.handled = true;
				e.preventDefault();
			}
		});
	});

	function refillPrescription(id) {
		Boxy.ask("Are you sure you want to refill this prescription line?", ['Yes', 'No'], function (choice) {
			if(choice=='Yes'){
				$.ajax({
					url: '/api/regimens.php',
					data: 'action=refill&id=' + id,
					type: 'GET',
					complete: function (xhr, status) {
						var x = xhr.responseText.split(":");
						console.log(xhr.responseText);
						if (x[0] == "success" && x[1] == "true") {
							Boxy.info("Prescription refilled");
						}
						else {
							Boxy.alert(x[1]);
						}
						showTabs(3);
					}
				});
			}
		});
	}
</script>