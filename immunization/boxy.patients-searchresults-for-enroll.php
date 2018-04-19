<!--this page is not called-->
<div style="width: 600px;height: 200px;font-size: small">
    <div id="results">
        Search Results for: <?php echo $_GET['id'] ?>
        <table class="table table-bordered table-hover" id="resultsTable">
            <thead>
            <tr>
                <th>*</th>
                <th>EMR ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Date Of Birth</th>
                <th>Phone</th>
            </tr>
            </thead>
            <tbody><?php include '../classes/class.patient.php';
            $patient = new Manager();
            echo $patient->doFindPatientForImmunizationEnrollment($_GET['id']); ?></tbody>
        </table>
    </div>
    </td></tr>


</div>
<script type="text/javascript">
    $(document).ready(function(){
        alert("exe");
        $("#resultsTable").tableScroll({height:100})
    });
</script>