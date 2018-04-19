<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/15
 * Time: 4:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/InsuranceItemsCost.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/NursingService.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
$DAO = (new NursingServiceDAO());
$admissionItems = $DAO->all();

if($_POST){
    if(is_blank($_POST['service_name']) || is_blank($_POST['service_price'])){
        exit("error:Check empty or invalid field");
    }
    $item = new NursingService();
    $item->setName($_POST['service_name']);
    $item->setBasePrice(parseNumber($_POST['service_price']));

    $newItem = $DAO->add($item);

    if($newItem !== NULL){
        exit("success:Service added");
    }

    exit("error:Failed to add service");
}
?>
<section>
    <a class="pull-right action" href="#hide2" title="New Item">Add a Nursing Service</a>
    <div class="clear"></div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Item</th>
            <th class="amount">Default Price</th>
            <th>*</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($admissionItems as $item) {//$item = new NursingService();?>
            <tr><td><?= $item->getName() ?></td><td class="amount"><?= $item->getBasePrice()?></td><td><a href="javascript:" data-href="/pages/pm/bedspaces/nursingServiceEdit.php?id=<?= $item->getId()?>" class="editServiceLink" data-id="<?= $item->getId()?>">Edit</a></td></tr>
        <?php }?>
        </tbody>
    </table>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" class="" style="display: none;" id="hide2" onsubmit="return AIM.submit(this, {onStart: _t_, onComplete: _o_})">
        <span></span>
        <label>Item/Service Name <input type="text" name="service_name" required="required"> </label>
        <label>Item/Service Cost <span class="pull-right"><i class="icon-info-sign"></i> <em>might be charged during clinical task execution</em></span><input type="number" name="service_price" min="0" step="0.01" required="required"> </label>
        <div class="btn-block">
            <button class="btn" type="submit">Add</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
        </div>
    </form>
</section>
<script type="text/javascript">
    $(document).ready(function () {
        $('section > a[href="#hide2"].pull-right.action').boxy({});

        $(".boxy-content").on("click", ".editServiceLink", function(e){
            if(!e.handled){
                Boxy.load($(this).data("href"), {title:"Edit Nursing Service"});
                e.handled = true;
            }
        });
    });
    function _t_(){
        $('form#hide2 > span:first-child').html('<img src="/img/ajax-loader.gif">');
    }
    function _o_(s){
        if(s.split(":")[0]=="error"){
            $('form#hide2 > span:first-child').html(s.split(":")[1]).removeClass("warning-bar").addClass("warning-bar");
        } else if(s.split(":")[0]=="success"){
            //reload this tab
            showTabs(7);
            $('form#hide2').get(0).reset();
            $('form#hide2 > span:first-child').html('');
            Boxy.get($("#hide2")).hide();
        }
    }
</script>