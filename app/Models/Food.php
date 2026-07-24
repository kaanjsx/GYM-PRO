<?php
class Food {
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    public function addLog($userId, $name, $cal, $p, $f, $c) {
        $stmt = $this->db->prepare("INSERT INTO food_log (user_id, food_name, calories, protein, fat, carbs) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $name, $cal, $p, $f, $c]);
    }

    public function getDailyTotal($userId, $date = NULL) {
        $targetDate = $date ?? date('Y-m-d');
        $stmt = $this->db->prepare("SELECT SUM(calories) as total_cal, SUM(protein) as total_p, SUM(fat) as total_f, SUM(carbs) as total_c FROM food_log WHERE user_id = ? AND log_date = ?");
        $stmt->execute([$userId, $targetDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDailyLogs($userId, $date = NULL) {
        $targetDate = $date ?? date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM food_log WHERE user_id = ? AND log_date = ? ORDER BY created_at DESC");
        $stmt->execute([$userId, $targetDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}