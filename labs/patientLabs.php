<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/20/14
 * Time: 4:25 PM
 */
$id = $_GET['id'];
error_log("paateint id".json_encode($_GET['id']));
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) {
	exit($protect->ACCESS_DENIED);
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$temp = (new LabGroupDAO())->getPatientLabGroups($id, $page, $pageSize, true);
$pLabs = $temp->data;
$totalSearch = $temp->total;
$ip = isset($_GET['aid']) ? (new InPatientDAO())->getInPatient($_GET['aid']) : null;

?>

<?php if ( /*($ip != null && $ip->getStatus() == 'Active' && isset($_GET['aid'])) && garki */ ($this_user->hasRole($protect->lab) || $this_user->hasRole($protect->lab_super) || $this_user->hasRole($protect->doctor_role))) { ?>
	<div class="menu-head">
	<span id="newLink"> <a href="javascript:void(0)" onClick="Boxy.load('/labs/allLabs.php?id=<?= $_GET['id'] ?><?= (isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '') ?>',{title: 'Lab Request', afterHide: function() {<?php if(isset($_GET['ivf'])){?>$('#tabbedPane li.active > a').click();<?php } else {?>showTabs(6);<?php } ?> }})">Request Lab</a></span>
	</div><?php } ?>
<div class="dataTables_wrapper">
	<?php
	if (count($pLabs) > 0) { ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Request date</th>
				<th>Lab Request ID</th>
				<th>Service Center</th>
				<th>Requested by</th>
				<th>Request Specimen</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($pLabs as $lg) { ?>
				<tr class="head-link" id="lab<?= $lg->getGroupName() ?>" data-group="<?= $lg->getGroupName() ?>">
					<td><?= date("d/m/Y g:iA", strtotime($lg->getRequestTime())) ?></td>
					<td><?= $lg->getGroupName() ?></td>
					<td><?= $lg->getServiceCentre() ? $lg->getServiceCentre()->getName() : "" ?></td>
					<td><?= $lg->getRequestedBy()->getFullname() ?></td>
					<td><?php
						$s__ = array();
						foreach ($lg->getPreferredSpecimens() as $s) {
							$s__[] = $s->getName();
						}
						echo implode(", ", $s__)
						?></td>
					<td>
						<a href="/labs/printLab.php?gid=<?= $lg->getGroupName() ?>&id=<?=$id?>" class="btn_ btn-small1 _p_action" title="Print this result" target="_blank" data-action="print">Print Group</a>
					</td>
				</tr>
				<tr>
					<td colspan="5" data-group="<?= $lg->getGroupName() ?>" style="display: none;">
						<table class="table table-striped table-bordered">
							<tr class="fadedText">
								<th>Test Type</th>
								<th>Test Result</th>
								<th>Range</th>
								<th>Tested By</th>
								<th>Specimen</th>
								<th>Notes</th>
							</tr>
							<?php foreach ($lg->getRequestData() as $pl) {//$pl=new PatientLab()?>
								<tr>
									<td><?= $pl->getTest()->getName() ?> | <a href="javascript:" data-patient-id="<?= $pl->getLabGroup()->getPatient()->getId() ?>" data-lab-id="<?= $pl->getTest()->getId() ?>" class="trend_lab" title="Trend <?= $pl->getTest()->getName() ?>"><i class="fa fa-line-chart"></i> Trend</a>
									</td>
									<td><?php if ($pl->getLabResult() !== null && ($pl->getLabResult()->isApproved() || $this_user->hasRole($protect->doctor_role))) { ?>
											<a href='javascript:;' onclick="new Boxy('<div><?= ($pl->getLabResult() !== null && !$pl->getLabResult()->isApproved() && $this_user->hasRole($protect->doctor_role)) ? '<div class=\\\'alert-error\\\'><i class=\\\'icon-exclamation-sign\\\'></i> Not yet approved</div>' : '' ?></div><div><table class=\'table table-striped table-bordered table-hover\'>'
												+ '<tr><td>Field</td><td>Value</td></tr>'
											<?php foreach ($pl->getLabResult()->getData() as $data) { ?>
												+'<tr><td><?= htmlspecialchars($data->getLabTemplateData()->getMethod()->getName()) ?><?php if($data->getLabTemplateData()->getReference()!=""){?><br>(Reference: <?= htmlspecialchars($data->getLabTemplateData()->getReference()) ?>)<?php }?></td><td><?= htmlspecialchars(escape($data->getValue()), ENT_QUOTES) ?></td></tr>'
											<?php } ?>
											<?php if ($pl->getLabResult()->getAbnormalValue()) { ?>
												+ '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
											<?php }
											if ($this_user->hasRole($protect->lab_super_user)) { ?>
												+ '<tr><td colspan=\'2\'><a href=\'javascript:;\' onclick=\'disapproveLabResult(<?= $data->getLabResult()->getId() ?>)\'>Reset</a></td></tr>'
											<?php } ?>
												+ '</table></div>', {title: '<?= $pl->getTest()->getName() ?> Result'}
												)" title="View this result">Result</a> <?= ($pl->getLabResult() !== null && !$pl->getLabResult()->isApproved() && $this_user->hasRole($protect->doctor_role)) ? '<i class="icon-exclamation-sign abnormal"></i> Not yet approved' : '' ?>
											<?php
										} else if ($pl->getLabResult() !== null && !($pl->getLabResult()->isApproved())) {
											echo 'Waiting Approval';
										} else {
											echo 'Not Ready';
										}
										?>
									</td>

									<td><?= $pl->getTest()->getReference() ?></td>
									<td><?= ($pl->getPerformedBy() !== null) ? $pl->getPerformedBy()->getFullname() : 'N/A' ?></td>
									<td><?php
										if (count($pl->getSpecimens()) < 1) {
											echo 'N/A';
										} else {
											$s_ = array();
											foreach ($pl->getSpecimens() as $s) {
												$s_[] = $s->getName();
											}
											echo implode(", ", $s_);
										}
										?></td>
									<td>
										<?php if ($pl->getStatus() == "open" && empty($pl->getSpecimens()) /* && euracare lag empty($pl->getValue())*/) { ?>
											<a href="javascript:;" title="cancel request item" class="btn_ cancelLabLink btn-mini_" data-id="<?= $pl->getId() ?>">Cancel</a>
										<?php } else if ($pl->getStatus() == "cancelled") { ?>
											<a href="javascript:" class="reOrderLabLink" data-id="<?= $pl->getId() ?>">Re-Order</a>
										<?php } ?>
										| <a href="javascript:void(0)" onClick="Boxy.load('/labs/lab.notes.php?id=<?= $pl->getId() ?>')">
											View Notes</a> |
										<a target="_blank" href="/labs/printLabSingle.php?gid=<?= $lg->getGroupName() ?>&mode=single&lid=<?= $pl->getId() ?>&id=<?= $id ?>">
											Print</a>
										
									</td>
								</tr>
							
							<?php } ?>
						</table>
					</td>
				</tr>
			<?php }
			?>
		</table>
	<?php } else { ?>
		<div class="notify-bar">Nothing found to display at the moment</div>
	<?php } ?>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
		results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
	</div>
	<div class="resultsPagerLabs no-footer dataTables_paginate">
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
	$(document).on('click', '.resultsPagerLabs.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "/labs/patientLabs.php?id=<?=$_GET['id']?>&page=" + page;
			$('#contentPane').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	}).ready(function () {
		$('a.cancelLabLink').live('click', function (e) {
			var id = $(this).data("id");
			if (!e.handled) {
				Boxy.ask("Are you sure you want to CANCEL this request line item?", ["Yes", "No"], function (choice) {
					if (choice === "Yes") {
						$.post('/api/labrequests.php', {id: id, action: "cancel"}, function (s) {
							if (s.trim() === "ok") {
								//remove this cancel button, also remove the take specimen link
								//$('a.cancelLabLink[data-id="'+id+'"]').next('a:contains("Take specimen")').remove();
								//$('a.cancelLabLink[data-id="'+id+'"]').remove();
								showTabs(6);
							} else {
								Boxy.alert("An error occurred");
							}
						});
					}
				});
				e.handled = true;
			}
		});
		$('a.reOrderLabLink').live('click', function (e) {
			var id = $(this).data("id");
			if (!e.handled) {
				Boxy.ask("This will create a new lab request. Are you sure you want to continue?", ["Yes", "No"], function (choice) {
					if (choice === "Yes") {
						$.post('/api/labrequests.php', {id: id, action: "re-order"}, function (s) {
							if (s.trim() === "ok") {
								showTabs(6);
							} else {
								Boxy.alert("An error occurred");
							}
						});
					}
				});
				e.handled = true;
			}
		});

		$('.trend_lab').live('click', function (e) {
			if (!e.handled) {
				var labId = $(this).data().labId;
				var patientId = $(this).data().patientId;
				$.post('/labs/trend-lab.php', {lab: labId, patient: patientId}, function (data) {
					if (data.status === 'success') {
						var parsedData = _.values(data.message);
						//console.log(parsedData);
						runit(parsedData);

					} else if (data.status === 'error') {
						Boxy.warn(data.message);
					}
				}, 'json');
				//Boxy.load("/labs/trend-results.php");
				e.handled = true;
			}
		});

	});
	$("tr[data-group]").click(function (e) {
		$("td[data-group='" + $(this).attr("data-group") + "']").toggle()
	});

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

	function runit(data) {
		/**source
		 * http://www.encodedna.com/javascript/populate-json-data-to-html-table-using-javascript.htm
		 */
		var col = [];
		for (var i = 0; i < data.length; i++) {
			for (var key in data[i]) {
				if (col.indexOf(key) === -1) {
					col.push(key);
				}
			}
		}
		// CREATE DYNAMIC TABLE.
		var table = document.createElement("table");
		table.className = 'table table-bordered table-hover';
		// CREATE HTML TABLE HEADER ROW USING THE EXTRACTED HEADERS ABOVE.
		var tr = table.insertRow(-1);
		// TABLE ROW.

		for (var i = 0; i < col.length; i++) {
			var th = document.createElement("th");// TABLE HEADER.
			th.className = 'header';
			th.setAttribute('nowrap', 'nowrap');
			th.innerHTML = col[i];
			tr.appendChild(th);
		}

		// ADD JSON DATA TO THE TABLE AS ROWS.
		for (var i = 0; i < data.length; i++) {
			tr = table.insertRow(-1);
			for (var j = 0; j < col.length; j++) {
				var tabCell = tr.insertCell(-1);
				tabCell.innerHTML = data[i][col[j]] !== undefined ? data[i][col[j]] : '---';
			}
		}
		// FINALLY ADD THE NEWLY CREATED TABLE WITH JSON DATA TO A CONTAINER.
		new Boxy('<div style="width:500px">'+$(table).prop('outerHTML')+'</div>');
	}

</script>