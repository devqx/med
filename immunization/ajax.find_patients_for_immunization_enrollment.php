<div>Search Results for: <?php echo $_GET['id'] ?>
    <div id="results">
        <table id="resultsTable" border="0" class="table table-bordered table-hover">
            <thead>
            <tr>
                <th><input type="checkbox" onclick="selectAll(this)"/> </th>
                <th>EMR ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Date Of Birth</th>
                <th>Phone</th>
            </tr>
            </thead>
            <tbody><?php include '../classes/class.patient.php';
            $patient = new Manager();
            echo $patient->doFindPatientForEnrollment($_GET['id'],'immunization'); ?></tbody>
        </table>
    </div>
    <script>
        $(document).ready(function(){
            $("#resultsTable").tableScroll({height:100})
        });
    </script>
</div>