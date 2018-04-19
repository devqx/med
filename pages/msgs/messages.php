<?php
$timeStart = microtime(true);
if (!isset($_SESSION)) {
	session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';

$depts = (new DepartmentDAO())->getDepartments();
$specs = (new StaffSpecializationDAO())->getSpecializations();
$sub_ts = (new PatientQueueDAO())->getSubTypes();

$specializations = [];
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/search_specializaton.php';


if (isset($_GET['type'])) { ?>
	<p xmlns="http://www.w3.org/1999/html"></p>
	<div class="document">
		<?php
		if ($_GET['type'] == 'queue') {
			$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
			$pageSize =10;
			
			$ts = getTypeOptions('type', 'patient_queue');
			
			if (isset($_POST['type'])) {
				$_SESSION['type'] = $_POST['type'];
			}
			if (isset($_POST['dept'])) {
				$_SESSION['dept'] = $_POST['dept'];
			}
			if (isset($_POST['specialty']) && $_POST['type'] == "Doctors") {
				$_SESSION['specialty'] = $_POST['specialty'];
			}
			if (@$_POST['type'] != "Doctors") {
				unset($_SESSION['specialty']);
			}
			if (isset($_POST['patient_id'])) {
				$_SESSION['patient_id'] = $_POST['patient_id'];
			}
			if (isset($_POST['sub_type']) && $_POST['sub_type'] !== "_" && $_POST['type'] == "Imaging") {
				$_SESSION['sub_type'] = $_POST['sub_type'];
			}
			
			$view = isset($_POST['seen']) ? ['Attended'] : ['Active', 'Blocked'];
			$queues = (new PatientQueueDAO())->getPatientQueueFiltered(date("Y-m-d"), date("Y-m-d"), [@$_SESSION['type']], $view, true, $page, $pageSize, @$_SESSION['dept'], @$_SESSION['specialty'], @$_SESSION['patient_id'], @$_SESSION['sub_type']); ?>

			<div class="ui-bar-c" style="margin-bottom: 5px; overflow: hidden">
				<label<?php if (@$_SESSION['type'] !== 'Doctors' && !isset($_POST['seen'])){ ?> class="hide"<?php }?> id="alreadySeen"><input type="checkbox"> View Already Seen Patients</label>
				<span class="pull-left">Patients on Queue</span>
				<select name="dept" id="changeDept" class="pull-left">
					<option value="_">All Departments</option>
					<?php foreach ($depts as $dept) { ?>
						<option value="<?= $dept->getId() ?>" <?= (isset($_SESSION['dept']) && $_SESSION['dept'] == $dept->getId()) ? ' selected="selected"' : '' ?>><?= $dept->getName() ?></option>
					<?php } ?>
				</select>
				<select name="docstype" multiple <?php if (@$_SESSION['type'] !== 'Doctors'){ ?>class="hide"<?php } ?> style="float: right;margin-left:20px;" id="changeDocQue" data-placeholder="Select specialization">
					<!--<option value="_">All Doctors</option>-->
					<?php foreach ($specs as $sp) { ?>
						<option value="<?= $sp->getId() ?>" <?= (isset($_SESSION['specialty']) && $_SESSION['specialty'] == $sp->getId() ? ' selected' : '') ?> ><?= $sp->getName() ?></option>
					<?php } ?>
				</select>
				<select name="subtype" style="float: right; margin-left:20px;" <?php if (@$_SESSION['type'] !== 'Imaging'){ ?>class="hide"<?php } ?> id='changeSubQue' placeholder="All Sub Queue">
					<option value="_">All Sub Queue</option>
					<?php foreach ($sub_ts as $t) { ?>
						<option value="<?= $t ?>"<?= @$_SESSION['sub_type'] == $t ? ' selected' : '' ?>><?= $t ?></option>
					<?php } ?>
				</select>
				<select name="type" style="float: right" id='changeQue'>
					<option value="_">All Queue</option>
					<?php foreach ($ts as $t) { ?>
						<option value="<?= $t ?>"<?= (@$_SESSION['type'] == $t ? ' selected' : '') ?>><?= $t ?></option>
					<?php } ?>
				</select></div>
			<div id="queueTableContainer">

				<input type="text" class="wide" name="patient_id" value="<?= (isset($_SESSION['patient_id']) ? $_SESSION['patient_id'] : '') ?>">

				<p></p>

				<table class="table queue table-hover table-striped ">
					<thead>
					<tr>
						<th><i class="icon-info-sign" title="Check this box to remove this item from queue when clicked"></i>#</th>
						<th>Tag No.</th>
						<th>Patient</th>
						<th>*</th>
						<th>Coverage</th>
						<th>To see:</th>
						<th>Specialty</th>
						<th class="hide">Department</th>
						<th>Since:</th>
						<th>Blocked by</th>
						<th>*</th>
					</tr>
					</thead>
					<tbody>
					<?php
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
					$bills = new Bills();
					$totalSearch = $queues->total;
					foreach ($queues->data as $q) {//$q=new PatientQueue();
						if ($q->PatientActive) {
							//$pat = (new PatientDemographDAO())->getPatient($q->PatientId, TRUE, NULL, NULL);
							$badge = $q->BadgeIcon;
							$_ = $q->Outstanding;
							$selfOwe = $_ > 0 ? $_ : 0;
							?>
							<tr
							<?php if ($q->Triaged) { ?> class="triaged" title="Triaged"<?php }?>
								data-type="<?= $q->Type ?>_"
								data-subtype="<?= (($q->SubType == '') ? '' : $q->SubType . '_') ?>"
								data-dept="<?= ($q->DepartmentId != null) ? $q->DepartmentName : '' ?>">
								<td><input type="checkbox" class="doItBox" id="qItem<?= $q->Id ?>" data-id="<?= $q->Id ?>"></td>
								<td nowrap><label class="inline" for="qItem<?= $q->Id ?>"><?= $q->TagNo ?></label> <?php if($q->Type == 'Doctors'){?> <button class="action btn-mini" type="button" data-id="<?= $q->Id ?>"><i class="icon icon-print"></i></button><?php }?>

								</td>
								<td nowrap>

									<a id="q<?= $q->Id ?>"
									   style="padding-left:5px"<?php if ($q->Type == "Billing" || $q->Type == "Nursing" || $q->Type == "Antenatal" || !$selfOwe > 0) { ?>
										href="/queue.php?qid=<?= $q->Id ?>&pid=<?= $q->PatientId ?>&type=<?= $q->Type ?>&fText=<?= urlencode($q->PatientName) ?>" <?php } else { ?> class="muted" title="Patient has outstanding balance of <?= $selfOwe ?>" <?php } ?>>
										<?= $q->PatientName ?></a>
								</td>
								<td><?= ($badge) ? html_entity_decode($badge) : '' ?></td>
								<td><?= $q->Coverage ?></td>
								<td class="fadedText"><?= $q->Type ?> <?= (($q->SubType != '') ? ' - ' . $q->SubType : '') ?></td>
								<td><?= ($q->Specialization != null ? $q->Specialization : ' - ') ?></td>
								<td class="hide"><?= (!is_null($q->DepartmentId)) ? $q->DepartmentName : '' ?></td>
								<td data-since="true"
								    data-time="<?= $q->EntryTime ?>">
									<?= date(MainConfig::$dateTimeFormat, strtotime($q->EntryTime)) ?>
								</td>
								<td>
									<?= ($q->BlockedBy !== '' ? $q->BlockedBy : '- -') ?>
								</td>
								<td><?= $q->Review ? '<span class="badge" title="Investigation Review">R</span>':'' ?><?= $q->FollowUp ? '<span class="badge" title="Follow up visit">F</span>' :''?></td>
							</tr>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
				<?php $execTime = microtime(true) - $timeStart; ?>
				<div class="resultsPager dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
					     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
						of <?= ceil($totalSearch / $pageSize) ?>) <em class="fadedText"><?= sprintf("%0.2f", $execTime) ?>s</em>
					</div>

					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				var page;
				$(document).on('click', '.resultsPager.dataTables_wrapper a.paginate_button', function (e) {
					if (!e.clicked) {
						page = $(this).data("page");
						if (!$(this).hasClass("disabled")) {
							changeQue(page);
						}
						e.clicked = true;
					}
				});
			</script>
		<?php }
		else if ($_GET['type'] == 'appointmentlist') {
		$page = (isset($_GET['page'])) ? $_GET['page'] : 0;
		$pageSize = 100;
		$today = date("Y-m-d", time());
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
		$patient = @$_POST['patient_id'];
		$apps = (new AppointmentDAO())->getAppointmentByDateSlim($today, $today, ['active', 'scheduled'], [], true, $page, $pageSize, $patient);
		$totalSearch = $apps->total;
		?>

			<div class="ui-bar-c " style="overflow:hidden">
				<div class="row-fluid">
					<div class="span2">
						Active Appointments
					</div>
					<div class="span2">
						<a href="/appointments">Appointments Home</a>
					</div>
					<div class="span2">
						<a href="javascript:" onclick="Boxy.load($(this).data('href'), {title: 'New Appointment'})" data-href="/appointments/boxy_createAppointment.php">New Appointment</a>
					</div>
					<div class="span6">
						<select name="type" style="float: right; font-size:14px" id="changeApp" disabled>
							<option value="_">Today's Appointments</option>
							<option value="Active">Attended To</option>
							<option value="Scheduled">Not Attended To</option>
						</select>
					</div>
				</div>
			</div>
			<p></p>
		<input type="text" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
			<p></p>
			<div id="appoPatientsListContainer">

				<table class="table table-hover table-striped " id="appoPatientsList">
					<thead>
					<tr>
						<th>#</th>
						<th>Start Date / Time</th>
						<th>Patient</th>
						<th>Coverage</th>
						<th>Clinic</th>
						<th>&nbsp;</th>
					</tr>
					</thead>
					<?php if ($totalSearch >= 1) {
						foreach ($apps->data as $i => $apt) {
							
							if ($apt->group_id && $apt->patient_id) {
								$pat = (new PatientDemographDAO())->getPatient($apt->patient_id, false, null, null); ?>
								<tr data-type="<?= $apt->status ?>_">
									<td><?= (($page * $pageSize + $i) + 1) ?></td>
									<td><?= date(MainConfig::$dateTimeFormat, strtotime($apt->start_time)) ?></td>
									<td><a href="/patient_profile.php?id=<?= $apt->patient_id ?>" target="_blank"><?= $pat->getFullname() ?></a></td>
									<td><?= $pat->getScheme()->getName() ?></td>
									<td><?= $apt->clinic_name ?></td>
									<td nowrap>
										<?php if($apt->clinicType === 'Doctors'){?>
										<?= (($apt->status === 'Scheduled') ? '<i class="icon-check"></i><a href="javascript:;" onclick="checkIn(this)" data-pid="' . $apt->patient_id . '" data-id="' . $apt->id . '">Check In</a>' : '<i class="icon-time"></i><span class="fadedText"> Already Checked In</span>') ?>
									<?php } else {?>
											<?= (($apt->status === 'Scheduled') ? '<i class="icon-check"></i><a href="javascript:;" onclick="checkInQ(this)" data-type="'.$apt->clinicType.'" data-pid="' . $apt->patient_id . '" data-id="' . $apt->id . '">Check In</a>' : '<i class="icon-time"></i><span class="fadedText"> Already Checked In</span>') ?>
									<?php }?>
									</td>
								</tr>
							<?php }
						}
					} else { ?>
						<tr>
							<td colspan="6">
								<div class="alert-box notice">No active appointment for today</div>
							</td>
						</tr>
					<?php } ?>
				</table>

				<div class="appointmentsPager dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
					     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
						of <?= ceil($totalSearch / $pageSize) ?>)
					</div>

					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>

						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>
			</div>
		
		<?php }
		else if ($_GET['type'] == 'secured') {
			echo '<h5 class="menu-head">Secured Mails</h5>No secured mails available at this time';
		}
		else if ($_GET['type'] == 'notifications') {
			echo '<h5 class="menu-head">Notifications</h5>There are no notifications at this time';
		}
		else if ($_GET['type'] == 'aqueue') {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ApprovedQueueDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$ts = getTypeOptions('type', 'approved_queue');
			$filter = (isset($_REQUEST['f_'])) ? $_REQUEST['f_'] : '';
			$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
			$pageSize = 10;
			$totalSearch = 0;
			$patientID = !is_blank(@$_REQUEST['patient_id']) ? @$_REQUEST['patient_id'] : null;
			$data = (new ApprovedQueueDAO())->allUnread($filter, $page, $pageSize, $patientID);
			$totalSearch = $data->total;
			
			$aqueues = $data->data;
			?>
			<div class="ui-bar-c" style="overflow:hidden"><span class="pull-left">Approved Items Queue</span>
				<select name="type" style="float: right; font-size:smaller" id='changeAQue'>
					<option value="">All Queue</option>
					<?php foreach ($ts as $t) {
						echo '<option value="' . $t . '">' . $t . '</option>';
					} ?>
				</select></div>
			<p></p>
		<input type="text" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
			<div id="approved_queue_report_container">
				<div class="dataTables_wrapper">
					<table class="table table-striped table-hover no-footer aqueue ">
						<colgroup>
							<col>
							<col>
							<col>
							<col>
							<col>
						</colgroup>
						<thead>
						<tr>
							<th>Patient / Tag No.</th>
							<th>Coverage</th>
							<th>Category:</th>
							<th>Request ID:</th>
							<th>Time Approved:</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ($aqueues as $i => $q) {
							$requestId = '';
							if ($q->getType() == 'Lab') {
								$requestId .= $q->getRequest()->getLabGroup()->getGroupName();
							} else if ($q->getType() == 'Imaging') {
								$requestId .= $q->getRequest()->getRequestCode();
							} else if ($q->getType() == 'Ophthalmology') {
								$requestId .= $q->getRequest()->getOphthalmologyGroup()->getGroupName();
							} else if (is_dir("dentistry") && $q->getType() == 'Dentistry') {
								$requestId .= $q->getRequest()->getRequestCode();
							} ?>
							<tr>
								<td nowrap><img class="profile_thumbnail" alt="img"
								                src="<?= $q->getPatient()->getPassportPath() ?>"/><a
										data-id="<?= $q->getId() ?>" class="readApproved" style="padding-left:5px"
										href="/patient_profile.php?id=<?= $q->getPatient()->getId() ?>&qtype=<?= strtolower($q->getType()) ?>"><?= $q->getPatient()->getFullname() ?></a>
								</td>
								<td><?= $q->getPatient()->getScheme()->getName() ?></td>
								<td><?= $q->getType() ?></td>
								<td><?= $requestId ?></td>
								<td><?= date('jS M, Y h:iA', strtotime($q->getApprovedTime())) ?></td>
							</tr>
						<?php }
						?>
						</tbody>
					</table>
				</div>
				<div class="list1 dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
					     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
						of <?= ceil($totalSearch / $pageSize) ?>)
					</div>
					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>
			</div>
		<?php }
		else if ($_GET['type'] == "referral") {
			$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
			$pageSize = 10;
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralsQueueDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			$patientId = !is_blank(@$_REQUEST['patient_id']) ? @$_REQUEST['patient_id'] : null;
			$specializationId = !is_blank(@$_REQUEST['specialization_id']) ? @$_REQUEST['specialization_id'] : null;
			$data = (new ReferralsQueueDAO())->filter($patientId, false, $specializationId, $page, $pageSize);
			$totalSearch = $data->total;
			?>
			<h5>Referrals List</h5>
			<div class="grow">
				<span class="span6">
				<input type="text" name="specialization_id" value="<?= (isset($_REQUEST['specialization_id']) ? $_REQUEST['specialization_id'] : '') ?>">
			</span>
				<span class="span6">
					<input type="text" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>"></span>
			
			</div>
			<p></p>
			<div id="referral_list_container">
				<table class="table table-striped">
					<thead>
					<tr>
						<th>Date</th>
						<th>Patient</th>
						<th>Specialization</th>
						<th>External?</th>
						<th>Acknowledged</th>
						<th>*</th>
					</tr>
					</thead>
					<?php foreach ($data->data as $item) {//$item=new ReferralsQueue();?>
						<tr>
							<td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getWhen())) ?></td>
							<td><a href="/patient_profile.php?id=<?= $item->getPatient()->getId() ?>" target="_blank"><?= $item->getPatient()->getFullname() ?></a></td>
							<td><?= $item->getSpecialization() ? $item->getSpecialization()->getName() : '- -' ?></td>
							<td><?= $item->getExternal() ? 'YES' : 'NO' ?></td>
							<td><?= $item->getAcknowledged() ? 'YES' : 'NO' ?></td>
							<td><?= truncate($item->getNote(), 50, true) ?></td>
							<td nowrap="nowrap">
								<?php if (!$item->getAcknowledged()) { ?>

									<div class="dropdown pull-right">
										<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
											Action
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
											<li>
												<?php if(!$item->getExternal()) { ?>
												<a title="Schedule patient to see a doctor now" class="referral_check_in" href="javascript:" data-pid="<?= $item->getPatient()->getId() ?>" data-id="<?= $item->getId() ?>">Book Appointment</a>
												<?php } ?>
												  <?php } ?>
											</li>
											<li>
												<a class="referral_details" href="javascript:" data-id="<?= $item->getId() ?>">Details</a>
											</li>
												<li>
													<a href="javascript:" class="referral_dismiss" data-id="<?= $item->getId() ?>">Dismiss</a>
												</li>
											
													<li>
														<a target="_blank" href="/patient_referrals_print.php?id=<?= $item->getId() ?>">Print</a>
													</li>
											
										</ul>
									</div>
									
								
								</td>
						</tr>
					<?php } ?>
				</table>
				<div class="referralList dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
					     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
						of <?= ceil($totalSearch / $pageSize) ?>)
					</div>
					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>
			</div>
		<!-- Signature Queue Section Begins -->
		<?php }
		else if ($_GET['type'] == "signature") {
			$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
			$pageSize = 10;
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SignatureDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
			$patientId = !is_blank(@$_REQUEST['patient_id']) ? @$_REQUEST['patient_id'] : null;
			$data = (new SignatureDAO())->filter($patientId, $page, $pageSize);
			$totalSearch = $data->total;
			?>
			<h5>Signature List</h5>
			<input type="text" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
			<p></p>
			<style type="text/css">
				.grow { transition: all .2s ease-in-out; height:20px }
				.grow:hover { transform: scale(10); background: #FEFEFE;
					box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
					background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
					opacity: 1;
				}
			</style>
			<div id="signatureListContainer">
				<table class="table table-striped">
					<thead>
					<tr>
						<th>Date</th>
						<th>Patient</th>
						<th>Active</th>
						<th>Preview</th>
						<th>*</th>
					</tr>
					</thead>
					<?php foreach ($data->data as $item) { ?>
						<tr>
							<td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getDate())) ?></td>
							<td><a href="/patient_profile.php?id=<?= $item->getPatient()->getId() ?>" target="_blank"><?= $item->getPatient()->getFullname() ?></a></td>
							<td><?= (bool)$item->getActive() ? 'Yes' : 'No' ?></td>
							<td><img class="grow" src="data:image/png;base64,<?=base64_encode( $item->getBlob() )?>"></td>
							<td nowrap="nowrap">
								<a class="delete_signature" href="javascript:" data-id="<?= $item->getId() ?>">Delete</a>

						</tr>
					<?php } ?>
				</table>

				<div class="signatureList dataTables_wrapper no-footer">
					<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
					     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
						of <?= ceil($totalSearch / $pageSize) ?>)
					</div>
					<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
						<a id="DataTables_Table_1_first" data-page="0"
						   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
						   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
							records</a>
						<a id="DataTables_Table_1_last"
						   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
						<a id="DataTables_Table_1_next"
						   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
						   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
					</div>
				</div>


			</div>

		<?php }?>

		<!-- Signature queue section End-->

		</div>
<?php } else { ?>
	View <a href="?type=secured">Secured Mails</a>, <a href="?type=notifications">Notifications</a>, <a
		href="?type=queue">Patients on Queue</a>, <a href="?type=aqueue">Approved items on Queue</a>
<?php } ?>

<script type="text/javascript">
	
	var specializations = <?= json_encode($specializations, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	$(".print_patient-referr").click(function (e) {

	});

	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get('/pages/msgs/messages.php?type=aqueue&f_=<?= @$filter ?>&page=' + page, function (s) {
					$('#approved_queue_report_container').html($(s).find('#approved_queue_report_container').html());
				});
			}
			e.clicked = true;
		}
	}).on('click', '.referralList.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				var $patientId = $('input[name="patient_id"]').val();
				var $specializationId = $('input[name="specialization_id"]').val();
				$.post('/pages/msgs/messages.php?type=referral', {page: page, patient_id: $patientId, specialization_id: $specializationId }, function (s) {
					$('#referral_list_container').html($(s).find('#referral_list_container').html());
				});
			}
			e.clicked = true;
		}
	}).on('click', '.referral_dismiss', function (e) {
		if (!e.clicked) {
			Boxy.ask('This item will no longer show on this list.<br> However it can still be accessed from the <br>patient\'s encounter tab. <br>Continue?<br>', ['Yes', 'Not now'], function (answer) {
				if (answer === 'Yes') {
					$.post('/api/patient_referrals_action.php', {id: $(e.target).data('id'), action: 'dismiss'}, function (response) {
						if (response) {
							var page = 0;
							var $patientId = $('input[name="patient_id"]').val();
							$.post('/pages/msgs/messages.php?type=referral', {page: page, patient_id: $patientId}, function (s) {
								$('#referral_list_container').html($(s).find('#referral_list_container').html());
							});
						} else {
							Boxy.warn('Action failed!');
						}
					}, 'json');
				}
			});
			e.clicked = true;
		}
	}).on('click', '.referral_details', function (e) {
		if (!e.clicked) {
			Boxy.load('/patient_referrals_detail.php?id='+$(e.target).data('id'));
			e.clicked = true;
		}
	}).on('click', '.referral_check_in', function (e) {
		if (!e.clicked) {
			$.post('/api/patient_referrals_action.php', {id: $(e.target).data('id'), action: 'check_in'}, function (response) {
				if (!_.includes([null, false], response)) {
					Boxy.load('/appointments/boxy_createAppointment.php?pid=' + response, {
						afterHide: function () {
							var page = 0;
							var $patientId = $('input[name="patient_id"]').val();
							$.post('/pages/msgs/messages.php?type=referral', {page: page, patient_id: $patientId}, function (s) {
								$('#referral_list_container').html($(s).find('#referral_list_container').html());
							});
						}
					});
				} else {
					Boxy.warn('Action failed!');
				}
			}, 'json');
			e.clicked = true;
		}
	});

	$(document).on('click', '.appointmentsPager.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get('/pages/msgs/messages.php?type=appointmentlist&page=' + page, function (s) {
					$('#appoPatientsListContainer').html($(s).find('#appoPatientsListContainer').html());
					//setPatient();
				});
			}
			e.clicked = true;
		}
	});

	$(document).on('click', '.signatureList.dataTables_wrapper a.paginate_button', function (d) {
		if(!d.clicked){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled")){
				$.get('/pages/msgs/messages.php?type=signature&page=' + page, function(a){
					$('#signatureListContainer').html($(a).find('#signatureListContainer').html());
				});
			}
		}
	}).on('click', '.delete_signature', function(e){
		var id = $(this).data('id');
		if (!e.handled){
			Boxy.ask('Are you sure you want to delete this signature?', ['Yes', 'No'], function(choice){
				if(choice==='Yes'){
					$.post('/api/remove_signature.php', {d: id}, function(s){
						if(s){
							$.get('/pages/msgs/messages.php?type=signature&page=' + 0, function(a){
								$('#signatureListContainer').html($(a).find('#signatureListContainer').html());
							});
						}
					}, 'json' );
				}
			});
			e.handled = true;
		}
	});

	$(document).ready(function () {
		$('.doItBox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});

		$(document).on('change', '.doItBox', function () {
			var q = $('#q' + $(this).data("id"));
			var href = q.attr("href");
			if (typeof href !== "undefined") {
				if ($(this).is(":checked")) {
					q.attr("href", href + "&remove=true");
				} else {
					q.attr("href", href.replace("&remove=true", ""));
				}
			}
		});

		$(document).on('click', '.readApproved', function (e) {
			if (!e.handled) {
				var id = $(this).data('id');
				$.post('/ajax.readApproved.php', {q: id});
				e.handled = true;
				return true;
			}
		});

		setTimeout(function () {
			setPatient('<?= $_GET['type']?>');
		}, 0);
		setInterval(function () {
			changeQue(0);
		}, 120000);
		//reload this page every 120 seconds = 2 minutes

		$("#changeApp").select2({
			width: '200px'
		}).change(function () {

		});
		$("#changeQue").select2({
			width: '200px'
		}).change(function () {
			$('#alreadySeen').addClass('hide');
			if ($(this).val() === 'Imaging') {
				$("#s2id_changeDocQue").addClass('hide');
				$("#s2id_changeSubQue").removeClass('hide');
				setTimeout(function () {
				}, 0);
			}
			else if ($(this).val() === 'Doctors') {
				$('#alreadySeen').removeClass('hide');
				$("#s2id_changeSubQue").addClass('hide');
				$("#s2id_changeDocQue").removeClass('hide');
				setTimeout(function () {
				}, 0);
			}
			else {
				$("#s2id_changeSubQue").addClass('hide');
				$("#s2id_changeDocQue").addClass('hide');
			}
			setTimeout(function () {
			}, 2);
			changeQue(0);
		});

		$("#changeSubQue").select2({
			width: '200px'
		}).change(function () {
			changeQue(0);
		});
		$("#changeAQue").select2({
			width: '200px'
		}).change(function () {
			$.get('/pages/msgs/messages.php?type=aqueue&page=0&f_=' + $(this).val(), function (s) {
				$('#approved_queue_report_container').html($(s).find('#approved_queue_report_container').html());
				setPatient('<?= $_GET['type']?>');
			});
		});

		$("#changeDocQue").select2({
			width: '350px'
		}).change(function () {
			changeQue(0);
		});

		$("#changeDept").select2({
			width: '300px'
		}).change(function () {
			changeQue(0);
		});

		$('#alreadySeen input:checkbox').live('change', function(e){
			changeQue(0);
		})
	});
	
	function checkIn(obj) {
		Boxy.load('/boxy.selectDepartment.php?pid=' + $(obj).data("pid") + '&qid=' + $(obj).data("id"), {title: 'Select Department'});
	}
	
	function checkInQ(obj) {
		if($(obj).data('type')) {
			Boxy.ask('The patient would be placed in the '+$(obj).data('type')+' queue', ['Continue', 'No'], function(answer){
				if(answer === 'Continue'){
					$.post('/check_in.php', {pid: $(obj).data("pid"), qid: $(obj).data("id"), type:$(obj).data('type')}, function (s) {
						if (s.status) {
							location.reload()
						} else {
							Boxy.alert("Failed to check in patient");
						}
					}, 'json').error(function(y) {
						console.log(y);
						if(_.isPlainObject(y)){
							var msg = y.responseText.split(":")[1];
							Boxy.alert(msg);
						} else {
							Boxy.alert("Sorry, failed to check the patient in; <br>You can try to check the patient in from the <a href='/messaging/menu_up.php?type=appointmentlist'>appointment</a> page");
						}
					});
				}
			})
		} else {
			Boxy.warn('The clinic configuration is incomplete');
		}
	}

	function changeQue(page) {
		var type = $("#changeQue").val();
		var dept = $("#changeDept").val();
		var specialist = $("#changeDocQue").val();
		var patient = $('[name="patient_id"]').val();
		var imageType = $("#changeSubQue").val();
		var specialization = $('[name="specialization_id"]').val();
		
		var postVars = {
			page: page,
			<?php if($_GET['type'] == 'queue'){?> type: type,
			dept: dept,
			specialty: specialist,<?php }?>
			patient_id: patient,
			sub_type: imageType,
			specialization_id: specialization,
		};
		<?php if($_GET['type'] == 'queue'){?>
		if(type !== 'Doctors'){
			$('#alreadySeen input:checkbox').prop('checked', false).iCheck('update');
		}
		if($('#alreadySeen input:checkbox').is(':checked')){
			postVars['seen'] = true;
		}
		<?php }?>
		
		$.post('/messaging/menu_up.php?type=<?=$_GET['type']?>', postVars, function (s) {
			setTimeout(function () {
				setPatient('<?= $_GET['type']?>');
			}, 0);
			<?php if($_GET['type']=='queue'){?>$('#queueTableContainer').html($(s).find('#queueTableContainer').html());
			$('.doItBox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
				$(event.currentTarget).trigger('change');
			});
			<?php }?>
			<?php if($_GET['type']=='appointmentlist'){?>$('#appoPatientsListContainer').html($(s).find('#appoPatientsListContainer').html());<?php }?>
			<?php if($_GET['type']=='referral'){?>$('#referral_list_container').html($(s).find('#referral_list_container').html());<?php }?>
			<?php if($_GET['type']=='aqueue'){?>$('#approved_queue_report_container').html($(s).find('#approved_queue_report_container').html());<?php }?>
		});
	}

	function setPatient(type) {
		var url, data;
		if (type === 'queue') {
			url = "/api/search_queue_patients.php";
			data = function (term, page) {
				return {
					q: term,
					type: $("#changeQue").val(),
					dept: $("#changeDept").val(),
					specialty: $("#changeDocQue").val()
				}
			};
		} else if (type === 'appointmentlist') {
			//url = "/api/search_appointments_patients.php";
			url = "/api/search_patients.php";
			data = function (term, page) {
				return {
					q: term
				}
			};
		} else if (type === 'aqueue') {
			//url = "/api/search_approved_queue_patients.php";
			url = "/api/search_patients.php";
			data = function (term, page) {
				return {
					q: term
				}
			};
		} else {
			url = "/api/search_patients.php";
			data = function (term, page) {
				return {
					q: term
				}
			};
		}

		$('[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: url,
				dataType: 'json',
				data: data,
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		}).change(function (e) {
			if (!e.handled) {
				changeQue(0);
				e.handled = true;
			}
		});
	}
	

	$('[name="specialization_id"]').select2({
		width: '100%',
		allowClear: true,
		placeholder: "Filter by specialization",
		data: {results: specializations, text: 'name'},
		formatResult: function (source) {
			return source.name;
		},
		formatSelection: function (source) {
			return source.name;
		},

	}).change(function (e) {
	  changeQue(0);
	});

	$(window).bind('beforeunload', function () {
	});
	$('button[type="button"].action[data-id]').live('click', function (e) {
		var $this = this;
		if(!e.handled){
			Boxy.load('/q/boxy_print_tag.php?id='+$($this).data('id'));
			e.handled=true;
		}
	})
</script>