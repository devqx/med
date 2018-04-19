<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/14
 * Time: 9:04 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/CostCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Department.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CostCenterDAO.php';
if($_POST){
    $dept = (new Department())
        ->setName($_POST['dept_name'])
        ->setCostCentre( new CostCenter($_POST['cost_centre_id']) );

    $ret = (new DepartmentDAO())->add($dept);
    if($ret!==NULL){
        exit("ok");
    }else {
        exit("error");
    }
}?>
<script>
    function start(){
        $('span[data-name="console"]').html("<img src='/img/ajax-loader.gif'><em>Creating department ...</em>");
    }
    function stopped(s){
        if(s==="ok"){
            Boxy.get($(".close")).hideAndUnload();
        }else {
            $('span[data-name="console"]').html("Create department failed").attr("class","warning-bar");
        }
    }
</script>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: start, onComplete: stopped})">
    <label><span data-name="console"></span></label>
    <label>Name of Department <input type="text" name="dept_name"></label>
    <label>Cost Centre <select name="cost_centre_id" required data-placeholder="-- Select Cost Centre --">
            <option value=""></option>
            <?php foreach( (new CostCenterDAO())->all() as $cc ){?><option value="<?= $cc->getId()?>"><?=$cc->getName()?></option><?php }?>
        </select></label>
    <label class="clearfix"></label>
    <label class="clearfix"></label>
    <button type="submit" class="btn">Create</button>
    <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
</form>