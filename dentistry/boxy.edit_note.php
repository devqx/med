<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 11:20 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDentistryNote.php';

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/DentistryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';

$all = (new DentistryDAO())->getServices();
$imagingTpls = (new DentistryTemplateDAO())->all();

if(isset($_GET['id'])){
    $noteID = $_GET['id'];
    $getNote = (new PatientDentistryNoteDAO())->getNote($noteID);
}
else {
    $noteID = '';
}

$bills=new Bills();
$scan = (new PatientDentistryDAO())->get($_GET['scan_id']);
$pat=(new PatientDemographDAO())->getPatient($scan->getPatient()->getId(),FALSE,NULL, NULL);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

if($_POST){
    if($noteID==''){
        exit("error:Select note to edit");
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
    $note = new PatientDentistryNote();
    if($selfOwe - $creditLimit > 0){
        exit("error:Patient has outstanding credit");
    }
    if(!is_blank($_POST['scan_note'])){
        $note->setId($noteID);
        $note->setNote($_POST['scan_note']);
    } else {
        exit("error:Note is blank");
    }

    $updatedNote = (new PatientDentistryNoteDAO())->editNote($note);
    if($updatedNote !== NULL){
        exit("success:Note updated");
    }
    exit("error:Failed to update note");
}
?>
<section style="width: 730px">
    <div class="well">
        Patients outstanding is: &#8358;<?= number_format($selfOwe,2); ?>
    </div>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: completed})">
        <h6>Note</h6>
        <label>Template <select name="template" id="template_what_text" placeholder="--select template--"><option></option>
                <?php foreach($imagingTpls as $tpl){ ?>
                    <option value="<?= $tpl->getId() ?>"><?= $tpl->getTitle() ?> (<?= $tpl->getCategory()->getName() ?>)</option>
                <?php } ?>
            </select></label>
        <label style="margin-top: 18px;"><textarea placeholder="type note here..." name="scan_note" id="scan_note"><?= (isset($getNote))? $getNote->getNote() : '' ?></textarea></label>
        <div class="btn-block">
            <button type="submit" class="btn"<?=($selfOwe - $creditLimit > 0 ?' disabled="disabled"':'') ?>>Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</section>
<script type="text/javascript">
function completed(s){
    var result = s.split(":");
    if(result[0]=="error"){
        Boxy.alert(result[1]);
    }else{
        Boxy.info(result[1], function () {
            Boxy.ask("Submit for approval?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/dentistry/ajax.approve_.php', {id: <?= $_GET['scan_id'] ?>}, function(s){
                        if(s.trim()=="ok"){
                            Boxy.get($(".close")).hideAndUnload();
                            $('#scanHomeMenuLinks a.approve').click();
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
                else {
                    Boxy.get($(".close")).hideAndUnload();
                }
            });
        });
    }
}

$(document).ready(function () {
    $("#template_what_text").change(function () {
        var d = $('#scan_note' ).code() + "<br>";
        $.get('/api/get_dentistry_tpl.php', { id: $(this).val() }, function(data){
            var s = JSON.parse(data);
            $('#scan_note' ).code(d + s.bodyPart);
        });
    });
    $('textarea[name="scan_note"]').summernote(SUMMERNOTE_CONFIG);

})
</script>