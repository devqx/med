<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/28/17
 * Time: 3:40 PM
 */

include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_item_category.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_item_group.php';
$cat = (new ItemCategoryDAO())->getCategories();

if ($_POST) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGeneric.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroupData.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';

    if (empty($_POST['name'])) {
        exit("error: name required");
    }
    $store = (new ItemGeneric())->setName($_POST['name'])->setCategory((new ItemCategoryDAO())->get($_POST['category_id']))->setDescription($_POST['description'])->add();
    if ($store !== NULL) {
        if (!is_blank($_POST['group_id'])) {
            $grp_d = (new ItemGroupData())->setGeneric((new ItemGenericDAO())->get($store->getId()))->setGroup((new ItemGroupDAO())->getItemGroup($_POST['group_id']))->add();

        }
        echo "success:Item Generic Added";
    } else {
        echo "error:Cannot add Item Generic";
    }
} ?>
<div>
    <form id="formItem" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
        <label for="item_generic">Name<input type="text" name="name" id="item_generic"></label>
        <label>Category
            <span class="pull-right"><a href="javascript:;" id="addCatLink">Add Category</a></span>
            <input type="hidden" name="category_id" id="category_id">
        </label>
        <label>Group<span class="pull-right"><a href="javascript:;" id="addGroupLink">Add Group</a></span>
            <input type="hidden" name="group_id" id="group_id"></label>
        <label>Description <textarea name="description"></textarea> </label>
        <div>
            <button class="btn" name="itemCatBtn" type="submit">Add</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    var itemCat = <?= json_encode($itemCategories, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
    var itemGroup = <?= json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
    function success(s) {
        var s1 = s.split(":");
        if (s1[0] === "success") {
            Boxy.info(s1[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        } else {
            if (s1[0] === "error") {
                Boxy.alert("Unable To Save Item Generic");
            }
        }
    }

    $(document).ready(function () {
        $('#addCatLink').on('click', function () {
            Boxy.load('/pm/items/add_category.php', {
                afterHide: function () {
                    $.ajax({
                        url: "/api/get_item_category.php",
                        type: "POST",
                        dataType: "json",
                        success: function (c) {
                            itemCat = c;
                            refreshICat();
                        }
                    });
                }
            });
        });

        $('#addGroupLink').on('click', function () {
            Boxy.load('/pm/items/addGroup.php', {
                afterHide: function () {
                    $.ajax({
                        url: "/api/get_item_group.php",
                        type: "POST",
                        dataType: "json",
                        success: function (c) {
                            itemGroup = c;
                            refreshGroup_();
                        }
                    });
                }
            });
        });
    });

    function refreshICat() {
        $("#category_id").select2("destroy");
        setTimeout(function () {
            $("#category_id").select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Select Item Category',
                data: {results: itemCat, text: 'name'},
                formatResult: function (source) {
                    return source.name;
                },
                formatSelection: function (source) {
                    return source.name;
                }
            });
        }, 50);
    }

    function refreshGroup_() {
        $("#group_id").select2("destroy");
        setTimeout(function () {
            $("#group_id").select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Select generic group',
                data: {results: itemGroup, text: 'name'},
                formatResult: function (source) {
                    return source.name;
                },
                formatSelection: function (source) {
                    return source.name;
                }
            });
        }, 50);
    }
    refreshICat();
    refreshGroup_();

</script>
