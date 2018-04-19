<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
$pid = $_GET['pid'];
$aid = $_GET['aid'];
$type = $_GET['type'];
$vitalSignsData = (new VitalSignDAO())->getPatientVitalSigns($pid, $aid, [$type]);
//error_log(json_encode($vitalSignsData));
?>
<div class="vital-container">
    <div id="<?= str_replace(" ", "_", $type); ?>_graph" class="vital-graph"></div>
<!--    <div align="center" class="vital-last-value shadow">
        Last <?= $type ?> Reading:      <?php //error_log('<?= str_replace(" ", "_", $type); ?>_graph') ?>
        <h3><?= (isset($vitalSignsData) && sizeof($vitalSignsData) > 0) ? $vitalSignsData[sizeof($vitalSignsData) - 1]->getValue() : 'N/A' ?></h3>
        <span class="fadedText block"><?= (isset($vitalSignsData) && sizeof($vitalSignsData) > 0) ? $vitalSignsData[sizeof($vitalSignsData) - 1]->getReadDate() : '' ?></span>
        <a href="javascript:void(0)" onClick="Boxy.load('newVital.php?type=<?= $type ?>&pid=<?= $pid ?>&aid=<?= $aid ?>', {title: 'New Reading', afterHide: function() {
                        location.reload()
                    }})">Take New Reading</a>
    </div>-->
</div>

<script type="text/javascript">
    $(document).ready(function() {
        //Plot Temperature
        window.chartTemprature = new Highcharts.Chart({
            chart: {
                renderTo: '<?= str_replace(" ", "_", $type); ?>_graph',
                type: 'areaspline',
                ignoreHiddenSeries: true,
                width: 805,
                height: 400
            },
            rangeSelector: {
                selected: 4,
                inputEnabled: false,
                buttons: [{
                        type: 'minute',
                        count: 60,
                        text: 'hr'
                    }, {
                        type: 'day',
                        count: 1,
                        text: 'Tod'
                    }, {
                        type: 'day',
                        count: 7,
                        text: 'wk'
                    }, {
                        type: 'month',
                        count: 1,
                        text: '1m'
                    }, {
                        type: 'all',
                        text: 'All'
                    }]
            },
            title: {
                text: "Patient's <?= $type ?>"
            },
            xAxis: {
                type: 'datetime',
//                dateTimeLabelFormats: {// don't display the dummy year
//                    month: '%e. %b',
//                    year: '%b'
//                },
                title: {
                    text: 'Read Time'
                },
            },
            yAxis: {
                title: {
                    text: '<?= $type ?>'
                }
            },
            //navigator: {enabled: false},
            scrollbar: {enabled: false},
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
            },
            series: [{
                
                
                
                <?php if(isset($vitalSignsData) && sizeof($vitalSignsData) > 0 && isset(explode("/", $vitalSignsData[0]->getValue())[1])){ ?>
                    name: 'Systolic',
                        data: [
                            <?php
                            if (isset($vitalSignsData))
                                foreach ($vitalSignsData as $w) {
                                    echo "[(new Date('" . $w->getReadDate() . "')).getTime(), " . explode("/", $w->getValue())[0] . "],";
                                }
                            ?>
                        ]
                    }, {
                    name: 'Diastolic',
                    data: [
                        <?php
                            if (isset($vitalSignsData))
                            foreach ($vitalSignsData as $w) {
                                echo "[(new Date('" . $w->getReadDate() . "')).getTime(), " . explode("/", $w->getValue())[1] . "],";
                            }
                        ?>
                    ]
                    
                <?php }else{ 
                    echo "name: '". $type."',";
                    echo "data: [";
                        if (isset($vitalSignsData))
                        foreach ($vitalSignsData as $w) {
                            echo "[(new Date('" . $w->getReadDate() . "')).getTime(), " . $w->getValue() . "],";
                            //error_log("[" . $w->getReadDate() . ", " . $w->getValue() . "],");
                        }
                    echo ']';
                } ?>
                    
            }]
        });

    })

</script>