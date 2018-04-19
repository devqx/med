<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:23 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
$p_p = (new PatientProcedureDAO())->get($_GET['id']);

$insurance = (new InsuranceDAO())->getInsurance($p_p->getPatient()->getId(), true);
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$bills = new Bills();
$pat = (new PatientDemographDAO())->getPatient($p_p->getPatient()->getId(), false, null, null);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

$status = $selfOwe - $creditLimit > 0 ? "disabled" : "enabled";
?>

<div class="patient_profile_container_" style="width:1200px !important;">
	<div id="profile_container">
		<?php $ARR['patient_ID'] = $p_p->getPatient()->getId();
		?>
		<div class="line">
			<div class="pull-left">
				<a title="Change Photo" href="javascript:void(0);">
					<img class="passport" src="<?= $p_p->getPatient()->getPassportPath(); ?>" width="53"/>
				</a>
			</div>
			<div>
				<h4 class="uppercase"><?= $p_p->getPatient()->getFullname() ?></h4>
				<span class="fadedText" id="pid_"><i class="icon icon-user"></i>
                    <a href="/patient_profile.php?id=<?= $ARR['patient_ID'] ?>"><?= $ARR['patient_ID'] ?> <?= (trim($p_p->getPatient()->getLegacyId()) != "") ? ' (' . $p_p->getPatient()->getLegacyId() . ')' : '' ?></a></span>
			</div>
			<div></div>

			<div class="dropdown pull-right">
				<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
					STATUS: <?= strtoupper($p_p->getStatus()) ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
					<?php if (in_array($p_p->getStatus(), ['open'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="schedule">Schedule</a></li><?php } ?>
					<?php if (in_array($p_p->getStatus(), ['scheduled'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="reschedule">Re-Schedule</a></li><?php } ?>
					<?php if (in_array($p_p->getStatus(), ['scheduled', 'open'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="start">Start</a></li><?php } ?>
					<?php if (in_array($p_p->getStatus(), ['started'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="conclude">Conclude</a></li><?php } ?>
					<?php if (!in_array($p_p->getStatus(), ['closed', 'started', 'cancelled'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="cancel">Cancel</a></li><?php } ?>
					<?php if (in_array($p_p->getStatus(), ['cancelled'])) { ?>
						<li><a href="javascript:" data-id="<?= $p_p->getId() ?>" data-action="reorder">Re-Order</a></li><?php } ?>
				</ul>
			</div>

			<!--<button class="hide drop-btn pull-right" onclick="changeStatus()"><i class="icon-adjust"></i> &nbsp;</button>
			<div class="pull-right"></div>-->
			<!--<button class="btn pull-right" disabled title="modify this request"><i class="icon-edit"></i></button>-->

		</div>
		<div class="line">
			<div class="item_block">
				<span>DOB</span>
				<span><?= date("d M, Y", strtotime($p_p->getPatient()->getDateOfBirth())) ?></span>
			</div>
			<div class="item_block">
				<span>Insurance Status</span>
				<span <?= !$insurance->getActive() ? 'class="abnormal" title="Insurance is not active"' : '' ?>><?= strtoupper($insurance->getScheme()->getName()) . " (<em>" . strtoupper($insurance->getScheme()->getType()) . "</em>)" ?></span>
			</div>
		</div>
		<div class="line">
			<div class="item_block">
				<span>Request #</span>
				<span><?= $p_p->getRequestCode() ?></span>
			</div>
			<div class="item_block">
				<span>Request Date</span>
				<span><?= date("d M, Y h:i A", strtotime($p_p->getRequestDate())) ?></span>
			</div>
			<div class="item_block">
				<span>Request Note</span>
				<span><?= $p_p->getRequestNote() ?></span>
			</div>
			<div class="item_block">
				<span>Request By</span>
				<span><?= $p_p->getRequestedBy()->getFullname() ?></span>
			</div>
			<div class="item_block">
				<span>Status</span>
				<span><?= ucwords($p_p->getStatus()) ?></span>
			</div>
		</div>
		<div class="line">
			<div class="item_block">
				<span>Diagnoses</span>
				<span>
					<?php if (count($p_p->getConditions()) == 0) { ?>
						N/A
					<?php } ?>
					
					<?php foreach ($p_p->getConditions() as $con) { ?>
						<span class="tag"><?= $con->getName() ?></span>
					<?php } ?>
				</span>
			</div>
			<div class="item_block">
				<span>Procedure</span>
				<span><?= $p_p->getProcedure()->getName() ?></span>
			</div>


		</div>
		<div class="line">
			<div class="pull-right"><?php if ($p_p->getHasAnesthesiologist()) {
					//should have anesthesiologist
					if (is_null($p_p->getAnesthesiologist())) {
						//show button to assign anesthesiologist
						?>
						<button class="action" onclick="setAnesthesiologist(<?= $p_p->getId() ?>)"><i class="icon-edit"></i>
							Anesthesiologist
						</button>
						<?php
					} else {
						//show the assigned anesthesiologist name
						?>
						<button class="action">Anesthesiologist: <i class="icon-stethoscope"></i></button>
						<?php
					}
				} ?>
			</div>
			<div class="pull-right"><?php if ($p_p->getHasSurgeon()) {
					//should have anesthesiologist
					if (is_null($p_p->getSurgeon())) {
						//show button to assign surgeon
						?>
						<button class="action" onclick="setSurgeon(<?= $p_p->getId() ?>)"><i class="icon-edit"></i> Surgeon</button>
						<?php
					} else {
						//show the assigned surgeon name
						?>
						<button class="action"> Surgeon: <i class="icon-heart"></i></button>
						<?php
					}
				} ?>
			</div>
			<div class="pull-right">
				<?php if (is_null($p_p->getTheatre())) { ?>
				<button class="action" onclick="setTheatre(<?= $p_p->getId() ?>)">Set Theatre</button><?php } else { ?>
					<button class="action"><i class="icon-hospital"></i> Theatre: <?= $p_p->getTheatre()->getName() ?>
					</button> <?php } ?>
			</div>
            <div class="pull-right">
                <?php if(!$p_p->getBilled()) { ?>
                    <button  class="btn btn-small1" onclick="charge(<?= $p_p->getId() ?>)">Bill Procedure</button>
                 <?php } ?>
            </div>
		</div>
	</div>
	<div id="tab-container" class="tab-container">
		<ul>
			<li><a href="/procedures/tabs/notes.php?id=<?= $p_p->getId() ?>" data-target="#notes">Notes/Findings</a></li>
			<li><a href="/procedures/tabs/pre-proc-list.php?id=<?= $p_p->getId() ?>" data-target="#pre-procs">Pre-Procedure
					List</a></li>
			<li><a href="/procedures/tabs/resources.php?id=<?= $p_p->getId() ?>" data-target="#resources">Resources</a></li>
			<li><a href="/procedures/tabs/attachments.php?id=<?= $p_p->getId() ?>" data-target="#attachments">Attachments</a></li>
			<li><a href="/procedures/tabs/materials.php?id=<?= $p_p->getId() ?>" data-target="#materials">Consumables</a></li>
			<li><a href="/procedures/tabs/nursing_services.php?id=<?= $p_p->getId() ?>" data-target="#nursing_tasks">Nursing Services</a></li>
			<li><a href="/procedures/tabs/regimens.php?id=<?= $p_p->getId() ?>" data-target="#regimens">Medications used</a></li>

			<!--            <li><a href="/procedures/tabs/tasks.php?id=<?= $p_p->getId() ?>" data-target="#tasks">Risks Assessment</a></li>-->
			<li><a href="/procedures/tabs/reports.php?id=<?= $p_p->getId() ?>" data-target="#reports">Medical Report</a></li>
			<li><a href="/billing/patient_bill_doc.php?id=<?= $p_p->getPatient()->getId() ?>" data-target="#billing">Billing</a></li>

		</ul>
		<div id="notes"></div>
		<div id="resources"></div>
		<div id="attachments"></div>
		<div id="materials"></div>
		<div id="nursing_tasks"></div>
		<div id="regimens"></div>
		<div id="tasks"></div>
		<div id="reports"></div>
		<div id="pre-procs"></div>
		<div id="billing"></div>
	</div>
</div>
<script>
	$("#tab-container").easytabs({
		animate: false,
		tabsClass: "nav nav-tabs",
		tabClass: "tabClass",
		updateHash: false,
		cache: false
	});

	function setTheatre(id) {
		Boxy.warn("This function has been deprecated.<br>Please use the 'Schedule' feature");
		<?php /*
		Boxy.load('/procedures/dialogs/assign_specialty_staff.php?type=t&id=' + id, {
			afterHide: function () {
				reloadProcedure(id);
			}
		}); */?>
	}
	function setSurgeon(id) {
		Boxy.load('/procedures/dialogs/assign_specialty_staff.php?type=s&id=' + id, {
			afterHide: function () {
				reloadProcedure(id);
			}
		});
	}
	function setAnesthesiologist(id) {
		Boxy.load('/procedures/dialogs/assign_specialty_staff.php?type=a&id=' + id, {
			afterHide: function () {
				reloadProcedure(id);
			}
		});
	}

	function charge(id) {
        $.post('/api/procedure_action.php', {status: "charge", id:id}, function (s) {
            var result = s.split(":");
            if (result[0] === "error") {
                Boxy.alert(result[1]);
            } else if (result[0] === "success") {
                reloadProcedure(<?=$p_p->getId()?>);

            }
        });
    }

	function changeStatus() {
		var status = '<?=$p_p->getStatus()?>';
		var question = "What do you want to do?";
		if (status === 'open') {
			//if open, start it or cancel it, or
			Boxy.ask(question, [{
				"label": "Start",
				"state": "<?=$status; ?>",
				"title": "Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>"
			}, {
				"label": "Conclude",
				"state": "<?=$status; ?>",
				"title": "Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>"
			}, {
				"label": "Cancel Procedure",
				"state": "",
				"title": "Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>"
			}, " x "], function (choice) {
				if (choice.label === "Start") {
					$.post('/api/procedure_action.php', {status: "start", id: "<?=$p_p->getId()?>"}, function (s) {
						var result = s.split(":");
						if (result[0] === "error") {
							Boxy.alert(result[1]);
						} else if (result[0] === "success") {
							reloadProcedure(<?=$p_p->getId()?>);
							//Boxy.info(result[1]);
						}
					});
				} else if (choice.label === "Conclude") {
					Boxy.load('/procedures/dialogs/concluding_info.php?id=<?=$p_p->getId()?>');
				} else if (choice.label === "Cancel Procedure") {
					$.post('/api/procedure_action.php', {status: "cancel", id: "<?=$p_p->getId()?>"}, function (s) {
						var data = s.split(":");
						if (data[0] === "error") {
							Boxy.alert(data[1]);
						} else if (data[0] === "success") {
							//Boxy.info(data[1]);
							reloadProcedure(<?=$p_p->getId()?>);
						}
					});
				}
			});
		} else if (status === "started") {
			//we can close it. can we cancel it?
			Boxy.ask(question, [{
				"label": "Conclude",
				"state": "<?=$status; ?>",
				"title": "Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>"
			}, " x "], function (choice) {
				if (choice.label === "Conclude") {
					$.post('/api/procedure_action.php', {status: "close", id: "<?=$p_p->getId()?>"}, function (s) {
						var data = s.split(":");
						if (data[0] === "error") {
							Boxy.alert(data[1]);
						} else if (data[0] === "success") {
							//Boxy.info(data[1]);
							reloadProcedure(<?=$p_p->getId()?>);
						}
					});
				}
			});
		}
	}

	function reloadProcedure(id) {
		// if in patient module, you should reload the boxy containing the document
		// let's just close the boxy. it's simpler that way bcos we're still in context
		if($(".close").length > 0) {
			setTimeout(function () {
				Boxy.get($(".close")).hideAndUnload(function () {
					Boxy.load('/procedures/tabs/home.php?id=' + id);
				});
			}, 50);
		}
	}
</script>