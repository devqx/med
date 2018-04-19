<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$patients = (new PatientDemographDAO())->getPatients(FALSE, NULL, FALSE);
?>
<h6>Inactive Patients:</h6>
<table id="patientList" class="table table-hover table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>*</th>
        </tr>
    </thead>
    <?php foreach ($patients as $pat) { //$pat=new PatientDemograph();?>
        <tr>
            <td><i class="icon-chevron-right"></i>
                <span style=""><a href="javascript:;"><?= $pat->getFullname() ?></a></span>
                <!--<span style=""><a href="/patient_profile.php?id=<?= $pat->getId() ?>&_a=FALSE"><?= $pat->getFullname() ?></a></span>-->
            </td>
            <td><i class="fa fa-lock"></i><a href="javascript:void(0);"> Disabled</a></td>
            <?php if ($pat->isActive()) { ?>                
                <td><i class="icon-lock"></i><a href="javascript:void(0);" onClick="changeState('Disable','<?= $pat->getId() ?>')">Disable</a></td>
            <?php } else { ?>
                <td><i class="icon-ok-circle"></i><a href="javascript:void(0);" onClick="changeState('Activate', '<?= $pat->getId() ?>')">Enable </a></td>
                    <?php } ?>
        </tr>
    <?php } ?>

</table>
<script type="text/javascript">
    $("#usersList").tableScroll({height: 380});
    
    function changeState(changeTo, pid) {
        Boxy.ask("Are you sure you want " + changeTo + " this patient's profile?", ["Yes", "Not yet"],
                function (d) {
                    if (d === "Yes") {
                        $.ajax({
                            url: "/changeAccountStatus.php",
                            type: "post",
                            data: {pid: pid, status: (changeTo === "Activate" ? 1 : 0)},
                            success: function (d) {
                                if (d.split(":")[0] === "ok") {
                                    location.href="/patient_profile.php?id="+pid;
                                } else {
                                    $("#notify").notify("create", {text: d.split(":")[1]}, {expires: 3000});
                                }
                            },
                            error: function (d) {
                                $("#notify").notify("create", {text: "Sorry action failed"}, {expires: 3000});
                            }
                        });
                    }
                })
    }
</script>

