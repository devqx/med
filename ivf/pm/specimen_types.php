<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 4:16 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticSpecimenDAO.php';
$data = (new GeneticSpecimenDAO())->all();
?>
    <div class="text-right clearfix clear">
        <a href="javascript:" class="action newBtn" data-href="new/specimen_type.php">New Specimen Category</a>
    </div>
<?php if(count($data) < 1){?>
    <div class="alert-box notice">No Genetic Specimens registered</div>
<?php } else { ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Reagent Name</th>
            <th>*</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $data as $item) {// $item=new GeneticSpecimen()?>
            <tr>
                <td><?= $item->getName() ?></td>
                <td><a class="edit" href="javascript:;" data-href="/ivf/pm/edit/specimen_type.php?id=<?= $item->getId()?>">Edit</a></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
<?php }?>