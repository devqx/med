<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";
$insurance = (new InsurerDAO())->getInsurer($_GET['id'], TRUE);
?>
<div style="width: 500px;">
    <table class="table table-bordered table-striped">
        <thead><tr>
            <th colspan="2">Company Details</th>
        </tr></thead>
        <tr>
            <td>Company Name:</td>
            <td><?= $insurance->getName() ?> </td>
        </tr>
        <tr>
            <td>Address:</td>
            <td><?= $insurance->getAddress() ?> </td>
        </tr>
        <tr>
            <td>Contact Phone:</td>
            <td><?= $insurance->getPhone() ?></td>
        </tr>
        <tr>
            <td>Contact Email:</td>
            <td><a href="mailto:"><?= str_replace("@", "(a)", $insurance->getEmail()) ?> </a></td>
        </tr>
    </table>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Insurance Schemes Operated</th>
        </tr>
        </thead>
        <?php if(count($insurance->getSchemes()) == 0){?>
            <tr>
                <td><div class="notify-bar">No schemes are managed by this  provider</div> </td>
            </tr>
        <?php } else {foreach ($insurance->getSchemes() as $scheme) { ?>
            <tr>
            <td><?= $scheme->getName() ?></td>
            </tr>
        <?php } }?>
    </table>
    <div class="btn-block"><a class="pull-right" href="javascript:;" onclick="Boxy.get(this).hideAndUnload()">Close</a></div>
</div>