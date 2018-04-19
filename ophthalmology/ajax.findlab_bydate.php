<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/17/15
 * Time: 3:26 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if (isset($_POST['date_start']) && $_POST['date_stop']){
    $start = $_POST['date_start'];
    $stop  = $_POST['date_stop'];
}else {
    $start = date("Y-m-d");
    $stop  = date("Y-m-d");
}

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$data = (new PatientOphthalmologyDAO())->findRequestsByDate($start, $stop, $page, $pageSize, TRUE);
$totalSearch = $data->total;
if ($totalSearch < 1) {
    echo '<div class="notify-bar">There are no Ophthalmology requests found that matched ' . $_POST['q'] . '</div>';
} else {
    ?>
    <div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Results found for dates between <em><?= date("Y M,d", strtotime($start)) ?></em> and <em><?= date("Y M,d", strtotime($stop)) ?></em></div>
    <table class="table bydatelist table-striped table-hover">
        <thead>
        <tr>
            <th>Request Date</th>
            <th class="hide"></th>
            <th>ID</th>
            <th>Ophthalmology Service</th>
            <th>Patient</th>
            <th>Result</th>
            <th>Note</th>
            <th>*</th>
        </tr>
        </thead>
        <?php foreach ($data->data as $labs) {  ?>
            <tr>
                <td class="nowrap"><?= date("d M, Y h:iA", strtotime($labs->getOphthalmologyGroup()->getRequestTime())) ?></td>
                <td class="hide"><?=strtotime($labs->getOphthalmologyGroup()->getRequestTime()) ?></td>
                <td><?= $labs->getOphthalmologyGroup()->getGroupName() ?></td>
                <td><?= $labs->getOphthalmology()->getName() ?></td>
                <td>
                    <?= ($labs->getOphthalmologyGroup()->getReferral() !== NULL) ? '<span title="Referred from '.$labs->getOphthalmologyGroup()->getReferral()->getName() .'('.$labs->getOphthalmologyGroup()->getReferral()->getCompany()->getName().')"><i class="icon-info-sign"></i></span>':'' ?>
                    <span data-pid="<?= $labs->getPatient()->getId() ?>" class="profile"><?= $labs->getPatient()->getShortname() ?></span> </td>
                <td class="nowrap"><?php if($labs->getStatus()=="open" && $labs->getOphthalmologyResult() === NULL /*&& $labs->getReceived()===FALSE*/){ ?>
                        <a href="javascript:;" class="cancelOphthalmologyLink" data-id="<?=$labs->getId()?>">Cancel</a> |
                    <?php }?>
                    <?php if ($labs->getStatus()=="open" && $labs->getOphthalmologyResult() === NULL /*&& !empty($labs->getSpecimens())*/) { ?>
                        <a href="javascript:void(0)" onclick="Boxy.load('/ophthalmology/boxy.fillTestResult.php?testId=<?= $labs->getId() ?>&testType=<?= $labs->getOphthalmology()->getId() ?>', {title: 'Fill Result for <?= escape($labs->getOphthalmology()->getName()) ?>', afterHide: function () {
                            location.reload();
                            }})">Fill Result</a>
                    <?php } else if ($labs->getOphthalmologyResult() !== NULL && !$labs->getOphthalmologyResult()->isApproved()) { ?>
                        Waiting Approval
                    <?php } else if ($labs->getOphthalmologyResult() !== NULL && $labs->getOphthalmologyResult()->isApproved()) { ?>
                        <a href='javascript:;' onclick="new Boxy('<div style=\'width:600px\'><table class=\'table table-striped table-bordered table-hover\'>'
                            + '<tr><th>Field</th><th>Value</th></tr>'
                        <?php foreach ($labs->getOphthalmologyResult()->getData() as $data) { ?>
                            +'<tr><td><?= $data->getOphthalmologyTemplateData()->getLabel() ?></td><td><?= $data->getValue() ?></td></tr>'
                        <?php } if($labs->getOphthalmologyResult()->getAbnormalValue()){ ?>
                            + '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr>'
                        <?php } ?>
                            + '</table></div>', {title: '<?= $labs->getOphthalmology()->getName() ?> Result'}
                            )" title="View this result">Result</a>
                    <?php } ?>
                </td>
                <td>
                    <a onclick="Boxy.load('/ophthalmology/ophthalmology.notes.php?id=<?= $labs->getId() ?>', {title: 'Ophthalmology Notes'})" href="javascript:void(0)">Notes</a>
                </td>
                <td class="nowrap">
                    <a href="/ophthalmology/printOphthalmology.php?gid=<?= $labs->getOphthalmologyGroup()->getGroupName() ?>" class="action _p_action"  title="Print this result" target="_blank" data-action="print"  ><i class="icon-print"></i></a>
                    <?php if ($labs->getOphthalmologyResult() !== NULL && !$labs->getOphthalmologyResult()->isApproved()) {?><a href="javascript:" onclick="editOphthalmologyResult(<?=$labs->getOphthalmology()->getId() ?>, <?= $labs->getId()?>, '<?=$labs->getOphthalmology()->getName() ?>')" class="action" title="Edit Result"><i class="icon-edit"></i></a><?php }?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <div class="bydatelist dataTables_wrapper no-footer">
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

<?php
}?>