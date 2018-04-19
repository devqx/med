<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/9/16
 * Time: 3:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';

$diagnoses = [];//(new DiagnosisDAO())->getDiagnoses();
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$diagnosis = (isset($_REQUEST['diagnosis']) && !is_blank($_REQUEST['diagnosis'])) ? $_REQUEST['diagnosis'] : null;
$from = (isset($_REQUEST['from']) && !is_blank($_REQUEST['from'])) ? $_REQUEST['from'] : null;
$to = (isset($_REQUEST['to']) && !is_blank($_REQUEST['to'])) ? $_REQUEST['to'] : null;
$pageSize = 10;
$data = (new PatientDiagnosisDAO())->reportAll($page, $pageSize, $from, $to, $diagnosis);
$totalSearch = $data->total;
?>
<p></p>
<div class="clearBoth"></div>
<div class="document">
	<div class="row-fluid">
		<div class="span2 input-prepend" style="margin-left: 0;">
			<span class="add-on">From</span>
			<input class="span10" type="text" placeholder="Start Date" name="from" id="from">
		</div>
		<div class="span2 input-prepend">
			<span class="add-on">To</span>
			<input class="span10" type="text" placeholder="End Date" name="to" id="to" disabled="disabled">
		</div>
		<label class="span2">
			<select name="type_diagnosis">
				<option value="icd10">ICD10</option>
				<option value="icpc-2">ICPC-2</option>
			</select>
		</label>
		<label class="span4">
			<input type="hidden" name="diagnosis_id" placeholder=" Filter by Diagnosis ">
			<option></option>
			<?php /*foreach ($diagnoses as $s) { */ ?><!--
                        <option <? /*= ($s->getId() == @$_REQUEST['diagnosis']) ? 'selected' : '' */ ?>
                            value="<? /*= $s->getId() */ ?>"><? /*= $s->getName() */ ?></option>
                    --><?php /*}*/ ?>
		</label>
		<div class="span1">
			<button class="btn wide" type="button" id="show">Show</button>
		</div>
		<div class="span1">
			<button class="btn wide" type="button" id="export">Export</button>
		</div>
	</div>

	<div id="area" class="dataTables_wrapper">
		<?php if ($totalSearch > 0) { ?>
			<table class="table table-striped">
				<thead>
				<tr>
					<th>Date</th>
					<th>Patient</th>
					<th>Diagnosis</th>
					<th>Code</th>
					<th>Type</th>
					<th>By</th>
					<th>Status</th>
				</tr>
				</thead>
				<?php foreach ($data->data as $d) { ?>
					<tr>
					<td><?= date(MainConfig::$dateFormat, strtotime($d->Date)) ?></td>
					<td><span class="profile" data-pid="<?= $d->patient_ID ?>"><?= $d->Patient ?></span></td>
					<td><?= $d->Diagnosis ?></td>
					<td nowrap><?= $d->DCode ?></td>
					<td nowrap><?= strtoupper($d->DType) ?></td>
					<td><?= $d->DiagnosedBy ?></td>
					<td><?= ucfirst($d->Status) ?></td>
					</tr><?php } ?>
			</table>
			<div class="list1 dataTables_wrapper no-footer">
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>

				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
				</div>
			</div>
		<?php } else { ?>
			<div class="notify-bar">Nothing found or you didn't select date range</div>
		<?php } ?>
	</div>

</div>
<script>
	$(document).ready(function () {
		$('select').select2({width: '100%', allowClear: true});

		$('#show').click(function (e) {
			if (!e.clicked) {
				goTo(0);
				e.clicked = true;
			}
		});
		$('#export').click(function (e) {
			if (!e.handled) {
				//alert("/excel.php?dataSource=diagnoses&filename=Diagnoses_Report&from=" + $("#from").val() + "&to=" + $("#to").val() + "&diagnosis=" + $('[name="diagnosis_id"]').val());
				window.open("/excel.php?dataSource=diagnoses&filename=Diagnoses_Report&from=" + $("#from").val() + "&to=" + $("#to").val() + "&diagnosis=" + $('[name="diagnosis_id"]').val(), '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});

		$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
			if (!e.clicked) {
				var page = $(this).data("page");
				if (!$(this).hasClass("disabled")) {
					goTo(page);
				}
				e.clicked = true;
			}
		});

		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() != "") {
					$("#to").val('').removeAttr('disabled');
				}
				else {
					$("#to").val('').attr({'disabled': 'disabled'});
				}
			}
		});
		$("#to").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
			}/*,
			 onSelectDate:function(ct,$i){
			 //                if($("#from").val() && $("#to").val()){}
			 }*/
		});

		if ($("#from").val().trim() != "") {
			$("#to").removeAttr('disabled');
		}


		$('input:hidden[name="diagnosis_id"]').select2({
			placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			}, formatSelection: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the diagnosis name or ICD-10/ICPC-2 code";
			},
			ajax: {
				url: '/api/get_diagnoses.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						type: $('[name="type_diagnosis"]:checked').val()
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		});
	});
	
	

	function goTo(page) {
		$.get('/pages/pm/reporting/report.diagnoses.php?page=' + page + "&from=" + $("#from").val() + "&to=" + $("#to").val() + "&diagnosis=" + $('[name="diagnosis_id"]').val(), function (s) {
			$('#area').html($(s).find('#area').html());
		});
	}
</script>