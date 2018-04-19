<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/15
 * Time: 4:01 PM
 */

?>
<div class="menu-head">
        <label style="display: inline-block">
            <a href="javascript:void(0)"
               onClick="Boxy.load('/boxy.addDiagnosis.php?pid=<?=$_GET['pid'] ?>&aid=<?=$_GET['aid'] ?>', {title: 'New Diagnosis', afterHide: function() {showTabs(4); }})">
                New Diagnosis</a>
        </label>
</div>
<?php
