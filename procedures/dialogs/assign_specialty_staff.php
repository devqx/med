<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/12/14
 * Time: 3:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Resource.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
$pp = (new PatientProcedureDAO())->get($_GET['id']);

$bills=new Bills();
$pat=(new PatientDemographDAO())->getPatient($pp->getPatient()->getId(),false,NULL, NULL);
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId()) - (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

$theatres = (new ResourceDAO())->getResources();

$surgeons = (new StaffDirectoryDAO())->getStaffs(TRUE);
//TODO: filter only surgeons?
$anesthesiologists = (new StaffDirectoryDAO())->getStaffs(TRUE);
//TODO: filter only anesthesiologists?
if($_POST){
//    include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
//    $pdo = (new MyDBConnector())->getPDO();
//    $pdo->beginTransaction();
    if(isset($_POST['theatre_id'])) {
        $pp->setTheatre( new Resource($_POST['theatre_id']) );
        //charge the patient the theatre price
        //$bil = new Bill();
        //$bil->setPatient($pp->getPatient());
        //$bil->setDescription("Procedure theatre charge: ".$pp->getProcedure()->getName());
        //
        //$bil->setItem($pp->getProcedure());
        //$bil->setSource( (new BillSourceDAO())->findSourceByName("procedure") );
        //$bil->setTransactionType("credit");
        //
        //$amount = (new InsuranceItemsCostDAO())->getItemPricesByCode($pp->getProcedure()->getCode(), $pp->getPatient()->getId(), TRUE);
        //$bil->setAmount($amount->theatrePrice);
        //$bil->setPriceType('theatrePrice');
        //$bil->setDiscounted(NULL);
        //$bil->setDiscountedBy(NULL);
        //
        //$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], TRUE);
        //$bil->setClinic($staff->getClinic());
        //$bil->setBilledTo($pp->getPatient()->getScheme());
        //
        //$bill=(new BillDAO())->addBill($bil, 1);
    }
    if(isset($_POST['surgeon_id'])) {
        $pp->setSurgeon( new StaffDirectory($_POST['surgeon_id']) );
        //charge the surgeon price
        $bil = new Bill();
        $bil->setPatient($pp->getPatient());
        $bil->setDescription("Procedure Surgeon charge: ".$pp->getProcedure()->getName());

        $bil->setItem($pp->getProcedure());
        $bil->setSource( (new BillSourceDAO())->findSourceById(8) );
        $bil->setTransactionType("credit");

        $amount = (new InsuranceItemsCostDAO())->getItemPricesByCode($pp->getProcedure()->getCode(), $pp->getPatient()->getId(), TRUE);
        $bil->setAmount($amount->surgeonPrice);
        $bil->setPriceType('surgeonPrice');
        $bil->setDiscounted(NULL);
        $bil->setDiscountedBy(NULL);

        $staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], TRUE);
        $bil->setClinic($staff->getClinic());
        $bil->setBilledTo($pp->getPatient()->getScheme());

        $bill=(new BillDAO())->addBill($bil, 1);
    }
    if(isset($_POST['anesthesiologist_id'])) {
        $pp->setAnesthesiologist( new StaffDirectory($_POST['anesthesiologist_id']) );
        //charge the anaesthesia price
        $bil = new Bill();
        $bil->setPatient($pp->getPatient());
        $bil->setDescription("Procedure Anaesthesia charge: ".$pp->getProcedure()->getName());

        $bil->setItem($pp->getProcedure());
        $bil->setSource( (new BillSourceDAO())->findSourceById(8) );
        $bil->setTransactionType("credit");

        $amount = (new InsuranceItemsCostDAO())->getItemPricesByCode($pp->getProcedure()->getCode(), $pp->getPatient()->getId(), TRUE);
        $bil->setAmount($amount->anaesthesiaPrice);
        $bil->setPriceType('anaesthesiaPrice');
        $bil->setDiscounted(NULL);
        $bil->setDiscountedBy(NULL);

        $staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], TRUE);
        $bil->setClinic($staff->getClinic());
        $bil->setBilledTo($pp->getPatient()->getScheme());

        $bill=(new BillDAO())->addBill($bil, 1);
    }

    $p_ = (new PatientProcedureDAO())->updateProcedure($pp);

    if($p_ !== NULL){
        exit("success:Patient procedure property set successfully");
    }
    exit("error:Failed to update patient procedure property");
}
?>
<div>
    <section>
        <div class="well">
            Patients outstanding is: &#8358;<?= number_format($selfOwe,2); ?>
        </div>
        <form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete:__done})">
        <?php if ($_GET['type'] == "t") { ?>
            <label>Theatre: <select name="theatre_id">
                    <?php foreach ($theatres as $t) {//$t=new Resource();?>
                        <option value="<?= $t->getId() ?>"><?= $t->getName() ?></option>
                    <?php } ?>

                </select>
            </label>
        <?php } ?>
            <?php if ($_GET['type'] == "s") { ?>
            <label>Surgeon: <select name="surgeon_id">
                    <?php foreach ($surgeons as $t) {//$t=new StaffDirectory();?>
                        <option value="<?= $t->getId() ?>"><?= $t->getFullname() ?></option>
                    <?php } ?>

                </select>
            </label>
        <?php } ?><?php if ($_GET['type'] == "a") { ?>
            <label>Anesthesiologist: <select name="anesthesiologist_id">
                    <?php foreach ($anesthesiologists as $t) {//$t=new StaffDirectory();?>
                        <option value="<?= $t->getId() ?>"><?= $t->getFullname() ?></option>
                    <?php } ?>

                </select>
            </label>
        <?php } ?>
<?php if(isset($_GET['type'])){
    ?><!-- <?=($selfOwe > 0 ?'disabled="disabled"':'') ?>--><div class="btn-block">
                <button class="btn">Assign Resource</button>
                <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
            </div>
<?php } ?>

        </form>
    </section>
</div>
<script type="text/javascript">
    function __done(s){
        var dat = s.split(":");
        if(dat[0]==="error"){
            Boxy.alert(dat[1]);
        }else {
            Boxy.info(dat[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }
    }
</script>