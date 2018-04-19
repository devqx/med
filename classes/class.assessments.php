<?php
class Assessments {

    public function formatObjectiveNote($str){
        return '<a href="javascript:;" class="objective_data" data-object="'.htmlspecialchars($str).'">[Assessment Data]</a>';
    }
}