<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 1:30 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
        $labs = (new OphthalmologyDAO())->all();?>
<ul class="list-blocks">
    <?php foreach ($labs as $lab) {?>
        <li class="tag"><?= $lab->getName() ?><span><a class="editLab" href="javascript:;" data-href="/pages/pm/ophthedit.php?id=<?= $lab->getId() ?>">Edit</a> | <a class="editLabTemplate" href="javascript:;" data-href="/pages/pm/ophthtemplate-edit.php?id=<?= $lab->getTemplate()->getId() ?>">Edit Template</a></span></li>
    <?php }?>
</ul>