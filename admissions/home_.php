<?php
if (!isset($_SESSION)) {
	@session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.admissions.php';
?>

<div class="mini-tab">
	<a class="tab on" id="inbound-patients" data-i="1" onclick="loadTab(1)" href="javascript:;" title="Inbound Patient">Inbound Patients</a>
	<a class="tab" id="admitted-patients" data-i="2" onclick="loadTab(2)" href="javascript:;" title="Patients on Admission">Patients on Admission</a>
	<a class="tab" id="my-patients" data-i="3" onclick="loadTab(3)" href="javascript:;" title="My Patients on Admission">My Patients on Admission</a>
	<a class="tab" id="rounding" data-i="4" onclick="loadTab(4)" href="javascript:;" title="Roundings">Clinical Tasks</a>
	<a class="tab" id="history" data-i="6" onclick="loadTab(6)" href="javascript:;" title="History">History</a>
	<?php if (!AdmissionSetting::$ipMedicationTaskRealTimeDeduct) { ?>
		<a class="tab pull-right" data-i="7" id="discharge" onclick="loadTab(7)" href="javascript:;" title="Complete Discharge">Complete Discharge</a>
	<?php }
	if (AdmissionSetting::$ipMedicationTaskRealTimeDeduct) { ?>
		<a class="tab pull-right" data-i="5" id="billing" onclick="loadTab(5)" href="javascript:;" title="Compute Admission Cost">Compute Discharge Bills </a>
	<?php } ?>
</div>

<div id="contentPane_">
</div>

<script type="text/javascript">
	$(document).ready(function () {
		loadTab(1);
		<?php if(isset($_GET['action'])){?>assignBed(<?=$_GET['aid']?>);<?php }?>
	});

	var assignBed = function (adid, tabIndex) {
		Boxy.load('/admissions/assignBed.php?aid=' + adid, {
			title: 'Assign Bed', afterHide: function () {
				if (tabIndex !== "undefined") {
					loadTab(tabIndex);
				}
			}
		});
	};
	var loadTab = function (i, pid, block, ward) {
		var urn;
		$('.tab.on').removeClass('on');
		if (i === 1) {
			urn = "homeTabs/" + "inBoundPatients.php";
			$('#inbound-patients').addClass('on');
		} else if (i === 2) {
			urn = "homeTabs/" + "admittedPatients.php";
			$('#admitted-patients').addClass('on');
		} else if (i === 3) {
			urn = "homeTabs/" + "myPatients.php";
			$('#my-patients').addClass('on');
		} else if (i === 4) {
			urn = "homeTabs/" + "wardRounds.php";
			$('#rounding').addClass('on');
		} else if (i === 5) {
			urn = "homeTabs/computeMedicationBills.php";
			$('#billing').addClass('on');
		} else if (i === 6) {
			urn = "homeTabs/admissionHistory.php";
			$('#history').addClass('on');
		} else if (i === 7) {
			urn = "homeTabs/incompleteDischarge.php";
			$('#discharge').addClass('on');
		}
		postData = {};
		if(pid !== undefined){
			postData['patient_id'] = pid;
		}
		if(block !== undefined){
			postData['block_id'] = block;
		}
		if(ward !== undefined){
			postData['ward_id'] = ward;
		}
		$("#contentPane_").load(urn, postData, function () {
			// $('#admission_container table').dataTable();
			//$('.dataTables_length select').select2();
			loadPatient();

		});
	};


	var loadPatient = function () {
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
					};
				},
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
				var i = $('.mini-tab > .tab.on').data('i');
				if(e.added !== undefined){
					loadTab(i, e.added.patientId, $('select[name="block_id"]').val(), $('select[name="ward_id"]').val());
				} else {
					loadTab(i, null, $('select[name="block_id"]').val(), $('select[name="ward_id"]').val());
				}
				e.handled = true;
			}
		});
	}
</script>