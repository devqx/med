<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/4/15
 * Time: 11:57 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
$csc = (new CostCenterDAO())->get($_GET['id']);
if($_POST){
    if(!is_blank($_POST['name']) && !is_blank($_POST['description'])){
        $csc->setName($_POST['name']);
        $csc->setDescription($_POST['description']);
        $csc->setAnalyticalCode($_POST['code']);

        if ( (new CostCenterDAO())->update($csc) ){
            exit("success:Updated");
        } else {
            exit("error:Error updating cost centre");
        }
    }else{
        exit("error:All fields are required");
    }
}
?>
<form autocomplete="off" id="hide2" class="boxy" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"
      onsubmit="return AIM.submit(this, {'onStart':beginEditCost, 'onComplete':doneEditCost});">
    <label>Name<input placeholder="Name of cost centre" type="text" name="name" value="<?=$csc->getName() ?>"/></label>
    <label>Analytical Code<input placeholder="Analytical Code" type="text" name="code" value="<?=$csc->getAnalyticalCode() ?>"/></label>
    <label>Description<input placeholder="Description" type="text" name="description" value="<?= $csc->getDescription()?>"/></label>
    <div class="btn-block">
        <button class="btn" type="submit">Update details &raquo;</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </div>
    <div class="wait"></div>
</form>
<script type="text/javascript">
    function beginEditCost(){
        $('#hide2.boxy.boxy-content').find('> .wait').html('<img src="/img/loading.gif"/> please wait ... ');
    }
    function doneEditCost(s){
        ret = s.split(":");
        if (ret[0] == 'success') {
            loadCostCentres();
            Boxy.get($(".close")).hide();
        } else if (ret[0] == 'error') {
            $('#hide2.boxy.boxy-content').find('> .wait').html('<span class="error alert-box">'+ret[1]+'</span>');
        }
    }
    $(document).ready(function () {

    })
</script>
