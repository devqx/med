<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/26/15
 * Time: 1:57 PM
 */
if(!isset($_SESSION)){
    @session_start();
}
if($_POST){
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalNote.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalNoteDAO.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';

    $instance = (new AntenatalEnrollmentDAO())->get($_POST['instance_id']);
    if (is_blank($_POST['noteText'])) {
        exit("error:Note is blank");
    }
    $instance = (new AntenatalEnrollmentDAO())->get($_POST['instance_id']);
    //error_log(json_encode($instance));
    $note = (new AntenatalNote())->setType("normal")->setPatient($instance->getPatient())->setAntenatalInstance($instance)->setEnteredBy(new StaffDirectory($_SESSION['staffID']))->setNote($_POST['noteText']);

    if((new AntenatalNoteDAO())->add($note) !== NULL){
        exit("success:Saved note");
    }
    exit("error:Failed to add Note");
}
?>
<div style="width: 700px">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
          onsubmit='return AIM.submit(this, {onStart : function(){$(document).ajaxStart()}, onComplete : function(s) {
            var data = s.split(":");
            if(data[0]==="error"){
            Boxy.alert(data[1]);
            } else {
            showTabs(1);
            Boxy.get($(".close")).hideAndUnload();
            }
          }});'>
        <label>Note Description:
            (subjective)<textarea rows="3" name="noteText" style="width:100% !important"></textarea></label>
        <input type="hidden" name="instance_id" value="<?= $_GET['instance'] ?>"/>

        <div class="btn-block">
            <button class="btn" type="submit">Save &raquo;</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
