<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/classes/AdmissionConfiguration.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AdmissionConfigurationDAO.php";
$confs = (new AdmissionConfigurationDAO())->getAdmissionConfigurations();
$conf = new AdmissionConfiguration();

if (isset($_POST['acid'])) {
    if (!isset($_POST['cost']) || trim($_POST['cost']) === "") {
        exit(json_encode("error:Please enter the admission cost"));
    }
    if (!isset($_POST['name']) || trim($_POST['name']) === "") {
        exit(json_encode("error:Please specify the label for this entry"));
    }


    $conf->setId($_POST['acid']);
    $conf->setName($_POST['name']);
    $conf->setDefaultPrice($_POST['cost']);

    $conf = (new AdmissionConfigurationDAO())->addAdmissionConfiguration($conf);
    if ($conf !== NULL) {
        exit(json_encode("success: Action completed successfully"));
    } else {
        exit(json_encode("error: Action failed!"));
    }
} else {//TODO: Use this to edit the Config
//    if (isset($_GET['acid']) && trim($_GET['acid']) !== "") {
//        $conf = (new AdmissionConfigurationDAO())->getAdmissionConfiguration($_GET['acid']);
//    }
}
?>
<div>
    <h5>Existing Admissions Cost</h5>
    <table class="table table-hover"  ALIGN ="CENTER" >
        <thead><tr><th>Label</th><th>Cost (N)</th></tr></thead>
        <tbody>
        <?php
            foreach ($confs as $e) {
                echo '<tr><td>'.$e->getName().'</td><td>'.$e->getDefaultPrice().'</td></tr>';
            }
        ?>
        </tbody>
    </table>

</div>
<h5>Add Admission Fixed Cost </h5>
<form action="..." method="POST" id="addAdmissionForm">
    <label>Label
        <input type="text" name="name" value="<?= $conf->getName() ?>" placeholder="eg: IP Admission">
    </label>
    <label>Cost
        <input type="number" min="0" name="cost" value="<?= $conf->getDefaultPrice() ?>">
    </label>

    <div class="btn-block">
        <input type="hidden" name="acid" value="<?= $conf->getId() ?>" >
        <button class="btn" type="button" name="addAdmission" id="addAdmission" value="true">Save</button>
        <button class="btn-link" type="reset">Reset</button>
        <div id="mgniu_"></div>
    </div>
</form>

<script type="text/javascript" >
    $(document).ready(function() {
        $("#addAdmission").click(function() {
            $.ajax({
                url: "/admissions/addAdmissionConfig.php",
                data: $("#addAdmissionForm").serialize(),
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
//                    alert(this.data)
                },
                success: function(d) {
                    console.log("Success: " + d);
                    if (d.indexOf("success") !== -1) {
                        Boxy.info(d.split(":")[1], function() {
                            location.reload();
                        });
                    } else {
                        Boxy.alert(d.split(":")[1]);
                    }
                },
                error: function(d) {
                    Boxy.alert(d.split(":")[1]);
                }
            });
        });
    });
</script>
