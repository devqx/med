<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/14/14
 * Time: 4:07 PM
 */
?><div class="dataTables_wrapper">
    
<?php if (count($Requests) > 0) { ?>
    <table class="table scantable">
        <thead>
        <tr>
            <th class="hide">sort time</th>
            <th>Request Date</th>
            <th>RQ #</th>
            <?php if (!isset($_GET['pid'])) { ?>
                <th>Patient</th><?php } ?>
            <th>Requester *</th>
            <th>Type</th>
            <th>Approved</th>
            <th>*</th>
        </tr>
        </thead>
        <?php foreach ($Requests as $ps) {
            //$ps = new PatientDentistry();
            if($ps->getPatient()){
            ?>
            <tr id="_dt_an_tr_<?= $ps->getId() ?>">
                <td class="hide"><?= strtotime($ps->getRequestDate()) ?></td>
                <td nowrap>
                    <div datetime="<?=strtotime($ps->getRequestDate()) ?>"
                          title="<?=strtotime($ps->getRequestDate()) ?>"><?= date("d M, Y h:iA", strtotime($ps->getRequestDate())) ?></div>
                </td>
                <td><a data-title="<?= $ps->getRequestCode()?>" class="boxy" href="javascript:;" data-href="/dentistry/request.details.php?id=<?= $ps->getId() ?>"><?= $ps->getRequestCode() ?></a></td>
                <?php if (!isset($_GET['pid'])) { ?>
                    <td><a target="_blank" title="<?=$ps->getPatient()->getFullname();?>" href="/patient_profile.php?id=<?= $ps->getPatient()->getId() ?>"><?= $ps->getPatient()->getShortname(); ?></a>
                    </td><?php } ?>
                <td>
                    <?= ($ps->getReferral() !== NULL) ? '<span title="Referred from '.$ps->getReferral()->getName() .' ('.$ps->getReferral()->getCompany()->getName().')"><i class="icon-info-sign"></i></span>':'' ?>
                    <span title="<?= $ps->getRequestedBy()->getFullname() ?>"><?= $ps->getRequestedBy()->getUsername() ?></span>
                 </td>
                <td><?php $dd = []; foreach ($ps->getServices() as $rq) { $dd[] = $rq->getName();} echo implode(", ", $dd)?></td>

                <td><?= ($ps->getApproved() && !$ps->getCancelled()) ? "Yes" : (!$ps->getCancelled()?"Pending":"Canceled") ?></td>
                <td><?php if($ps->getApproved()){ ?>
                        <a href="javascript:;" class="printDentistryNotes" data-page-id="<?= $ps->getId() ?>"><i class="icon-print"></i>Print</a>
                    <?php } else {
                        if($ps->getStatus() && !$ps->getCancelled()){
                            echo 'Waiting for approval';
                        } else if(!$ps->getCancelled()){ ?>
                            <?php if (!isset($_GET['pid'])) {?><a href="javascript:;" class="submitToApproveDentistry" data-id="<?= $ps->getId() ?>" style="white-space: nowrap;">Submit for Approval</a> |<?php }?> <a href="javascript:;" class="cancelRequest" data-id="<?= $ps->getId()?>">Cancel Request</a>
                        <?php } } ?>
                </td>
            </tr>
        <?php } }?>
    </table>

<?php } else { ?>
    <div class="notify-bar">Nothing found to display at the moment</div>
<?php } ?>

<script type="text/javascript">
    if(typeof jQuery !== "undefined"){
        $(document).on("click", ".cancelRequest", function (e) {
            var request_id = $(this).data('id');
            if(!e.handled){
                Boxy.ask("Cancel Request?", ['Yes', 'No'], function(response) {
                    if(response === "Yes"){
                        $.post("/api/cancel_dentistry_request.php", {id:request_id}, function(data) {
                            if(data){
                                try {
                                    $('a[data-href="to_fulfil.php"]').click();
                                } catch(except){}

                                try {
                                    <?php if(isset($_GET['aid'])){?>showTabs(8);<?php } else {?>showTabs(16);
                                    <?php }?>
                                } catch(except){}

                            } else {
                                Boxy.alert("Failed to process request.");
                            }
                        }, 'json');
                    }
                });
                e.handled = true;
            }
        });
    }

</script>