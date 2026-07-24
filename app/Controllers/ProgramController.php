<?php
class ProgramController extends Controller {
    public function save() {
        $input = json_decode(file_get_contents('php://input'), true);
        if($this->model('User')->assignProgram($_SESSION['user_id'], $input['member_id'], $input['program_name'], json_encode($input['exercises'])))
            echo json_encode(['status'=>'success', 'message'=>'Program atandı!']);
    }
}