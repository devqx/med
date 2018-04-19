<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 9:18 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
$gr_id = (new ItemGroupDAO())->getItemGroup($_GET['gid']);
if ($_POST) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroupData.php';

    if (empty($_POST['group_id'])) {
        exit("error: Group name required");
    }

    $generic__ids = array_filter(explode(",", $_POST['generic']));
    if (count($generic__ids) == 0) {
        exit('error:At least a generic is required for the group');
    }
    $initial_generics = array_filter(explode(",", $_POST['initial_generic']));
    foreach ($generic__ids as $g => $ge) {
        $gen = (new ItemGroupDAO())->findGroupByGeneric($ge, $_POST['group_id'], null);
        if ($gen !== false) {
            exit("error: " . $gen->getGeneric()->getName() . " already exist in this Groups");
        }
    }

    $data = (new ItemGroupData())->setGroup((new ItemGroupDAO())->getItemGroup($_POST['group_id']))->setGeneric($generic__ids)->add();
    if ($data) {
        echo "success: Successfully added";
    } else {
        echo "error:Cannot add data";
    }

} ?>
<div><span class="error"></span>
    <form id="formItem" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
        <label for="group">Name
            <input type="text" value="<?= $gr_id->getName() ?>" readonly="readonly">
            <input type="hidden" name="group_id" value="<?= $_GET['gid'] ?>">
        </label>
        <label for="generic">Generic<input type="hidden" name="generic" class="generic" id="generic">
            <input type="hidden" name="initial_generic" value="<?= implode(',', $generics) ?>"></label>
        <div>
            <button class="btn" name="itemCatBtn" type="submit">Add</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        getGenerics();
    });

    function getGenerics() {
        $.ajax({
            url: '/api/get_item_generic.php',
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (result) {
                setGenerics(result);
            },

        });
    }

    function setGenerics(data) {
        $('input[name="generic"]').select2({
            width: '100%',
            allowClear: true,
            placeholder: "select item generic name",
            multiple: true,
            data: function () {
                return {results: data, text: 'name'};
            },
            formatResult: function (source) {
                return source.name;
            },
            formatSelection: function (source) {
                return source.name;
            }
        });
    }

    function start() {
    }
    function success(s) {
        var s1 = s.split(":");
        if (s1[0] === "success") {
            Boxy.info(s1[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        } else {
            if (s1[0] === "error") {
                Boxy.alert(s1[1]);
            }
        }
    }

</script>
