<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/23/15
 * Time: 10:21 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDAO.php';
$lab = (new OphthalmologyResultDAO())->get($_GET['id'], TRUE);

?>
<div style="width:600px;">
    <table class="table table-striped table-bordered table-hover">
        <tr><th>Field</th><th>Value</th></tr>
    <?php foreach ($lab->getData() as $k=>$data) {?>
        <tr>
            <td><?= $data->getOphthalmologyTemplateData()->getLabel() ?><?php if($data->getOphthalmologyTemplateData()->getReference()!=""){?><br>
                (Reference: <?= $data->getOphthalmologyTemplateData()->getReference()?>)<?php }?></td>
            <td><?= $data->getValue() ?></td>
        </tr>
    <?php } ?>
        <tr>
            <td colspan="2"><label><input name="abnormal" type="checkbox" data-id="<?= $lab->getId() ?>" class="__setAbnormal"<?= ($lab->getAbnormalValue())? ' checked':'' ?>> Abnormal Ophthalmology Value?</label></td>
        </tr>
        <tr>
            <td colspan="2">
                <a href="javascript:" data-ophthalmology-id="<?= $lab->getPatientOphthalmology()->getOphthalmologyGroup()->getGroupName() ?>" data-id="<?= $lab->getId() ?>" class="__aprove">Approve</a> |
                <a href="javascript:" onclick="editOphthalmologyResult(<?=$lab->getPatientOphthalmology()->getOphthalmology()->getId() ?>, <?= $lab->getPatientOphthalmology()->getId()?>, '<?=$lab->getPatientOphthalmology()->getOphthalmology()->getName() ?>')" title="Edit Result">Edit</a> |
                <a href="javascript:" data-id="<?= $lab->getId() ?>" class="__reject">Reject</a>
            </td>
        </tr>
    </table>
</div>
<script>
 var editOphthalmologyResult = function(testId, plId, testName){
    Boxy.load('/ophthalmology/editResult.php?testId='+testId+'&plId='+plId, {title: testName, afterHide:function(){
        Boxy.get($(".close")).hideAndUnload();
    }});
};
$(document).ready(function(){
    $('input[name="abnormal"]').on('click', function(e){
        var chkd = $(this).is(":checked");
        var abnVal = (chkd === true)? 1 : 0;
        var id = $(this).data('id');

        if(e.enabled != true){
            Boxy.ask("Would you want to mark this result as "+(chkd?"AB":"")+"NORMAL?", ["Yes", "Not really"], function (dat) {
                if(dat === "Yes"){
                    $.post('/ophthalmology/result.action.php', {id: id, a: abnVal, action:"abnormal"});
                }
            });
            e.enabled=true;
        }
    });
});
</script>