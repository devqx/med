<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/14/14
 * Time: 4:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
?>

<?php if (count($pScans) > 0) { ?>
	<table class="table scantable table-striped">
		<thead>
		<tr>
			<th class="hide">sort time</th>
			<th>Request Date</th>
			<th>RQ #</th>
			<?php if (!isset($_GET['pid'])) { ?>
				<th>Patient</th><?php } ?>
			<th>Requester</th>
			<th>Type</th>
			<th>Attachment?</th>
			<th>Approved</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($pScans as $ps) {
			//$ps = new PatientScan();
			if ($ps->getPatient()) {
				?>
				<tr id="_sc_an_tr_<?= $ps->getId() ?>">
					<td class="hide"><?= strtotime($ps->getRequestDate()) ?></td>
					<td nowrap>
						<time datetime="<?= strtotime($ps->getRequestDate()) ?>" title="<?= strtotime($ps->getRequestDate()) ?>"><?= date(MainConfig::$dateTimeFormat, strtotime($ps->getRequestDate())) ?></time>
					</td>
					<td>
						<a data-title="<?= $ps->getRequestCode() . ": " . htmlentities($ps->getScan()->getName()) ?>" class="boxy" href="javascript:;" data-href="/imaging/scan.details.php?id=<?= $ps->getId() ?>"><?= $ps->getRequestCode() ?></a>
					</td>
					<?php if (!isset($_GET['pid'])) { ?>
						<td nowrap="nowrap">
						<?= $ps->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?>
						<a class="profile" data-pid="<?= $ps->getPatient()->getId(); ?>" target="_blank" data-title="<?= $ps->getPatient()->getFullname(); ?>" href="/patient_profile.php?id=<?= $ps->getPatient()->getId() ?>"><?= $ps->getPatient()->getShortname(); ?></a>
						</td><?php } ?>
					<td>
						<?= ($ps->getReferral() !== null) ? '<span title="Referred from ' . $ps->getReferral()->getName() . ' (' . $ps->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						<span title="<?= $ps->getRequestedBy()->getFullname() ?>"><?= $ps->getRequestedBy()->getUsername() ?></span>
					</td>
					<td><?= $ps->getScan()->getName() ?>
					</td>
					<td><?php foreach ($ps->getAttachments() as $attach) { ?>
						<a target="_blank" href="<?= $attach->getAttachmentURL() ?>"><i class="icon-paper-clip"></i>View
							</a><?php } ?>
					</td>
					<td>
						<?= ($ps->getApproved() && !$ps->getCancelled()) ? "Yes" : (!$ps->getCancelled() ? "Pending" : "Canceled") ?>
					</td>
					<td nowrap>
						
						<div class="dropdown">
							<button class="drop-btn dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
							<ul class="img dropdown-menu" role="menu" aria-labelledby="dLabel"><?php if ($ps->getCancelled()) { ?>
									<li><a href="javascript:" class="reOrderLink_" data-id="<?= $ps->getId() ?>">Re-Order</a></li><?php } ?>
								<?php if (!$ps->getCaptured() && !$ps->getCancelled() && !$ps->getApproved()) { ?>
									<li><a class="captured-link" href="javascript:" data-id="<?= $ps->getId() ?>">Captured?</a></li><?php } ?>
								<?php if($ps->getResource() == null && !$ps->getStatus() && !$ps->getCancelled() && !$ps->getCaptured()){?>
									<li><a class="schedule" href="javascript:;" data-id="<?= $ps->getId() ?>">Schedule</a></li><?php }?>
								<?php if ($ps->getApproved()) { ?>
									<li><a href="javascript:" class="printNotes" data-page-id="<?= $ps->getId() ?>">Print</a></li><?php } else {
									if ($ps->getStatus() && !$ps->getCancelled()) {
									} else if (!$ps->getCancelled()) { ?><?php if (!isset($_GET['pid'])) { ?>
										<li><a href="javascript:" class="submitToApprove" data-id="<?= $ps->getId() ?>" style="white-space: nowrap;">Submit for Approval</a></li> <?php } ?>
										<?php if (!$ps->getCaptured()) { ?>
										<li><a href="javascript:" class="cancelRequest" data-id="<?= $ps->getId() ?>">Cancel</a></li><?php } ?><?php }
								} ?>
							</ul>
						</div>

					</td>
				</tr>
			<?php }
		} ?>
	</table>

<?php } else { ?>
	<div class="notify-bar">Nothing found to display at the moment</div>
<?php } ?>

<script type="text/javascript">
	$(document).on("click", ".cancelRequest", function (e) {
		var request_id = $(this).data('id');
		if (!e.handled) {
			Boxy.ask("Cancel Request?", ['Yes', 'No'], function (response) {
				if (response === "Yes") {
					$.post("/api/cancel_image_request.php", {id: request_id}, function (data) {
						if (data) {
							try {
								$('a[data-href="to_fulfil.php"]').click();
							} catch (except) {
							}

							try {
								<?php if(isset($_GET['aid'])){?>showTabs(8);
								<?php } else {?>showTabs(11);
								<?php }?>
							} catch (except) {
							}

						} else {
							Boxy.alert("Failed to process request.");
						}
					}, 'json');
				}
			});
			e.handled = true;
		}
	}).on('click', '.reOrderLink_', function (e) {
		var id = $(this).data("id");
		if (!e.handled) {
			Boxy.ask("This will recreate the imaging request. Are you sure you want to continue?", ["Yes", "No"], function (choice) {
				if (choice === "Yes") {
					$.post('/api/re-order-imaging.php', {id: id, action: "re-order"}, function (s) {
						if (s.trim() === "ok") {
							showTabs(11);
						} else {
							Boxy.alert("An error occurred");
						}
					});
				}
			});
			e.handled = true;
		}
	})
		.on('click', '.captured-link', function (e) {
			var $id = $(this).data("id");
			if (!e.handled) {
				Boxy.ask('Are you sure this request has been captured in the scan room?', ['Yes', 'No'], function (answer) {
					if (answer === 'Yes') {
						$.ajax({
							url: "/api/ajax.captured.php",
							data: {id: $id},
							type: "post",
							beforeSend: function () {
							},
							success: function (s) {
								if ($('#scanHomeMenuLinks a.tab.on')[0] !== undefined) {
									setTimeout(function () {
										$('#scanHomeMenuLinks a.tab.on')[0].click();
									}, 10);
								}

								//location.reload();
							}, error: function () {
								Boxy.alert("Capture failed");
							}
						});
					}
				});
				e.handled = true;
			}
		})
		.on('click', '.submitToApprove', function (e) {
			if (!e.handled) {
				var scanId = $(this).data('id');
				Boxy.ask("Submit for approval?", ["Yes", "No"], function (choice) {
					if (choice === "Yes") {
						$.post('/imaging/ajax.approve_.php', {id: scanId}, function (s) {
							if (s.trim() === "ok") {
								Boxy.info("Scan request sent for approval");
								$('#scanHomeMenuLinks a.approve').click();
							} else if(s.trim() === "error1") {
								Boxy.warn("No note found to approve");
							}
							else {
								Boxy.alert("An error occurred");
							}
						});
					}
					else {
					}
				});
				e.handled = true;
			}
		}).on('click', '.schedule', function (e) {
			if (!e.handled) {
				var scanId = $(this).data('id');
				Boxy.load('/imaging/schedule_request.php?id='+scanId);
				e.handled = true;
			}
		}).on('click', '.printNotes', function (e) {
		if (!e.handled) {
			window.open('/imaging/printNotes.php?id=' + $(this).data("page-id"));
			e.handled = true;
		}
	});
</script>
