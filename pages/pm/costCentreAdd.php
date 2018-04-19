<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 3:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
    if(!is_blank($_POST['name']) && !is_blank($_POST['description'])){
        $cc = new CostCenter();
        $cc->setName($_POST['name']);
        $cc->setDescription($_POST['description']);
        $cc->setAnalyticalCode($_POST['code']);

        if ( (new CostCenterDAO())->add($cc) ){
            exit("success:Added");
        } else {
            exit("error:Error adding a cost centre");
        }
    }else{
        exit("error:All fields are required");
    }
}
?>
<form autocomplete="off" id="hide2" class="boxy" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"
      onsubmit="return AIM.submit(this, {'onStart':beginAddCost, 'onComplete':doneAddCost});">
    <label>Name<input placeholder="Name of cost centre" type="text" name="name"/></label>
    <label>Analytical code<input placeholder="Analytical code" type="text" name="code"/></label>
    <label>Description<input placeholder="Description" type="text" name="description"/></label>
    <div class="btn-block">
        <button class="btn" type="submit">Add cost Centre &raquo;</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
    </div>
    <div class="wait"></div>
</form>
<script type="text/javascript">
    function beginAddCost() {
        $('#hide2.boxy.boxy-content').find('> .wait').html('<img src="/img/loading.gif"/> Please Wait ... ');
    }
    function doneAddCost(s) {
        ret = s.split(":");
        if (ret[0] === 'success') {
            loadCostCentres();
            $("#hide2").get(0).reset();
            Boxy.get($(".close")).hide();
        } else if (ret[0] === 'error') {
            $('#hide2.boxy.boxy-content').find('> .wait').html('<span class="error alert-box">'+ret[1]+'</span>');
        }
    }
    
</script>
