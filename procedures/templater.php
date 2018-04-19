<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/10/14
 * Time: 11:48 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$allCentres = (new ServiceCenterDAO())->all('Procedure');
$categories = (new ProcedureCategoryDAO())->all();
?>
<div class="row-fluid">
	<label class="span6">Business Unit/Service Center
		<select name="service_centre_id" data-placeholder="Select a receiving procedure center">
			<option></option>
			<?php foreach ($allCentres as $center) { ?>
				<option value="<?= $center->getId() ?>"<?= (isset($_REQUEST['service_centre_id']) && $_REQUEST['service_centre_id'] == $center->getId()) ? ' selected="selected"' : '' ?>><?= $center->getName() ?></option>
			<?php } ?>
		</select>
	</label>
	<label class="span6">
		Procedure Category
		<select name="category_id" data-placeholder="- select -">
			<option></option>
			<?php foreach ($categories as $category) { ?>
				<option value="<?= $category->getId() ?>"<?= (isset($_REQUEST['category_id']) && $_REQUEST['category_id'] == $category->getId()) ? ' selected="selected"' : '' ?>><?= $category->getName() ?></option>
			<?php } ?>
		</select>
	</label>
</div>

<?php if (count($pro) > 0) { ?>
	<div id="area" class="dataTables_wrapper">
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th>Procedure</th><?php if ($pro[0]->getPatient() != null) { ?>
					<th>Patient</th><?php } ?>
				<th>Status</th>
				<th>Resource(s)</th>
				<th>Scheduled Date</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($pro as $p) {//$p=new PatientProcedure();
				$resources = [];
				foreach ($p->getScheduledResources() as $resource) {
					$resources[] = $resource->getName();
				}
				?>
				<tr>
					<td>
						<?= ($p->getReferral() !== null) ? '<span title="Referred from ' . $p->getReferral()->getName() . '(' . $p->getReferral()->getCompany()->getName() . ')"><i class="icon-info-sign"></i></span>' : '' ?>
						<a data-key="<?= $p->getId() ?>" href="javascript:;" title="more details"><?= $p->getProcedure()->getName() ?></a>
					</td>
					<?php if ($p->getPatient() != null) { ?>
						<td nowrap>
						<span class="profile" data-pid="<?= $p->getPatient()->getId(); ?>" xtitle="<?= $p->getPatient()->getFullname(); ?>"><?= $p->getPatient()->getFullname(); ?></span>
						</td><?php } ?>
					<td><?= ucfirst($p->getStatus()) ?></td>

					<td><?= ($p->getStatus() == 'scheduled') ? implode(',<br>', $resources) : ' - -' ?></td>
					<td nowrap><?= ($p->getStatus() == 'scheduled') ? date(MainConfig::$dateTimeFormat, strtotime($p->getTimeStart())) . '&rArr;<br>' . date(MainConfig::$dateTimeFormat, strtotime($p->getTimeStop())) : '--' ?></td>
					<td nowrap>
						<div class="dropdown">
							<button class="drop-btn large dropdown-toggle" data-toggle="dropdown">
								Action
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
								<?php if (in_array($p->getStatus(), ['open'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="schedule">Schedule</a></li><?php } ?>
								<?php if (in_array($p->getStatus(), ['scheduled'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="reschedule">Re-Schedule</a></li><?php } ?>
								<?php if (in_array($p->getStatus(), ['scheduled','open'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="start">Start</a></li><?php } ?>
								<?php if (in_array($p->getStatus(), ['started'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="conclude">Conclude</a></li><?php } ?>
								<?php if (!in_array($p->getStatus(), ['closed', 'cancelled'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="cancel">Cancel</a></li><?php } ?>
								<?php if (in_array($p->getStatus(), ['cancelled'])) { ?>
									<li><a href="javascript:" data-id="<?= $p->getId() ?>" data-action="reorder">Re-Order</a></li><?php } ?>
							</ul>
						</div>

					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="<?= isset($pager) ? $pager : 'list1' ?> dataTables_wrapper no-footer">
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
	</div>
<?php } else { ?>
	<div class="notify-bar">Nothing found</div>
<?php } ?>
<script type="text/javascript">
	var procedureContainer;
	if ($('#procedure_container').length !== 0) {
		procedureContainer = $('#procedure_container');
	} else {
		procedureContainer = $('#contentPane');
	}
	$(document).ready(function () {
		
		$('a[data-key]').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/procedures/tabs/home.php?id=' + $(this).data("key"));
				e.handled = true;
			}
			e.preventDefault();
		});

		$('ul.dropdown-menu[role="menu"][aria-labelledby="dLabel_"] li a[data-id][data-action]').live('click', function (e) {
			if (!e.handled) {
				var id = $(this).data('id');
				var action = $(this).data('action');
				switch (action) {
					case 'schedule':
						Boxy.load('/procedures/schedule_request.php?id=' + id);
						break;
					case 'reschedule':
						Boxy.warn("Don't forget to cancel the schedule on the calendar<br>in order to free up the resource", function () {
							Boxy.load('/procedures/schedule_request.php?id=' + id);
						});
						break;
					case 'start':
						Boxy.ask('You want to mark this procedure as STARTED.', ['Continue', 'No'], function (choice) {
							if (choice === 'Continue') {
								$.post('/api/procedure_action.php', {status: "start", id: id}, function (s) {
									var result = s.split(":");
									if (result[0] === "error") {
										Boxy.alert(result[1]);
									} else if (result[0] === "success") {
										if (typeof reloadProcedure === 'function') {
											reloadProcedure(id);
											reloadThisPage();
										} else {
											reloadThisPage();
										}
									}
								});
							}
						});
						break;
					case 'conclude':
						Boxy.ask('You want to mark this procedure as CONCLUDED.<br>You will not be able to add notes to it.', ['Continue', 'No'], function (choice) {
							if (choice === 'Continue') {
								Boxy.load('/procedures/dialogs/concluding_info.php?id=' + id);
							}
						});
						break;
					case 'cancel':
						Boxy.ask('You want to CANCEL this procedure request?', ['Continue', 'No'], function (choice) {
							if (choice === 'Continue') {
								$.post('/api/procedure_action.php', {status: "cancel", id: id}, function (s) {
									var data = s.split(":");
									if (data[0] === "error") {
										Boxy.alert(data[1]);
									} else if (data[0] === "success") {
										if (typeof reloadProcedure === 'function') {
											reloadProcedure(id);
											reloadThisPage();
										} else {
											reloadThisPage();
										}
									}
								});
							}
						});
						break;
					case 'reorder':
						Boxy.ask("This will create a new procedure request.<br>Are you sure you want to continue?", ["Yes", "No"], function (choice) {
							if (choice === "Yes") {
								$.post('/api/procedure_action.php', {id: id, action: "re-order"}, function (s) {
									if (s.trim() === "ok") {
										showTabs(8);
										//todo: this will fail in other places apart from the patient profile
									} else {
										Boxy.alert("An error occurred");
									}
								});
							}
						});
						break;
				}
				e.handled = true;
			}
		});

		$('a.reOrderLink').live('click', function (e) {
			var id = $(this).data("id");
			if (!e.handled) {
				e.handled = true;
			}
		});

		$('select[name="service_centre_id"]').select2({width: '100%', allowClear: true}).change(function (evt) {
			var currentUrl = $('.mini-tab .tab.on').data("href") || "<?= @$sourcePage ?>" || "/patient_profile.php?id=<?= @$_GET['pid']?>&view=procedures";
			if (currentUrl === "?open") {
				currentUrl += "&date=" + $('input[name="date_start"]').val() + "," + $('input[name="date_stop"]').val();
				currentUrl += "&date1=" + $('input[name="date_start1"]').val() + "," + $('input[name="date_stop1"]').val();
			}
			if ($('select[name="category_id"]').select2('data') !== null) {
				currentUrl += "&category_id=" + $('select[name="category_id"]').select2('data').id;
			}
			if ($('input[name="resource_id"]').select2('data') !== null) {
				currentUrl += "&resource_id=" + $('input[name="resource_id"]').select2('data').id;
			}
			if (evt.added !== undefined) {
				procedureContainer.load(currentUrl + "&service_centre_id=" + evt.added.id);
			} else {
				procedureContainer.load(currentUrl);
			}
		});
		$('select[name="category_id"]').select2({width: '100%', allowClear: true}).change(function (evt) {
			var currentUrl = $('.mini-tab .tab.on').data("href") || "<?= @$sourcePage ?>" || "/patient_profile.php?id=<?= @$_GET['pid']?>&view=procedures";
			if (currentUrl === "?open") {
				currentUrl += "&date=" + $('input[name="date_start"]').val() + "," + $('input[name="date_stop"]').val();
				currentUrl += "&date1=" + $('input[name="date_start1"]').val() + "," + $('input[name="date_stop1"]').val();
			}
			if ($('select[name="service_centre_id"]').select2('data') !== null) {
				currentUrl += "&service_centre_id=" + $('select[name="service_centre_id"]').select2('data').id;
			}
			if (evt.added !== undefined) {
				procedureContainer.load(currentUrl + "&category_id=" + evt.added.id);
			} else {
				procedureContainer.load(currentUrl);
			}
		});
	});
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			var currentUrl = $('.mini-tab .tab.on').data("href") || "<?=@$sourcePage ?>" || "/patient_profile.php?id=<?= @$_GET['pid']?>&view=procedures";
			if (currentUrl === "?open") {
				currentUrl += "&date=" + $('input[name="date_start"]').val() + "," + $('input[name="date_stop"]').val();
				currentUrl += "&date1=" + $('input[name="date_start1"]').val() + "," + $('input[name="date_stop1"]').val();
			}
			var serviceCentre = "&page=" + page;
			if (!$(this).hasClass("disabled")) {
				if ($('select[name="service_centre_id"]')) {
					if ($('select[name="service_centre_id"]').val() !== '') {
						serviceCentre += "&service_centre_id=" + $('select[name="service_centre_id"]').val();
					}
				}
				procedureContainer.load(currentUrl + serviceCentre);
			}
			e.clicked = true;
		}
	});

	var reloadThisPage = function () {
		try {
			$('.mini-tab > a.tab.on').get(0).click();
		} catch (exception) {
		}
		
		try {
			$('select[name="service_centre_id"]').trigger('change');
		} catch (exception){
		}
	};
</script>