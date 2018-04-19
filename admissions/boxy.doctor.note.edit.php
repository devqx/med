<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/18/16
 * Time: 8:15 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] ."/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if(!$this_user->hasRole($protect->doctor_role))
    exit ($protect->ACCESS_DENIED);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProgressNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
$note_id = $_REQUEST['note_id'];
$progressNote = (new ProgressNoteDAO())->getProgressNote($note_id);

if (isset($_POST['note'])) {
    if(isset($_POST['note']) && strlen(trim($_POST['note']))<2){
        exit("error:Please make some note");
    }
    $_GET['suppress'] = true;
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
    $pNote = new ProgressNote();
    $pNote->setInPatient(new InPatient($_POST['aid']));
    $pNote->setNote($_POST['note']);
    $pNote->setNotedBy($staff);

    $pNote_ = (new ProgressNoteDAO())->updateProgressNote($pNote);
    if ($pNote_ === NULL) {
        exit("error:Sorry something went wrong");
    } else {
        exit("success:Progress Note updated successfully");
    }
}
?>


<div>
    <form method="post" id="pNForm" >
        <label>Note
            <textarea class="form-control" name="note"><?= $progressNote->getNote() ?></textarea>
        </label>
        <div class="btn-block">
            <input type="hidden" name="aid" value="<?= $_REQUEST['aid'] ?>" />
            <button type="button" onclick="save()" class="btn" disabled>Save &raquo;</button>
            <button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
        </div>
    </form>

</div>

<script type="text/javascript">
    function save() {
        $.ajax({
            url: '<?= $_SERVER["PHP_SELF"] ?>',
            type: 'post',
            datType: 'json',
            data: $("#pNForm").serialize(),
            success: function(data) {
                console.log(data);
                var ret = data.split(":");
                if(ret[0]==="error"){
                    Boxy.alert(ret[1]);
                } else {
                    Boxy.get($('.close')).hideAndUnload();
                }
            },
            error: function(data) {
                Boxy.alert("Oops! Something went wrong");
            }
        });
    }
</script>