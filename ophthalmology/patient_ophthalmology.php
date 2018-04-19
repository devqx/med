<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 11/30/15
 * Time: 12:34 PM
 */
$id = $_GET['id'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyCategoryDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) {
    exit($protect->ACCESS_DENIED);
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$temp = (new PatientOphthalmologyDAO())->findOphthalmologyRequests($id, $page, $pageSize, TRUE);
$pLabs = $temp->data;
$totalSearch = $temp->total;
?>

<div class="menu-head"><a href="javascript:" onclick="showTabs(17, 2)">Items Request</a> | <span id="newLink" class="bold"><a href="javascript:void(0)" onClick="Boxy.load('/ophthalmology/new.php?id=<?= $_GET['id'] ?>',{title: 'New Ophthalmology Request', afterHide: function() {showTabs(17); }})">New Ophthalmology Request</a></span></div>
<div class="dataTables_wrapper">
    <?php
    if (count($pLabs) > 0) { ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Requested by</th>
                <th>Date</th>
                <th>Ophthalmology Test</th>
                <th>Result</th>
                <th>Notes</th>
                <th>*</th>
            </tr>
            </thead>
            <?php foreach ($pLabs as $opthalms) { // $opthalms=new PatientOphthalmology(); ?>
                <tr>
                    <td><?= $opthalms->getOphthalmologyGroup()->getGroupName() ?></td>
                    <td><?= ($opthalms->getOphthalmologyGroup()->getReferral() !== NULL) ? '<span title="Referred from '.$opthalms->getOphthalmologyGroup()->getReferral()->getName() .'('.$opthalms->getOphthalmologyGroup()->getReferral()->getCompany()->getName().')"><i class="icon-info-sign"></i></span>':'' ?>

                        <span title="<?= $opthalms->getOphthalmologyGroup()->getRequestedBy()->getFullname() ?>"><?= $opthalms->getOphthalmologyGroup()->getRequestedBy()->getShortName() ?></span></td>
                    <td><?= date("d M, Y h:iA", strtotime($opthalms->getOphthalmologyGroup()->getRequestTime())) ?></td>
                    <td><?= $opthalms->getOphthalmology()->getName() ?></td>
                    <td><?php if($opthalms->getOphthalmologyResult() === NULL && $opthalms->getStatus()!=="cancelled"){ ?><a href="javascript:;" class="cancelOphthalmologyLink" data-id="<?=$opthalms->getId()?>">Cancel</a> |<?php }?><?php if ($opthalms->getOphthalmologyResult() === NULL && $opthalms->getStatus() !== "cancelled") { ?>
                            <a href="javascript:void(0)" onclick="Boxy.load('/ophthalmology/boxy.fillTestResult.php?testId=<?= $opthalms->getId() ?>&testType=<?= $opthalms->getOphthalmology()->getId() ?>', {title: 'Fill Result for <?= escape($opthalms->getOphthalmology()->getName()) ?>', afterHide: function () {
                                showTabs(17);
                                }})">Fill Result</a>
                        <?php } else if ($opthalms->getOphthalmologyResult() !== NULL && !$opthalms->getOphthalmologyResult()->isApproved()) { ?>
                            Waiting Approval
                        <?php } else if ($opthalms->getOphthalmologyResult() !== NULL && $opthalms->getOphthalmologyResult()->isApproved()) { ?>
                            <a href='javascript:;' onclick="new Boxy('<div><table class=\'table table-striped table-bordered table-hover\'>'
                                + '<tr><th>Field</th><th>Value</th></tr>'
                            <?php foreach ($opthalms->getOphthalmologyResult()->getData() as $data) { ?>
                                +'<tr><td><?= $data->getOphthalmologyTemplateData()->getLabel() ?></td><td><?= $data->getValue() ?></td></tr>'
                            <?php } if($opthalms->getOphthalmologyResult()->getAbnormalValue()){ ?>
                                + '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
                            <?php } ?>
                                + '</table></div>', {title: '<?= $opthalms->getOphthalmology()->getName() ?> Result'}
                                )" title="View this result">Result</a>
                        <?php } ?></td>
                    <td><a onclick="Boxy.load('/ophthalmology/ophthalmology.notes.php?id=<?= $opthalms->getId() ?>', {title: 'Lab Notes'})" href="javascript:void(0)">Notes</a></td>
                    <td class="nowrap">
                        <a href="/ophthalmology/printOphthalmology.php?gid=<?= $opthalms->getOphthalmologyGroup()->getGroupName() ?>" class="action _p_action"  title="Print this result" target="_blank" data-action="print"  ><i class="icon-print"></i></a>
                        <?php if ($opthalms->getOphthalmologyResult() !== NULL && !$opthalms->getOphthalmologyResult()->isApproved()) {?><a href="javascript:" onclick="editOphthalmologyResult(<?=$opthalms->getOphthalmology()->getId() ?>, <?= $opthalms->getId()?>, '<?=$opthalms->getOphthalmology()->getName() ?>')" class="action" title="Edit Result"><i class="icon-edit"></i></a><?php }?>
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
            var url = "/ophthalmology/patient_ophthalmology.php?id=<?=$_GET['id']?>&page="+page;
            $('#contentPane').load(url, function (responseText, textStatus, req) {});
            e.handled = true;
        }
    });
    $(function(){
        $('a.cancelOphthalmologyLink').live('click', function(e){
            var id = $(this).data("id");
            if(e.handled != true){
                Boxy.ask("Are you sure you want to cancel this request line item?", ["Yes", "No"], function(choice){
                    if(choice == "Yes"){
                        $.post('/api/ophthalmologyrequests.php', {id: id, action:"cancel"}, function(s){
                            if(s.trim()=="ok"){
                                $('a.cancelOphthalmologyLink[data-id="'+id+'"]').parent('td').html('&nbsp;');
                            } else {
                                Boxy.alert("An error occurred [The request might have been cancelled already] OR [Service Bills has been transferred,Please Reverse the bill AND try again]");
                            }
                        });
                    }
                });
                e.handled=true;
            }
        });
    });
</script>