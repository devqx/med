<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/4/18
 * Time: 10:51 AM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EarlyWarningScore.php';

$ew = ( new EarlyWarningScore() )->getAll($_GET['pid'], $_GET['aid']);

/***
 * @param $score
 * @return string
 */
function get_color_code($score){

    $score_average = $score / 7;

    $color_code = array();
    switch ($score_average){

        case ($score_average < 1 && $score_average > 0 ):
            $color_code['bg_color'] = '';
            $color_code['color'] = '';
        break;

        case ($score_average == 1  ):
            $color_code['bg_color'] = '';
            $color_code['color'] = '';
            break;

        case ($score_average < 2 && $score_average > 1 ):
            $color_code['bg_color'] = 'yellow';
            $color_code['color'] = '#000';

            break;



        case ($score_average == 2 ):
            $color_code['bg_color'] = 'yellow';
            $color_code['color'] = '#000';

            break;

        case ($score_average <= 3 && $score_average > 2 ):
            $color_code['bg_color'] = 'red';
            $color_code['color'] = 'white';
            break;



        case ($score_average == 3  ):
            $color_code['bg_color'] = 'red';
            $color_code['color'] = 'white';
            break;


    }

    return $color_code;


}



?>


<div class="menu-head">
		<a href="javascript:void(0)"
		   onClick="Boxy.load('/admissions/patientTabs/early_warning.new.php?pid=<?=$_GET['pid']?>&aid=<?= $_GET['aid'] ?>', {title: 'New Early Warning ', afterHide: function() {showTabs(30); }})">
New Reading </a>
	</div>

<?php if( @count( $ew ) > 0 ){?>

<table class="table table-bordered">

    <thead>
    <tr> <th>Date / Time</th> <th> RR </th> <th> SpO2 </th> <th> SuppO2 </th> <th> Temp. </th> <th> Sy BP </th> <th> HR</th>  <th> LoC </th>   <th>SCORE</th> </tr>
    </thead>

    <tbody>

        <?php foreach ($ew as $key => $value ) {  $c = get_color_code($value['score']);  ?>

            <tr style="background: <?= $c['bg_color'];?>;color:<?= $c['color'];?>">
                <td> <?php  $date = explode(' ', $value['take_time'] ) ;?>

                    <span> <?php echo $date[0];?> &nbsp; <?php echo $date[1];?>  </span><br>

                By : <?= $value['firstname'] . ' ' .$value['lastname']?>
                </td>
                <td> <?= $value['respiration_rate'] ?></td>
                <td> <?= $value['oxygen_saturations'] ?></td>
                <td> <?= $value['supplemental_oxygen'] ?></td>
                <td> <?= $value['temperature'] ?></td>
                <td> <?= $value['systolic_bp'] ?></td>
                <td> <?= $value['heart_rate'] ?></td>
                <td> <?= $value['loc'] ?></td>
                <td> <?= $value['score'] ?></td>

            </tr>

        <?php }?>

    </tbody>
</table>

<?php } else echo "<div class='warning-bar' style='margin-top:5px'>No Early warning records for the patient</div>"?>

