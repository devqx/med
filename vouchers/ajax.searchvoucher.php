<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/27/15
 * Time: 2:43 PM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$vouchers=(new VoucherDAO())->findVouchers($_POST['q']);
?>
<?= ((sizeof($vouchers) > 0) ? '' : '<div class="notify-bar">No vouchers matched the filter</div>') ?>
<?php if(sizeof($vouchers) > 0){?><table class="searchTable table outer table-hover table-striped">
    <thead>
    <tr>
        <th>Batch ID</th>
        <th>Code</th>
        <th>Value (<?= $currency ?>)</th>
        <th>Type</th>
        <th>Description</th>
        <th>Status</th>
        <th>Patient</th>
    </tr>
    </thead>
    <tbody>
    <?php if(isset($vouchers) && count($vouchers)>0){
        foreach($vouchers as $key => $voucher){ /*$voucher=new Voucher();*/ ?>
            <tr>
                <td><?= $voucher->getBatch()->getId() ?></td>
                <td><?= $voucher->getCode() ?></td>
                <td><?= $voucher->getBatch()->getAmount() ?></td>
                <td><?= ucwords($voucher->getBatch()->getType()) ?></td>
                <td><?= ucfirst($voucher->getBatch()->getDescription()) ?></td>
                <td><?= ($voucher->getUsedDate() === NULL)? 'VALID':'USED' ?></td>
                <td><?= ($voucher->getVoucherUser()==null)?'':$voucher->getVoucherUser()->getFullname() ?></td>
            </tr>
        <?php
        }
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="2"></th>
        <th colspan="5">Total:</th>
    </tr>
    </tfoot>
</table>
<?php }?>