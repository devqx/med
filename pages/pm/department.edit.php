<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/30/16
 * Time: 7:29 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/CostCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Department.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CostCenterDAO.php';

$department = (new DepartmentDAO())->get($_GET['id']);
$costCentres = (new CostCenterDAO())->all();

if($_POST){
    $department->setName($_POST['dept_name'])
        ->setCostCentre( new CostCenter($_POST['cost_centre_id']) );

    $ret = (new DepartmentDAO())->update($department);
    if($ret!==NULL){
        exit("ok");
    }else {
        exit("error");
    }
}?>
<script>
    function startUpdate(){
        $('span[data-name="console"]').html("<img src='/img/ajax-loader.gif'><em>Updating department ...</em>");
    }
    function stoppedUpdate(s){
        if(s==="ok"){
            Boxy.get($(".close")).hideAndUnload();
        }else {
            $('span[data-name="console"]').html("Create department failed").attr("class","warning-bar");
        }
    }
</script>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: startUpdate, onComplete: stoppedUpdate})">
    <label><span data-name="console"></span></label>
    <label>Name of Department <input required type="text" name="dept_name" value="<?= $department->getName()?>"></label>
    <label>Cost Centre <select name="cost_centre_id" required data-placeholder="-- Select Cost Centre --">
            <option value="" <?= ($department->getCostCentre() == NULL) ? ' selected': ''?>></option>
            <?php foreach($costCentres as $c){?><option value="<?= $c->getId()?>" <?= ($department->getCostCentre() != NULL && $department->getCostCentre()->getId()===$c->getId()) ? ' selected': ''?>><?= $c->getName()?></option><?php }?>
        </select></label>
    <label class="clearfix"></label>
    <label class="clearfix"></label>
    <button type="submit" class="btn">Update</button>
    <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
</form>