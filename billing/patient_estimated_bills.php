<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/23/17
 * Time: 11:32 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillLineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';



$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);

$page = isset($_POST['page']) ? $_POST['page'] : 0;
$pageSize = 10;

$data = (new EstimatedBillsDAO())->EstimatedBills($_REQUEST['pid'],$page,$pageSize);



?>





    <table class="table table-striped estimatedBills">

            <thead>
            <tr>
                <th>ESB#</th>
                <th>Insurance Scheme</th>
                <th>Valid Date</th>
                <th>Date created</th>
                <th>Total</th>
                <th>*</th>
            </tr>
            </thead>


        <?php
              foreach ($data->data as $patient_bills) {
                  ?>
                  <tr>
                      <td><?= $patient_bills->escode ?></td>
                      <td><?= $patient_bills->Scheme ?></td>
                      <td><?= $patient_bills->period ?></td>
                      <td><?= $patient_bills->created_on ?></td>
                      <td><?= $patient_bills->total ?></td>

                      <td>
                          <?php if ($patient_bills->status === 'approved') {
                              ?>
                              <a target="_blank"
                                 href="/billing/estimated_bill_sheet.php?id=<?= $patient_bills->esid ?>&pid=<?= $patient_bills->PatientID ?>&reprint">View-Print</a>
                              <?php
                          } else {
                              ?>
                              <a onclick="approveBill(<?= $patient_bills->esid ?>,<?= $patient_bills->PatientID ?>)"
                                 href="javascript:void(0) ">Approve</a> &nbsp;|&nbsp;<a href="javascript:void(0)"
                                                                                        onclick="Boxy.load('/billing/boxy.edit_estimated_bills.php?id=<?= $patient_bills->esid ?>&pid=<?= $patient_bills->PatientID ?>')">View-Approve</a>
                              <?php

                          }
                          ?>
                      </td>
                  </tr>
                  <?php
              }


            ?>
    </table>

<script type="text/javascript">
    function approveBill(id,pid) {
        jQuery.ajax({
            url: '/api/approve_estimated_bill.php',
            type:'POST',
            data:{id:id,pid:pid},
            cache:false,
            success:function (data) {
                Boxy.info('Approved');
            }
        });

    }

    function deleteLine(id) {
        jQuery.ajax({
            url:'/api/del_estimated_bill_line.php',
            type:'POST',
            data:{id:id},
            cache:false,
            success:function (data) {

                Boxy.info('Removed');
            },
            error:function (data) {
                Boxy.alert('Failed');

            }
        })
    }

</script>