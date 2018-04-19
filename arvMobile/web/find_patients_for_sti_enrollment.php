<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/15/16
 * Time: 11:05 AM
 */
?>
<div>Search Results for: <span class="bo"><?= $_REQUEST['id'] ?></span>
    <div id="results">
        <table id="resultsTable" border="0" class="table table-hover table-striped">
            <thead>
            <tr>
                <th>*</th>
                <th>EMR ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Sex</th>
                <th>Date Of Birth</th>
                <th>Phone</th>
            </tr>
            </thead>
            <tbody><?php require_once $_SERVER['DOCUMENT_ROOT']. '/classes/class.patient.php';
            $patient = new Manager();
            echo $patient->doFindPatientForEnrollment($_REQUEST['id'], 'sti'); ?></tbody>
        </table>
    </div>
    <script>
        $(document).ready(function () {
            $("#resultsTable").tableScroll({height: 350});
            $("html, body").animate({ scrollTop: $(document).height() }, 1000);

            $("a.enrollBtn").on('click','', function(e){
                Boxy.load("/sti_clinic/boxy.enroll.php?pid="+$(this).data("id"),{title:$(this).data("title")});
            });
        });
    </script>
</div>
