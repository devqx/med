<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/20/14
 * Time: 4:25 PM
 */
$id = $_GET['id'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) {
    exit($protect->ACCESS_DENIED);
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$temp = (new LabGroupDAO())->getPatientLabGroups($id, $page, $pageSize, true);
$pLabs = $temp->data;
$totalSearch = $temp->total;
?>

<?php if ($this_user->hasRole($protect->lab) || $this_user->hasRole($protect->lab_super) || $this_user->hasRole($protect->doctor_role)) { ?><div class="menu-head"><span id="newLink"><i class="icon-picture"></i><a href="javascript:void(0)" onClick="Boxy.load('/labs/allLabs.php?id=<?= $_GET['id'] ?>',{title: 'Lab Request', afterHide: function() {showTabs(6); }})">Request Lab</a></span></div><?php } ?>
<div class="dataTables_wrapper">
<?php
if (count($pLabs) > 0) { ?>
<table class="table">
    <thead>
        <tr>
            <th>Lab Request ID</th>
            <th>Requested by</th>
            <th>Request date</th>
            <th>Request Specimen</th>
            <th>*</th>
        </tr>
    </thead>
    <?php foreach ($pLabs as $lg) { ?>
        <tr class="head-link" id="lab<?= $lg->getGroupName() ?>" data-group='<?= $lg->getGroupName() ?>'>
            <td><?= $lg->getGroupName() ?></td>
            <td><?= $lg->getRequestedBy()->getFullname() ?></td>
            <td><?= date("M d, Y", strtotime($lg->getRequestTime())) ?></td>
            <td><?php
                $s__ = array();
                foreach ($lg->getPreferredSpecimens() as $s) {
                    $s__[] = $s->getName();
                }
                echo implode(", ", $s__)
                ?></td>
            <td><a href="/labs/printLab.php?gid=<?= $lg->getGroupName() ?>" class="btn btn-small1 _p_action"  title="Print this result" target="_blank" data-action="print"  >Print</a></td>
        </tr>
        <tr>
            <td colspan="5" data-group='<?= $lg->getGroupName() ?>' style="display: none;">
                <table class="table table-striped">
                    <tr class="fadedText">
                        <th>Test Type</th>
                        <th>Test Result</th>
                        <th>Range</th>
                        <th>Tested By</th>
                        <th>Specimen</th>
                        <th>Notes</th>
                    </tr>
                    <?php foreach ($lg->getRequestData() as $pl) { ?>
                        <tr>
                            <td><?= $pl->getTest()->getName() ?></td>
                            <td><?php if ($pl->getLabResult() !== NULL && $pl->getLabResult()->isApproved()) { ?>
                                    <a href='javascript:;' onclick="new Boxy('<div><table class=\'table table-striped table-bordered table-hover\'>'
                                                    + '<tr><td>Field</td><td>Value</td></tr>'
                                    <?php foreach ($pl->getLabResult()->getData() as $data) { ?>
                                                +'<tr><td><?= $data->getLabTemplateData()->getLabel() ?><?php if($data->getLabTemplateData()->getReference()!=""){?><br>(Reference: <?= $data->getLabTemplateData()->getReference()?>)<?php }?></td><td><?= $data->getValue() ?></td></tr>'
                                    <?php } if($pl->getLabResult()->getAbnormalValue()){ ?>
                                        + '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
                                    <?php } ?>

                                            + '</table></div>', {title: '<?= $pl->getTest()->getName() ?> Result'}
                                            )" title="View this result">Result</a> 
                                       <?php
                                   } else if ($pl->getLabResult() !== NULL && !($pl->getLabResult()->isApproved())) {
                                       echo 'Waiting Approval';
                                   } else {
                                       echo 'Not Ready';
                                   }
                                   ?>
                            </td>

                            <td><?= $pl->getTest()->getReference() ?></td>
                            <td><?= ($pl->getPerformedBy() !== NULL) ? $pl->getPerformedBy()->getFullname() : 'N/A' ?></td>
                            <td><?php
                                if (count($pl->getSpecimens()) < 1) {
                                    echo 'N/A';
                                } else {
                                    $s_ = array();
                                    foreach ($pl->getSpecimens() as $s) {
                                        $s_[] = $s->getName();
                                    }
                                    echo implode(", ", $s_);
                                }
                                ?></td>
                            <td>
                                <?php if($pl->getStatus()=="open" && empty($pl->getSpecimens())){ ?>
                                    <a href="javascript:;" title="cancel request item" class="btn cancelLabLink btn-mini" data-id="<?=$pl->getId()?>"><i class="icon-remove"></i></a>
                                <?php }?>

                                <i class="icon-paste"></i><a href="javascript:void(0)" onClick="Boxy.load('/labs/lab.notes.php?id=<?= $pl->getId() ?>')">View
                                    Lab Notes</a></td>
                        </tr>

                    <?php } ?>
                </table>
            </td>
        </tr>
    <?php }
    ?>
</table>
    <?php } else { ?>
    <div class="notify-bar">Nothing found to display at the moment</div>
<?php } ?>
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div class="resultsPager no-footer dataTables_paginate">
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click', '.resultsPager.dataTables_paginate a.paginate_button', function(e){
        var page = $(this).data("page");
        if(!$(this).hasClass("disabled") && !e.handled){
            var url = "/labs/patientLabs.php?id=<?=$_GET['id']?>&page="+page;
            $('#contentPane').load(url, function (responseText, textStatus, req) {});
            e.handled = true;
        }
    });
    $("tr[data-group]").click(function(e){
        //console.log("td[data-group='"+$(this).attr("data-group")+"']")
        $("td[data-group='"+$(this).attr("data-group")+"']").toggle()
    })
</script>