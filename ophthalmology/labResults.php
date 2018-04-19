<table class="table table-striped table-bordered table-hover">
    <tr>
        <td>Field</td>
        <td>Value</td>
    </tr>

    <?php
    foreach($results[0]->getData() as $data){ ?>
    <tr>
        <td><?= $data->getLabTemplateData()->getLabel() ?></td>
        <td><?= $data->getValue() ?></td>
    </tr>
    <?php } ?>
</table>