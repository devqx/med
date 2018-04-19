<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
//$protect = new Protect();
$labs = (new PatientOphthalmologyDAO())->getPatientOphthalmologyByGroupCode($_GET['gid'], TRUE);
$requestedBy = (new StaffDirectoryDAO())->getStaff($labs[0]->getOphthalmologyGroup()->getRequestedBy()->getId());
$approvedlLabs = $approvedlBy = $referredBy = array();
foreach($labs as $l){
    $referredBy[] = ($l->getOphthalmologyGroup()->getReferral()!=null)? $l->getOphthalmologyGroup()->getReferral()->getName()." [".$l->getOphthalmologyGroup()->getReferral()->getCompany()->getName()  ."]":'-';
    if($l->getOphthalmologyResult()!=null && $l->getOphthalmologyResult()->isApproved()){
        $approvedlLabs[] = $l->getOphthalmologyResult()->isApproved();
        $approvedlBy[] = $l->getOphthalmologyResult()->getApprovedBy()->getFullname();
    }
}
$referredBy = array_unique($referredBy);
$approvedlLabs = array_unique($approvedlLabs);
$approvedlBy = array_unique($approvedlBy);
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
    <meta charset="UTF-8">

    <script src="/js/jquery-2.1.1.min.js"></script>
    <script src="/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
    <link href="/style/def.css" rel="stylesheet" type="text/css"/>
    <link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/style/font-awesome.css" rel="stylesheet" type="text/css"/>
    <meta name="viewport" content="width=device-width">
    <style>
        .table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
            padding: 2px !important;
        }

        .table {
            color: #000;
        }

        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: center; font-size: 28px; margin-top: 230px">Ophthalmology Investigation</div>
    <br/>
    <div class="row-fluid">
        <div class="span3">Patient's Name:</div>
        <div class="span3"><?= $labs[0]->getPatient()->getFullname() ?></div>
        <div class="span3">Sex/Age:</div>
        <div class="span3"><?= ucfirst($labs[0]->getPatient()->getSex()) ?>/<?= $labs[0]->getPatient()->getAge() ?></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Patient EMR:</div>
        <div class="span3"><?= $labs[0]->getPatient()->getId() ?></div>
        <div class="span3">Nationality:</div>
        <div class="span3"><?= ucfirst($labs[0]->getPatient()->getNationality()) ?></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Patient Phone:</div>
        <div class="span3"><?= $labs[0]->getPatient()->getPhoneNumber() ?></div>
        <div class="span3"></div>
        <div class="span3"></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Coverage:</div>
        <div class="span3"><?= $labs[0]->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Covered" ?><br>
            (<?=$labs[0]->getPatient()->getScheme()->getName()?>)
        </div>
        <div class="span3">Request Date:</div>
        <div class="span3"><?= date("d M, Y h:i A", strtotime($labs[0]->getOphthalmologyGroup()->getRequestTime())) ?></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Request ID:</div>
        <div class="span3"><?= $labs[0]->getOphthalmologyGroup()->getGroupName() ?></div>
        <div class="span3">Approved By:</div>
        <div class="span3"><?= ((count($approvedlLabs)>0)? $approvedlBy[0] : '') ?></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Referred By:</div>
        <div class="span3"><?= $referredBy[0] ?></div>
    </div>
    <div class="row-fluid">
        <div class="span3">Ophthalmology Services Requested:</div>
        <div class="span9"><?php
            $reqs = [];
            foreach ($labs as $lab) {
                $reqs[] = $lab->getOphthalmology()->getName();
            }
            echo implode(", ", $reqs);
            ?></div>
    </div>

    <?php foreach ($labs as $lab) { //$lab = new PatientOphthalmology();?>
        <div class="box">
            <!--            <table class="table table-condensed">-->
            <div class="row-fluid">
                <div class="span12 underline"><?= "<strong>" . $lab->getOphthalmology()->getName() . "</strong>" . (($lab->getOphthalmologyResult() === NULL) ? " Result is Not Ready" : "") ?></div>
            </div>

            <div class="row-fluid">
                <?php if ($lab->getOphthalmologyResult() !== NULL) { ?>
                    <div class="span4"><strong><u>---</u></strong></div>
                    <div class="span4"><strong><u>Result</u></strong></div>
                    <div class="span4"><strong><u>Reference</u></strong></div>
                <?php } ?>
            </div>
            <?php
            if ($lab->getOphthalmologyResult() !== NULL && $lab->getOphthalmologyResult()->isApproved()) {
                foreach ($lab->getOphthalmologyResult()->getData() as $result) {
                    ?>
                    <div class="row-fluid">
                        <div class="span4"><?= $result->getOphthalmologyTemplateData()->getLabel() ?></div>
                        <div class="span4"><?= $result->getValue() ?></div>
                        <div class="span4"><?= $result->getOphthalmologyTemplateData()->getReference() ?></div>
                    </div>
                <?php
                }
            }
            ?>
            <!--            </table>-->
        </div>
    <?php } ?>
    <?php if(count($approvedlLabs)>0){ ?>
        <div class="block clearfix" style="margin-top:100px;">
            <span class="pull-right">APPROVED BY: <?= $approvedlBy[0] ?></span>
        </div>

    <?php } ?>

    <div class="pull-right no-print" style="margin-bottom: 20px">
        <!--<a href="javascript:Print();" class="action" title="Print this Lab Result">
            <i class="icon-print"></i> Print</a>-->
        <a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($labs[0]->getOphthalmologyGroup()->getGroupName())?>" class="action"><i class="icon-book"></i> PDF</a>
    </div>
</div>

<script>
    function Print() {
        $('.container').print({
            addGlobalStyles: true,
            stylesheet: null,
            rejectWindow: true,
            noPrintSelector: ".no-print",
            iframe: true,
            append: "Generated by MedicPlus"
        });
    }
    $(document).ready(function () {
    });
    $(document).on('keydown', function(e) {
        if(e.ctrlKey && (e.key == "p" || e.charCode == 16 || e.charCode == 112 || e.keyCode == 80) ){
            alert("Please use the Print PDF button below for a better rendering on the document");
            e.cancelBubble = true;
            e.preventDefault();

            e.stopImmediatePropagation();
        }

    });
</script>
</body>
</html>