<?php
class AdminController extends Controller {
    public function index() {
        if ($_SESSION['role'] !== 'admin') exit;
        $this->view('dashboard/admin_panel', ['users' => $this->model('User')->getAllUsers()]);
    }
    public function delete($id) { if($_SESSION['role']=='admin') $this->model('User')->deleteUser($id); header("Location: /gym-pro/public/admin?msg=deleted"); }
    public function changeRole() { if($_SESSION['role']=='admin') $this->model('User')->changeUserRole($_POST['user_id'], $_POST['new_role']); header("Location: /gym-pro/public/admin?msg=ok"); }
}