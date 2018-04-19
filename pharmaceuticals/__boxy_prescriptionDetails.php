<?php
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
    $press=(new PrescriptionDAO())->getPrescriptionByCode($_GET['pCode'], TRUE);
?>
<div>
    <table class="table">
        <tbody>
        <tr>
            <td rowspan="3" style="width:100px"><img style="width:100px" src="<?=$press->getPatient()->getPassportPath()?>"> </td>
            <td><span class="fadedText">ID:</span> <?=$press->getPatient()->getId()?></td>
            <td><span class="fadedText">Date of Birth:</span> <?=date("jS M, Y",strtotime($press->getPatient()->getDateOfBirth()))?></td>
        </tr>
        <tr>
            <td><span class="fadedText">Name:</span> <?=$press->getPatient()->getFullName()?></td>
            <td><span class="fadedText">Insurance:</span> <?=(new PatientDemographDAO())->getPatient($press->getPatient()->getId(), TRUE)->getScheme()->getName()?></td>
        </tr>
        <tr>
            <td><span class="fadedText">Sex:</span> <?=ucwords($press->getPatient()->getSex())?></td>
            <td><span class="fadedText">Last Weight:</span> <?=((new PatientDemographDAO())->getPatientMedical($press->getPatient()->getId(), TRUE)->getVitalSigns()['weight']!= NULL)?(new PatientDemographDAO())->getPatientMedical($press->getPatient()->getId(), TRUE)->getVitalSigns()['weight']->getValue().'kg':'N/A'?></td>
        </tr>
        </tbody>
    </table>
    <h6>Prescription List: <?=$press->getCode()?> <span class="pull-right"><i class="icon-print"></i><a href="javascript:;" class="_p_action" data-reg="<?=$press->getCode() ?>" data-action="print">print</a></span></h6>
    <table class="table">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Drug</th>
                <th>Dosage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($press->getData() as $key=>$pre){?>
                <tr>
                    <td><?= ($key+1) ?></td>
                    <td><?= (($pre->getDrug()==NULL)? $pre->getGeneric()->getName():$pre->getDrug()->getName()) ?></td>
                    <td><?= $pre->getDose(). " ".$pre->getGeneric()->getForm().(($pre->getDose()!=1)?'s':'') ." ".$pre->getFrequency() ?> for <?= $pre->getDuration()?> days</td>
                    <td>
                        <span><?= $pre->getStatus() ?></span>
                        <?php if ($_GET['mode'] == "pharmacy") { ?>
                            <?php if ($pre->getStatus() !== "cancelled") { ?>
                                <a class="_p_action btn btn-mini" href="javascript:void(0)" data-action="cancel"
                                   data-id="<?= $pre->getId() ?>" title="Cancel this prescription"><i
                                        class="icon-remove"></i></a>
                                <?php if ($pre->getFilledBy() !== NULL && $pre->getStatus() == 'filled') { ?>
                                    <a href="javascript:;" class="_pres_action"
                                       data-href="/boxy.fillprescription.php?regid='<?= $pre->getId() ?>&drug=<?= $pre->getDrug()->getId() ?>&pid=' . $pres->getPatient()->getId() . '&action=complete"
                                       data-title="Complete Prescription">COMPLETE</a>
                                <?php } else if ($pre->getFilledBy() === NULL && $pre->getStatus() == 'open') { ?>
                                    <a href="javascript:;" class="_pres_action"
                                       data-href="/boxy.fillprescription.php?gid=<?= $pre->getGeneric()->getId() ?>&regid=<?= $pre->getId() ?>&drug=<?= ($pre->getDrug() === NULL ? "" : $pre->getDrug()->getId()) ?>&pid=<?= $press->getPatient()->getId() ?>&action=fill">FILL</a>
                                <?php } else { ?>
                                    COMPLETED
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
