<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/26/15
 * Time: 2:38 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_GET['search'])) {?>
    <form method="post" action="ajax.searchvoucher.php"
          onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':loadResult});">
        <div class="input-append">
            <input type="text" name="q" id="q" style="width: 90%;"
                   placeholder="search voucher by voucher code, batch id, patient emr or name" autocomplete="off" class="bigSearchField">
            <button type="submit" class="btn remainder"><i class="icon-search"></i> Search</button>
        </div>
    </form>
    <div id="searchBox"></div>
    <?php exit;
}

$script_block = <<< EOF
function aTab(o) {
    container = $('#voucher_container');
    $('a.tab').each(function () {
        $(this).removeClass('on');
    });
    if (o === 1) {
        $('a.tab.batches').addClass('on');
        url = '/vouchers/batch_vouchers.php';
    } else if (o === 2) {
        $('a.tab.search').addClass('on');
        url = $('a.tab.search').attr('data-href');
    }
    LoadDoc(container, url)
}

function LoadDoc(container, url) {
    $.ajax({
        url: url,
        beforeSend: function () {
            loading(container);
        },
        complete: function (s) {
            loaded(container, s);
        }
    });
    return false;
}
function loading(container) {
    container.show();
    container.html('<div align="center"><img src="/img/loading.gif" /> Loading Data</div>');
}
function loaded(container, respObj) {
    container.html(respObj.responseText);
}

function start() {
    $('#searchBox').html('Please wait ...<img src="/img/loading.gif"/>');
}
function loadResult(s) {
    $('#searchBox').html(s);
    $('table.searchTable').dataTable({
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            // Total over all pages
            total = api
                .column( 2 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                } );

            // Total over this page
            pageTotal = api
                .column( 2, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            // Update footer
            $( api.column( 2 ).footer() ).html(
                'Total: <?= $currency->getSymbolLeft() ?>' + parseFloat(pageTotal).toFixed(2)+'<?= $currency->getSymbolRight() ?>'
            );
        }
    });
}
$(document).ready(function () {
    aTab(1);
    $('a.tab.new').click(function () {
        Boxy.load('/vouchers/boxy.add-voucher.php', {title: 'Generate Voucher'});
    });
});
EOF;
$page = "pages/vouchers/index.php";
$title = "Vouchers";
include "../template.inc.in.php";