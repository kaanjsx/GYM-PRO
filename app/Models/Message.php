<?php
class Message {
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }
    public function getConversation($u1, $u2) {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
        $stmt->execute([$u1, $u2, $u2, $u1]); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function send($s, $r, $m) { $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)"); return $stmt->execute([$s, $r, $m]); }
    public function getContacts($myId, $myRole) {
        $targetRole = ($myRole == 'pt') ? 'member' : 'pt';
        $stmt = $this->db->prepare("SELECT id, name, avatar FROM users WHERE role = ?");
        $stmt->execute([$targetRole]); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}