<?php

class Antenatal {
    public $chartStageLabels = array (0,1,2,3,4,5,6,7,8,9,10,'');
    public $maxChartStages;
    function __construct() {
        if(!isset($_SESSION)){@session_start();}
    }

    public $ERROR_PATIENT_NOT_ANTENATAL='<div class="warning-bar">Sorry, Pregnancy Life Course Chart failed to load</div>';
    public $HEADER1 = '<thead>
    <tr >
        <th>PERIOD</th>
        <th>Preconception</th>
        <th colspan="3" style="text-align: center">TRIMESTER 1</th>
        <th colspan="3" style="text-align: center">TRIMESTER 2</th>
        <th colspan="4" style="text-align: center">TRIMESTER 3</th>
        <th>Post-partum</th>
    </tr></thead>';

}