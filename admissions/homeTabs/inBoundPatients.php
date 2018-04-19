<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/7/15
 * Time: 11:05 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admissions/InPatientHealthState.php';

$pageSize = 10;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$ward = (isset($_POST['ward_id']) && !is_blank($_POST['ward_id'])) ? $_POST['ward_id'] : null;

$block = (isset($_POST['block_id']) && !is_blank($_POST['block_id'])) ? $_POST['block_id'] : null;
$h_id = (isset($_POST['health_state_id']) && !is_blank($_POST['health_state_id'])) ? $_POST['health_state_id'] : null;

error_log("health state".json_encode($h_id));


$patientId = isset($_POST['patient_id']) && !is_blank($_POST['patient_id']) ? $_POST['patient_id'] : null;
$inPatients = (new InPatientDAO())->getInboundInPatients(true, $block, $ward, $page, $pageSize, $patientId, $h_id);
$totalSearch = $inPatients->total;
$wards = [];
$blocks = [];

$health_states = ( new InPatientHealthState() )->getAllHealthStates();

if (!isset($_GET['outpatient'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';
	$blocks = (new BlockDAO())->getBlocks();
	if (isset($_POST['block_id']) && $_POST['block_id']!="") {
		$wards = (new WardDAO())->getWardsByBlock($_POST['block_id']);
	} else {
		$wards = (new WardDAO())->getWards();
	}
}

?>
<div id="admission_container" class="dataTables_wrapper">
	<div class="row-fluid">
		<?php if (!isset($_GET['outpatient'])) { ?>
			<label class="span3">
				<select name="block_id">
					<option value="">-- Block Filter --</option><?php foreach (@$blocks as $b) { ?>
						<option value="<?= $b->getId() ?>"<?php if (@$_POST['block_id'] === $b->getId()) { ?> selected="selected"<?php } ?>><?= $b->getName() ?></option><?php } ?>
				</select>
			</label>
			<label class="span4">
				<select name="ward_id">
					<option value="">-- Ward Filter --</option><?php foreach (@$wards as $ward) { ?>
						<option value="<?= $ward->getId() ?>"<?php if (@$_POST['ward_id'] === $ward->getId()) { ?> selected="selected"<?php } ?>><?= $ward->getName() ?></option><?php } ?>
				</select>
			</label>



		<?php } ?>
		<label class="span3">
			<input type="hidden" name="patient_id" value="<?= (isset($_POST['patient_id']) ? $_POST['patient_id'] : '') ?>">
		</label>

        <label class="span2">
            <select name="health_state">
                <option value="">-- Health State Filter --</option>
                <?php foreach ($health_states as $key => $val ){?>

                    <option value="<?php echo $val['id'];?>" <?php if( @$_POST['health_state_id'] ===  $val['id'] ) { ?> selected="selected"<?php } ?> );?> <?php echo $val['state'];?></option>

                <?php } ?>
            </select>
        </label>

	</div>
	<table class="table table-hover table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th>Reason</th>
			<th>On Admission</th>
			<th>Admitted By</th>
			<th>Bed (Room)</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($inPatients->data as $ip) {
			if ($ip->active) {//$ip = new InPatient();?>
				<tr>
					<td >
						<a class="profile" data-pid="<?= $ip->patient_id ?>" href="/admissions/inpatient_profile.php?pid=<?= $ip->patient_id ?>&aid=<?= $ip->id ?>"><?= $ip->patientName ?> [<?= $ip->sex ?>]</a>


                        <?php

                        $inp_health_state = ( new InPatientHealthState() )->getInPatientHealthState($ip->patient_id);

                        if( ! empty($inp_health_state['state'] ) ){?>

                            <?php if( $inp_health_state['state'] == "Stable"){?>

                                <i class="fa fa-circle fa-2x" title="<?php echo $ip->patientName;?>'s Health Status " style="color:#32C744;cursor: pointer"></i>
                            <?php }?>

                            <?php if( $inp_health_state['state'] == "Critical"){?>

                                <i class="fa fa-circle fa-2x" title="<?php echo $ip->patientName;?>'s Health Status " style="color:red;cursor: pointer"></i>
                            <?php }?>

                            <?php if( $inp_health_state['state'] == "Intermediate"){?>

                                <i class="fa fa-circle fa-2x" title="<?php echo $ip->patientName;?>'s Health Status " style="color:yellow;cursor: pointer"></i>
                            <?php }?>

                            <!--                            <img src="../img/icons/fall-risk.jpg" title="Patient's Risk To Fall - --><?php //echo $inp_health_state['risk_to_fall'];?><!--" width="32px" height="32px" />-->

                        <?php }?>

                        <?php if(  ! empty($inp_health_state['risk_to_fall'] ) && $inp_health_state['risk_to_fall'] == 1){?>
                            <i class="fa fa-exclamation-triangle fa-2x"  title=" <?php echo $ip->patientName;?>'s Risk To Fall " style="color:red;cursor"></i>
                        <?php }?>



					</td>
					<td><?= $ip->reason ?></td>
					<td nowrap><?= date("D d M, Y h:ia", strtotime($ip->date_admitted)) ?> <span am-time-ago="<?= date("c", strtotime($ip->date_admitted)) ?>"></span></td>
					<td nowrap><?= $ip->staffName ?></td>
					<td><?= ($ip->bed_id != null ? $ip->bedName . " (" . $ip->roomName . ")" : "") ?>
						<?php if ($ip->bed_id == null) { ?><a href="javascript:;" onclick="assignBed('<?= $ip->id ?>', 1)" class="btn btn-small">Assign Bed</a><?php } ?></td>
				</tr>
			<?php }
		} ?>
		</tbody>
	</table>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
	<div class="resultsPagerOpenInBound no-footer dataTables_paginate">
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>

</div>
<script>
	$(document).ready(function () {


        $('select[name="health_state"]').select2({
            placeholder: "Filter list by Health State",
            width: '100%',
            allowClear: true
        }).change(function (e) {

            if (!e.handled) {
                $.post('/admissions/homeTabs/inBoundPatients.php', {'page': 0, 'block_id': $('select[name="block_id"]').val(), 'ward_id': $('select[name="ward_id"]').val(), 'patient_id': $('input[name="patient_id"]').val(), 'health_state_id': e.added.id  }, function (s) {
                    $('#admission_container').html(s);
                    loadPatient();
                });
                e.handled = true;
            }
        });



		$('select[name="block_id"]').select2({
			placeholder: "Filter list by Block",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				$.post('/admissions/homeTabs/inBoundPatients.php', {'page': 0, 'block_id': $('select[name="block_id"]').val(), 'ward_id': $('select[name="ward_id"]').val(), 'patient_id': $('input[name="patient_id"]').val(), 'health_state_id': $('input[name="health_state"]').val() }, function (s) {
					$('#admission_container').html(s);
					loadPatient();
				});
				e.handled = true;
			}
		});



		$('select[name="ward_id"]').select2({
			placeholder: "Filter list by Ward",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				$.post('/admissions/homeTabs/inBoundPatients.php', {'page': 0, 'block_id': $('select[name="block_id"]').val(), 'ward_id': $('select[name="ward_id"]').val(), 'patient_id': $('input[name="patient_id"]').val(), 'health_state_id': $('input[name="health_state"]').val() }, function (s) {
					$('#admission_container').html(s);
					loadPatient();
				});
				e.handled = true;
			}
		});

		$(document).on('click', '.resultsPagerOpenInBound.dataTables_paginate a.paginate_button', function (e) {
			if (!$(this).hasClass("disabled") && !e.handled) {
				var page = $(this).data("page");
				$.post('/admissions/homeTabs/inBoundPatients.php', {'page': page, 'ward_id': $('select[name="ward_id"]').val(), 'patient_id': $('input[name="patient_id"]').val(),'health_state_id': $('select[name="health_state"]').val(), 'health_state_id': $('input[name="health_state"]').val() }, function (s) {
					$('#admission_container').html(s);
					loadPatient();
				});
				e.handled = true;
			}
		});

	});
</script>