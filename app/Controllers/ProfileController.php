<?php
class ProfileController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        
        $userModel = $this->model('User');
        $msg = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_SESSION['user_id'];
            
            if (!empty($_FILES['avatar']['name'])) {
                $f = time() . "_" . basename($_FILES["avatar"]["name"]);
                $target = __DIR__ . "/../../public/assets/uploads/" . $f; 
                if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $target)) { 
                    $userModel->updateAvatar($userId, $f); 
                    $_SESSION['avatar'] = $f; 
                }
            }

            $name = !empty($_POST['name']) ? $_POST['name'] : ''; // İSİM EKLENDİ
            $weight = !empty($_POST['weight']) ? $_POST['weight'] : 0;
            $height = !empty($_POST['height']) ? $_POST['height'] : 0;
            $fat = !empty($_POST['fat']) ? $_POST['fat'] : 0;

            if (!empty($name)) {
                $userModel->updateName($userId, $name);
                $_SESSION['user_name'] = $name; 
                $_SESSION['name'] = $name; 
            }

            $userModel->updatePhysicalStats($userId, $weight, $height, $fat);

            if (method_exists($userModel, 'addDailyStat')) {
                $userModel->addDailyStat($userId, $weight, $fat);
            }

            if (isset($_POST['start_weight']) && isset($_POST['target_weight'])) {
                $userModel->saveGoal($userId, $_POST['start_weight'], $_POST['target_weight'], $_POST['target_fat'] ?? 0);
            }
            
            $msg = "Profil bilgilerin başarıyla güncellendi! ✅";
        }

        $user = $userModel->getUserById($_SESSION['user_id']);
        
        $latestStats = method_exists($userModel, 'getLatestStats') ? $userModel->getLatestStats($_SESSION['user_id']) : [];
        $userGoal = method_exists($userModel, 'getGoal') ? $userModel->getGoal($_SESSION['user_id']) : [];

        $this->view('dashboard/profile', [
            'user' => $user, 
            'stats' => $latestStats,
            'goal' => $userGoal,
            'userModel' => $userModel, 
            'msg' => $msg
        ]);
    }
}