<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/15
 * Time: 3:08 PM
 */?>
    <div class="menu-head">
            <a href="javascript:void(0)"
               onClick="Boxy.load('/boxy.addPatientAllergen.php?id=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>', {title: 'New Allergen', afterHide: function() {showTabs(7); }})">
                New Allergen</a>
    </div>
<?php
$_GET['id'] = $_GET['pid'];
$_GET['view'] = "allergens";
include_once $_SERVER['DOCUMENT_ROOT'] . '/patient_profile.php';