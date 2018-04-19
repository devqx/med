<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/23/15
 * Time: 3:17 PM
 */
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Ward.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_wards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_blocks.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$costCentres = (new CostCenterDAO())->all();

$ward = (new WardDAO())->getWard($_REQUEST['id'], TRUE);
if ($_POST) {
    if(is_blank($_POST['name'])){
        exit("error:Please enter the ward name");
    }
    if(is_blank($_POST['base_price'])){
        exit("error:Please enter the ward base price");
    }
    if(is_blank($_POST['block'])){
        exit("error:Please select block where this ward is located");
    }
    if(is_blank($_POST['cost_centre_id'])){
        exit("error:Please select cost centre");
    }

    $ward->setName($_POST['name'])
        ->setBlock(new Block($_POST['block']))
        ->setCostCentre( new CostCenter($_POST['cost_centre_id']) )
        ->setBasePrice(parseNumber($_POST['base_price']));
    $update=(new WardDAO())->updateWard($ward);
    if($update===NULL){
        exit("error:Sorry we are unable to save this ward");
    }else{
        exit("success:Ward saved successfully");
    }
}
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
    <label>Ward Name
        <input type="text" name="name" placeholder="e.g. Surgery Ward" value="<?= $ward->getName() ?>" /></label>
    <label>Base Price <input type="number" value="<?= $ward->getBasePrice() ?>" name="base_price"></label>
    <label>Cost Centre <select name="cost_centre_id" required data-placeholder="-- Select Cost Centre --">
            <option value="" <?= ($ward->getCostCentre() == NULL) ? ' selected': ''?>></option>
            <?php foreach($costCentres as $c){?><option value="<?= $c->getId()?>" <?= ($ward->getCostCentre() != NULL && $ward->getCostCentre()->getId()===$c->getId()) ? ' selected': ''?>><?= $c->getName()?></option><?php }?>
        </select></label>
    <label>Block Name/Label  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addBlock.php', {title: 'Add Block', afterHide:function(){reloadBlock();}})">Add Block</a></span>
        <select name="block" id="block">
            <option value="">--- select block ---</option>
            <?php foreach($blocks as $key=>$b){ ?>
                <option value="<?= $b->getId() ?>" <?= ($b->getId()===$ward->getBlock()->getId()?"selected":"") ?>><?= $b->getName() ?></option>
            <?php } ?>
        </select></label>

    <div class="btn-ward">
        <button class="btn" type="submit" name="addWard">Save</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        <div id="console1"></div>
        <input type="hidden" name="id" value="<?= $ward->getId()?>">
    </div>
</form>
<script type="text/javascript">
    function start_() {
        $('#console1').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            $('#console1').html('<span class="alert-success">' + status_[1] + '</span>');
            Boxy.get($(".close")).hideAndUnload();
            showTabs(4);
        } else {
            $('#console1').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    function reloadBlock(){
        $.ajax({
            url: '/api/get_blocks.php',
            type: 'get',
            dataType: 'json',
            success: function(d){
                html="";
                for(var i=0; i<d.length; i++){
                    html=html+"<option value='"+d[i].id+"' >"+d[i].name+"</option>";
                }
                $("#block").html(html);
            }
        });
    }
</script>