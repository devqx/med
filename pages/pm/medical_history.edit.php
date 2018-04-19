<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/22/15
 * Time: 11:58 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryTemplateDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';
$typeOptions = getTypeOptions('datatype', 'history_template_data');
$history = (new HistoryDAO())->get($_GET['id']);

if($_POST){
    exit("error:Failed to update data");
    exit("success:test return data");
}
?>
<section style="width: 500px">
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: submitted_})">
        <div class="row-fluid">
            <label>Template Label <input type="text" name="t_label" value="<?= $history->getTemplate()->getLabel()?>"></label>
        </div>
        <div class="row-fluid">
            Data Elements <span class="pull-right add_line"><a href=""><i class="fa fa-plus-circle"></i>New Data Line</a> </span>
        </div>
        <?php foreach((new HistoryTemplateDataDAO())->byTemplate($history->getTemplate()->getId()) as $data){//$data = new HistoryTemplateData();?>
        <div class="row-fluid" data-id="<?=$data->getId() ?>">
            <div class="span7">
                <label><input type="text" name="tData_label[<?=$data->getId() ?>]" value="<?=$data->getLabel() ?>"></label>
            </div>
            <div class="span4">
            <label><select name="tData_type[<?=$data->getId() ?>]">
                    <?php foreach ($typeOptions as $op) {?>
                <option <?= ($data->getDataType()==$op) ?' selected="selected"' : ''?> value="<?= $op?>"><?= ucwords($op)?></option><?php }?>
            </select></label>
            </div>
            <div class="span1">
                <a class="btn" href="javascript:;" data-id="<?=$data->getId() ?>"><i class="fa fa-remove"></i></a>
            </div>
        </div>
        <?php }?>

        <div class="btn-block"></div>
        <button class="btn" type="submit">Update</button>
        <button class="btn-link" type="reset">Reset</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </form>

</section>
<script type="text/javascript">
    $newIdxs = ['a','b', 'c', 'd', 'e', 'f', 'g'];
    var chosen= -1;
    $(document).on('click','.span1 > a[data-id]', function (e) {
        if(!e.handled){
            // only delete those ones we have not saved [the ones with letter indices]
            if(!isNaN($(this).data("id") / 1 )){
                Boxy.alert("This template line might have been referenced in a patient data.");
            } else {
                // chosen = chosen == -1 ? -1: chosen - 1;
                $('.row-fluid[data-id="'+$(this).data('id')+'"]').remove();
            }
            e.handled = true;
        }
    });
    $(document).on('click','.add_line', function (e) {
        if(!e.handled){
            ++chosen;

            if(chosen in $newIdxs){
                var $randIdx = $newIdxs[chosen];

                var sel = '<select name="tData_type['+$randIdx+']"><?php foreach ($typeOptions as $op) {?><option <?= ($data->getDataType()==$op) ?' selected="selected"' : ''?> value="<?= $op?>"><?= ucwords($op)?></option><?php }?></select>';
                var containerDivParent = $('<div>');
                containerDivParent.attr('class', 'row-fluid');
                containerDivParent.attr("data-id", $randIdx) ;

                var span7 = $('<div>');
                span7.attr('class', 'span7');
                span7.append( $('<label>').append( $('<input type="text" name="tData_label['+$randIdx+']">') ) );

                var span4 = $('<div>');
                span4.attr('class', 'span4');
                span4.append( $('<label>').append(sel) );

                var span1 = $('<div>');
                span1.attr('class', 'span1');
                span1.append( $('<a class="btn" href="javascript:;" data-id="'+$randIdx+'"><i class="fa fa-remove"></i></a>') );
                containerDivParent.append(span7);
                containerDivParent.append(span4);
                containerDivParent.append(span1);

                setTimeout(function () {
                    $('select[name="tData_type['+$randIdx+']"]').select2({width:'100%'});
                }, 500);

                $('.row-fluid[data-id]:last').after(
                    containerDivParent
                );
            } else {
                alert("Oops! Options exhausted");
            }

            e.handled=true;
            e.preventDefault();
            return false
        }
    });

    var submitted_ = function(s){
        if(s.split(":")[0]==="error"){
            Boxy.alert(s.split(":")[1]);
        } else if (s.split(":")[0]==="success"){
            Boxy.info(s.split(":")[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            })
        }
    }
</script>