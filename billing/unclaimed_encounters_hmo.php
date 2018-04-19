<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/3/16
 * Time: 11:49 PM
 */
$time1 = microtime(true);
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.assessments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
//if (!$this_user->hasRole($protect->bill_auditor) && !$this_user->hasRole($protect->hmo_officer))exit($protect->ACCESS_DENIED);

$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$from = isset($_REQUEST['from']) ? $_REQUEST['from'] : null;
$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : null;

?>



<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';

$encounters = (new EncounterDAO())->unclaimedEncountersHMO($_GET['sid'], $_GET['provider_id'], FALSE, $page, $pageSize, $from, $to);

$totalSearch = $encounters->total;
?>
<?php if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records)) { ?>
    <?php if (count($totalSearch) > 0) { ?>
<div class="document" style="overflow: scroll;">
		<div class="Filters4">
			<div class="row-fluid">
				<div class="span5">
					Transaction Date:
					<div class="input-prepend">
						<span class="add-on">From</span>
						<input class="span5" type="text" name="date_from" id="date_from4" placeholder="Start Date">
						<span class="add-on">To</span>
						<input class="span5" type="text" name="date_to" id="date_to4" placeholder="Stop Date">
					</div>
				</div>
				<div class="span3">
					Page Size:
					<label>
						<select name="pageSize" id="pageSize4">
							<option value="10" selected="selected">10-20</option>
							<option value="40">20-40</option>
							<option value="60">40-60</option>
							<option value="80">60-80</option>
							<option value="100">60-100</option>
							<option value="5000">Infinite</option>
						</select>
					</label>
				</div>
		</div>
        <div id="encountersList">
            <table class="table table-striped" style="max-width:100%">
                <thead>
                <tr>
                    <th width="" nowrap>Date</th>
                    <th width="" nowrap>Patient</th>
                    <th>Specialization</th>
	                  <th>Insurance Scheme</th>
                </tr>
                </thead>
                <?php foreach ($encounters->data as $e) {
                    //$e=new Encounter();
                    $complaints = $diagnoses = $plans = [];
                    foreach ($e->getPresentingComplaints() as $pc) {
                        $complaints[] = $pc->description;
                    }
                    unset($pc);
                    foreach ($e->getDiagnoses() as $pc) {
                        $diagnoses[] = $pc->description;
                    }
                    unset($pc);
                    foreach ($e->getPlan() as $pc) {
                        $plans[] = $pc->description;
                    }
                    unset($pc);
                    ?>
                    <tr>

                        <td nowrap><?= date("d/m/y  g:ia", strtotime($e->getStartDate())) ?></td>
                        <td nowrap><a href="/patient_profile.php?id=<?= $e->getPatient()->getId() ?>"><?= $e->getPatient() ? $e->getPatient()->getLname() .' '. $e->getPatient()->getFname() : 'N/A' ?></a></td>
                        <td><?= $e->getSpecialization() ? $e->getSpecialization()->getName() : 'N/A' ?></td>
	                      <td><?= $e->getScheme() ?  $e->getScheme() : "" ?></td>

                    </tr>
                <?php } ?>
            </table>
            <!--  pagination here -->
	        <div class="list1 dataTables_wrapper no-footer">
		        <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
		             aria-live="polite"> <?= $totalSearch ?>
			        results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
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
			           data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
				        records</a>
			        <a id="DataTables_Table_1_next"
			           class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			           data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
				        records</a>
		        </div>
	        </div>

        </div>
</div>
	
	<?php } else { ?>
        <div class="notify-bar">No encounters available</div>
    <?php } ?>
<?php } else { ?>
    <div class="warning-bar"><?= $protect->ACCESS_DENIED ?></div>
<?php } ?>
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			console.log(<?= $_REQUEST['sid'] ?>);
			//console.log(<?//= $_REQUEST['provider_id'] ?>//);
			if (!$(this).hasClass("disabled")) {
				$.post("/billing/unclaimed_encounters_hmo.php", {
					page: page
					
				
				}, function (s) {
					$("#patient_container").html($(s).filter("#patient_container").html());
				});
			}
			e.clicked = true;
		}
	});
	
	$(document).ready(function () {
		var now = new Date().toISOString().split('T')[0];
		$('#date_from4').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now
				});
			},
			onChangeDateTime: function () {
				$("#date_to4").val("")
			}
		});

		$('#date_to4').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now,
					minDate: $("#date_from4").val() ? $("#date_from4").val() : false
				});
			}
		});
		
		$("#date_to4").change(function (e) {
       var d_from = $("#date_from4").val();
       var d_to = $(this).val();
       if(d_from && d_to){
	       var page = $(this).data("page");
		       $.post("/billing/unclaimed_encounters_hmo.php", {page: page, from: d_from, to:d_to}, function (s) {
			       $("#patient_container").html($(s).filter("#patient_container").html());
		       });
       }
			e.handled = true;
		})
		
	});

</script>

