<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if(is_blank($_POST['q'])){
    exit('<div class="warning-bar">Empty search parameter</div>');
}
//ob_clean();

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$data = (new PatientOphthalmologyDAO())->findRequests($_POST['q'], $page, $pageSize, TRUE);

$totalSearch = $data->total;

if ($totalSearch < 1) {
    ?><div class="notify-bar">No search results for <?=$_POST['q']?></div>
<?php
} else {
    ?>
    <div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Results found</div>
    <table class="table table-striped table-hover no-footer">
        <thead>
            <tr>
                <th>Request Date</th>
                <th class="hide"></th>
                <th>ID</th>
                <th>Ophthalmology</th>
                <th>Patient</th>
                <th>Result</th>
                <th>Note</th>
                <th>*</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($data->data as $requests) {//$requests=new PatientOphthalmology();
            if($requests->getPatient()){ ?>
                <tr>
                    <td class="nowrap"><?= date("d M, Y h:iA", strtotime($requests->getOphthalmologyGroup()->getRequestTime())) ?></td>
                    <td class="hide"><?=strtotime($requests->getOphthalmologyGroup()->getRequestTime())?></td>
                    <td><?= $requests->getOphthalmologyGroup()->getGroupName() ?></td>
                    <td><?= $requests->getOphthalmology()->getName() ?></td>
                    <td>
                        <?= ($requests->getOphthalmologyGroup()->getReferral() !== NULL) ? '<span title="Referred from '.$requests->getOphthalmologyGroup()->getReferral()->getName() .'('.$requests->getOphthalmologyGroup()->getReferral()->getCompany()->getName().')"><i class="icon-info-sign"></i></span>':'' ?>

                        <span data-pid="<?= $requests->getPatient()->getId() ?>" class="profile"><?= $requests->getPatient()->getShortname() ?></span></td>
                    <td class="nowrap"><?php if($requests->getOphthalmologyResult() === NULL && $requests->getStatus()=="open" /*&& $requests->getReceived()===FALSE*/){ ?>
                            <a href="javascript:;" class="cancelLabLink" data-id="<?=$requests->getId()?>">Cancel</a> |
                        <?php }?>
                        <?php if ($requests->getOphthalmologyResult() === NULL && $requests->getStatus()=="open" /*&& !empty($requests->getSpecimens()) && $requests->getReceived()===TRUE*/) { ?>
                            <a href="javascript:void(0)" onclick="Boxy.load('/ophthalmology/boxy.fillTestResult.php?testId=<?= $requests->getId() ?>&testType=<?= $requests->getOphthalmology()->getId() ?>', {title: 'Fill Result for <?= escape($requests->getOphthalmology()->getName()) ?>', afterHide: function () {
                                location.reload();
                                }})">Fill Result</a>
                        <?php } else if ($requests->getOphthalmologyResult() !== NULL && !$requests->getOphthalmologyResult()->isApproved()) { ?>
                            Waiting Approval
                        <?php } else if ($requests->getOphthalmologyResult() !== NULL && $requests->getOphthalmologyResult()->isApproved()) { ?>
                            <a href='javascript:;' onclick="new Boxy('<div><table class=\'table table-striped table-bordered table-hover\'>'
                                + '<tr><th>Field</th><th>Value</th></tr>'
                            <?php foreach ($requests->getOphthalmologyResult()->getData() as $data) { ?>
                                +'<tr><td><?= $data->getOphthalmologyTemplateData()->getLabel() ?></td><td><?= htmlentities($data->getValue()) ?></td></tr>'
                            <?php } if($requests->getOphthalmologyResult()->getAbnormalValue()){ ?>
                                + '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
                            <?php } ?>
                                + '</table></div>', {title: '<?= $requests->getOphthalmology()->getName() ?> Result'}
                                )" title="View this result">Result</a>
                        <?php } ?>
                    </td>
                    <td>
                        <a onclick="Boxy.load('/ophthalmology/ophthalmology.notes.php?id=<?= $requests->getId() ?>', {title: 'Lab Notes'})" href="javascript:void(0)">Notes</a>
                    </td>
                    <td class="nowrap">
                        <a href="/ophthalmology/printOphthalmology.php?gid=<?= $requests->getOphthalmologyGroup()->getGroupName() ?>" class="action _p_action"  title="Print this result" target="_blank" data-action="print"  ><i class="icon-print"></i></a>
                        <?php if ($requests->getOphthalmologyResult() !== NULL && !$requests->getOphthalmologyResult()->isApproved()) {?><a href="javascript:" onclick="editOphthalmologyResult(<?=$requests->getOphthalmology()->getId() ?>, <?= $requests->getId()?>, '<?=$requests->getOphthalmology()->getName() ?>')" class="action" title="Edit Result"><i class="icon-edit"></i></a><?php }?>
                    </td>
                </tr>
            <?php } } ?>
        </tbody>

    </table>
    <div class="resultsPager dataTables_wrapper no-footer">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>

        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <?php /*<span>
                <?php if(ceil($data->total/$pageSize) >= 1 ){?><a class="paginate_button <?= (1 == $page) ?"current":""?>" data-page="1">1</a><?php }?>
                <?php if(ceil($data->total/$pageSize) >= 2){?><a class="paginate_button <?= (2 == $page) ?"current":""?>" data-page="2">2</a><?php }?>
                <?php if(ceil($data->total/$pageSize) > 2){?><span>&hellip;</span> <a class="paginate_button" data-page="<?= ceil($data->total/$pageSize) ?>"><?= ceil($data->total/$pageSize) ?></a><?php }?>
            </span> */?>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
    <script type="text/javascript">
        function editOphthalmologyResult(testId, plId, testName){
            Boxy.load('/ophthalmology/editResult.php?testId='+testId+'&plId='+plId, {title: testName});
        }
    </script>

<?php } ?>