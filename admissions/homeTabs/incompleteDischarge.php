<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/10/16
 * Time: 2:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
$pageSize = 10;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$data = (new InPatientDAO())->getIncompletelyDischargedInPatients(TRUE, $page, $pageSize);
$totalSearch = $data->total;
?>
<div id="admission_container" class="dataTables_wrapper">
    <?php if($totalSearch == 0){?>
	    <div class="notify-bar">Nothing found!</div>
    <?php } else {?>
	    <table class="table table-striped">
		    <thead>
		    <tr>
			    <th>Patient</th>
			    <th>Coverage</th>
			    <th>Admitted</th>
			    <th>Discharged</th>
			    <th>*</th>
		    </tr>
		    </thead>
		    <?php foreach ($data->data as $history) {
			    if ($history->active) //$history = new InPatient();?>
				    <tr>
				    <td><span class="profile" data-pid="<?= $history->patient_id ?>"><?= $history->patientName ?></span></td>
			    <td><?= $history->scheme_name ?></td>
			    <td><?= date(MainConfig::$dateTimeFormat, strtotime($history->date_admitted)) ?></td>
			    <td><?= date(MainConfig::$dateTimeFormat, strtotime($history->date_discharged)) ?></td>

			    <td style="text-align: right">
				    <a href="/admissions/inpatient_profile.php?pid=<?= $history->patient_id ?>&aid=<?= $history->id ?>">
					    Open Instance</a> |
				    <a class="complete_discharge_link" href="javascript:;" data-id="<?= $history->id ?>">Complete Discharge</a>
			    </td>
			    </tr>
		    <?php } ?>
	    </table>
	<?php }?>
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div class="resultsPagerOpenIncomplete no-footer dataTables_paginate">
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.complete_discharge_link').live('click', function(e){
        if(!e.handled){
            Boxy.load("/admissions/dialogs/boxy.complete.discharge.dialog.php?aid="+$(this).data("id"), {title: 'Select Bill Items', afterHide: function(){
                loadTab(7);
            }});
            e.handled = true;
        }
    });
    $(document).on('click', '.resultsPagerOpenIncomplete.dataTables_paginate a.paginate_button', function(e){
        if(!$(this).hasClass("disabled") && !e.handled){
            var page = $(this).data("page");
            var url = "/admissions/homeTabs/incompleteDischarge.php?page="+page;
            $("#admission_container").load(url+" #admission_container>*","");
            e.handled = true;
        }
    });
</script>

