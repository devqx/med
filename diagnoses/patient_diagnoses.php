<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/30/15
 * Time: 1:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.config.main.php';
$types = getTypeOptions('_status', 'patient_diagnoses');
$seves = getTypeOptions('severity', 'patient_diagnoses');
if(!$this_user){
    $this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
}
if ($this_user->hasRole($protect->pharmacy) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) {?>
<div class="menu-head">
    <span id="newLink"><a href="javascript:void(0)" onClick="showNewDiagnosisDlg()">New Record</a></span>
    <span class="pull-right hide">
        <label><select id="_changeDiagnosisSeverity_" class="no-margin"><option value="" <?=((isset($_REQUEST['severity']) && $_REQUEST['severity']=="") || !isset($_REQUEST['severity']))?' selected="selected"':'' ?>>-- Filter By Severity --</option><?php foreach($seves as $sev){?><option value="<?= $sev?>"<?=(isset($_REQUEST['severity']) && $_REQUEST['severity']==$sev)?' selected="selected"':'' ?>><?= ucwords($sev) ?></option><?php }?></select></label>
    </span>
    <span class="pull-right hide">
        <label><select id="_changeDiagnosisType_" class="no-margin"><option value="" <?=((isset($_REQUEST['type']) && $_REQUEST['type']=="") || !isset($_REQUEST['type']))?' selected="selected"':'' ?>>-- Filter By Type --</option><?php foreach($types as $type){?><option value="<?= $type?>"<?=(isset($_REQUEST['type']) && $_REQUEST['type']==$type)?' selected="selected"':'' ?>><?= ucwords($type) ?></option><?php }?></select></label>
    </span>
    <span class="pull-right">
        <label><select id="_changeDiagnosisStatus_" class="no-margin"><option value="" <?=((isset($_REQUEST['active']) && $_REQUEST['active']=="") || !isset($_REQUEST['active']))?' selected="selected"':'' ?>>-- Filter By Status --</option><option value="true"<?=(isset($_REQUEST['active']) && $_REQUEST['active']=="true")?' selected="selected"':'' ?>>UnResolved</option><option value="false"<?=(isset($_REQUEST['active']) && $_REQUEST['active']=="false")?' selected="selected"':'' ?>>Resolved</option></select></label>
    </span>
</div>
<?php }?>
<div class="dataTables_wrapper">
<?php if(count($data->data)==0){?><div class="notify-bar">No diagnoses available</div> <?php } else {
    $totalSearch = $data->total;
    ?>
<table class="table">
    <thead><tr><th>Date</th><th>Diagnosis</th><th class="hide">Body part</th><th>Type</th><th class="hide">Severity</th><th>Comment</th><th>Status</th><th>By</th></tr></thead>
    <tbody>
    <?php foreach($data->data as $diagnosis){//$diagnosis=new PatientDiagnosis();?>
        <tr><td><?= date(MainConfig::$dateTimeFormat, strtotime($diagnosis->date_of_entry))?></td>
            <td><?= strtoupper($diagnosis->diagnosisType)?> (<?=trim($diagnosis->code)?>): <?=$diagnosis->case?></td>
            <td class="hide"><?= $diagnosis->body_part !== null ?  $diagnosis->body_part  : '- - -'?></td>
            <td><?=ucwords($diagnosis->_status)?></td>
            <td class="hide"><?=ucwords($diagnosis->severity) ?></td>
            <td><?= wordwrap(ucwords($diagnosis->diagnosisNote), 40, ' ', true) ?></td>
            <td><?=($diagnosis->active) ? 'Active | <a href="javascript:;" class="resolveConditionLink" data-pid="' . $diagnosis->patient_ID . '" data-id="' . $diagnosis->id . '">Resolve</a>':'Resolved'?></td><td><?=$diagnosis->username?></td></tr>
    <?php }?>
    </tbody>
</table>
<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $data->total ?> records found (Page <?= $page+1?> of <?= ceil($data->total / $pageSize)?>)</div>
<div class="resultsPagerDiagnoses no-footer dataTables_paginate">
    <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
        <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
    </div>
</div>
    <script>
        $(document).on('click', '.resultsPagerDiagnoses.dataTables_paginate a.paginate_button', function(e){
            var page = $(this).data("page");
            if(!$(this).hasClass("disabled") && !e.handled){
                $("#contentPane").load('/patient_profile.php?id=<?= $id ?>&view=precon&type=<?=@$_REQUEST['type']?>&active=<?=@$_REQUEST['active']?>&page='+page);
                e.handled = true;
            }
        });
    </script>
<?php }?>
</div>
