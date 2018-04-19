<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

$bills=new Bills();
$procedure = (new PatientProcedureDAO())->get($_GET['id']);
$pat=(new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(),false,NULL, NULL);

$templates = (new ProcedureTemplateDAO())->all();
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientProcedureNote.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/StaffDirectory.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientProcedureMedicalReport.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureMedicalReportDAO.php';
    $pNote = new PatientProcedureMedicalReport();
    $pNote->setPatientProcedure( new PatientProcedure($_GET['id']) );
    if(!is_blank($_POST['note'])){$pNote->setContent($_POST['note']);}else{exit("error:Report Content ?");}

    if(!isset($_SESSION)){session_start();}
    $pNote->setCreateUser(new StaffDirectory($_SESSION['staffID']));

    $newNote = (new PatientProcedureMedicalReportDAO())->add($pNote);

    if($newNote !== NULL){
        exit("success:Report saved successfully");
    }
    exit("error:Failed to add report");
}
?>
<section style="width: 900px;">
    <form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:__start,onComplete:__done})">
        <label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="procedure_template_link" data-href="/consulting/template_help.php">help</a> <!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>--> | <i class="icon-plus-sign"></i><a href="javascript:;" class="procedure_template_link" data-href="dialogs/template_new.php">add to list</a></span>
            <select name="template_id" id="template_id" placeholder="Select Custom Text Templates">
                <option></option>
                <?php foreach($templates as $t){//$t=new ExamTemplate()?><option value="<?=$t->getId()?>" data-text="<?= ($t->getContent())?>"><?=$t->getCategory()->getName()?></option><?php } ?>
            </select>
        </label>
        <label>Report:<textarea name="note" placeholder="Medical Report Content..."></textarea></label>

        <div class="btn-block">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>

    </form>
</section>
<script type="text/javascript">
    function __start(){}
    function __done(s){
        var data = s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1]);
        }else if(data[0]==="success"){
            Boxy.info(data[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }
    }

    $(document).ready(function () {
        $('[name="note"]').summernote(SUMMERNOTE_CONFIG);

        $('.boxy-content a.procedure_template_link').click(function(){
            Boxy.load(""+$(this).data("href"));
        });

        $('.boxy-content #template_id').select2().change(function (data) {
            if(data.added != null){
                var content = $(data.added.element).data("text");
                $('textarea[name="note"]').code(content).focus();
            } else {
                $('textarea[name="note"]').code('').focus();
            }
        }).trigger('change');
    });

    function refreshProcedureTemplates(){
        $.ajax({
            url:"/api/get_procedure_templates.php",
            dataType:'json',
            complete: function(s){
                var data = s.responseJSON;
                var str = '<option></option>';
                for(var i=0;i< data.length;i++){
                    str += '<option value="'+data[i].id+'" data-text="'+data[i].content+'">'+data[i].category.name+'</option>';
                }
                $('#template_id').html(str);
            }
        });
    }
</script>