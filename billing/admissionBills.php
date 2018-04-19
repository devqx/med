<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$bills = (new BillDAO())->getInPatientBills(TRUE);

if (count($bills) === 0) {?>
<div class="warning-bar">There is no outstanding admission bill</div>
<?php } else { ?>

<!--    <form action="find_bills.php" method="post">-->
<!--        <div class="input-append">-->
<!--            <input type="text" name="q" id="q" autocomplete="off" placeholder="Patient EMR or Name or bill #" style="width: 90%;">-->
<!--            <button class="btn" type="submit" name="button" id="button" style="width: 10%;">Find Bill &raquo;</button>-->
<!--        </div>-->
<!--    </form>-->
    <div>
        <table class="table table-condensed table-hover table-striped">
            <thead>
                <tr>
                    <th>BILL#</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Details</th>
                    <th>Amount</th>
                    <th>Transaction Type</th>
                    <th>PAYING</th>
                    <th>*</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bills as $b){ ?>
                <tr>
                    <td>
                        <?= $b->getId() ?>
                    </td>
                    <td>
                        <a href="/patient_profile.php?id=<?= $b->getPatient()->getId() ?>" title="View <?= $b->getPatient()->getFullname() ?> profile"><?= $b->getPatient()->getFullname() ?></a>
                    </td>
                    <td>
                        <?= date("d M, Y", strtotime($b->getTransactionDate())) ?>
                    </td>
                    <td>
                        <?= $b->getDescription() ?>
                    </td>
                    <td>
                        <?= number_format($b->getAmount(),2, '.', ',') ?>
                    </td>
                    <td>
                        <?= ucfirst($b->getTransactionType()) ?>
                    </td>
                    <td>
                        <?= $b->getBilledTo()->getName() ?>
                    </td>
                    <td>
                        <?php if("Uncomputed"=== $b->getInPatient()->getBillStatus()){ ?>
                        <a href="javascript:;" onclick="Boxy.load('computeCost.php?ipid=<?= $b->getInPatient()->getId() ?>', {title: 'Admission Cost Computation'})" title="Compute Patient Admission Cost" >Compute Cost?</a>
                        <?php }else{
                            
                        } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
