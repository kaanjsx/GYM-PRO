<?php
class User {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllMembers() {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'member'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users WHERE role != 'admin' ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserStats($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_stats WHERE user_id = :uid ORDER BY date ASC");
        $stmt->bindParam(":uid", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastProgram($memberId) {
        $stmt = $this->db->prepare("SELECT * FROM programs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAvatar($id, $fileName) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$fileName, $id]);
    }

    public function deleteUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function changeUserRole($userId, $newRole) {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$newRole, $userId]);
    }

    public function updateProfile($id, $name, $height, $weight, $fat_ratio) {
    // name = ? kısmını ekledik!
    $stmt = $this->db->prepare("UPDATE users SET name = ?, height = ?, weight = ?, fat_ratio = ? WHERE id = ?");
    return $stmt->execute([$name, $height, $weight, $fat_ratio, $id]);
}

    public function updateProfileInfo($id, $height) {
        $stmt = $this->db->prepare("UPDATE users SET height = :h WHERE id = :id");
        return $stmt->execute([':h' => $height, ':id' => $id]);
    }

    public function addDailyStat($userId, $weight, $fat) {
        $check = $this->db->prepare("SELECT id FROM user_stats WHERE user_id = ? AND date = CURRENT_DATE");
        $check->execute([$userId]);
        
        if($check->rowCount() > 0) {
            $stmt = $this->db->prepare("UPDATE user_stats SET weight = ?, fat_ratio = ? WHERE user_id = ? AND date = CURRENT_DATE");
            return $stmt->execute([$weight, $fat, $userId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO user_stats (user_id, weight, fat_ratio, date) VALUES (?, ?, ?, CURRENT_DATE)");
            return $stmt->execute([$userId, $weight, $fat]);
        }
    }

    public function getLatestStats($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_stats WHERE user_id = ? ORDER BY date DESC LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkEmailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register($data) {
        $sql = "INSERT INTO users (name, email, password, role, avatar) VALUES (:name, :email, :password, :role, 'default.png')";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password', $data['password']);
        $stmt->bindValue(':role', $data['role']);
        
        return $stmt->execute();
    }

    public function getAllTrainers() {
        $stmt = $this->db->prepare("SELECT id, name, avatar, email FROM users WHERE role = 'pt'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignTrainerToMember($memberId, $ptId) {
        $stmt = $this->db->prepare("UPDATE users SET pt_id = :pt_id WHERE id = :id");
        return $stmt->execute([':pt_id' => $ptId, ':id' => $memberId]);
    }

public function saveGoal($userId, $startWeight, $targetWeight, $targetFat = 0) {
        // Artık goals tablosu yerine doğrudan users tablosunu güncelliyoruz
        $stmt = $this->db->prepare("UPDATE users SET start_weight = ?, target_weight = ? WHERE id = ?");
        return $stmt->execute([$startWeight, $targetWeight, $userId]);
    }

    public function updateName($userId, $name) {
        $stmt = $this->db->prepare("UPDATE users SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $userId]);
    }
    

    public function getUnassignedMembers() {
        // Sadece 'member' rolünde ve pt_id'si NULL olanlar
        $stmt = $this->db->prepare("SELECT id, name, email, avatar, created_at FROM users WHERE role = 'member' AND pt_id IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyStudents($ptId) {
        $stmt = $this->db->prepare("SELECT id, name, email, avatar, height, created_at FROM users WHERE pt_id = ?");
        $stmt->execute([$ptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStudentToPt($memberId, $ptId) {
        $stmt = $this->db->prepare("UPDATE users SET pt_id = :pt_id WHERE id = :user_id");
        return $stmt->execute([':pt_id' => $ptId, ':user_id' => $memberId]);
    }

    public function removeStudent($memberId) {
        $stmt = $this->db->prepare("UPDATE users SET pt_id = NULL WHERE id = :user_id");
        return $stmt->execute([':user_id' => $memberId]);
    }
    
public function getGoal($userId) {
        $stmt = $this->db->prepare("SELECT start_weight, target_weight FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return ['start_weight' => 0, 'target_weight' => 0];
        }
        return $result;
    }

    public function updatePhysicalStats($userId, $weight, $height, $fat) {
        $sql = "UPDATE users SET weight = ?, height = ?, fat_ratio = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$weight, $height, $fat, $userId]);
    }

}

