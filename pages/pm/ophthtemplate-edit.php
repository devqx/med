<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 8/21/15
 * Time: 11:00 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyTemplateDAO.php';

$template = (new OphthalmologyTemplateDAO())->getTemplate($_REQUEST['id']);
if($_POST){
    $labTempDatum = array();
    foreach ($_POST['tData'] as $id=>$data) {
        $labTempData = new OphthalmologyTemplateData();
        $labTempData->setId($id);
        $labTempData->setLabel($data['name']);
        $labTempData->setReference($data['ref']);
        $labTempDatum[] = $labTempData;
    }
    $update = (new OphthalmologyTemplateDataDAO())->update($labTempDatum);
    exit(json_encode($update));
}
?>
<div style="width: 600px;">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': _start, 'onComplete': _done});">
        <input type="hidden" name="id" value="<?=$template->getId()?>">
        <label>Ophthalmology Template
            <input type="text" readonly name="labTemplate" id='labTemplateLabel' placeholder="Malaria Template" value="<?=$template->getLabel()?>"></label>
        <label>Template Fields
            <?php foreach($template->getData() as $tData){ ?>
                <div class="row-fluid">
                    <div class="span6">Label:
                        <input name="tData[<?=$tData->getId() ?>][name]" type="text" value="<?= $tData->getLabel()?>">
                    </div>
                    <div class="span6">Reference:
                        <input type="text"  name="tData[<?=$tData->getId() ?>][ref]" value="<?= $tData->getReference()?>" placeholder="Reference" />
                    </div>
                </div>
            <?php }?></label>
        <label></label>

        <div class="btn-block">
            <button type="submit" class="btn">Update Template</button>
            <button type="button" data-name="cancel" onclick="Boxy.get(this).hide()" class="btn-link">Cancel &raquo;</button>
        </div>

    </form>
</div>
<script>
    function _start(){
        $('.loading_place').html('Please wait...');
    }
    function _done(s){
        var data = JSON.parse(s);
        if(data[0] !== null){
            Boxy.info("Changes have been saved", function(){
                Boxy.get($(".close")).hideAndUnload();
            });
        }
        else {
            Boxy.alert('An error occurred, please try again');
        }
    }
</script>