<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$lab_centres = (new ServiceCenterDAO())->all('Lab');
$lab_categories = (new LabCategoryDAO())->getLabCategories();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff(@$_SESSION['staffID']);
if (isset($_GET['incomplete'])) {
	$lab_centre = isset($_POST['lab_centre_id']) ? $_POST['lab_centre_id'] : null;
	$is_Admitted = isset($_POST['under_admission']) ? $_POST['under_admission'] : null;
	$lab_category = isset($_POST['lab_category_id']) ? $_POST['lab_category_id'] : null;
	$patient = (!is_blank(@$_SESSION['pid']) ? @$_SESSION['pid'] : !is_blank(@$_POST['patient_id']) ? @$_POST['patient_id']: null );
	$page = isset($_POST['page']) ? $_POST['page'] : 0;
	$pageSize = 10;
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'asc';
	$data = (new PatientLabDAO())->getLabWithoutResult($page, $pageSize, $sort, $lab_centre, $lab_category, TRUE, $patient, $is_Admitted);
	$totalSearch = $data->total;
	?>
	<div class="row-fluid">
		<label class="span3">Search open labs by patient emr
			<input type="hidden" name="patient_id" value="<?= $patient ?>">
		</label>
		<label class="span3">Business Unit/Service Center
			<select name="lab_centre_id" data-placeholder="-- Select processing laboratory --">
				<option></option>
				<?php foreach ($lab_centres as $l) { ?>
					<option value="<?= $l->getId() ?>" <?= (isset($_POST['lab_centre_id']) && $_POST['lab_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
			</select></label>

		<label class="span3">Laboratory Categories
			<select name="lab_category_id" data-placeholder="-- Select laboratory category --">
				<option></option><?php foreach ($lab_categories as $lc) { ?>
					<option value="<?= $lc->getId() ?>"<?= isset($_POST['lab_category_id']) && $_POST['lab_category_id'] === $lc->getId() ? ' selected="selected"' : '' ?>><?= $lc->getName() ?></option><?php } ?>
			</select></label>
		<label class="span3">Patients on admission
			<select name="under_admission" data-placeholder="-- Filter List by Admission Status --">
				<option></option>
				<option value="1" <?= isset($_POST['under_admission']) && $_POST['under_admission'] == 1 ? 'selected="selected"': '' ?> >Patient on admission</option>
			</select>
		</label>
	</div>
	
	<?php if ($totalSearch < 1) { ?>
		<div class="notify-bar">There are no lab requests to fulfil</div>
		<script>
			$('select[name="lab_centre_id"], select[name="lab_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?incomplete', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'patient_id': $('input[name="patient_id"]').val(),
					'pid': $('input[name="pid"]').val(),
					'under_admission': $('select[name="under_admission').val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});
		</script>
	<?php } else {
		?>
		<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Requests</div>
		<table class="table table-striped table-hover no-footer">
			<thead>
			<tr>
				<th class="sort <?= $sort ?>">Request Date <?php if ($sort == 'asc') { ?>
						<i class="icon-sort-up"></i><?php } else { ?><i class="icon-sort-down"></i><?php } ?></th>
				<th>ID</th>
				<th>Lab</th>
				<th>Request specimen(s)</th>
				<th>Patient</th>
				<th>By</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($data->data as $labs ) {
				if ($labs->getPatient() &&  $labs->getLabGroup() != null) {
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
					<tr <?=$labs->getLabGroup()->getUrgent() ? ' class="urgent_request" title="Urgent"':''?>>
						<td class="nowrap"><?= date("d M, Y h:iA", strtotime($labs->getLabGroup()->getRequestTime())) ?></td>
						<td><?= $labs->getLabGroup()->getGroupName() ?></td>
						<td><?= $labs->getTest()->getName() ?></td>
						<td><?= $specimen; ?></td>
						<td>
							<?= $labs->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?> <span class="profile" data-pid="<?= $labs->getPatient()->getId() ?>" xtitle="<?= $labs->getPatient()->getFullname() ?><br>Male"><?= $labs->getPatient()->getShortName() ?></span>
						</td>
						<td>
							<?= ($labs->getLabGroup()->getReferral() !== null) ? '<span title="Referred from ' . $labs->getLabGroup()->getReferral()->getName() . '(' . $labs->getLabGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>

							<span title="<?= $labs->getLabGroup()->getRequestedBy()->getFullname() ?>"><?= $labs->getLabGroup()->getRequestedBy()->getShortName() ?></span>
						</td>
						<td class="nowrap">
							<?php if ($labs->getStatus() == "open" /* && $labs->getReceived() === FALSE */  && empty($labs->getValue())  ) { ?>
								<a href="javascript:;" class="cancelLabLink" data-id="<?= $labs->getId() ?>">Cancel</a> |
							<?php } ?>
							<?php if (empty($labs->getSpecimens()) && $labs->getStatus() == "open") { ?>
								<a href="javascript:void(0)" onclick="Boxy.load('boxy.collectSpecimen.php?id=<?= $labs->getId() ?>&aid=<?= $labs->getPatient()->isAdmitted() ?>&testid=<?= $labs->getTest()->getId() ?>&testgroupid=<?= $labs->getLabGroup()->getGroupName() ?>', {title: 'Take Specimen for <?= escape($labs->getTest()->getName()) ?>', afterHide: function () {
									reloadThisPage();
									}})">Take specimen</a>
							<?php } ?>
							<?php if (!empty($labs->getSpecimens()) && $labs->getReceived() === FALSE) { ?>
								<a href="javascript:void(0)" class="receiveLab" data-id="<?= $labs->getId() ?>" data-lab="<?= $specimen ?>">Receive
									specimen</a>
							<?php } ?>
							<?php if (!empty($labs->getSpecimens()) && $labs->getReceived() === TRUE && empty($labs->getValue())) { ?>
								<a href="javascript:void(0)" onclick="Boxy.load('boxy.fillTestResult.php?testId=<?= $labs->getId() ?>&testType=<?= $labs->getTest()->getId() ?>', {title: 'Fill Result for <?= escape($labs->getTest()->getName()) ?>', afterHide: function () {
									reloadThisPage();
									}})">Fill Result</a>
							<?php } ?>
						</td>
					</tr>
				<?php }
			} ?>
		</table>
		<div class="list1 dataTables_wrapper no-footer">
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
		<script>
			$('select[name="lab_centre_id"], select[name="lab_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?incomplete', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'patient_id': $('select[name="patient_id"]').val(),
					'under_admission': $('select[name="under_admission').val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});

			$('select[name="under_admission"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?incomplete', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'patient_id': $('input[name="patient_id"]').val(),
					'pid': $('input[name="pid"]').val(),
					'under_admission': $(this).val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});
			//reload page function
			reloadThisPage = function () {
				var page = '#DataTables_Table_1_next';
				var sortDate = ($('th.sort').hasClass('asc')) ? 'asc' : 'desc';
				$.post('index.php?incomplete', {
					'page': eval($(page).data("page") - 1),
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'patient_id': $('input[name="patient_id"]').val(),
					'under_admission': $('select[name="under_admission').val(),
					allowClear: true,
					'sort': sortDate
				}, function (s) {
					$('#labTest_container').html(s);
				});
			};
			$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
				if (!e.clicked) {
					var page = $(this).data("page");
					if (!$(this).hasClass("disabled")) {
						$.post('index.php?incomplete', {
							'page': page,
							'lab_centre_id': $('select[name="lab_centre_id"]').val(),
							'lab_category_id': $('select[name="lab_category_id"]').val(),
							'patient_id': $('input[name="patient_id"]').val(),
							'under_admission': $('select[name="under_admission').val()

						}, function (s) {
							$('#labTest_container').html(s);
						});
					}
					e.clicked = true;
				}
			});
			
			$(function () {
				$('th.sort').on('click', function (e) {
					if (!e.clicked) {
						var sortDate = ($(this).hasClass('asc')) ? 'desc' : 'asc';
						$.post('index.php?incomplete', {
							'page': 0,
							'lab_centre_id': $('select[name="lab_centre_id"]').val(),
							'lab_category_id': $('select[name="lab_category_id"]').val(),
							'patient_id': $('select[name="patient_id"]').val(),
							'under_admission': $('select[name="under_admission').val(),
							'sort': sortDate
						}, function (s) {
							$('#labTest_container').html(s);
						});
					}
					e.clicked = true;
				});
			});
			
			$('input[name="patient_id"]').select2({
				placeholder: "Filter List by Patient EMR or Name",
				minimumInputLength: 3,
				width: '100%',
				allowClear: true,
				ajax: {
					url: "/api/search_patients.php",
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term
						}
					},
					results: function (data, page) {
						return {results: data};
					}
				},
				formatResult: function (data) {
					var details = [];
					details.push(data.patientId ? "EMR ID:"+data.patientId : null);
					details.push(data.fname ? data.fname : null);
					details.push(data.mname ? data.mname : null);
					details.push(data.lname ? data.lname : null);
					return implode(" ", details);
					//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
				},
				formatSelection: function (data) {
					var details = [];
					details.push(data.patientId ? "EMR ID:"+data.patientId : null);
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
					$.post('index.php?incomplete', {
						'page': 0,
						'lab_centre_id': $('select[name="lab_centre_id"]').val(),
						'lab_category_id': $('select[name="lab_category_id"]').val(),
						'patient_id':  e.val,
						'under_admission': $('select[name="under_admission').val()
					}, function (s) {
						$('#labTest_container').html(s);
					});
					e.handled = true;
				}
			});
			
		</script>
		<?php
	}
	exit;
}
else if (isset($_GET['search'])) { ?>
	<form method="post" action="ajax.findlab.php" onsubmit="return AIM.submit(this, {'onStart':start, onComplete: loadResult});">
		<div class="row-fluid">
			<label class="span5">Search By Lab Request ID<input type="search" name="q" id="q" class="bigSearchField" placeholder="search lab requests by request id" autocomplete="off"></label>
			<label class="span5">Search By Patient Name / EMR #<input type="hidden" name="patient_id"></label>
			<button type="submit" class="btn span2" style="margin-top: 24px;">Search &raquo;</button>
		</div>
	</form>
	<div id="searchBox"></div>
	<script>
		document.getElementById('q').focus();
		$(document).on('click', '.resultsPager.dataTables_wrapper a.paginate_button', function (e) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('ajax.findlab.php', {'q': $('#q').val(), 'page': page, patient_id: $('input[name="patient_id"]').val()}, function (s) {
					$('#searchBox').html(s);
				});
			}
		});

		$('input[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					}
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
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
				$.post('ajax.findlab.php', {'q': $('#q').val(), 'page': 0, patient_id: $('input[name="patient_id"]').val() }, function (s) {
					$('#searchBox').html(s);
				});
				e.handled = true;
			}
		});
	</script>

	<?php exit;
}
else if (isset($_GET['datesearch'])) {
	if (isset($_POST['date_start']) && $_POST['date_stop']) {
		$start = $_POST['date_start'];
		$stop = $_POST['date_stop'];
	} else {
		$start = date("Y-m-d");
		$stop = date("Y-m-d");
	}
	?>
	<div class="ui-bar-c">
		Filter by date:
		<form method="post" name="dateFilter" action="ajax.findlab_bydate.php" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':loadResult});">
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span2" type="text" name="date_start" value="<?= isset($start) ? $start : '' ?>" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span2" type="text" name="date_stop" value="<?= isset($stop) ? $stop : '' ?>" placeholder="Stop Date">
				<button class="btn" type="submit" id="date_filter">Apply</button>
			</div>
		</form>
	</div>
	<div id="searchBox"></div>
	<script>
		$(document).on('click', '.bydatelist.dataTables_wrapper a.paginate_button', function (e) {
			var page = $(this).data("page");
			var date_start = $('form[name="dateFilter"] > .input-prepend > input[name="date_start"]').val();
			var date_stop = $('form[name="dateFilter"] > .input-prepend > input[name="date_stop"]').val();
			if (!$(this).hasClass("disabled")) {
				$.post('ajax.findlab_bydate.php', {
					'page': page,
					'date_start': date_start,
					'date_stop': date_stop
				}, function (s) {
					$('#searchBox').html(s);
				});
			}
		});
	</script>
	<?php exit;
} else if (isset($_GET['approve']) && $this_user->hasRole($protect->lab_super)) {
	$lab_centre = (isset($_POST['lab_centre_id'])) ? $_POST['lab_centre_id'] : null;
	$lab_category = (isset($_POST['lab_category_id'])) ? $_POST['lab_category_id'] : null;
	$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
	$is_Admitted = isset($_POST['under_admission']) ? $_POST['under_admission'] : null;
	$pageSize = 10;
	$data = (new LabResultDAO())->getUnApprovedLabResultSlim($page, $pageSize, $lab_centre, $lab_category, true, $is_Admitted);
	$totalSearch = $data->total;
	if ($totalSearch > 0) {
		?>
		<div class="row-fluid">
			<label class="span4">Business Unit/Service Center <select name="lab_centre_id" data-placeholder="-- Select processing laboratory --">
					<option></option><?php foreach ($lab_centres as $l) { ?>
						<option value="<?= $l->getId() ?>" <?= (isset($_POST['lab_centre_id']) && $_POST['lab_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
				</select></label>
			<label class="span4">Laboratory
				Categories<select name="lab_category_id" data-placeholder="-- Select laboratory category --">
					<option></option><?php foreach ($lab_categories as $lc) { ?>
						<option value="<?= $lc->getId() ?>"<?= isset($_POST['lab_category_id']) && $_POST['lab_category_id'] === $lc->getId() ? ' selected="selected"' : '' ?>><?= $lc->getName() ?></option><?php } ?>
				</select></label>
			<label class="span4">Patients on admission
				<select name="under_admission" data-placeholder="-- Filter List by Admission Status --">
					<option></option>
					<option value="1" <?= isset($_POST['under_admission']) && $_POST['under_admission'] == 1 ? 'selected="selected"': '' ?> >Patient on admission</option>
				</select>
			</label>
		</div>
		<table class="table table-striped approveList">
			<thead>
			<tr>
				<th class="hide">sort time</th>
				<th>Request Date</th>
				<th>Lab/Group ID</th>
				<th>Test Name</th>
				<th>Patient</th>
				<th>Result</th>
				<th>Notes</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody><?php

			$group = "";
			$groupList = [];
			foreach ($data->data as $l) { //$l=new LabResult();
				?>
				<tr>
					<td class="hide"><?= strtotime($l->time_entered) ?></td>
					<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($l->time_entered)) ?>
						<!--<?= $l->id ?>--></td>
					<td><?= $l->lab_group_id ?></td>
					<td><?= $l->testName ?></td>
					<td nowrap>
						<?php //= ($l->getPatientLab()->getLabGroup()->getReferral() !== null) ? '<span title="Referred from ' . $l->getPatientLab()->getLabGroup()->getReferral()->getName() . '(' . $l->getPatientLab()->getLabGroup()->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						<?= $l->is_admitted ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?>
						<span class="profile" data-pid="<?= $l->patientId ?>" xtitle="<?= $l->patientName ?>"><?= $l->patientName ?></span>
					</td>
					<td>
						<a class="<?= $l->lab_group_id ?>" href="javascript:;" onclick="Boxy.load('/labs/viewLabResults.php?id=<?= $l->id ?>', {title:'<?= escape($l->testName) ?> Result'})">Values</a>
					</td>
					<td>
						<a href="javascript:;" onclick="Boxy.load('/labs/lab.notes.php?id=<?= $l->patient_lab_id ?>', {title: 'Lab Notes'})">Notes</a>
					</td>
					<?php
					if ($group !== $l->lab_group_id) {
						$group = $l->lab_group_id;
						$groupList[count($groupList)] = 1;
						?>
						<td data-group='<?= $group ?>' rowspan="0" style="display: table-cell; vertical-align: middle; text-align: center">
							<a href="/labs/printLab.php?gid=<?= $group ?>&id=<?= $l->patientId ?>" title="Print this result" target="_blank"><i class="icon-print"></i></a>
						</td>
						<?php
					} else {
						$groupList[count($groupList) - 1] = $groupList[count($groupList) - 1] + 1;
					}
					?>
				</tr>
			<?php unset($l); } ?>
			</tbody>
		</table>
		<div class="list3 dataTables_wrapper no-footer">
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
		<script>
			$('select[name="lab_centre_id"], select[name="lab_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?approve', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'pid': $('input[name="pid"]').val(),
					'under_admission': $('select[name="under_admission').val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});

			$('select[name="under_admission"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?approve', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'pid': $('input[name="pid"]').val(),
					'under_admission': $(this).val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});
			
			$(document).on('click', '.list3.dataTables_wrapper a.paginate_button', function (e) {
				if (!e.handled) {
					var page = $(this).data("page");
					if (!$(this).hasClass("disabled")) {
						$.post('index.php?approve', {
							'page': page,
							'lab_centre_id': $('select[name="lab_centre_id"]').val(),
							'lab_category_id': $('select[name="lab_category_id"]').val(),
							'under_admission': $('select[name="under_admission').val(),
							'pid': $('input[name="pid"]').val()
						}, function (s) {
							$('#labTest_container').html(s);
						});
					}
					e.handled = true;
				}

			});

		</script>
	<?php } else { ?>
		<div class="row-fluid">
			<label class="span6">Business Unit/Service Center <select name="lab_centre_id" placeholder="-- Select processing laboratory --">
					<option></option><?php foreach ($lab_centres as $l) { ?>
						<option value="<?= $l->getId() ?>" <?= (isset($_POST['lab_centre_id']) && $_POST['lab_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
				</select></label>
			<label class="span6">Laboratory
				Categories<select name="lab_category_id" placeholder="-- Select laboratory category --">
					<option></option><?php foreach ($lab_categories as $lc) { ?>
						<option value="<?= $lc->getId() ?>"<?= isset($_POST['lab_category_id']) && $_POST['lab_category_id'] === $lc->getId() ? ' selected="selected"' : '' ?>><?= $lc->getName() ?></option><?php } ?>
				</select></label>
		</div>
		<script>
			$('select[name="lab_centre_id"], select[name="lab_category_id"]').select2({
				width: '100%',
				allowClear: true
			}).change(function () {
				$.post('index.php?approve', {
					'page': 0,
					'lab_centre_id': $('select[name="lab_centre_id"]').val(),
					'lab_category_id': $('select[name="lab_category_id"]').val(),
					'pid': $('input[name="pid"]').val(),
					'under_admission': $('select[name="under_admission').val()
				}, function (s) {
					$('#labTest_container').html(s);
				});
			});
		</script>
		<div class="notify-bar">No "approve-pending" labs</div>
	<?php } ?>
	<script>
		var groupList =<?= json_encode(@$groupList) ?>;
		$("table tr td[data-group]").each(function (ind) {
			//console.log(groupList[ind] + "\t" + ind)
			$(this).attr("rowspan", groupList[ind]);
		});
		//$('table.approveList').dataTable();
	</script>
	<?php
	exit;
}

$script_block = <<<EOF
function aTab(o){
    container = $('#labTest_container');
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    if(o===1){
        $('a.tab.incomplete').addClass('on');
        url = $('a.tab.incomplete').attr('data-href');
    }else if(o === 2){
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
    }
    else if(o === 3){
       $('a.tab.addnew').addClass('on');
       Boxy.load('/labs/allLabs.php',{afterHide:function(){location.reload()}});
       return true;
    }else if(o===4) {
        $('a.tab.approve').addClass('on');
        url = $('a.tab.approve').attr('data-href');
    }else if(o===5) {
        $('a.tab.datesearch').addClass('on');
        url = $('a.tab.datesearch').attr('data-href');
    }
    LoadDoc(container, url);
}

function LoadDoc(container, url){
    \$.ajax({
        url:url,
        beforeSend: function(){
            loading(container);
        },
        complete:function(s){
            loaded(container, s);

            $('input[name="date_start"]').datetimepicker({
                format:'Y-m-d',
                timepicker:false
            });$('input[name="date_stop"]').datetimepicker({
                format:'Y-m-d',
                timepicker:false
            });
            $("*[title]").tooltipster();
        },
    });
    return false;
}
function loading(container){
    container.html('<div align="center"><img src="/img/loading.gif" /> Loading Data ...</div>').show();
}
function loaded(container, respObj){
    container.html(respObj.responseText);
    //\$('table.table').tableScroll({height:500});
    //\$('table.table').dataTable({"aoColumnDefs": [{ "iDataSort": 1, "aTargets": [ 0 ] }],"fnDrawCallback": function( oSettings ) { \$("*[title]").tooltipster(); }});
    //do that filter thing here
//    if(\$.param("fText")){
//        $('.dataTables_filter input[type=search]').val(decodeURIComponent(\$.param("fText"))).trigger("keyup");
//    }
}
function start(){
	\$('#searchBox').html('<img src="/img/loading.gif"/> Please wait ...');
}

function loadResult(s){
    \$('#searchBox').html(s);
    jQuery.event.trigger("ajaxStop");
	//\$('table.table').tableScroll({height:500});
	//\$('table.table').dataTable({"aoColumnDefs": [{ "iDataSort": 0, "aTargets": [ 1 ] }],"fnDrawCallback": function( oSettings ) { \$("*[title]").tooltipster(); }});
	\$("*[title]").tooltipster();
}

$(document).ready(function(){
    aTab(1);
    \$('a.__aprove').live('click',function(){
        var id_ = $(this).data("id");
        var lid_ = $(this).data("lab-id");
        Boxy.confirm("Are you sure to approve this result", function(){
            \$.ajax({
                url:'/labs/result.action.php',
                data:{id:id_, action:'approve'},
                type:'post',
                dataType:'json',
                beforeSend:function(){
                    \$.blockUI({message: '<h6 class="fadedText" style="font-size:200%">Please wait ...</h6>',css: {borderWidth: '0',backgroundColor:'transparent',}});
                },
                success:function(data){
                    \$.unblockUI();
                    if(data.status==="ok"){
                        Boxy.info(data.message, function(){
                            Boxy.confirm("Do you want to print this result?", function(){
                                window.open("/pdf.php?page=/labs/printLab.php?gid="+lid_+"&title="+lid_, "_blank");
                            });
                        });
                        Boxy.get($('.close')).hideAndUnload();
                        aTab(4);
                    }else {
                        Boxy.alert(data.message);
                    }
                },
                error:function(){
                    Boxy.alert('Failed to approve result. A server error occurred');
                    \$.unblockUI();
                }
            });
        });
    });

    \$('a.__reject').live('click',function(){
        var id_ = $(this).data("id");
        Boxy.confirm("Are you sure to reject this result; <br>this will remove the results attached to this request.", function(){
        \$.ajax({
            url:'/labs/result.action.php',
            data:{id:id_, action: 'reject'},
            type:'post',
            dataType:'json',
            beforeSend:function(){
                \$.blockUI({message: '<h6 class="fadedText" style="font-size:200%">Please wait ...</h6>',css: {borderWidth: '0',backgroundColor:'transparent',}});
            },
            success:function(data){
                \$.unblockUI();
                if(data.status==="ok"){
                    Boxy.info(data.message);
                    Boxy.get($('.close')).hideAndUnload();
                    aTab(4);
                }else {
                    Boxy.alert(data.message);
                }
            },
            error:function(){
                Boxy.alert('Action failed. A server error occurred');
                \$.unblockUI();
            }
        });});
    });

    \$('a.cancelLabLink').live('click', function(e){
        var id = $(this).data("id");
        if(e.handled != true){
            Boxy.ask("Are you sure you want to cancel this request line item?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/api/labrequests.php', {id: id, action:"cancel"}, function(s){
                        if(s.trim()=="ok"){
                            //remove this cancel button, also remove the take specimen link
                            $('a.cancelLabLink[data-id="'+id+'"]').next('a:contains("Take specimen")').remove();
                            $('a.cancelLabLink[data-id="'+id+'"]').remove();
                            //todo: show "Cancelled" status text
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
            });
            e.handled=true;
        }
    });

    \$('a.receiveLab').live('click', function(e){
        var id = $(this).data("id");
        var lab = $(this).data("lab");
        if(e.handled != true){
            Boxy.ask("Acknowledge to receive specimen: "+lab, ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/api/labrequests.php', {id: id, action:"receive"}, function(s){
                        if(s.trim()=="ok"){
                            reloadThisPage();
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
            });
            e.handled=true;
        }
    });
});
EOF;
$page = "pages/laboratory/index.php";
$title = "Laboratory";
include "../template.inc.in.php";
?>
