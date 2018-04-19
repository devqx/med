<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/21/17
 * Time: 12:34 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillLineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EstimatedBills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EstimatedBillLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

$pdo = (new MyDBConnector())->getPDO();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);

$es_bill_lines = (new EstimatedBillLineDAO())->getEstimatedBillLineById($_GET['id']);
$bill_est = (new EstimatedBillsDAO())->getEstimatedBillById($_GET['id']);

$service_category = (new BillSourceDAO())->getBillSources();
$id = $_GET['id'];
$pid = $_GET['pid'];
$total = 0;

$existing_ids = [];

if ($_POST) {
    $es_id = $_GET['id'];
    $request_item = json_decode($_POST['requested_items']);

    $narration = $_POST['narration'];
    $last_modified = date('Y-m-d H:i:s');
    $es_bill = new EstimatedBills();
    $es_bill->setLastModified($last_modified);
    $es_bill->setNarration($narration);

    if ($request_item !== null){
        $sel_lines = [];
        foreach ($request_item as $line){
            $line = (object)$line;
            $total = $total + $line->quantity * $line->selling_price;
            $line->estimated_bill_id = $es_id;
            $sel_lines[] = $line;
        }
        //file_put_contents('/tmp/data.txt',json_encode($sel_lines));
        $es_lines = (new EstimatedBillLineDAO())->addEsBillLines($sel_lines,$pdo);
        if ($es_lines === null){
            exit('error: Failed to update');
        }

        $total_estimate = (new EstimatedBillsDAO())->getEstimatedBillById($es_id,$pdo)->getTotalEstimate();
        $newEstTotal = $total + $total_estimate;


        $es_bill->setId($es_id);
        $es_bill->setTotalEstimate($newEstTotal);
        $updes_total = (new EstimatedBillsDAO())->updateTotalEstimate($es_bill,$pdo);
        if ($updes_total === null){
            exit('error:Failed');
        }
        exit('success: Updated');

    }
    else if ($request_item === null){
        $newEstTotal = (new EstimatedBillsDAO())->getEstimatedBillById($es_id,$pdo)->getTotalEstimate();
        $es_bill->setTotalEstimate($newEstTotal);
        $es_bill->setId($es_id);
        $upd = (new EstimatedBillsDAO())->updateTotalEstimate($es_bill,$pdo);
        if ($upd === null){
            exit('error:Operation failed');
        }
        exit('success:Update successful!');
    }

}
?>

<section style="width: 900px">

    <hr class="border">
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: s, onComplete: d2})">
        <label>Note
            <textarea name="narration" id="narration" cols="30" rows="4"><?=$bill_est->getNarration()?></textarea>
        </label>

        <label>Service Category
            <input type="hidden" name="service_category" id="service_category">
        </label>
        <label>
            Service Item
            <input type="hidden" id="service_item" name="service_item">
            <td><button class="btn btn-mini" id="add-btn" type="button" ><i class="icon-plus-sign"></i></button></td>
        </label>
        <input type="hidden" name="requested_items">

        <div class="row-fluid">
            <table class="table table-striped" width="950">
                <tr>
                    <td>
                        <div style="height:150px; overflow:scroll; border: solid 10px #f9f9f9;">
                            <table class="table table-striped" width="900" id="item_list">
                                <thead><tr>
                                    <th>Service</th>
                                    <th>Qty</th>
                                    <th> Unit Price</th>
                                    <th>Amount</th>
                                    <th>*</th>
                                </tr>
                                </thead>
                                <?php foreach ($es_bill_lines as $es_bill_line){
                                    ?>
                                    <tr>
                                        <td><?= $es_bill_line->getItemDescription()?></td>
                                        <td><?= $es_bill_line->getQuantity()?></td>
                                        <td><?= number_format($es_bill_line->getUnitPrice(), 2) ?></td>
                                        <td><?=number_format($es_bill_line->getQuantity() * $es_bill_line->getUnitPrice(),2)?></td>
                                         <?php $item_price = $es_bill_line->getQuantity() * $es_bill_line->getUnitPrice();
                                         $existing_ids[] = $es_bill_line->getItemCode();
                                         ?>
                                        <td><button class="btn btn-mini cancel" type="button" onclick="deleteLine(<?=$es_bill_line->getId()?>,<?=$es_bill_line->getEstimatedBillId()?>,<?=$item_price?>)" ><i class="icon-remove-sign"></i></button></td>
                                    </tr>
                                <?php
                                } ?>

                            </table>

                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <button type="submit" class="btn">Update New Items</button>
        <?php
          if ($this_user->hasRole($protect->bill_auditor)){
              ?>
              <button type="button" class="btn" onclick="approveBill(<?=$id?>,<?=$pid?>,'<?=$action = 'approved'?>')">Approve</button>
        <?php
          }
          else{}
        ?>
        <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </form>

</section>
<script type="text/javascript">

  var service_category = <?= json_encode($service_category)?>;
  var selected = [];
  var stack_c = [];
  var existing = [];
  existing = <?=json_encode($existing_ids)?>

   $(document).ready(function () {
       getServiceCategory();
       $('#narration').summernote(SUMMERNOTE_MINI_CONFIG);
   });
    function s() {
        $(document).trigger('ajaxSend');
    }

    function d2(s) {
        $(document).trigger('ajaxStop');
        var data = s.split(":");
        if (data[0] == "success") {
            Boxy.info(data[1]);
            Boxy.get($(".close")).hideAndUnload();
        } else {
            Boxy.warn(data[1]);
        }
    }

    function getServiceCategory() {
      $('input[name="service_category"]').select2({
          placeholder:'select category',
          allowClear:true,
          width:'100%',
          data: {results: service_category, text:'name'},
          formatResult:function (data) {
              return data.name;
          },
          formatSelection: function (data) {
              return data.name;
          }
      }).on('change',function (s) {
          if (!s.handled){
              if ($(this).val() !== ''){
                  getServiceItems($(this).val());
              }
          }

      })

    }

    function getServiceItems(category_id) {
        $.getJSON('/api/get_item_cost.php?group_id='+category_id,function (data) {
            setServiceItems(data);
        })

    }

    function setServiceItems(data) {
        $('input[name="service_item"]').select2({
            placeholder:"select Items",
            width:'100%',
            allowClear:true,
            multiple:true,
            data: {results:data, text:'item_description'},
            formatResult:function (data) {
                return data.item_description+'['+data.default_price+']';

            },
            formatSelection: function (data) {
                return data.item_description+'['+data.default_price+']';

            }
        }).on('change',function (s_item) {
            if (! s_item.handled){
                if (s_item.added !== undefined){
                    if (_.includes(existing, s_item.added.item_code)) {
                        Boxy.alert(s_item.added.item_description + ' exist in the list');
                        $('input[name="service_item"]').select2('data','');
                    }
                    else {
                        getQuantity(s_item.added, function () {
//                        console.log(s_item.added);
//                        _.each($(s_item.target).select2("data"), function (i) {
//
//
//                        });
//
                        });
                    }

                }
                s_item.handled =true;
            }


        });

    }

    
    function approveBill(id,pid,action) {
        Boxy.ask("Are you sure you want to approve this?", ['Yes', 'No'], function (choice) {
            if (choice == 'Yes') {
                jQuery.ajax({
                    url: '/api/approve_estimated_bill.php',
                    type: 'POST',
                    data: {id: id, pid: pid,action:action},
                    cache: false,
                    success: function (data) {
                        Boxy.info('Approved');
                         Boxy.get($(".close")).hideAndUnload();

                    }
                });
            }
        });
    }

    function deleteLine(id,es_id,price) {

        Boxy.ask("Are you sure you want to remove this bill?", ['Yes', 'No'], function (choice) {
            if (choice == 'Yes') {
                jQuery.ajax({
                    url:'/api/del_estimated_bill_line.php',
                    type:'POST',
                    data:{id:id,bill_id:es_id,price:price},
                    cache:false,
                    success:function (data) {

                        Boxy.info('Removed');
                    },
                    error:function (data) {
                        Boxy.alert('Failed');

                    }
                });

            }
        });
    }
    
    $('#add-btn').click(function () {
        var it = $('#service_item').val() == ''? '': $('#service_item').val();
        var cat = $('#service_category').select2('data');
        if (it !=='') {
            var item = $('input[name="service_item"]').select2('data');
            _.each(item,function (select_item) {
                select_item.service_description =  cat.name;
                selected.push(select_item);
                var amount = select_item.selling_price * select_item.quantity;
                var str = '<tr><td>' + select_item.item_description + '</td> <td>' + select_item.quantity + '</td><td>' + select_item.selling_price + '</td><td>' + amount + '</td><td>&nbsp;</td></tr>'
                $('#item_list').append(str);
                $('input[name="service_category"]').select2('data', '');
                $('#service_category').select2('val','');
                $('input[name="service_item"]').select2('data', '');
                $('#service_item').select2('val','');
            });
            _.each(selected,function (e) {
                stack_c.push(e);

            });
          var all = $('input[name="requested_items"]').val(JSON.stringify(stack_c));

        }
    });

  var getQuantity = function (obj, callbackFn) {
      vex.dialog.prompt({
          message: 'Specify the quantity',
          placeholder: '',
          value: 1,
          overlayClosesOnClick: false,
          beforeClose: function (e) {
              e.preventDefault();
          },
          callback: function (value) {
              if (value !== false && value !== '') {
                  obj.quantity = value;
              } else {
                  obj.quantity = 1;
              }
              if (typeof callbackFn !== "undefined") {
                  callbackFn();
              }
          }, afterOpen: function ($vexContent) {
              var $submit = $($vexContent).find('[type="submit"]');
              $submit.attr('disabled', true);
              $('.vex-dialog-prompt-input').attr('autocomplete', 'off');
              $vexContent.find('.vex-dialog-prompt-input').on('input', function () {
                  if ($(this).val().trim() != '') {
                      $submit.removeAttr('disabled');
                  } else {
                      $submit.attr('disabled', true);
                  }
              }).trigger('input');
          }
      });
  }

</script>
