<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$trainer_id = $_SESSION['user_id'] ?? 1;

$dbPath = __DIR__ . '/../../Config/database.php';
if (file_exists($dbPath)) { require_once $dbPath; }
if (!isset($db)) {
    try {
        $db = new PDO("mysql:host=localhost;dbname=gym_db;charset=utf8", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Veritabanı Hatası: " . $e->getMessage()); }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_student') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $error = "Bu e-posta zaten kayıtlı!";
    } else {
        $sql = "INSERT INTO users (name, email, role, created_at) VALUES (?, ?, 'member', NOW())";
        if ($db->prepare($sql)->execute([$name, $email])) {
            header("Location: ?view=dashboard&msg=added"); exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_program') {
    $p_user_id = $_POST['student_id'];
    $program_json = json_encode($_POST['program'], JSON_UNESCAPED_UNICODE);
    
    $check = $db->prepare("SELECT id FROM programs WHERE user_id = ?");
    $check->execute([$p_user_id]);
    
    if ($check->rowCount() > 0) {
        $sql = "UPDATE programs SET program_details = ?, trainer_id = ? WHERE user_id = ?";
    } else {
        $sql = "INSERT INTO programs (program_details, trainer_id, user_id) VALUES (?, ?, ?)";
    }
    
    if($db->prepare($sql)->execute([$program_json, $trainer_id, $p_user_id])) {
        $success = "Program başarıyla kaydedildi!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_message') {
    $receiver_id = $_POST['receiver_id'];
    $message_text = htmlspecialchars($_POST['message_text']);
    
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    if ($db->prepare($sql)->execute([$trainer_id, $receiver_id, $message_text])) {
        header("Location: ?view=dashboard&msg=msgsent"); exit;
    }
}


if (isset($_GET['msg']) && $_GET['msg'] == 'added') {
    $success = "Yeni öğrenci başarıyla eklendi! 🎉";
}

if (isset($_GET['msg']) && $_GET['msg'] == 'msgsent') {
    $success = "Mesaj başarıyla gönderildi! 📩";
}


$view = $_GET['view'] ?? 'dashboard';
$student_id = $_GET['id'] ?? null;

$current_prog = [];
$active_student = null;

if ($view == 'program' && $student_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$student_id]);
    $active_student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $progStmt = $db->prepare("SELECT program_details FROM programs WHERE user_id = ?");
    $progStmt->execute([$student_id]);
    $existing = $progStmt->fetch(PDO::FETCH_ASSOC);
    $current_prog = $existing ? json_decode($existing['program_details'], true) : [];
}

$sql = "SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC";
$students = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function getInitials($name) {
    $parts = explode(" ", $name);
    $i = mb_substr($parts[0], 0, 1);
    if(isset($parts[1])) $i .= mb_substr($parts[1], 0, 1);
    return mb_strtoupper($i);
}
$days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
?>
<!DOCTYPE html>
<html lang="tr" class="h-full bg-dark">
<head>
    <meta charset="UTF-8">
    <title>Eğitmen Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] }, colors: { primary: '#6366f1', dark: '#0f172a' } } } }
    </script>
    <style>
        .glass-panel { background: #111827; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .glass-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; }
        .glass-input:focus { outline: none; border-color: #6366f1; background: rgba(255,255,255,0.08); }
        .btn-primary { background: linear-gradient(135deg, #6366f1, #a855f7); transition: 0.3s; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="h-screen flex bg-[#0f172a] text-slate-200 overflow-hidden">

    <aside class="w-64 flex flex-col p-6 border-r border-white/5 bg-[#0f172a] shrink-0">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-primary to-purple-500 mb-10">GYM PRO</h1>
        <nav class="space-y-2">
            <a href="?view=dashboard" class="flex items-center gap-3 p-3 <?= $view=='dashboard' ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white' ?> rounded-xl transition">
                <i class='bx bxs-dashboard'></i> Genel Bakış
            </a>
        </nav>
        <a href="/gym-pro/public/auth/logout" class="mt-auto flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all duration-300 font-bold">
            <i class='bx bx-log-out text-xl'></i> Çıkış
        </a>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto relative">
        
        <?php if(isset($success)): ?>
            <div id="toast" class="absolute top-8 right-8 bg-green-500/20 text-green-400 px-6 py-4 rounded-xl border border-green-500/30 flex items-center gap-3 animate-bounce shadow-lg z-50">
                <i class='bx bxs-check-circle text-2xl'></i>
                <span class="font-bold"><?= $success ?></span>
            </div>
            <script>setTimeout(() => document.getElementById('toast').remove(), 3000);</script>
        <?php endif; ?>

        <?php if ($view == 'dashboard'): ?>
            
            <header class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Öğrenci Yönetimi 👋</h2>
                    <p class="text-slate-400">Toplam <?= count($students) ?> kayıtlı üye var.</p>
                </div>
                <button onclick="document.getElementById('addModal').style.display='flex'" class="btn-primary px-6 py-3 rounded-xl text-white font-bold flex items-center gap-2">
                    <i class='bx bx-plus'></i> Yeni Üye Ekle
                </button>
            </header>

            <div class="glass-panel rounded-2xl overflow-hidden min-h-[600px]">
                <?php if(isset($error)): ?><div class="bg-red-500/20 text-red-400 p-4 border-b border-red-500/20"><?= $error ?></div><?php endif; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-5">Öğrenci</th>
                                <th class="px-6 py-5">Rol</th>
                                <th class="px-6 py-5">Kayıt Tarihi</th>
                                <th class="px-6 py-5 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if(empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-20 text-slate-500">Henüz öğrenci yok.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($students as $s): ?>
                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                                <?= getInitials($s['name']) ?>
                                            </div>
                                            <div>
                                                <h4 class="text-white font-bold"><?= htmlspecialchars($s['name']) ?></h4>
                                                <p class="text-xs text-slate-500"><?= htmlspecialchars($s['email']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 uppercase">
                                            <?= htmlspecialchars($s['role'] ?: 'Belirsiz') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        <?= date('d.m.Y', strtotime($s['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-100 md:opacity-60 md:group-hover:opacity-100 transition">
                                            <button onclick="openMsgModal(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')" class="btn-primary px-3 py-2 rounded-lg text-white text-xs font-bold flex items-center gap-1 shadow-lg bg-gradient-to-r from-emerald-500 to-teal-600">
                                                <i class='bx bx-envelope text-base'></i> <span class="hidden md:inline">Mesaj</span>
                                            </button>
                                            <a href="?view=program&id=<?= $s['id'] ?>" class="btn-primary px-3 py-2 rounded-lg text-white text-xs font-bold flex items-center gap-1 shadow-lg">
                                                <i class='bx bx-dumbbell text-base'></i> <span class="hidden md:inline">Program</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($view == 'program' && $active_student): ?>

            <div class="max-w-6xl mx-auto animate-[fadeIn_0.3s_ease-in-out]">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <a href="?view=dashboard" class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition">
                            <i class='bx bx-arrow-back text-xl'></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Program Editörü</h2>
                            <p class="text-slate-400 text-sm">Öğrenci: <span class="text-primary font-bold"><?= htmlspecialchars($active_student['name']) ?></span></p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" form="programForm" class="btn-primary px-8 py-3 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-primary/25">
                            <i class='bx bx-save text-xl'></i> Kaydet ve Yayınla
                        </button>
                    </div>
                </div>

                <form id="programForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-20">
                    <input type="hidden" name="action" value="save_program">
                    <input type="hidden" name="student_id" value="<?= $active_student['id'] ?>">

                    <?php foreach($days as $day): ?>
                    <div class="glass-panel p-5 rounded-2xl group hover:border-primary/30 transition duration-300">
                        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                            <i class='bx bx-calendar text-primary'></i> <?= $day ?>
                        </h3>
                        <textarea 
                            name="program[<?= $day ?>]" 
                            rows="5" 
                            class="glass-input w-full p-4 rounded-xl text-sm placeholder-slate-600 resize-none"
                            placeholder="- Hareket Adı 3x12&#10;- Hareket Adı 4x10"
                        ><?= htmlspecialchars($current_prog[$day] ?? '') ?></textarea>
                    </div>
                    <?php endforeach; ?>
                </form>
            </div>

        <?php else: ?>
            <div class="text-center text-red-400 mt-20">Bir hata oluştu veya öğrenci bulunamadı. <a href="?view=dashboard" class="underline">Geri Dön</a></div>
        <?php endif; ?>

    </main>

    <div id="addModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-md border border-white/10 shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Hızlı Üye Ekle</h3>
                <button onclick="document.getElementById('addModal').style.display='none'" class="text-slate-400 hover:text-white"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_student">
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Ad Soyad</label>
                        <input type="text" name="name" class="glass-input w-full p-3 rounded-xl border border-white/10" required>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">E-Posta</label>
                        <input type="email" name="email" class="glass-input w-full p-3 rounded-xl border border-white/10" required>
                    </div>
                    <button type="submit" class="w-full py-3 btn-primary text-white rounded-xl font-bold mt-2">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <div id="msgModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-md border border-white/10 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white"><i class='bx bx-envelope text-emerald-400'></i> Mesaj Gönder</h3>
            <button onclick="document.getElementById('msgModal').style.display='none'" class="text-slate-400 hover:text-white"><i class='bx bx-x text-2xl'></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="receiver_id" id="msg_receiver_id">
            
            <p class="text-sm text-slate-400 mb-4">Alıcı Öğrenci: <span id="msg_receiver_name" class="text-emerald-400 font-bold"></span></p>
            
            <textarea name="message_text" rows="4" class="glass-input w-full p-4 rounded-xl border border-white/10 resize-none mb-4 focus:border-emerald-500" placeholder="Motivasyon mesajınızı yazın..." required></textarea>
            
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:opacity-90 text-white rounded-xl font-bold transition">Gönder</button>
        </form>
    </div>
</div>

<script>
function openMsgModal(id, name) {
    document.getElementById('msg_receiver_id').value = id;
    document.getElementById('msg_receiver_name').innerText = name;
    document.getElementById('msgModal').style.display = 'flex';
}
</script>

</body>
</html>