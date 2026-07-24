<?php
class DashboardController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) { 
            header("Location: /gym-pro/public/auth/login"); 
            exit; 
        }
        
        $role = $_SESSION['role'] ?? 'member';

        if ($role === 'admin') { 
            header("Location: /gym-pro/public/admin"); 
            exit; 
        }

        if ($role === 'trainer' || $role === 'pt') {
            
            $this->view('dashboard/trainer_dashboard');
            exit;
        }

        $this->view('dashboard/member_panel');
    }


}