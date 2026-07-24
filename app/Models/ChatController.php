<?php

class ChatController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { 
            header('Location: /gym-pro/public/auth/login'); exit; 
        }

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'member'; 
        
        $chatModel = $this->model('ChatModel');

        $userList = $chatModel->getChatList($userId, $role);

        $activeChatId = $_GET['chat_with'] ?? ($userList[0]['id'] ?? null);
        
        $activeUser = null;
        if ($activeChatId) {
            foreach ($userList as $u) {
                if ($u['id'] == $activeChatId) {
                    $activeUser = $u;
                    break;
                }
            }
        }

        $messages = [];
        if ($activeChatId) {
            $messages = $chatModel->getConversation($userId, $activeChatId);
        }

        $this->view('dashboard/chat', [
            'userList' => $userList,      // Sol menüdeki kişiler
            'activeUser' => $activeUser,  // Şu an mesajlaştığım kişi
            'messages' => $messages,      // Mesaj geçmişi
            'user_id' => $userId          // Benim ID'm
        ]);
    }

    public function send() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            
            $sender = $_SESSION['user_id'];
            $receiver = $input['receiver_id'] ?? null;
            $message = trim($input['message'] ?? '');

            if ($sender && $receiver && $message) {
                $this->model('ChatModel')->sendMessage($sender, $receiver, $message);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            exit;
        }
    }
    
    public function fetch_messages() {
        $sender = $_SESSION['user_id'];
        $receiver = $_GET['receiver_id'] ?? null;
        if($receiver) {
            $msgs = $this->model('ChatModel')->getConversation($sender, $receiver);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'messages' => $msgs]);
        }
    }
}