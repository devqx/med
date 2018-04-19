<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/4/15
 * Time: 2:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ServiceCenter.php';
$all = (new ServiceCenterDAO())->all('Pharmacy');
?>
<div style="width: 550px;">
    Available pharmacies
    <a href="/pages/pm/pharmacy/pharmacy-new.php" class="boxy pull-right">New Pharmacy</a>
    <table class="table table-striped">
        <thead><tr><th>Name/Code</th><th>Department</th><th>Cost Centre</th></tr></thead>
        <?php foreach ($all as $pharmacy) {//$pharmacy=new ServiceCenter();?>
        <tr><td><?=$pharmacy->getName()?></td><td><?=$pharmacy->getDepartment()->getName()?></td><td><?= $pharmacy->getCostCentre()->getName()?></td></tr>
        <?php }?>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        //$('a[href="#hide-45"]').boxy({title:"New Pharmacy"});
    });
</script>
