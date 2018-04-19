<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/29/16
 * Time: 4:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
$data = (new DeathDAO())->all(0, 999999);

$mortalities = $data->data;
$total = $data->total;
?>

<div class="document">
    <?php if($total==0){?><div class="notify-bar">No recorded mortality</div><?php }?>
<table class="table mortality">
    <thead><tr><th>Patient</th><th>Date/Time of death</th><th>Ward</th><th>Cause of Death</th><th>*</th></tr></thead>
    <?php foreach ($mortalities as $d) {//$d=new Death?>
    <tr><td><?= $d->getPatient()->getFullname()?></td><td><?= $d->getTimeOfDeath()?></td><td><?= ($d->getInPatient()  && $d->getInPatient()->getWard() )? $d->getInPatient()->getWard()->getName() : 'N/A' ?></td><td><?= $d->getDeathCausePrimary() ? $d->getDeathCausePrimary(): 'N/A'?></td><td><?php if($d->getInPatient()){?><a target="_blank" href="/admissions/inpatient_profile.php?pid=<?=$d->getInPatient()->getPatient()->getId() ?>&aid=<?=$d->getInPatient()->getId()?>">Details</a><?php }?> </td></tr>
    <?php }?>
</table>
</div>
<script>
    $(document).ready(function () {
        $('table.table.mortality').dataTable();
    })
</script>
