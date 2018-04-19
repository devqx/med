<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InvoiceLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';

//$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], TRUE);
if (!isset($_SESSION)) {@session_start();}
$currencyTitle = (new CurrencyDAO())->getDefault()->getTitle();

?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
    <?php
    if(!isset($_REQUEST['id'])) {
        $bill_lines = $_REQUEST['bills'];
        //create the invoice
        $invoice_ = new Invoice();

        $lines = array();
        foreach (explode(",", $_REQUEST['bills']) as $bill) {
            $line = new InvoiceLine();
            $line->setBill(new Bill($bill));

            $lines[] = $line;
        }
        $invoice_->setLines($lines);
        if (isset($_GET['mode']) && $_GET['mode'] == "patient") {
            $invoice_->setPatient(new PatientDemograph($_GET['pid']));
        } else {
            $invoice_->setPatient(NULL);
        }

        if(isset($_GET['mode']) && $_GET['mode']=="insurance"){
            $invoice_->setScheme( (new InsuranceScheme($_GET['sid'])) );
        }else {
            $invoice_->setScheme(NULL);
        }

        $invoice_->setCashier(new StaffDirectory($_SESSION['staffID']));

        $invoice = (new InvoiceDAO())->create($invoice_);
        if ($invoice === NULL) {
            exit("Error creating Invoice");
        }
    } else {
        //we are to get the invoice
        $invoice = (new InvoiceDAO())->get($_REQUEST['id']);
        $bill_lines = array();
        foreach ($invoice->getLines() as $line) {
            $bill_lines[] = $line->getBill()->getId();
        }
        $bill_lines = implode(",", $bill_lines);
    }
    //continue to show the invoice
    ?>

    <style>
        @import url(../style/bootstrap.css);
        @import url(../style/def.css);

        .table > tr:not(.small) > td {
            padding: 10px;
            /*color: red;*/
        }

        code {
            line-height: 30px;
        }
    </style>
</head>
<body>
<?php
$clinic = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true)->getClinic();
?>

<div class="uppercase" style="margin: 50px auto; text-align: center">
    <img src="<?= $clinic->getLogoFile()?>" style="height: 100px"><h2><?= $clinic->getName() ?></h2>
    <h4><?= $clinic->getAddress() ?></h4>
    <h4><?= $clinic->getPhoneNo() ?></h4>
</div>
<div style="width:750px;margin:20px auto 0 auto">
    <table class="table">
        <tr>
            <td colspan="4">
                <?php if($invoice->getPatient() != NULL/* && $_REQUEST['mode']!="insurance"*/){?>
                    <table class="demograph table">
                        <tr class="small">
                            <td>Patient:</td>
                            <td><code><?= $invoice->getPatient()->getFullname() ?></code></td>
                            <td>Sex/Age:</td>
                            <td><code><?= ucfirst($invoice->getPatient()->getSex()) ?>/<?= $invoice->getPatient()->getAge() ?></code></td>
                        </tr>
                        <tr class="small">
                            <td>EMR:</td>
                            <td><code><?= $invoice->getPatient()->getId() ?></code></td>
                            <td>Nationality:</td>
                            <td><code><?= ucfirst($invoice->getPatient()->getNationality()) ?></code></td>
                        </tr>
                        <tr class="small">
                            <td>Patient Phone:</td>
                            <td><code><?= $invoice->getPatient()->getPhoneNumber() ?></code></td>
                            <td>DOB:</td>
                            <td><code><?= date("dS M, Y", strtotime($invoice->getPatient()->getDateOfBirth())) ?></code></td>
                        </tr>
                        <tr class="small">
                            <td>Coverage:</td>
                            <td colspan="3">
                                <code><?= $invoice->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Insured" ?></code>
                            </td>

                        </tr>
                    </table>
                <?php } else {?>
                    <table class="demograph table">
                        <tr class="small">
                            <td>Invoice For: </td>
                            <td><code><?=$invoice->getScheme()->getName(); ?></code></td>
                        </tr>
                    </table>

                <?php }?>
            </td>
        </tr>

        <tr>
            <td colspan="4">
                <table class="table table-striped table-bordered">

                    <tr>
                        <td colspan="4"><h2 align="center">Receipt/Invoice: #<?= (int)$invoice->getId()?></h2></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>Bill Source</strong></td>
                        <td class="amount"><strong>Amount</strong></td>
                    </tr>
                    <?php
                    $outstanding_total = 0;
                    require $_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';
                    mysql_select_db($database_dbconnection, $dbconnection);
                    //                    $sql = "SELECT bs.name AS bill_source, SUM(b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.bill_id IN (".$bill_lines.") /*b.invoiced <> 'yes' OR b.invoiced IS NULL*/) /*AND b.patient_id='" . mysql_real_escape_string($_GET['pid']) . "'*/ AND b.transaction_type='credit' /*AND s.pay_type = 'self'*/ GROUP BY bill_source ORDER BY transaction_date DESC";
                    $sql = "SELECT b.description, bs.name AS bill_source, (b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.bill_id IN (".$bill_lines.") /*b.invoiced <> 'yes' OR b.invoiced IS NULL*/) /*AND b.patient_id='" . mysql_real_escape_string($_GET['pid']) . "'*/ AND b.transaction_type='credit' /*AND s.pay_type = 'self'*/ /*GROUP BY bill_source*/ AND cancelled_on IS NULL ORDER BY transaction_date DESC";
                    $result = mysql_query($sql, $dbconnection);
                    $row = mysql_fetch_assoc($result);
                    ?>
                    <?php if (mysql_num_rows($result) == 0) { ?>
                        <tr>
                            <td colspan="3"><em>No un-invoiced items</em></td>
                            <td>&nbsp;</td>
                        </tr>
                    <?php } else {
                        do { ?>
                            <tr>
                                <td colspan="2"><?= ucwords($row['description']); ?></td>
                                <td><?= ucwords($row['bill_source']); ?></td>
                                <td class="amount"><?= number_format($row['amount'], 2, '.', ',');
                                    $outstanding_total += $row['amount']; ?></td>
                            </tr>
                        <?php } while ($row = mysql_fetch_assoc($result));
                        ?>
                        <tr>
                            <td colspan="4" class="amount">
                                TOTAL CHARGE: <?= ucwords(convert_number_to_words($outstanding_total)) ?> <?= $currencyTitle ?>
                                (<?= number_format($outstanding_total, 2, '.', ',') ?>)
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="amount">
                                PAYMENT: <?= ucwords(convert_number_to_words($outstanding_total)) ?> <?= $currencyTitle ?>
                                (<?= number_format($outstanding_total, 2, '.', ',') ?>)
                            </td>
                        </tr>

                    <?php } ?>
                </table>

            </td>
        </tr>
        <tr class="no-print"><td colspan="4">
                <a href="/pdf.php?page=<?= urlencode('/billing/invoice.php?id='.(int)$invoice->getId()) ?>&title=Invoice<?= (int)$invoice->getId()?>" class="action">Save as PDF</a>
                <a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
            </td></tr>
    </table>
</div>
</body>
</html>
