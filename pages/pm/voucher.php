<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 3:14 PM
 */
?>
<div>
    <div id="existingLabCenters">

    </div>
</div>
<script>
    $(document).ready(function () {
        $('#existingLabCenters').load("/pages/pm/voucher-centers.php", function () {
            $('table.table').dataTable();
        });
    });
</script>
