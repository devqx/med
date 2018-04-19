<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/14/14
 * Time: 3:39 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$patient = (!is_blank(@$_REQUEST['patient_id'])) ? @$_REQUEST['patient_id'] : NULL;
$isAdmitted = (!is_blank(@$_REQUEST['under_admission_aw'])) ? @$_REQUEST['under_admission_aw'] : NULL;
$temp = (new PatientScanDAO())->getScans(NULL, NULL, $page, $pageSize, 'awaiting', $patient, null, $isAdmitted);
$totalSearch = $temp->total;
$data = array();

foreach ($temp->data as $scan) {
	$data[] = $scan;
}?>

<div class="row-fluid ui-bar-c">
	<label class="span6">
		Filter Awaiting List by Patient
		<input type="hidden" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
	</label>
	<label class="span6">
		Patients on admission
		<select name="under_admission_aw" data-placeholder="-- Filter List by Admission Status --">
			<option></option>
			<option value="1"<?= isset($_REQUEST['under_admission_aw']) && $_REQUEST['under_admission_aw'] == 1 ? 'selected="selected"': '' ?> >Patient on Admission</option>
		</select>
	</label>
</div>
<div class="clear"></div>
<div class="dataTables_wrapper">
	<table class="table table-striped scantable_">
		<thead><tr><th>Request Date</th><th>RQ #</th><?php if(!isset($_GET['pid'])){?><th>Patient</th><?php }?><th>Requester</th><th>Type</th><th>Attachment</th></tr></thead>
		<?php foreach ($data as $ps) {?>
			<tr id="_sc_an_tr_<?= $ps->getId() ?>">
				<td><time datetime="<?= strtotime($ps->getRequestDate())?>"><?= date("d M, Y h:iA", strtotime($ps->getRequestDate()))?></time></td>
				<td><a data-title="<?= $ps->getRequestCode() .": ". $ps->getScan()->getName()?>"  class="boxy" href="javascript:;" data-href="/imaging/scan.details.php?id=<?= $ps->getId() ?>"><?= $ps->getRequestCode()?></a></td>
				<td>
					<?= $ps->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?>
					<a title="<?=$ps->getPatient()->getFullname();?>" href="/patient_profile.php?id=<?=$ps->getPatient()->getId()?>"><?=$ps->getPatient()->getShortname();?></a></td>
				<td><span ><?=$ps->getRequestedBy()->getFullname()?></span></td>
				<td><?= $ps->getScan()->getName() ?></td>
				<td><?php foreach ($ps->getAttachments() as $attach) {?>
						<i class="icon-paper-clip"></i> <a href="<?= $attach->getAttachmentURL()?>" target="_blank">view</a><?php }?></td>
			</tr>
		<?php }?>
	</table>
	<div class="dataTables_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
	<div class="resultsPagerAwaiting no-footer dataTables_paginate">
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
			<a data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>

			<a class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
			<a class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$(document).on('click', '.resultsPagerAwaiting.dataTables_paginate a.paginate_button', function(e){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled") && !e.handled){
				var url = "/imaging/awaiting_list.php?page="+page;
				if($('[name="patient_id"]').val()!==''){
					url += '&patient_id='+$('[name="patient_id"]').val()
				}
				if($('select[name="under_admission_aw"]').val()!==''){
					url += '&under_admission_aw='+$('select[name="under_admission_aw"]').val()
				}
				$('.container > .inner').load(url, function (responseText, textStatus, req) {
				});
				e.handled = true;
			}
		});
		$('[name="patient_id"]').css({'font-weight':400}).select2({
			placeholder: "Search and select patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
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
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);

			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function(element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function(data) { callback(data); });
				}
			}
		}).change(function(e) {
			if(!e.handled){
				var url = "/imaging/awaiting_list.php?patient_id="+$(this).val();
				$('.container > .inner').load(url, function (responseText, textStatus, req) { });
				e.handled = true;
			}
		});

		$('#date_filter').live('click', function (e) {
			if(!e.handled){
				var url = "<?=$_SERVER['REQUEST_URI'] ?>?date="+encodeURIComponent($('input[name="date_start"]').val())+","+encodeURIComponent($('input[name="date_stop"]').val());
				if($('[name="patient_id"]').val()!==''){
					url += '&patient_id='+$('[name="patient_id"]').val()
				}
				$('.container > .inner').load(url, function (responseText, textStatus, req) { });
				e.handled = true;
			}
		});

		$('select[name="under_admission_aw"]').select2({
			width: '100%',
			allowClear: true
		}).change(function (e) {
			var url = "/imaging/awaiting_list.php?under_admission_aw=" + $(this).val();
			$('.container > .inner').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		})
		
	});
</script>
