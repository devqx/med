<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientCareMemberDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
$aid = $_GET['aid'];
$pid = $_GET['pid'];
$ip = (new InPatientDAO())->getInPatient($aid, FALSE);
$patientCMs = (new PatientCareMemberDAO())->getPatientCareMembersByInPatient($aid, TRUE);
?>
<div class="menu-head"><?php if($ip->getStatus() == 'Active'){?>
    <a href="javascript:void(0)" onclick="Boxy.load('/admissions/dialogs/editPatientCareTeam.php?aid=<?= $aid ?>', {title: 'Edit Patient Care Team', afterHide: function () {
        showTabs(9)
    }})" title="Edit Care Team">Add/Edit Team</a><?php }?>


</div>
<div>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>SN</th>
                <th>Team/Member Name</th>
                <th>Team/Member Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($patientCMs) === 0) { ?>
                <tr><td colspan="4"><div class="warning-bar">No Care Team is assigned to this patient <a href="javascript:void(0)" title="Assign Care Team" onclick="Boxy.load('/admissions/editPatientCareTeam.php?aid=<?= $aid ?>', {title: 'Patient Care Team', afterHide: function () {
                                    showTabs(1, 3)
                                }})">Assign Care team</a></div></td></tr>
                <?php
                } else {
                    foreach ($patientCMs as $key => $ct) {
                        ?>
                    <tr>
                        <td><?= $key + 1 ?></td>
                        <td><?= $ct->getType() === "Team" ? $ct->getCareTeam() : $ct->getCareMember() ?></td>
                        <td><?= $ct->getType() === "Team" ? $ct->getCareTeam()->getDescription() : $ct->getCareMember()->getProfession() ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>