<?php
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ExamRoomDAO.php';
$DATA = (new ExamRoomDAO())->getExamRooms();?>
<h5>Existing Examination/Consultation Rooms</h5>
<div>
    <table class="table table-hover table-striped"><thead><tr><th>Room Name</th><th>Available?</th><th>Consultant</th><th>Specialization</th></tr></thead>

        <?php foreach ($DATA as $er){
            echo '<tr><td>'.$er->getName().'</td><td>'. ($er->getAvailable()==1?'Yes':'No').'</td><td>'. ($er->getConsultant()!=NULL?$er->getConsultant()->getFullname():'N/A').'</td><td>'. ($er->getSpecialization()!=NULL?$er->getSpecialization()->getName():'N/A').'</td></tr>';
        }
        ?>
    </table>
</div>


