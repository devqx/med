<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 3:04 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/sti_clinic/classes/class.patient.php';
$search = isset($_REQUEST['id']) && $_REQUEST['id']!="" ? $_REQUEST['id']:"";
?>
<div style="width:1050px"><?php if(isset($_REQUEST['id']) && $_REQUEST['id']!=""){?>Search Results for query: <em><?= $_REQUEST['id'] ?></em><?php }?>
    <table class="table table-striped table-hover" id="resultsTable">
        <thead>
        <tr>
            <th>*</th>
            <th>EMR ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Sex</th>
            <th>Date Of Birth</th>
            <th>Phone</th>
            <th>*</th>
        </tr>
        </thead>
        <tbody >
        <?php
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == "immunization") {
            echo $patient->doFindPatient($search, 'immunization');
        } else if (isset($_REQUEST['type']) && $_REQUEST['type'] == "antenatal") {
            echo $patient->doFindPatient($search, 'antenatal');
        } else if (isset($_REQUEST['type']) && $_REQUEST['type'] == "labour") {
            echo $patient->doFindPatient($search, 'labour');
        } else {
            echo $patient->doFindPatient($search);
        } ?>
        </tbody>
    </table>
</div>
<script>
    if(typeof jQuery!=='undefined'){
        $(document).ready(function () {
            $('#resultsTable').dataTable();
            setTimeout(function () {
                $('.dataTables_length select').select2('destroy')
            }, 50);
        });
    }

</script>
