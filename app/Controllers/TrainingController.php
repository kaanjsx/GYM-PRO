<?php

class TrainingController extends Controller {

    // 1. Programı Kaydet (AJAX ile çalışır)
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Gelen verileri kontrol et
            $studentId = $input['student_id'] ?? null;
            $programName = $input['program_name'] ?? 'Yeni Program';
            $exercises = $input['schedule'] ?? []; // Günler ve hareketler

            if (!$studentId || empty($exercises)) {
                echo json_encode(['status' => 'error', 'message' => 'Eksik bilgi gönderildi.']);
                exit;
            }

            $creatorId = $_SESSION['user_id'];
            $model = $this->model('TrainingModel');

            if ($model->createProgram($studentId, $creatorId, $programName, $exercises)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
            }
            exit;
        }
    }
}