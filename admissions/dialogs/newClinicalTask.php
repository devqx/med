<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Admission.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskData.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
$combo = (new ClinicalTaskComboDAO())->all();

//$options = getTypeOptions('type', 'vital_sign');
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$options = array_col($vitalTypes, 'name');
$options[] = "Others";

if (!isset($_SESSION)) {
	@session_start();
}

if (isset($_POST['aid'])) {
	$task = new ClinicalTask();
	$task->setPatient(new PatientDemograph($_POST['pid']));
	$task->setInPatient(is_blank(@$_POST['aid']) && is_blank(@$_POST['source']) ? null : new InPatient($_POST['aid']));
	$task->setObjective("");
	if(!is_blank(@$_POST['source'])){
		$task->setSource($_POST['source']);
		if($_POST['source']=='ivf'){
			$task->setSourceInstance(new IVFEnrollment($_POST['aid']));
			$task->setInPatient(null);
		}
		//handle other clinics when they're there
	}

	$types = isset($_POST['type']) ? $_POST['type'] : [];
	
	$tData = [];
	$staff = new StaffDirectory($_SESSION['staffID']);
	foreach ($types as $type) {
		$data = new ClinicalTaskData();
		if ($type == "Others") {
			$data->setDescription($_POST['others_task']);
		}
		$data->setFrequency(floor($_POST[$type . "_freq"] * $_POST[$type . "_interval"]));
		$data->setType((new VitalDAO())->getByName(str_replace("_", " ", $type) ));
		$data->setCreatedBy($staff);
		$data->setTaskCount($_POST[$type . "_taskcount"]);
		$data->setStartTime($_POST[$type . "_start_time"]);

		$data->setPrivate(!is_blank(@$_POST[$type . '_private']) && @$_POST[$type . '_private'] == 'on' ? true : false);

		$tData[] = $data;
		unset ($type);
	}
	$comboTasks = array_filter(explode(',', preg_replace('["]', '', @$_POST['clinical_task_combo'])[0]));
	$tasksFromCombos = [];
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDataDAO.php';
	foreach ($comboTasks as $comboId) {
		$combo = (new ClinicalTaskComboDAO())->get($comboId);
		foreach ($combo->getData() as $comboData ){
			// $comboData = (new ClinicalTaskComboData());
			$data = new ClinicalTaskData();
			$data->setFrequency(floor($comboData->getFrequency() * $comboData->getInterval()));
			$data->setType((new VitalDAO())->getByName($comboData->getType()));
			$data->setDescription($comboData->getDescription());
			$data->setCreatedBy($staff);
			$data->setTaskCount($comboData->getTaskCount());
			$data->setStartTime(date(MainConfig::$mysqlDateTimeFormat));// this prop was not captured in the combo config
			
			$data->setPrivate(false); // this prop was not captured in the combo config
			
			$tData[] = $data;
			unset($comboData);
			unset($comboId);
		}
	}
	
	$task->setClinicalTaskData($tData);
	$nTask = (new ClinicalTaskDAO())->addClinicalTask($task);
	if ($nTask === null) {
		exit(json_encode("error:Sorry, We are unable to complete your request"));
	} else {
		exit(json_encode("ok:" . $nTask->getId()));
	}
}
?>
<div style="width: 750px">
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="clinicalTaskForm">
		
		<?php foreach ($options as $key => $option) { ?>
			<div class="menu-head vgrouped">
				<label><input type="checkbox" name="type[]" data-index="<?= $key ?>" onclick="toggleChild(this)" value="<?= str_replace(" ", "_", $option) ?>"> <?= $option ?>
				</label>
				<?php if ($option === "Others") { ?>
					<div class="row-fluid" style="display: none" data-index="<?= $key ?>">
						<input type="text" placeholder="Task description" class="span12" name="others_task">
					</div>
				<?php } ?>
				<div class="row-fluid" style="display: none" data-index="<?= $key ?>">
					<div class="span2">
						<label>
							<span class="">Every</span>
							<input style="max-width:90px;width:90px;min-width:90px" type="number" data-decimals="0" name="<?= str_replace(" ", "_", $option) ?>_freq" value="" placeholder="eg: 3" title="periodic interval">
						</label>
					</div>
					<div class="span3">
						<label>
							<span class="">Interval</span>
							<select name="<?= str_replace(" ", "_", $option) ?>_interval">
								<option value="1">minutes</option>
								<option value="60">hours</option>
								<option value="<?= intval(60 * 24) ?>">days</option>
								<option value="<?= intval(60 * 24 * 7) ?>">weeks</option>
								<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>">months</option><!-- // should we use 30 or 31  -->
							</select>
						</label>
					</div>
					<div class="span2">
						<label>
							<span class="">Task Count</span>
							<input type="number" style="min-width: 90px;max-width: 90px;width:90px" data-decimals="0" name="<?= str_replace(" ", "_", $option) ?>_taskcount" value="" placeholder="eg: 4" title="number of times to run task">
						</label>
					</div>
					<div class="span3">
						<label>
							<span class="">Start Time</span>
							<input type="text" class="start_time" name="<?= str_replace(" ", "_", $option) ?>_start_time" value="" placeholder="Time to start task" title="">
						</label>
					</div>
					<?php if(!is_blank(@$_GET['source'])){?>
					<div class="span2">
						<label>
							<span class="">Private ?</span>
							<input type="checkbox" name="<?= str_replace(" ", "_", $option) ?>_private" title="" style="display: block;margin-top: 8px;">
						</label>
					</div>
					<?php }?>
				</div>
			</div>
		<?php } ?>
		<div class="menu-head vgrouped"><label><input type="checkbox" name="medication" id="medication" value="Medication"> Add Medication</label></div>
		
		<p class="clear"></p>
		<label class="menu-head">Choose from pre-configured group(s) of task(s)
			<input id="clinical_task_combo" type="hidden" name="clinical_task_combo[]">
			<p class="clear"></p>
		</label>
		
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
		<?php if(!is_blank(@$_GET['source'])){?><input type="hidden" name="source" value="<?=$_GET['source']?>"> <?php }?>
		<?php if(!is_blank(@$_REQUEST['pid'])){?><input type="hidden" name="pid" value="<?= $_REQUEST['pid'] ?>"><?php }?>

		<button type="button" id="saveCT" class="btn btn-primary">Save</button>
		<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</form>
</div>

<script type="text/javascript">
	function toggleChild(el) {
		if ($(el).val() !== "Medication") {
			var interest = $('div[data-index="' + $(el).data('index') + '"]');
			interest.toggle();

			interest.find('input,select').not('[id^=s2id]').not(':checkbox').each(function (index, item) {
				if($(el).is(":checked")){
					$(item).attr('required', 'required');
				} else {
					$(item).removeAttr('required');
				}
			});
		}
	}

	$(document).ready(function () {
		var taskCombos = <?=json_encode($combo, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		$("#saveCT").click(function () {
			var form = $("#clinicalTaskForm");
			if( form[0].checkValidity() ){
				$.ajax({
					url: "<?= $_SERVER['PHP_SELF'] ?>",
					type: "POST",
					dataType: "json",
					data: form.serialize(),
					success: function (data) {
						if (data.split(":")[0] === "ok") {
							if ($("#medication").is(":checked")) {
								Boxy.load('/admissions/vitals/medication.php?ctid=' + data.split(":")[1] + '<?= !is_blank(@$_GET['source']) ? '&source='.@$_GET['source'] : '' ?>&pid=<?= $_REQUEST['pid'] ?>&aid=<?= $_REQUEST['aid'] ?>', {
									title: 'Medication', afterHide: function () {
										setTimeout(function () {
											Boxy.get($(".close")).hideAndUnload();
										}, 500);
									}
								});
							} else {
								if (typeof inPatientContext === "undefined") {
									Boxy.get($(".close")).hideAndUnload();
								} else {
									//showTabs(1);
									Boxy.get($(".close")).hideAndUnload();
								}
							}
						} else if (data.split(":")[0] === "error") {
							Boxy.alert("Oops! Something went wrong<br>" + data.split(":")[1])
						} else {
							Boxy.get($(".close")).hideAndUnload();
						}
					},
					error: function (data) {
						alert("Oops! Something went wrong");
					}
				});
			} else {
				if(typeof form[0].reportValidity === 'function') {
					form[0].reportValidity();
				} else {
					//todo get those particular fields that are invalid and highlight them?
					// and show the user the heading/Type of task
					Boxy.warn("All fields for the specified task(s) are NOT optional");
				}
			}
		});

		$('.start_time').datetimepicker({format: 'Y-m-d H:i'});
		
		$('#clinical_task_combo').select2({
			placeholder: "Search and select task combos",
			width: '100%',
			allowClear: true,
			multiple: true,
			data: {results: taskCombos, text: 'name'},

			formatResult: function (data) {
				return data.name;
			},
			formatSelection: function (data) {
				return data.name;
			}
		})
	});

</script>