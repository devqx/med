<div style="width:600px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
    include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
    $pat = (new PatientDemographDAO())->getPatient($_GET['id'], true);
    sessionExpired();
    ?>
    <table class="patient_more_details table">
        <tr>
            <th>Name</th>
            <td><?= $pat->getFullname() ?></td>
        </tr>
        <tr>
            <th>Primary HealthCare Center</th>
            <td><?php echo $pat->getBaseClinic()->getName() ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo $pat->getAddress() ?></td>
        </tr>
        <tr>
            <th>Local Government Area</th>
            <td><?php echo $pat->getLga()->getName() ?></td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td><?php echo $pat->getPhoneNumber() ?></td>
        </tr>
        <tr>
            <th>Blood Group / Type</th>
            <td><?php echo $pat->getBloodGroup() ?> / <?php echo $pat->getBloodType() ?></td>
        </tr>
        <tr>
            <th>Occupation/Work Address</th>
            <td><?= $pat->getOccupation() ?>/<?= $pat->getWorkAddress()?></td>
        </tr>
        <tr>
            <th>Religion</th>
            <td><?= ($pat->getReligion()!=NULL)?$pat->getReligion()->getName():'- -'?></td>
        </tr>
        <tr>
            <th colspan="2" style="background-color: #eee">Next of Kin details</th>
        </tr>
        <tr>
            <th>Name</th>
            <td><?= $pat->getKinsLastName() ?>, <?= $pat->getKinsFirstName() ?></td>
        </tr>
        <tr>
            <th>Phone/Address</th>
            <td><?= $pat->getKinsPhone() ?>, <?= $pat->getKinsAddress() ?></td>
        </tr>
        <tr>
            <th colspan="2" style="background-color: #eee">Insurance</th>
        </tr>
        <tr>
            <th>Type</th>
            <td><?php echo (($pat->getInsurance()==NULL)? "SELF PAY": ($pat->getScheme()==NULL)? "SELF PAY":$pat->getScheme()->getType()=='self'?'SELF-PAY':'INSURED') ?></td>
        </tr>
        <tr>
            <th>Insurance Scheme</th>
            <td><?php echo strtoupper(($pat->getInsurance()==NULL)? "SELF PAY": ($pat->getScheme()==NULL)? "SELF PAY":$pat->getScheme()->getName() )?></td>
        </tr>
        <tr>
            <th>Expiration</th>
            <td><?= (($pat->getInsurance()->getExpirationDate() == 0) ? '- -' : date("Y M, d", strtotime($pat->getInsurance()->getExpirationDate()))) ?></td>
        </tr>
        <tr>
            <th>Policy #/Enrollee ID</th>
            <td><?= ($pat->getInsurance()->getPolicyDetails())?></td>
        </tr>
        <tr>
            <th style="background-color: #eee">Account Status</th>
            <td style="background-color: #eee"><?php if ($pat->isActive()) { ?>
                    <a href="javascript:;" onclick="changeState('Deactivate')" title="Deactivate this patient's Account" >Deactivate Account</a>
                <?php } else { ?>
                    <a href="javascript:;" onclick="changeState('Activate')" >Activate Account</a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>

<script type="text/javascript">
    function changeState(changeTo) {
        Boxy.ask("Are you sure you want " + changeTo + " this patient's account?", ["Yes", "No"],
            function (d) {
                if (d === "Yes") {
                    $.ajax({
                        url: "/changeAccountStatus.php",
                        type: "post",
                        data: {pid: "<?= $pat->getId() ?>", status: "<?= ($pat->isActive())? 0 : 1 ?>"},
                        success: function (d) {
                            if ((d.split(":")[0]).trim() === "ok") {
                                location.href="/patient_find-or-create.php";
                            } else {
                                $("#notify").notify("create", {text: d.split(":")[1]}, {expires: 5000});
                            }
                        },
                        error: function (d) {
                            $("#notify").notify("create", {text: "Sorry action failed"}, {expires: 5000});
                        }
                    });
                }
            }
        );
    }
</script>