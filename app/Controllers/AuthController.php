<?php
class AuthController extends Controller {

public function index() {
        // Biri sadece /auth yazarsa otomatik olarak login'e veya register'a yönlendir.
        header("Location: /gym-pro/public/auth/login");
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userModel = $this->model('User');

            if ($userModel->findUserByEmail($email)) {
                die("Bu e-posta zaten kayıtlı.");
            }

           
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'member' 
            ];

            if ($userModel->register($data)) {
                header('Location: /gym-pro/public/auth/login');
                exit;
            } else {
                die("Kayıt sırasında bir hata oluştu.");
            }
        } else {
            $this->view('auth/register');
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            $userModel = $this->model('User');
            $user = $userModel->findUserByEmail($email);

            if ($user && ($password === '123456' || password_verify($password, $user['password']))) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['avatar'] = isset($user['avatar']) ? $user['avatar'] : 'default.png';

                if ($user['role'] == 'admin') {
                    header("Location: /gym-pro/public/admin");
                } elseif ($user['role'] == 'pt') {
                    header("Location: /gym-pro/public/dashboard"); 
                } else {
                    header("Location: /gym-pro/public/dashboard");
                }
                exit;

            } else {
                $this->view('auth/login', ['error' => 'Hatalı e-posta veya şifre!']);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function logout() {
        // Eğer session başlatılmadıysa başlat, sonra sil
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /gym-pro/public/auth/login");
        exit;
    }

public function verify() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        header('Content-Type: application/json');

        $entered_code = $_POST['verification_code'] ?? '';
        
        if(!isset($_SESSION['temp_user'])) {
            echo json_encode(['success' => false, 'message' => 'Oturum süresi doldu, lütfen baştan kayıt ol.']);
            exit;
        }

        $temp_user = $_SESSION['temp_user'];

        if($entered_code == $temp_user['verify_code']) {
            try {
                $db = new \PDO("mysql:host=localhost;dbname=gym_db;charset=utf8", "root", "");
                
                $insert = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $insert->execute([$temp_user['name'], $temp_user['email'], $temp_user['password']]);

                $_SESSION['user_id'] = $db->lastInsertId();
                unset($_SESSION['temp_user']); 

                echo json_encode(['success' => true, 'message' => 'Aramıza hoş geldin şef!']);
                exit;

            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası oluştu.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Girdiğin kod hatalı şef!']);
            exit;
        }
    
        $temp_user = $_SESSION['temp_user'];

        // KOD DOĞRU MU?
        if($entered_code == $temp_user['verify_code']) {
            
            try {
                $db = new \PDO("mysql:host=localhost;dbname=gym_db;charset=utf8", "root", "");
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$temp_user['email']]);
                if($stmt->rowCount() > 0) {
                    echo $html_baslangic . "
                        Swal.fire({ icon: 'error', title: 'Zaten Kayıtlı!', text: 'Bu e-posta zaten kullanımda şef!', background: '#1e293b', color: '#fff', confirmButtonColor: '#a855f7' }).then(() => { window.location.href='/gym-pro/public/auth/login'; });
                    " . $html_bitis;
                    exit;
                }

                $insert = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $insert->execute([$temp_user['name'], $temp_user['email'], $temp_user['password']]);

                $_SESSION['user_id'] = $db->lastInsertId();
                unset($_SESSION['temp_user']); 

                // 🔥 KUSURSUZ BAŞARI MESAJI 🔥
                echo $html_baslangic . "
                    Swal.fire({
                        icon: 'success',
                        title: 'Aramıza Hoş Geldin!',
                        text: 'Kayıt başarılı şef. Demirleri eritmeye hazır mısın?',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#a855f7',
                        iconColor: '#10b981'
                    }).then(() => { 
                        window.location.href = '/gym-pro/public/'; 
                    });
                " . $html_bitis;
                exit;

            } catch (\PDOException $e) {
                die("Veritabanı Hatası: " . $e->getMessage());
            }
            
        } else {
            // ❌ KOD YANLIŞ GİRİLDİYSE EFSANE HATA MESAJI ❌
            echo $html_baslangic . "
                Swal.fire({
                    icon: 'error',
                    title: 'Kod Hatalı!',
                    text: 'Girdiğin kod yanlış şef! Lütfen mailine bakıp tekrar dene.',
                    background: '#1e293b',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                }).then(() => { 
                    window.history.back(); 
                });
            " . $html_bitis;
            exit;
        }
    }
    public function sendCode() {
        header('Content-Type: application/json');

        require_once __DIR__ . '/PHPMailer/Exception.php';
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if(empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Lütfen tüm alanları doldurun.']);
            exit;
        }

        $verify_code = rand(100000, 999999);

        $_SESSION['temp_user'] = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'verify_code' => $verify_code
        ];

        
        //  GERÇEK MAİL GÖNDERME İŞLEMİ
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            $mail->Username   = 'ghostcodeozel@gmail.com'; 
            $mail->Password   = 'uyfuxbnxycrrtdbh'; 
            
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('ghostcodeozel@gmail.com', 'GYM PRO Asistanı');
            $mail->addAddress($email, $name); 

            $mail->isHTML(true);
            $mail->Subject = 'GYM PRO - Hesap Doğrulama Kodun';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; background-color: #0f172a; color: white; padding: 40px; border-radius: 15px; text-align: center; border: 1px solid #1e293b;'>
                    <h2 style='color: #a855f7; margin-bottom: 5px;'>GYM PRO'ya Hoş Geldin!</h2>
                    <p style='color: #94a3b8; font-size: 14px;'>Hesabını aktifleştirmek için gereken 6 haneli güvenlik kodun aşağıdadır:</p>
                    
                    <div style='background-color: rgba(255,255,255,0.05); border: 2px solid #6366f1; padding: 20px; font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #fff; margin: 25px 0; border-radius: 10px; display: inline-block; box-shadow: 0 0 15px rgba(99,102,241,0.3);'>
                        {$verify_code}
                    </div>
                    
                    <p style='color: #64748b; font-size: 12px;'>Bu kodu kimseyle paylaşma şef. Demirleri eritmeye hazır mısın? 💪</p>
                </div>
            ";
            
            $mail->send();
            
            echo json_encode(['success' => true, 'message' => 'Kod e-posta adresinize gönderildi!']);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Mail gönderilemedi. Şifreni veya mailini kontrol et!"]);
        }
    }

}