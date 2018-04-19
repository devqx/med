<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if (is_blank($_POST['q']) && is_blank($_POST['patient_id'])) {
	exit('<div class="warning-bar">Empty search parameter</div>');
}
//ob_clean();

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$patient = (!is_blank($_POST['patient_id'])) ? $_POST['patient_id'] : null;
$pageSize = 10;
$data = (new PatientLabDAO())->findLabRequests2($_POST['q'], $patient, $page, $pageSize, TRUE);

$totalSearch = $data->total;

if ($totalSearch < 1) {
	?>
	<div class="notify-bar">No search results for <?= $_POST['q'] ?></div>
	<?php
} else {
	?>
	<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Results found</div>
	<table class="table table-striped table-hover no-footer">
		<thead>
		<tr>
			<th>Request Date</th>
			<th class="hide"></th>
			<th>ID</th>
			<th>Lab</th>
			<th>Preferred Specimen(s)</th>
			<th>Patient</th>
			<th>Result</th>
			<th>Note</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data->data as $labs) {//$labs=new PatientLab();
			if ($labs->getPatient()) {
				$specimen = '';
				if (count($labs->getLabGroup()->getPreferredSpecimens()) > 0) {
					$s_ = array();
					foreach ($labs->getLabGroup()->getPreferredSpecimens() as $spc) {
						$s_[] = $spc->getName();
					}
					$specimen .= implode(", ", $s_);
				} else {
					$specimen .= 'N/A';
				}
				?>
				<tr>
					<td class="nowrap"><?= date("d M, Y h:iA", strtotime($labs->getLabGroup()->getRequestTime())) ?></td>
					<td class="hide"><?= strtotime($labs->getLabGroup()->getRequestTime()) ?></td>
					<td><?= $labs->getLabGroup()->getGroupName() ?></td>
					<td><?= $labs->getTest()->getName() ?></td>
					<td><?= $specimen ?></td>
					<td nowrap>
						<?= ($labs->getLabGroup()->getReferral() !== null) ? '<span title="Referred from ' . $labs->getLabGroup()->getReferral()->getName() . '(' . $labs->getLabGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						
						<?= $labs->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?> <span class="profile" data-pid="<?= $labs->getPatient()->getId() ?>" xtitle="<?= $labs->getPatient()->getFullname() ?>"><?= $labs->getPatient()->getShortname() ?></span>
					</td>
					<td class="nowrap"><?php if ($labs->getStatus() == "open" && empty($labs->getValue())) { ?>
							<a href="javascript:;" class="cancelLabLink" data-id="<?= $labs->getId() ?>">Cancel</a> |
						<?php } ?>
						<?php if (empty($labs->getSpecimens()) && $labs->getStatus() == "open") { ?>
							<a href="javascript:void(0)" onclick="Boxy.load('/labs/boxy.collectSpecimen.php?id=<?= $labs->getId() ?>&aid=<?= $labs->getPatient()->isAdmitted() ?>&testid=<?= $labs->getTest()->getId() ?>&testgroupid=<?= $labs->getLabGroup()->getGroupName() ?>', {title: 'Take Specimen for <?= escape($labs->getTest()->getName()) ?>', afterHide: function () {
								//location.reload();
								reloadThisPage();
								}})">Take specimen</a>
						<?php }
						if ($labs->getLabResult() === null && !empty($labs->getSpecimens()) && $labs->getReceived() === FALSE) { ?>
							<a href="javascript:void(0)" class="receiveLab" data-id="<?= $labs->getId() ?>" data-lab="<?= $specimen ?>">Receive
								specimen</a>
						<?php } else if ($labs->getLabResult() === null && !empty($labs->getSpecimens()) && $labs->getReceived() === TRUE) { ?>
							<a href="javascript:void(0)" onclick="Boxy.load('/labs/boxy.fillTestResult.php?testId=<?= $labs->getId() ?>&testType=<?= $labs->getTest()->getId() ?>', {title: 'Fill Result for <?= escape($labs->getTest()->getName()) ?>', afterHide: function () {
								reloadThisPage();
								//                                location.reload();
								}})">Fill Result</a>
						<?php } else if ($labs->getLabResult() !== null && !$labs->getLabResult()->isApproved()) { ?>
							Waiting Approval
						<?php } else if ($labs->getLabResult() !== null && $labs->getLabResult()->isApproved()) { ?>
							<a href='javascript:;' onclick="new Boxy('<div><table class=\'table table-striped table-bordered table-hover\'>'
								+ '<tr><th>Field</th><th>Value</th></tr>'
							<?php foreach ($labs->getLabResult()->getData() as $data) { ?>
								+'<tr><td><?= htmlentities($data->getLabTemplateData()->getMethod()->getName()) ?></td><td><?= htmlentities(escape($data->getValue())) ?></td></tr>'
							<?php }
							if ($labs->getLabResult()->getAbnormalValue()) { ?>
								+ '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
							<?php }
							if ($this_user->hasRole($protect->lab_super_user)) { ?>
								+ '<tr><td colspan=\'2\'><a href=\'javascript:;\' onclick=\'disapproveLabResult(<?= $labs->getLabResult()->getId() ?>)\'>Reset</a></td></tr>'
							<?php } ?>
								+ '</table></div>', {title: '<?= $labs->getTest()->getName() ?> Result'}
								)" title="View this result">Result</a>
						<?php } ?>
					</td>
					<td>
						<a onclick="Boxy.load('/labs/lab.notes.php?id=<?= $labs->getId() ?>', {title: 'Lab Notes'})" href="javascript:void(0)">Notes</a>
					</td>
					<td class="nowrap">
						<a href="/labs/printLab.php?gid=<?= $labs->getLabGroup()->getGroupName() ?>&id=<?= $labs->getPatient()->getId() ?>" class="action _p_action" title="Print this result" target="_blank" data-action="print">PrintGroup</a>
						<a href="/labs/printLabSingle.php?gid=<?= $labs->getLabGroup()->getGroupName() ?>&mode=single&id=<?= $labs->getPatient()->getId() ?>&lid=<?= $labs->getId() ?>" class="action _p_action" title="Print this result" target="_blank" data-action="print">Print Single</a>
						<?php if ($labs->getLabResult() !== null && !$labs->getLabResult()->isApproved()) { ?>
							<a href="javascript:" onclick="editLabResult(<?= $labs->getTest()->getId() ?>, <?= $labs->getId() ?>, '<?= $labs->getTest()->getName() ?>')" class="action" title="Edit Result">
								<i class="icon-edit"></i></a><?php } ?>
					</td>
				</tr>
			<?php }
		} ?>
		</tbody>

	</table>
	<div class="resultsPager dataTables_wrapper no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
			results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>

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
	<script type="text/javascript">
		function editLabResult(testId, plId, testName) {
			Boxy.load('/labs/editResult.php?testId=' + testId + '&plId=' + plId, {title: testName});
		}

		reloadThisPage = function () {
			var page = '#DataTables_Table_1_next';
			var sortDate = ($('th.sort').hasClass('asc')) ? 'asc' : 'desc';
			$.post('ajax.findlab.php', {
				'q': '<?=$_POST['q'] ?>',
				'page': eval($(page).data("page") - 1),
				'lab_centre_id': $('select[name="lab_centre_id"]').val(),
				'lab_category_id': $('select[name="lab_category_id"]').val(),
				'sort': sortDate,
				'patient_id': $('input[name="patient_id"]').val()
			}, function (s) {
				$('#searchBox').html(s);
			});
		};

		function disapproveLabResult(id) {
			Boxy.confirm("Are you sure to reset this result", function () {
				$.ajax({
					url: '/labs/result.action.php',
					data: {id: id, action: 'disapprove'},
					type: 'post',
					dataType: 'json',
					success: function (data) {
						if (data.status === "ok") {
							Boxy.get($('.close')).hideAndUnload();
							reloadThisPage();
						} else {
							Boxy.alert(data.message);
						}
					},
					error: function () {
						Boxy.alert('Failed to reset result. A server error occurred');
					}
				});
			});
		}
	</script>

<?php } ?>