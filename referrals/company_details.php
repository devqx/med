<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/6/15
 * Time: 10:30 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
$ref = (new ReferralDAO())->by_company($_GET['c_id']);
$company = (new ReferralCompanyDAO())->get($_GET['c_id']);

?>
<div>
    <div class="well"><?=count($ref) ?> referrals registered under <?= $company->getName();?></div>
    <table class="table table-striped table-bordered">
        <thead>
        <tr><th>Name</th><th>Company</th><th>Phone</th><th>Specialization</th><th>Bank Name / Account #</th></tr>
        </thead>
        <tbody><?php foreach($ref as $referral){?>
            <tr><td><?= $referral->getName() ?></td><td><?= $referral->getCompany()->getName()?></td><td><?=$referral->getPhone()?></td><td><?= $referral->getSpecialization()->getName()?></td><td><?=$referral->getBankName()?> [<?=$referral->getAccountNumber()?>]</td></tr>
        <?php }?></tbody>
    </table>
</div>
<script>
    $(document).ready(function(){
        $('.boxy-content > table.table.table-striped').dataTable();
    });
</script>

