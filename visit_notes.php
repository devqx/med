<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/22/16
 * Time: 11:49 PM
 */

@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.assessments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
if(!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse))
    return $protect->ACCESS_DENIED;
$assessment = new Assessments();
$page= isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$notes = (new VisitNotesDAO())->getPatientNotes($_GET['id'], $page, $pageSize);
$totalSearch = $notes->total;
$_SESSION['w'] = null;
$pid = $_GET['id'];

$ip = (new InPatientDAO())->getActiveInPatient($pid, TRUE);
?>
<!--    start if open encounter-->
<div class="menu-head hide">
<div style="width:50%;float:left">

    <?php if ($this_user->hasRole($protect->doctor_role)) { ?>
        <i class="icon-folder-open"></i>
        <label class="no-margin" style="display: inline-block">
            <a href="javascript:void(0)" onClick="Boxy.load('boxy.soap.php?pid=<?= $_GET['id'] ?>',{title:'Add Note / Diagnosis Report'})">
                S. O. A. P.</a>
        </label> |<?php } ?>

    <?php if ($this_user->hasRole($protect->doctor_role)) {
        if (is_dir("admissions")) { ?>
            <a href="javascript:void(0)">
                <label class="no-margin" style="cursor:pointer;display:inline-block">
                <?php if ($ip === NULL ) { ?>
                    <input type="checkbox" id="toAdmit" name="ger89" value="<?= $pid ?>" onClick="askAdmit($(this),'<?= $pid ?>')">Request Admission<?php } else { ?>
                    <input type="checkbox" name="ger89" value="<?= $pid ?>" onClick="askDischarge($(this), '<?= $pid ?>')">Discharge<?php } ?>
            </label>
            </a> | <?php }
    } ?>
    <?php if ($this_user->hasRole($protect->doctor_role)) { ?>
        <label class="no-margin" style="display: inline-block">
            <i class="icon-book"></i>
            <a href="javascript:void(0)" onclick="showNewDocNote()">Doc. Note</a>
        </label> <?php } ?>
</div>

    <?php if ($this_user->hasRole($protect->doctor_role)) { ?>
        <div style="width:auto;float:right">
            <i class="icon-exchange"></i>
            <a href="javascript:void(0)" onClick="Boxy.load('/boxy.transferPatient.php?id=<?= $_GET['id'] ?>',{title:'Transfer Patient'})" title="Transfer Patient to another Doctor">Refer Patient</a>
        </div><?php } ?>

</div>
    <!--    end if open encounter-->
<?php
if($notes->total <= 0){?>
    <div class="warning-bar">This patient does not have any <?=((@$type != NULL) ? @$type : '') ?> visit record </div>
<?php } else {?>
<div id="visitContainer">
    <div class="dataTables_wrapper">
        <table class="table table-striped" id="visits_">
            <thead><tr><th align="left" class="nowrap">Date</th><th align="left">Notes</th><th align="left">Noted By</th></tr></thead>
            <?php foreach($notes->data as $row_data){
                //$row_data = new VisitNotes();
                if((date("d/m/Y", strtotime($row_data->date_of_entry))==date("d/m/Y"))&&$row_data->noted_by ==$this_user->getId()){
                    $edit_option = ' | <a href="javascript:;" onClick="Boxy.load(\'/boxy.doctor.note.edit.php?note_id='.$row_data->id.'\',{title:\'Edit Doctor Note\'})">Edit</a>';
                } else {
                    $edit_option = '';
                }?>

                <tr<?= ((date("jS M, Y", strtotime($row_data->date_of_entry))!=$_SESSION['w'])?' style="border-top:solid 2px #bbb"':'')?>>
                    <td class="nowrap"><?=date("<\\s\\m\\a\\l\\l>jS M, Y h:ia</\\s\\m\\a\\l\\l>", strtotime($row_data->date_of_entry))?></td>
                    <td>
                        <?php if($row_data->note_type=='o'){?>
                            <?=$assessment->formatObjectiveNote($row_data->description);?>
                        <?php }else if($row_data->note_type=='d'){?>
                            <span class="doc_note">Doc Note:</span>
                            <?= $row_data ->description.$edit_option;?>
                        <?php }else if(in_array($row_data->note_type, ['p','m'])){?>
                            <span class="plan_note">Plan:</span>
                            <?=$row_data->description.$edit_option;?>
                        <?php }else if($row_data->note_type=='a'){?>
                            <span class="diag_note">Diagnosis:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='t'){?>
                            <span class="diag_note">Medical History:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='i'){?>
                            <span class="inv_note">Investigation:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='g'){?>
                            <span class="diag_note">Diagnosis Note:</span>
                            <?= $row_data->description;?>
                        <?php }else if($row_data->note_type=='e'){?>
                            <span class="inv_note">Examination:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='r'){?>
                            <span class="ref_note">Referral:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='v'){?>
                            <span class="review_note">Systems Review:</span>
                            <?=$row_data->description;?>
                        <?php }else if($row_data->note_type=='x'){?>
                            <span class="review_note">Physical Exam:</span>
                            <?=$row_data->description;?>
                        <?php }else {?>
                            <span class="com_note">Complaint:</span>
                            <?=$row_data->description.$edit_option;?>
                        <?php } ?></td>
                    <td valign="top"><?= $row_data->noted_by ? $row_data->username :'N/A' ?></td></tr>
                <?php $_SESSION['w'] = date("jS M, Y", strtotime($row_data->date_of_entry));
            }?>
        </table>
        <div class="list3 dataTables_wrapper no-footer">
            <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>

            <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
                <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
                <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
                <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
                <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
            </div>
        </div>
    </div>
</div>

<?php }?>

<script>
    $(document).on('click', '.list3.dataTables_wrapper a.paginate_button', function(e){
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                $.get('/visit_notes.php', {'page': page, 'id':'<?= $_GET['id']?>'}, function (s) {
                    $('#visitContainer').html($(s).filter('#visitContainer').html());
                });
            }
            e.clicked = true;
        }
    });
</script>
