<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/1/15
 * Time: 1:12 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/AntenatalEnrollmentDAO.php';
$enrInstances = (new AntenatalEnrollmentDAO())->getPatientInstances($_GET['pid'], TRUE);
?>
<section style="width: 520px">
    <div class="well">
        Recorded Antenatal instances
    </div>
    <div>
        <table class="table table-striped">
            <thead><tr>
                <th>Enrollment Date</th><th>Reason</th><th>Status</th>
            </tr></thead>
            <?php foreach ($enrInstances as $enr) {
                $link = '/antenatal/patient_antenatal_profile.php?id='.$enr->getPatient()->getId() .'&aid='.$enr->getId();
                ?>
                <tr>
                    <td><?= date("d M, Y", strtotime($enr->getEnrolledOn())) ?></td><td><?= (($enr->getBookingIndication()=='routine')? 'Routine': $enr->getComplicationNote()) ?></td><td><?= ($enr->getDateClosed() == NULL ? 'Active':'Closed on '. date("d M, Y", strtotime($enr->getDateClosed()))) ?> <span class="pull-right"><a href="<?= $link ?>">Open</a></span></td>
                </tr>
            <?php  } ?>

        </table>
    </div>


    <div class="btn-block">
        <div class="pull-left"><a class="btn" href="javascript:;" onclick="Boxy.get(this).hideAndUnload()">Close</a></div>
    </div>
</section>