<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$user_id = $_SESSION['user_id'] ?? 1; 

try {
    $dsn = "mysql:host=localhost;dbname=gym_db;charset=utf8";
    $username = "root";
    $password = "";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) { 
    // Hata loglama işlemi
    error_log("Bağlantı Hatası: " . $e->getMessage());
    die("Sistem geçici olarak hizmet dışıdır."); 
}

if (isset($_POST['action']) && $_POST['action'] == 'update_water') {
    $amount = floatval($_POST['amount'] ?? 0);
    $today = date('Y-m-d');
    $sql = "INSERT INTO water_log (user_id, amount, date) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE amount = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $amount, $today, $amount]);
    echo "Kaydedildi:" . $amount;
    exit; 
}

$today_str = date('Y-m-d');
$stmt = $db->prepare("SELECT amount FROM water_log WHERE user_id = ? AND date = ?");
$stmt->execute([$user_id, $today_str]);
$w_res = $stmt->fetch(PDO::FETCH_ASSOC);

$current_liters = ($w_res) ? (float)$w_res['amount'] : 0;
$target_liters = 5.0;
$progress_percent = ($current_liters / $target_liters) * 100;

$all_msgs = [];
try {
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY m.created_at DESC");
    $stmt->execute([$user_id]);
    $all_msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$user = ['name' => 'Misafir', 'height' => 0, 'weight' => 0, 'fat_ratio' => 0, 'avatar' => 'default.png'];
try {
    $stmt = $db->prepare("SELECT name, avatar, height, weight, fat_ratio FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetched) { $user = array_merge($user, $fetched); }
} catch (Exception $e) { }

$program_data = [];
$has_program = false;
$last_update = "---";
try {
    $stmt = $db->prepare("SELECT program_details, updated_at, created_at FROM programs WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['program_details'])) {
        $program_data = json_decode($result['program_details'], true);
        $has_program = true;
        $date = $result['updated_at'] ? $result['updated_at'] : $result['created_at'];
        $last_update = date('d.m.Y', strtotime($date));
    }
} catch (Exception $e) {}

$days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
$todayIndex = date('N') - 1; 

$unread_msgs = [];
try {
    $stmt = $db->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? AND m.is_read = 0 ORDER BY m.created_at DESC");
    $stmt->execute([$user_id]);
    $unread_msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$view = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GYM PRO | Panel</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"> 
    
    <style>
        /* TEMA DEĞİŞKENLERİ SİSTEMİ BEYNİ */
        :root, .theme-classic {
            --primary: #6366f1; /* Classic Default */
            --secondary: #a855f7;
            --dark: #0f172a;
            --panel: rgba(255, 255, 255, 0.02);
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.05);
        }

        .theme-aggressive {
            --primary: #e11d48;
            --secondary: #eab308;
            --dark: #050505;
            --panel: rgba(17, 17, 17, 0.8);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(225, 29, 72, 0.1);
        }

        .theme-elite {
            --primary: #cfa87a;
            --secondary: #9c7a53;
            --dark: #080808;
            --panel: rgba(18, 18, 18, 0.8);
            --text-main: #f4efe6;
            --text-muted: #8b7a66;
            --glass-border: rgba(207, 168, 122, 0.1);
        }

        body { background-color: var(--dark); color: var(--text-main); transition: background-color 0.5s ease; }
        
        .glass-card { background: var(--panel); backdrop-filter: blur(25px); border: 1px solid var(--glass-border); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5); transition: all 0.5s ease; }
        .glass-input { background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); color: var(--text-main); transition: all 0.3s ease; }
        .glass-input:focus { border-color: var(--primary); box-shadow: 0 0 12px var(--primary); outline: none; }
        
        .btn-neon { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: var(--dark); transition: all 0.3s ease; border: 1px solid var(--glass-border); font-weight: bold; }
        .btn-neon:hover { box-shadow: 0 0 20px var(--primary); transform: translateY(-1px); border-color: var(--primary); color: white; }
        
        .text-primary-var { color: var(--primary); }
        .text-secondary-var { color: var(--secondary); }
        .bg-primary-var { background-color: var(--primary); }
        .border-primary-var { border-color: var(--primary); }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--secondary); border-radius: 4px; }
        @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-fadeIn { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        
        .muscle-polygon { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; transform-origin: center; stroke: #333; }
        .muscle-polygon:hover { filter: drop-shadow(0 0 10px var(--primary)) brightness(1.2); }
        .muscle-pumped { filter: drop-shadow(0 0 20px var(--primary)) !important; fill: var(--primary) !important; stroke: var(--text-main) !important; stroke-width: 1.5; }
        #muscleTooltip { pointer-events: none; transition: opacity 0.2s; z-index: 100; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { 
                        primary: 'var(--primary)', 
                        secondary: 'var(--secondary)', 
                        dark: 'var(--dark)'
                    }
                }
            }
        }
    </script>
</head>
<body class="h-screen flex overflow-hidden">
    
    <aside class="w-80 h-full flex flex-col p-8 flex-shrink-0 relative z-20 hidden md:flex glass-card" style="border-right-width: 1px; border-radius: 0;">
        <div class="mb-12 px-2">
            <h1 class="text-4xl font-black tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary drop-shadow-md">GYM PRO</h1>
            <p class="text-[10px] text-secondary font-bold tracking-[0.4em] mt-2 uppercase pl-1" data-i18n="app_desc">Management System</p>
        </div>
        <nav class="flex-1 flex flex-col space-y-4">
            <a href="?view=dashboard" class="group relative flex items-center p-4 rounded-2xl border <?= $view == 'dashboard' ? 'bg-primary/10 border-primary/40' : 'border-transparent hover:bg-white/5' ?> transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><i class='bx bxs-dashboard text-2xl'></i></div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_dash">Panelim</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="nav_stat">Genel İstatistikler</span></div>
            </a>
            <a href="?view=analysis" class="group relative flex items-center p-4 rounded-2xl border <?= $view == 'analysis' ? 'bg-primary/10 border-primary/40' : 'border-transparent hover:bg-white/5' ?> transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><i class='bx bx-scan text-2xl'></i></div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_analysis">Analiz</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="nav_map">Kas & Güç Haritası</span></div>
            </a>
            <a href="?view=gallery" class="group relative flex items-center p-4 rounded-2xl border <?= $view == 'gallery' ? 'bg-primary/10 border-primary/40' : 'border-transparent hover:bg-white/5' ?> transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><i class='bx bxs-camera text-2xl'></i></div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_gallery">Gelişimim</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="nav_diary">Fotoğraf Günlüğü</span></div>
            </a>
            <a onclick="openCalculator()" class="group relative flex items-center p-4 rounded-2xl border border-transparent hover:bg-white/5 transition-all duration-300 cursor-pointer">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><i class='bx bxs-calculator text-2xl'></i></div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_calc">Hesaplayıcı</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="nav_body">Vücut Analizi</span></div>
            </a>
            <a href="/gym-pro/public/profile" class="group relative flex items-center p-4 rounded-2xl border border-transparent hover:bg-white/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary"><i class='bx bxs-user-circle text-2xl'></i></div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_profile">Profilim</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="profile_edit">Profil Düzenle</span></div>
            </a>
            <a href="?view=messages" class="group relative flex items-center p-4 rounded-2xl border <?= $view == 'messages' ? 'bg-primary/10 border-primary/40' : 'border-transparent hover:bg-white/5' ?> transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition shadow-[0_0_15px_var(--primary)]">
                    <i class='bx bxs-chat text-2xl'></i>
                    <?php if(!empty($unread_msgs)): ?><span class="absolute top-4 right-4 w-3 h-3 bg-secondary border-2 border-dark rounded-full animate-bounce"></span><?php endif; ?>
                </div>
                <div class="ml-4"><h4 class="text-main font-bold text-lg leading-tight" data-i18n="nav_msg">Mesajlarım</h4><span class="text-secondary opacity-70 text-xs font-medium" data-i18n="nav_contact">İletişim</span></div>
            </a>
        </nav>
        <a href="/gym-pro/public/auth/logout" class="group flex items-center justify-center gap-3 p-4 rounded-2xl border border-white/5 hover:bg-white/5 text-slate-400 hover:text-primary transition-all duration-300 mt-auto">
            <i class='bx bx-log-out text-xl'></i><span class="font-bold tracking-wider uppercase text-xs" data-i18n="logout">Çıkış Yap</span>
        </a>
    </aside>

    <main class="flex-1 overflow-y-auto p-6 pb-28 md:p-10 relative z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent">
        
        <header class="flex justify-between items-center mb-10">
            <div>
                <?php if ($view == 'messages'): ?>
                    <div class="flex items-center gap-3 mb-2"><span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-[0.3em] rounded-full border border-primary/20" data-i18n="nav_contact">İletişim</span></div>
                    <h2 class="text-3xl md:text-4xl font-light tracking-tight" data-i18n="nav_msg">Mesajlarım</h2>
                <?php elseif ($view == 'gallery'): ?>
                    <div class="flex items-center gap-3 mb-2"><span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-[0.3em] rounded-full border border-primary/20" data-i18n="nav_diary">Arşiv</span></div>
                    <h2 class="text-3xl md:text-4xl font-light tracking-tight" data-i18n="nav_gallery">Gelişimim</h2>
                <?php elseif ($view == 'analysis'): ?>
                    <div class="flex items-center gap-3 mb-2"><span class="px-3 py-1 bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-[0.3em] rounded-full border border-primary/20" data-i18n="details">Detaylar</span></div>
                    <h2 class="text-3xl md:text-4xl font-light tracking-tight" data-i18n="nav_analysis">Analiz</h2>
                <?php else: ?>
                    <div class="flex items-center gap-3 mb-2"><span class="px-3 py-1 bg-secondary/10 text-secondary text-[10px] font-bold uppercase tracking-[0.3em] rounded-full border border-secondary/20" data-i18n="summary">Özet</span></div>
                    <h2 class="text-3xl md:text-4xl font-light tracking-tight" data-i18n="nav_dash">Dashboard</h2>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex gap-2">
                    <select id="themeSelector" onchange="changeTheme(this.value)" class="bg-black/40 border border-white/10 text-xs text-white rounded-xl px-2 py-2 outline-none focus:border-primary cursor-pointer">
                        <option value="theme-classic">Classic Pro</option>
                        <option value="theme-aggressive">Aggressive Red</option>
                        <option value="theme-elite">Selected Elite</option>
                    </select>
                    <select id="langSelector" onchange="changeLang(this.value)" class="bg-black/40 border border-white/10 text-xs text-white rounded-xl px-2 py-2 outline-none focus:border-primary cursor-pointer">
                        <option value="tr">TR</option>
                        <option value="en">EN</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 bg-white/5 border border-white/5 p-2 md:pr-6 rounded-full hover:bg-white/10 transition-all duration-300 group backdrop-blur-md shadow-lg ml-2 cursor-pointer" onclick="window.location.href='/gym-pro/public/profile'">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full p-[2px] bg-gradient-to-tr from-primary to-secondary group-hover:rotate-12 transition-transform duration-500">
                        <?php 
                            $avatar = !empty($user['avatar']) ? $user['avatar'] : 'default.png';
                            $avatarUrl = ($avatar === 'default.png') ? "https://ui-avatars.com/api/?name=".urlencode($user['name'])."&background=random&color=fff&bold=true" : "/gym-pro/public/assets/uploads/".htmlspecialchars($avatar);
                        ?>
                        <img src="<?= $avatarUrl ?>" class="w-full h-full rounded-full object-cover border-2 border-dark">
                    </div>
                    <div class="hidden md:block"><span class="block text-xs text-secondary uppercase font-bold tracking-widest group-hover:text-primary transition" data-i18n="welcome">Hoş Geldin,</span><span class="block font-light text-sm group-hover:scale-105 transition-transform"><?= htmlspecialchars($user['name']) ?></span></div>
                </div>
            </div>
        </header>

        <?php if ($view == 'messages'): ?>
            <div class="animate-fadeIn max-w-4xl mx-auto pb-10">
                <div class="relative pl-4 md:pl-8 border-l border-primary/20 space-y-8">
                    <?php if(empty($all_msgs)): ?>
                        <div class="glass-card p-10 md:p-20 text-center rounded-3xl border-dashed border-white/5"><i class='bx bx-mail-send text-6xl text-slate-700 mb-4'></i><p class="text-slate-500 font-medium" data-i18n="no_msg">Henüz bir mesajın bulunmuyor.</p></div>
                    <?php else: ?>
                        <?php foreach($all_msgs as $m): ?>
                        <div class="relative group">
                            <div class="absolute -left-[24px] md:-left-[40px] top-6 w-4 h-4 rounded-full bg-dark border-[3px] <?= $m['is_read'] ? 'border-white/10' : 'border-primary shadow-[0_0_10px_var(--primary)]' ?> transition-all duration-500"></div>
                            <div class="glass-card p-5 md:p-6 rounded-3xl transition-all duration-300 hover:border-primary/50 relative overflow-hidden">
                                <?php if(!$m['is_read']): ?><div class="absolute top-0 right-0 bg-primary text-xs text-white font-bold px-3 py-1 rounded-bl-xl tracking-tighter uppercase" data-i18n="new">Yeni</div><?php endif; ?>
                                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-5">
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-gradient-to-tr from-primary to-secondary flex items-center justify-center text-white font-bold shrink-0 shadow-lg"><?= mb_substr($m['sender_name'], 0, 1) ?></div>
                                    <div class="flex-1 w-full min-w-0">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-1 mb-3">
                                            <h4 class="font-bold text-base md:text-lg tracking-tight group-hover:text-primary transition-colors"><?= htmlspecialchars($m['sender_name']) ?></h4>
                                            <span class="text-secondary opacity-70 text-[10px] md:text-xs font-medium italic"><i class='bx bx-calendar-check'></i> <?= date('d M Y, H:i', strtotime($m['created_at'])) ?></span>
                                        </div>
                                        <div class="bg-black/20 p-4 rounded-2xl border border-white/5"><p class="text-sm md:text-base leading-relaxed break-all whitespace-normal font-light">"<?= nl2br(htmlspecialchars($m['message'])) ?>"</p></div>
                                        <?php if(!$m['is_read']): ?><div class="mt-4 flex justify-end"><a href="?read_msg=<?= $m['id'] ?>" class="text-[11px] font-bold text-primary hover:text-white flex items-center gap-1 transition-colors uppercase tracking-widest"><span data-i18n="mark_read">Okundu İşaretle</span> <i class='bx bx-check-double text-lg'></i></a></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif ($view == 'gallery'): ?>
            <div class="animate-fadeIn max-w-5xl mx-auto pb-10">
                <div class="glass-card p-6 md:p-8 rounded-[2.5rem] border-t border-white/5 mb-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-2xl font-light" data-i18n="visual_diary">Görsel Günlük</h3>
                            <p class="text-secondary opacity-80 text-xs mt-1" data-i18n="diary_desc">Fiziksel dönüşümünü galerinde takip et. 📸</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
                        <div onclick="document.getElementById('uploadBefore').click()" class="relative group rounded-3xl overflow-hidden border border-white/10 aspect-[4/5] cursor-pointer transition-all hover:border-primary bg-black/40">
                            <img id="imgBefore" src="https://via.placeholder.com/500x650/111/fff?text=Ilk+Halini+Yukle" class="w-full h-full object-cover opacity-30 group-hover:opacity-60 transition-opacity">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40"><i class='bx bx-upload text-5xl text-primary drop-shadow-lg'></i></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 to-transparent flex items-end p-8 pointer-events-none"><span class="font-bold tracking-[0.2em] uppercase text-sm" data-i18n="before">İLK HALİ</span></div>
                            <input type="file" id="uploadBefore" class="hidden" accept="image/*">
                        </div>
                        <div onclick="document.getElementById('uploadAfter').click()" class="relative group rounded-3xl overflow-hidden border border-primary/50 aspect-[4/5] cursor-pointer transition-all hover:border-primary shadow-lg bg-black/40">
                            <img id="imgAfter" src="https://via.placeholder.com/500x650/111/fff?text=Guncel+Formu+Yukle" class="w-full h-full object-cover opacity-50 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-primary/10 z-10"><i class='bx bx-upload text-5xl text-white drop-shadow-lg'></i></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent flex items-end p-8 pointer-events-none z-10"><span class="font-bold tracking-[0.2em] uppercase text-sm" data-i18n="after">GÜNCEL FORM</span></div>
                            <input type="file" id="uploadAfter" class="hidden" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($view == 'analysis'): ?>
            <div class="animate-fadeIn max-w-7xl mx-auto pb-10">
                <div class="flex justify-between items-center mb-8 px-2">
                    <p class="text-secondary text-sm font-light"><span data-i18n="map_hint1">Tamamlanan hareketler haritayı</span> <span class="text-primary font-bold" data-i18n="map_hint2">otomatik aydınlatır</span>.</p>
                    
                    <div class="flex items-center gap-3">
                        <button onclick="resetDailyData()" class="bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 px-4 py-2 rounded-xl text-xs font-bold transition border border-white/10 uppercase tracking-wider" data-i18n="reset">Sıfırla</button>
                        <button onclick="downloadPDF()" class="btn-neon px-5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 uppercase tracking-wider">
                            <i class='bx bxs-file-pdf text-lg'></i> <span data-i18n="dl_report">Rapor İndir</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                    <div class="glass-card p-6 md:p-8 rounded-[2.5rem] flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-heading text-xl md:text-2xl font-light flex items-center gap-2"><i class='bx bx-radar text-primary'></i> <span data-i18n="power_radar">Güç Radarı</span></h3>
                                <p class="text-secondary opacity-70 text-xs mt-1" data-i18n="radar_desc">Günün antrenman eforunu izle.</p>
                            </div>
                        </div>
                        <div class="flex-1 w-full flex items-center justify-center min-h-[400px] md:min-h-[450px] relative">
                            <canvas id="powerRadarChart" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <div class="glass-card p-6 md:p-8 rounded-[2.5rem] flex flex-col relative">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-heading text-xl md:text-2xl font-light flex items-center gap-2"><i class='bx bx-scan text-primary'></i> <span data-i18n="heat_map">Isı Haritası</span></h3>
                                <p class="text-secondary opacity-70 text-xs mt-1" data-i18n="map_desc">Renkli = Hedeflenen | Gri = Dinlenmiş</p>
                            </div>
                        </div>
                        
                        <div class="flex-1 w-full flex items-center justify-center bg-black/20 rounded-2xl border border-white/5 relative py-8 overflow-visible min-h-[400px] md:min-h-[450px] shadow-inner">
                            <svg viewBox="0 0 100 200" xmlns="http://www.w3.org/2000/svg" class="w-auto h-[350px] md:h-[400px] scale-125 md:scale-150 transform drop-shadow-lg z-10 transition-transform duration-700" id="muscleHeatmap">
                                <polygon points="45,10 55,10 60,25 50,35 40,25" fill="rgba(255,255,255,0.01)" stroke="#333" stroke-width="1"/>
                                <polygon id="m-shoulder-l" class="muscle-polygon" points="25,35 40,35 30,55 15,45" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sol Omuz')" onmouseout="hideTooltip()"/>
                                <polygon id="m-shoulder-r" class="muscle-polygon" points="60,35 75,35 85,45 70,55" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sağ Omuz')" onmouseout="hideTooltip()"/>
                                <polygon id="m-chest" class="muscle-polygon" points="40,35 60,35 65,55 50,65 35,55" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Göğüs')" onmouseout="hideTooltip()"/>
                                <polygon id="m-back-l" class="muscle-polygon" points="25,55 35,55 35,80 20,70" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sol Kanat/Sırt')" onmouseout="hideTooltip()"/>
                                <polygon id="m-back-r" class="muscle-polygon" points="65,55 75,55 80,70 65,80" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sağ Kanat/Sırt')" onmouseout="hideTooltip()"/>
                                <polygon id="m-abs" class="muscle-polygon" points="35,65 65,65 60,100 40,100" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Karın (Core)')" onmouseout="hideTooltip()"/>
                                <polygon id="m-arm-l" class="muscle-polygon" points="15,45 30,55 20,90 10,80" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sol Kol')" onmouseout="hideTooltip()"/>
                                <polygon id="m-arm-r" class="muscle-polygon" points="85,45 70,55 80,90 90,80" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sağ Kol')" onmouseout="hideTooltip()"/>
                                <polygon id="m-leg-l" class="muscle-polygon" points="35,105 48,105 40,180 25,180" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sol Bacak')" onmouseout="hideTooltip()"/>
                                <polygon id="m-leg-r" class="muscle-polygon" points="65,105 52,105 60,180 75,180" fill="rgba(255,255,255,0.05)" stroke="#555" stroke-width="1" onmouseover="showTooltip(event, 'Sağ Bacak')" onmouseout="hideTooltip()"/>
                            </svg>
                        </div>
                        <div id="muscleTooltip" class="absolute bg-dark/95 backdrop-blur-md border border-primary/50 px-4 py-2 rounded-lg text-white text-xs font-bold opacity-0 shadow-xl pointer-events-none transform -translate-x-1/2 -translate-y-full tracking-wider">
                            <span id="ttName" class="text-primary block text-sm">Kas</span>
                            <span id="ttStatus" class="text-secondary opacity-70 font-light">Durum</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="animate-fadeIn">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
                    <div class="xl:col-span-2 glass-card p-8 md:p-10 rounded-[2.5rem] overflow-hidden relative group h-full flex flex-col justify-center">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity duration-700"><i class='bx bxs-bot text-8xl text-primary'></i></div>
                        <div class="relative z-10">
                            <h3 class="font-heading text-2xl md:text-3xl font-light flex items-center gap-4 mb-2"><span class="flex h-3 w-3 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-50"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span></span><span data-i18n="ai_title">GYM PRO AI Asistan</span></h3>
                            <p class="text-secondary opacity-80 text-sm mb-8 font-light tracking-wide" data-i18n="ai_desc">Öğünlerini gir, makro değerlerini anında analiz edelim.</p>
                            <div class="flex flex-col md:flex-row gap-4">
                                <input type="text" id="aiQuery" placeholder="Örn: 1 porsiyon tavuk, 2 tabak pilav..." class="glass-input flex-1 p-5 rounded-2xl font-light text-sm md:text-base tracking-wide">
                                <button onclick="askAIAsistan()" id="askBtn" class="btn-neon px-10 py-5 rounded-2xl font-bold flex items-center justify-center gap-2 tracking-widest"><i class='bx bxs-zap'></i> <span data-i18n="analyze">ANALİZ ET</span></button>
                            </div>
                            <div id="aiResponse" class="hidden mt-8 p-6 md:p-8 rounded-3xl bg-black/40 border border-white/5 animate-fadeIn shadow-inner">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="text-center p-4 bg-white/5 rounded-2xl border border-white/5"><p class="text-[10px] text-secondary uppercase font-bold tracking-widest mb-1" data-i18n="cal">🔥 Kalori</p><p id="resCal" class="text-xl md:text-2xl font-light">0</p></div>
                                    <div class="text-center p-4 bg-primary/10 rounded-2xl border border-primary/30"><p class="text-[10px] text-primary uppercase font-bold tracking-widest mb-1" data-i18n="pro">🥩 Protein</p><p id="resProt" class="text-xl md:text-2xl font-light">0g</p></div>
                                    <div class="text-center p-4 bg-white/5 rounded-2xl border border-white/5"><p class="text-[10px] text-secondary uppercase font-bold tracking-widest mb-1" data-i18n="carb">🍚 Karb</p><p id="resCarb" class="text-xl md:text-2xl font-light">0g</p></div>
                                    <div class="text-center p-4 bg-white/5 rounded-2xl border border-white/5"><p class="text-[10px] text-secondary uppercase font-bold tracking-widest mb-1" data-i18n="fat">🥑 Yağ</p><p id="resFat" class="text-xl md:text-2xl font-light">0g</p></div>
                                </div>
                                <p id="aiNote" class="mt-6 text-sm font-light text-center tracking-wide text-primary"></p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 md:p-10 rounded-[2.5rem] relative overflow-hidden group h-full flex flex-col justify-center">
                        <div class="absolute -right-5 -bottom-5 opacity-5 group-hover:opacity-10 transition-opacity duration-700"><i class='bx bx-droplet text-[8rem] md:text-[12rem] text-blue-400'></i></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-end mb-8 gap-2">
                                <div><h3 class="font-heading text-xl md:text-2xl font-light flex items-center gap-2"><i class='bx bxs-drop text-blue-400/70'></i> <span data-i18n="water">Su Tüketimi</span></h3></div>
                                <div class="text-right">
                                    <span id="waterLitersDisplay" class="text-3xl md:text-5xl font-light leading-none"><?= number_format($current_liters, 2) ?></span>
                                    <span class="text-secondary font-bold uppercase tracking-widest text-xs">/ <?= $target_liters ?>L</span>
                                </div>
                            </div>
                            <div class="w-full h-3 md:h-4 bg-black/40 rounded-full overflow-hidden mb-8 border border-white/5">
                                <div id="waterProgressBar" class="h-full bg-gradient-to-r from-blue-900/50 via-blue-500/50 to-blue-400/80 rounded-full transition-all duration-1000 ease-out" style="width: <?= $progress_percent ?>%"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <button onclick="addWater(0.25)" class="bg-white/5 hover:bg-blue-500/10 border border-white/5 hover:border-blue-500/30 p-3 rounded-xl transition-all"><p class="text-xs font-bold tracking-wider">+250ml</p></button>
                                <button onclick="addWater(0.5)" class="bg-white/5 hover:bg-blue-500/10 border border-white/5 hover:border-blue-500/30 p-3 rounded-xl transition-all"><p class="text-xs font-bold tracking-wider">+500ml</p></button>
                                <button onclick="resetWater()" class="bg-white/5 hover:bg-red-500/20 border border-white/5 hover:border-red-500/50 p-3 rounded-xl transition-all hover:text-red-400"><i class='bx bx-refresh text-xl'></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-8 md:p-10 rounded-[2.5rem] mb-8">
                     <div class="grid grid-cols-3 gap-4 md:gap-8 items-center divide-x divide-white/10 text-center md:text-left">
                         <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-4 group py-2 md:py-0 md:pl-6"><div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center text-primary"><i class='bx bx-body text-2xl md:text-3xl'></i></div><div><p class="text-[10px] md:text-xs text-secondary uppercase font-bold tracking-widest mb-1" data-i18n="height">Boy</p><p class="text-xl md:text-4xl font-light"><?= $user['height'] ?> <span class="text-sm md:text-lg opacity-50 font-normal">cm</span></p></div></div>
                         <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-4 group py-2 md:py-0 md:pl-6"><div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-white/5 border border-white/5 flex items-center justify-center text-primary"><i class='bx bx-dumbbell text-2xl md:text-3xl'></i></div><div><p class="text-[10px] md:text-xs text-secondary uppercase font-bold tracking-widest mb-1" data-i18n="weight">Kilo</p><p class="text-xl md:text-4xl font-light"><?= $user['weight'] ?> <span class="text-sm md:text-lg opacity-50 font-normal">kg</span></p></div></div>
                         <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-4 group py-2 md:py-0 md:pl-6"><div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary"><i class='bx bxs-hot text-2xl md:text-3xl'></i></div><div><p class="text-[10px] md:text-xs text-primary uppercase font-bold tracking-widest mb-1" data-i18n="fat_ratio">Yağ %</p><p class="text-xl md:text-4xl font-light">%<?= $user['fat_ratio'] ?></p></div></div>
                     </div>
                </div>

                <div class="glass-card rounded-[2.5rem] min-h-[400px] md:min-h-[600px] flex flex-col relative overflow-hidden">
                    <div class="p-8 md:p-10 pb-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div><h3 class="font-heading text-2xl md:text-3xl font-light flex items-center gap-3"><i class='bx bx-notepad text-primary'></i> <span data-i18n="my_program">Programım</span></h3><p class="text-secondary opacity-70 text-sm mt-2 font-light tracking-wide"><span data-i18n="prog_hint">Sadece BUGÜNÜN hareketlerini işaretleyebilirsin.</span> (<?= $days[$todayIndex] ?>)</p></div>
                        <?php if($has_program): ?><div class="flex items-center gap-3 bg-primary/10 border border-primary/30 px-4 py-2 rounded-xl mt-4 md:mt-0"><span class="relative flex h-2 w-2 md:h-3 md:w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 md:h-3 md:w-3 bg-primary"></span></span><span class="text-xs font-bold text-primary uppercase tracking-[0.2em]" data-i18n="active">Aktif</span></div><?php endif; ?>
                    </div>
                    <?php if ($has_program): ?>
                        <div class="mt-8 px-6 md:px-10 flex space-x-3 overflow-x-auto no-scrollbar border-b border-white/5 pb-0">
                            <?php foreach ($days as $index => $day): ?>
                                <button onclick="switchDay('<?= $day ?>', this)" class="day-tab whitespace-nowrap px-6 py-4 rounded-t-2xl font-bold text-xs tracking-widest uppercase transition-all border-b-2 <?= ($index == $todayIndex) ? 'bg-black/30 text-primary border-primary' : 'opacity-60 border-transparent hover:opacity-100' ?>">
                                    <?= mb_substr($day,0,3) ?> <?= ($index == $todayIndex) ? '✦' : '' ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div id="programContent" class="flex-1 p-6 md:p-10 bg-black/20">
                            <?php foreach ($days as $index => $day): 
                                $raw_exercises = explode("\n", trim($program_data[$day] ?? ''));
                                $exercises = [];
                                foreach($raw_exercises as $r_ex) {
                                    $clean_ex = trim($r_ex);
                                    if(!empty($clean_ex)) { $exercises[] = $clean_ex; }
                                }
                                $isToday = ($index == $todayIndex); 
                            ?>
                                <div id="day-<?= $day ?>" class="day-content <?= (!$isToday) ? 'hidden' : '' ?> animate-fadeIn">
                                    <?php if (!empty($exercises)): ?>
                                        <div class="grid grid-cols-1 gap-4">
                                            <?php foreach($exercises as $k => $ex): ?>
                                                <div class="group bg-white/[0.02] <?= $isToday ? 'hover:bg-white/[0.05]' : 'opacity-40 grayscale' ?> border border-white/5 p-5 md:p-6 rounded-2xl transition-all duration-300 flex items-center justify-between">
                                                    <div class="flex items-center gap-5"><div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-black/50 border border-white/5 flex items-center justify-center font-light text-sm md:text-base text-secondary <?= $isToday ? 'group-hover:text-primary group-hover:border-primary/30 transition-colors' : '' ?>"><?= $k + 1 ?></div><h5 class="font-light text-base md:text-lg tracking-wide <?= $isToday ? 'group-hover:text-primary' : '' ?> transition-colors leading-tight"><?= htmlspecialchars($ex) ?></h5></div>
                                                    
                                                    <?php if($isToday): ?>
                                                        <button type="button" 
                                                                class="ex-btn shrink-0 px-6 py-3 rounded-xl text-xs font-bold tracking-widest uppercase transition-all border border-white/10 bg-black/50 hover:bg-primary/10 hover:border-primary/50"
                                                                onclick="toggleExercise(this, '<?= addslashes(htmlspecialchars($ex)) ?>')">
                                                            <span data-i18n="done_btn">YAPTIM</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" disabled class="shrink-0 px-6 py-3 rounded-xl text-xs font-bold tracking-widest uppercase border border-transparent bg-transparent opacity-50 cursor-not-allowed flex items-center gap-2">
                                                            <i class='bx bx-lock-alt'></i> <span data-i18n="locked">KİLİTLİ</span>
                                                        </button>
                                                    <?php endif; ?>

                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex flex-col items-center justify-center py-20 text-center"><div class="w-32 h-32 bg-white/5 rounded-full flex items-center justify-center mb-6"><i class='bx bx-spa text-6xl text-secondary opacity-50'></i></div><h4 class="text-2xl font-light text-secondary tracking-widest uppercase" data-i18n="rest_day">Dinlenme Günü</h4></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <nav class="md:hidden fixed bottom-0 left-0 w-full glass-card bg-dark/95 backdrop-blur-xl border-t border-white/5 z-50 px-4 py-2 flex justify-between items-center pb-safe">
        <a href="?view=dashboard" class="flex flex-col items-center gap-1 p-2 <?= $view == 'dashboard' ? 'text-primary' : 'text-secondary/50 hover:text-secondary' ?> transition-colors"><i class='bx bxs-dashboard text-xl'></i><span class="text-[9px] font-bold tracking-widest uppercase" data-i18n="nav_dash">Panel</span></a>
        <a href="?view=analysis" class="flex flex-col items-center gap-1 p-2 <?= $view == 'analysis' ? 'text-primary' : 'text-secondary/50 hover:text-secondary' ?> transition-colors"><i class='bx bx-scan text-xl'></i><span class="text-[9px] font-bold tracking-widest uppercase" data-i18n="nav_analysis">Analiz</span></a>
        <div class="relative -top-6"><button onclick="openCalculator()" class="w-16 h-16 rounded-full btn-neon flex items-center justify-center text-dark shadow-[0_0_20px_var(--primary)] border-4 border-dark hover:scale-105 transition-transform"><i class='bx bx-plus text-3xl font-black'></i></button></div>
        <a href="/gym-pro/public/profile" class="flex flex-col items-center gap-1 p-2 text-secondary/50 hover:text-secondary transition-colors"><i class='bx bxs-user-circle text-xl'></i><span class="text-[9px] font-bold tracking-widest uppercase" data-i18n="nav_profile">Profil</span></a>
        <a href="?view=messages" class="relative flex flex-col items-center gap-1 p-2 <?= $view == 'messages' ? 'text-primary' : 'text-secondary/50 hover:text-secondary' ?> transition-colors"><i class='bx bxs-chat text-xl'></i><span class="text-[9px] font-bold tracking-widest uppercase" data-i18n="nav_msg">Mesaj</span></a>
    </nav>

    <div id="calcModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/90 backdrop-blur-md">
        <div class="relative w-full max-w-md mx-4 glass-card p-8 md:p-10 rounded-[2.5rem] border border-primary/20 animate-fadeIn">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-light tracking-wide" data-i18n="nav_body">Vücut Analizi</h3>
                <button onclick="closeCalculator()" class="opacity-50 hover:text-primary transition"><i class='bx bx-x text-3xl'></i></button>
            </div>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-5">
                    <div><label class="text-xs text-secondary mb-2 block font-bold tracking-widest uppercase" data-i18n="weight">Kilo</label><input type="number" id="cWeight" class="glass-input w-full p-4 rounded-xl font-light text-xl" value="<?= $user['weight'] ?>"></div>
                    <div><label class="text-xs text-secondary mb-2 block font-bold tracking-widest uppercase" data-i18n="height">Boy (cm)</label><input type="number" id="cHeight" class="glass-input w-full p-4 rounded-xl font-light text-xl" value="<?= $user['height'] ?>"></div>
                </div>
                <button onclick="calculateBMI()" class="btn-neon w-full py-5 rounded-xl font-bold tracking-widest text-sm uppercase mt-2 shadow-lg"><span data-i18n="start_analysis">Analizi Başlat</span></button>
            </div>
            <div id="bmiResultArea" class="hidden mt-8 p-6 md:p-8 rounded-3xl bg-black/40 border border-white/5 animate-fadeIn shadow-inner">
                <div class="text-center mb-6">
                    <p class="text-secondary opacity-70 text-[10px] uppercase tracking-[0.3em] font-bold mb-2">BMI</p>
                    <p id="bmiValueDisplay" class="text-5xl md:text-6xl font-light text-primary mb-3">0.0</p>
                    <span id="bmiStatusBadge" class="px-5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.2em] inline-block border"></span>
                </div>
                <div class="grid grid-cols-2 gap-4 border-t border-white/5 pt-6 mt-2">
                    <div class="text-center"><p class="text-[10px] text-secondary opacity-70 uppercase font-bold tracking-widest" data-i18n="ideal_w">İdeal Kilo</p><p id="bmiIdealWeight" class="text-xl font-light mt-1">-</p></div>
                    <div class="text-center"><p class="text-[10px] text-secondary opacity-70 uppercase font-bold tracking-widest" data-i18n="diff">Fark</p><p id="bmiDifference" class="text-xl font-light mt-1">-</p></div>
                </div>
                <p id="bmiAdvice" class="text-xs opacity-50 text-center mt-8 font-light tracking-wide"></p>
            </div>
        </div>
    </div>

    <div style="display:none;">
        <div id="pdfTemplate" class="p-12" style="background-color: var(--dark); color: var(--text-main); font-family: 'Outfit', sans-serif; width: 800px; border: 8px solid var(--panel);">
            <div class="flex justify-between items-center border-b pb-8 mb-10" style="border-color: var(--primary);">
                <div>
                    <h1 class="text-4xl font-black tracking-widest" style="color: var(--primary);">GYM PRO</h1>
                    <p class="text-xs tracking-[0.3em] uppercase mt-1" style="color: var(--text-muted);" data-i18n="pdf_title">Kişisel Gelişim Raporu</p>
                </div>
                <div class="text-right">
                    <p class="font-light text-xl tracking-wide"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="text-xs mt-1 tracking-widest" style="color: var(--text-muted);"><?= date('d.m.Y H:i') ?></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mb-12">
                <div class="p-6 rounded-2xl border text-center" style="background: var(--panel); border-color: var(--glass-border);">
                    <p class="text-[10px] uppercase font-bold tracking-widest" style="color: var(--secondary);" data-i18n="height">Boy</p>
                    <p class="text-2xl font-light mt-2"><?= $user['height'] ?> cm</p>
                </div>
                <div class="p-6 rounded-2xl border text-center" style="background: var(--panel); border-color: var(--glass-border);">
                    <p class="text-[10px] uppercase font-bold tracking-widest" style="color: var(--secondary);" data-i18n="weight">Kilo</p>
                    <p class="text-2xl font-light mt-2"><?= $user['weight'] ?> kg</p>
                </div>
                <div class="p-6 rounded-2xl border text-center" style="background: var(--panel); border-color: var(--glass-border);">
                    <p class="text-[10px] uppercase font-bold tracking-widest" style="color: var(--secondary);" data-i18n="fat_ratio">Yağ Oranı</p>
                    <p class="text-2xl font-bold mt-2" style="color: var(--primary);">%<?= $user['fat_ratio'] ?></p>
                </div>
            </div>

            <div class="flex gap-10 mb-12">
                <div class="flex-1">
                    <h3 class="text-lg font-light mb-6 flex items-center gap-2 tracking-wide" data-i18n="map_targets">Kas Hedefleri</h3>
                    <div id="pdfMuscleMap" class="rounded-3xl p-8 border flex items-center justify-center" style="background: var(--panel); border-color: var(--glass-border);"></div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-light mb-6 flex items-center gap-2 tracking-wide" data-i18n="power_radar">Güç Analizi</h3>
                    <div id="pdfRadarMap" class="rounded-3xl p-8 border flex items-center justify-center" style="background: var(--panel); border-color: var(--glass-border);">
                        <img id="radarImage" src="" style="max-width: 100%;">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-light mb-6 tracking-wide" data-i18n="workout_summary">Günün Antrenman Özeti</h3>
                <div id="pdfExercises" class="space-y-3"></div>
            </div>

            <div class="mt-24 pt-8 border-t text-center" style="border-color: var(--glass-border);">
                <p class="text-[10px] font-bold tracking-[0.3em] uppercase" style="color: var(--text-muted);">GYM PRO Management System</p>
            </div>
        </div>
    </div>

<script>
    const dict = {
        tr: {
            app_desc: "Yönetim Sistemi", nav_dash: "Panelim", nav_stat: "Genel İstatistikler", nav_analysis: "Analiz", nav_map: "Kas & Güç Haritası", nav_gallery: "Gelişimim", nav_diary: "Fotoğraf Günlüğü", nav_calc: "Hesaplayıcı", nav_body: "Vücut Analizi", nav_profile: "Profilim", profile_edit: "Profil Düzenle", nav_msg: "Mesajlarım", nav_contact: "İletişim", logout: "Çıkış Yap", summary: "Özet", details: "Detaylar", welcome: "Hoş Geldin,", no_msg: "Henüz bir mesajın bulunmuyor.", new: "YENİ", mark_read: "Okundu İşaretle", visual_diary: "Görsel Günlük", diary_desc: "Fiziksel dönüşümünü galerinde takip et. 📸", before: "İLK HALİ", after: "GÜNCEL FORM", map_hint1: "Tamamlanan hareketler haritayı", map_hint2: "otomatik aydınlatır", reset: "Sıfırla", dl_report: "Rapor İndir", power_radar: "Güç Radarı", radar_desc: "Günün antrenman eforunu izle.", heat_map: "Isı Haritası", map_desc: "Renkli = Hedeflenen | Gri = Dinlenmiş", ai_title: "GYM PRO AI Asistan", ai_desc: "Öğünlerini gir, makro değerlerini anında analiz edelim.", analyze: "ANALİZ ET", cal: "🔥 Kalori", pro: "🥩 Protein", carb: "🍚 Karb", fat: "🥑 Yağ", water: "Su Tüketimi", height: "Boy", weight: "Kilo", fat_ratio: "Yağ %", my_program: "Programım", prog_hint: "Sadece BUGÜNÜN hareketlerini işaretleyebilirsin.", active: "AKTİF", done_btn: "YAPTIM", locked: "KİLİTLİ", rest_day: "Dinlenme Günü", start_analysis: "Analizi Başlat", ideal_w: "İdeal Kilo", diff: "Fark", pdf_title: "Kişisel Gelişim Raporu", map_targets: "Kas Hedefleri", workout_summary: "Günün Antrenman Özeti"
        },
        en: {
            app_desc: "Management System", nav_dash: "Dashboard", nav_stat: "General Stats", nav_analysis: "Analysis", nav_map: "Muscle & Power Map", nav_gallery: "My Progress", nav_diary: "Photo Diary", nav_calc: "Calculator", nav_body: "Body Analysis", nav_profile: "My Profile", profile_edit: "Edit Profile", nav_msg: "Messages", nav_contact: "Contact", logout: "Logout", summary: "Summary", details: "Details", welcome: "Welcome,", no_msg: "You don't have any messages yet.", new: "NEW", mark_read: "Mark as Read", visual_diary: "Visual Diary", diary_desc: "Track your physical transformation. 📸", before: "BEFORE", after: "CURRENT", map_hint1: "Completed exercises will", map_hint2: "automatically light up", reset: "Reset", dl_report: "Download PDF", power_radar: "Power Radar", radar_desc: "Track today's workout effort.", heat_map: "Heat Map", map_desc: "Colored = Targeted | Gray = Rested", ai_title: "GYM PRO AI Assistant", ai_desc: "Enter your meals, we analyze macros instantly.", analyze: "ANALYZE", cal: "🔥 Calories", pro: "🥩 Protein", carb: "🍚 Carbs", fat: "🥑 Fat", water: "Water Intake", height: "Height", weight: "Weight", fat_ratio: "Fat %", my_program: "My Program", prog_hint: "You can only mark TODAY'S exercises.", active: "ACTIVE", done_btn: "DONE", locked: "LOCKED", rest_day: "Rest Day", start_analysis: "Start Analysis", ideal_w: "Ideal Weight", diff: "Difference", pdf_title: "Personal Progress Report", map_targets: "Muscle Targets", workout_summary: "Today's Workout Summary"
        }
    };

    function changeLang(lang) {
        localStorage.setItem('gym_lang', lang);
        document.querySelectorAll('[data-i18n]').forEach(el => {
            let key = el.getAttribute('data-i18n');
            if(dict[lang] && dict[lang][key]) { el.innerText = dict[lang][key]; }
        });
        document.getElementById('langSelector').value = lang;
    }

    function changeTheme(themeName) {
        localStorage.setItem('gym_theme', themeName);
        document.documentElement.className = themeName;
        document.getElementById('themeSelector').value = themeName;
        
        setTimeout(() => { if(myRadarChart) { initPowerRadar(); } }, 100); 
    }

    let savedLang = localStorage.getItem('gym_lang') || 'tr';
    let savedTheme = localStorage.getItem('gym_theme') || 'theme-classic'; 
    changeTheme(savedTheme);
    changeLang(savedLang);

    const userId = "<?php echo $user_id; ?>";
    const todayDateStr = new Date().toISOString().split('T')[0];
    const storageKey = `gym_state_${userId}_${todayDateStr}`;
    
    let gymState = JSON.parse(localStorage.getItem(storageKey));
    if (!gymState || !Array.isArray(gymState.checked) || !Array.isArray(gymState.radar) || gymState.radar.length !== 6) {
        gymState = { checked: [], pumped: [], radar: [20, 20, 20, 20, 20, 20] };
    }

    let myRadarChart;

    function analyzeMuscle(exName) {
        let str = exName.toLowerCase().replace(/[^a-z0-9ğüşıöç ]/g, "");
        if (str.includes('leg ext') || str.includes('leg curl') || str.includes('calf') || str.includes('kalf')) return { id: 'leg', radarIndex: 2 };
        if (str.includes('hammer curl') || str.includes('bicep') || str.includes('tricep') || str.includes('pushdown')) return { id: 'arm', radarIndex: 4 };
        if (str.includes('lat pull') || str.includes('seated row') || str.includes('dumbell row') || str.includes('barbell row')) return { id: 'back', radarIndex: 1 };
        if (/(göğüs|gogus|chest|bench|incline|decline|fly|pec|pushup|şınav|sinav|dips|pullover|crossover|press)/.test(str) && !/(leg|shoulder|omuz)/.test(str)) return { id: 'chest', radarIndex: 0 };
        if (/(sırt|sirt|back|row|lat|pull|barfiks|deadlift|kanat|kürek|kurek|chin up)/.test(str)) return { id: 'back', radarIndex: 1 };
        if (/(omuz|shoulder|lateral|overhead|deltoid|front|raise|military|upright)/.test(str)) return { id: 'shoulder', radarIndex: 3 };
        if (/(bacak|leg|squat|lunge|quad|hamstring|glute|hip|calf)/.test(str)) return { id: 'leg', radarIndex: 2 };
        if (/(kol|arm|curl|extension|french|kickback|preacher)/.test(str)) return { id: 'arm', radarIndex: 4 };
        if (/(karın|karin|mekik|crunch|plank|abs|core|situp|twist|russian)/.test(str)) return { id: 'abs', radarIndex: 5 };
        return null;
    }

    function initPowerRadar() {
        const ctx = document.getElementById('powerRadarChart');
        if(!ctx) return;
        
        let rootStyle = getComputedStyle(document.documentElement);
        let cPrimary = rootStyle.getPropertyValue('--primary').trim();
        let cSecondary = rootStyle.getPropertyValue('--secondary').trim();
        
        if(myRadarChart) myRadarChart.destroy(); 
        
        myRadarChart = new Chart(ctx, {
            type: 'radar',
            data: { labels: ['Göğüs', 'Sırt', 'Bacak', 'Omuz', 'Kol', 'Karın'], datasets: [{ label: 'Score', data: gymState.radar, backgroundColor: cPrimary+'22', borderColor: cPrimary, borderWidth: 1.5, pointBackgroundColor: cSecondary, pointBorderColor: '#000', pointHoverBackgroundColor: cPrimary, pointHoverBorderColor: '#fff', pointRadius: 3, }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { r: { angleLines: { color: 'rgba(255, 255, 255, 0.05)' }, grid: { color: 'rgba(255, 255, 255, 0.05)', circular: true }, pointLabels: { color: cSecondary, font: { size: 11, family: 'Outfit', weight: 'bold' } }, ticks: { display: false, max: 100, min: 0, stepSize: 20 } } }, plugins: { legend: { display: false } } }
        });
    }

    function updateButtonUI(btn, isDone) {
        let lang = localStorage.getItem('gym_lang') || 'tr';
        let txtDone = lang === 'en' ? "DONE" : "YAPILDI";
        let txtDo = lang === 'en' ? "MARK DONE" : "YAPTIM";
        
        if(isDone) {
            btn.innerHTML = `<i class='bx bx-check-double text-lg'></i> ${txtDone}`;
            btn.className = "ex-btn shrink-0 px-6 py-3 rounded-xl text-xs font-bold tracking-widest uppercase transition-all border border-primary-var bg-primary/10 text-primary-var flex items-center gap-2 shadow-[0_0_15px_var(--primary)]";
        } else {
            btn.innerHTML = txtDo;
            btn.className = "ex-btn shrink-0 px-6 py-3 rounded-xl text-xs font-bold tracking-widest uppercase transition-all border border-white/10 bg-black/50 hover:bg-white/5 text-slate-400 hover:border-primary-var hover:text-primary-var flex items-center gap-2";
        }
    }

    function syncAllMuscles() {
        const allMuscles = ['chest', 'back', 'leg', 'shoulder', 'arm', 'abs'];
        allMuscles.forEach(m => {
            let isPumped = gymState.pumped.includes(m);
            let elements = [];
            if(m === 'chest' || m === 'abs') elements.push(document.getElementById('m-' + m));
            else { elements.push(document.getElementById('m-' + m + '-l')); elements.push(document.getElementById('m-' + m + '-r')); }
            
            elements.forEach(el => {
                if(el) {
                    if(isPumped) el.classList.add('muscle-pumped');
                    else el.classList.remove('muscle-pumped');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initPowerRadar();
        syncAllMuscles();

        document.querySelectorAll('.ex-btn').forEach(btn => {
            let clickAttr = btn.getAttribute('onclick');
            if(clickAttr) {
                let match = clickAttr.match(/'([^']+)'/);
                if(match && match[1]) {
                    let exName = match[1];
                    if(gymState.checked.includes(exName)) {
                        updateButtonUI(btn, true);
                    }
                }
            }
        });

        const savedBefore = localStorage.getItem('gym_before_pic_' + userId);
        const savedAfter = localStorage.getItem('gym_after_pic_' + userId);
        if(savedBefore && document.getElementById('imgBefore')) { document.getElementById('imgBefore').src = savedBefore; document.getElementById('imgBefore').classList.remove('opacity-30'); document.getElementById('imgBefore').classList.add('opacity-100'); }
        if(savedAfter && document.getElementById('imgAfter')) { document.getElementById('imgAfter').src = savedAfter; document.getElementById('imgAfter').classList.remove('opacity-50'); document.getElementById('imgAfter').classList.add('opacity-100'); }
    });

    function toggleExercise(btn, exName) {
        try {
            let isCurrentlyDone = gymState.checked.includes(exName);
            let isDoneNow = !isCurrentlyDone;
            
            if(isDoneNow) { gymState.checked.push(exName); } 
            else { gymState.checked = gymState.checked.filter(e => e !== exName); }

            updateButtonUI(btn, isDoneNow);

            gymState.pumped = [];
            gymState.radar = [20, 20, 20, 20, 20, 20];
            
            gymState.checked.forEach(checkedEx => {
                let analysis = analyzeMuscle(checkedEx);
                if(analysis) {
                    if(!gymState.pumped.includes(analysis.id)) { gymState.pumped.push(analysis.id); }
                    if(gymState.radar[analysis.radarIndex] < 100) { gymState.radar[analysis.radarIndex] += 15; }
                }
            });

            localStorage.setItem(storageKey, JSON.stringify(gymState));

            syncAllMuscles();
            if(myRadarChart) {
                myRadarChart.data.datasets[0].data = gymState.radar;
                myRadarChart.update();
            }
        } catch (error) { console.error("Error:", error); }
    }

    function showTooltip(e, name) {
        const tt = document.getElementById('muscleTooltip'); if(!tt) return;
        document.getElementById('ttName').innerText = name;
        let lang = localStorage.getItem('gym_lang') || 'tr';
        if(e.target.classList.contains('muscle-pumped')) { document.getElementById('ttStatus').innerText = lang === 'en' ? "Active 🔥" : "Aktif 🔥"; document.getElementById('ttStatus').className = "text-primary-var font-light"; } 
        else { document.getElementById('ttStatus').innerText = lang === 'en' ? "Rested" : "Dinlenmiş"; document.getElementById('ttStatus').className = "text-slate-500 font-light"; }
        tt.style.left = e.pageX + 'px'; tt.style.top = (e.pageY - 15) + 'px'; tt.style.opacity = '1';
    }
    function hideTooltip() { const tt = document.getElementById('muscleTooltip'); if(tt) tt.style.opacity = '0'; }
    function resetDailyData() { 
        let lang = localStorage.getItem('gym_lang') || 'tr';
        let msg = lang === 'en' ? "Are you sure you want to reset today's data?" : "Sıfırlamak istediğinize emin misiniz?";
        if(confirm(msg)) { localStorage.removeItem(storageKey); location.reload(); } 
    }

    function downloadPDF() {
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        let lang = localStorage.getItem('gym_lang') || 'tr';
        btn.innerHTML = "<i class='bx bx-loader-alt animate-spin'></i> ...";
        btn.disabled = true;

        const muscleSvg = document.getElementById('muscleHeatmap').cloneNode(true);
        muscleSvg.style.transform = "scale(1)"; 
        document.getElementById('pdfMuscleMap').innerHTML = '';
        document.getElementById('pdfMuscleMap').appendChild(muscleSvg);

        const radarCanvas = document.getElementById('powerRadarChart');
        const radarImg = radarCanvas.toDataURL("image/png");
        document.getElementById('radarImage').src = radarImg;

        const doneExercises = gymState.checked;
        let exHtml = "";
        if(doneExercises.length > 0) {
            doneExercises.forEach(ex => {
                exHtml += `<div class="p-5 rounded-xl border text-sm font-light flex items-center gap-4 tracking-wide" style="background: var(--panel); border-color: var(--glass-border); color: var(--text-main);">
                    <span style="color: var(--primary);">✦</span> ${ex}
                </div>`;
            });
        } else { exHtml = `<p class='text-slate-600 italic text-sm tracking-widest'>${lang==='en'?'No data.':'Veri yok.'}</p>`; }
        document.getElementById('pdfExercises').innerHTML = exHtml;

        const element = document.getElementById('pdfTemplate');
        element.parentElement.style.display = 'block'; 
        
        let rootStyle = getComputedStyle(document.documentElement);
        let bgDark = rootStyle.getPropertyValue('--dark').trim();
        
        const opt = {
            margin:       [20, 0, 20, 0],
            filename:     'GymPro_Rapor.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, backgroundColor: bgDark, useCORS: true, logging: false },
            jsPDF:        { unit: 'px', format: [800, 1100], orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            element.parentElement.style.display = 'none';
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    }

    document.getElementById('uploadBefore')?.addEventListener('change', function(e) { if(e.target.files[0]) { const r = new FileReader(); r.onload = function(ev) { document.getElementById('imgBefore').src = ev.target.result; document.getElementById('imgBefore').classList.remove('opacity-30'); localStorage.setItem('gym_before_pic_' + userId, ev.target.result); }; r.readAsDataURL(e.target.files[0]); } });
    document.getElementById('uploadAfter')?.addEventListener('change', function(e) { if(e.target.files[0]) { const r = new FileReader(); r.onload = function(ev) { document.getElementById('imgAfter').src = ev.target.result; document.getElementById('imgAfter').classList.remove('opacity-50'); localStorage.setItem('gym_after_pic_' + userId, ev.target.result); const b = document.getElementById('imgAfter').parentElement; b.classList.add('animate-pulse'); setTimeout(() => b.classList.remove('animate-pulse'), 1000); }; r.readAsDataURL(e.target.files[0]); } });

    let currentLiters = parseFloat(<?php echo $current_liters; ?>); const targetLiters = 5.0;
    function addWater(amount) { currentLiters += parseFloat(amount); updateWaterUI(); }
    function resetWater() { 
        let lang = localStorage.getItem('gym_lang') || 'tr';
        let msg = lang === 'en' ? "Reset water intake?" : "Sıfırlamak istiyor musun?";
        if(confirm(msg)) { currentLiters = 0; updateWaterUI(); } 
    }
    function updateWaterUI() { const p = Math.min(100, (currentLiters/5.0)*100); if(document.getElementById('waterLitersDisplay')) document.getElementById('waterLitersDisplay').innerText=currentLiters.toFixed(2); if(document.getElementById('waterProgressBar')) document.getElementById('waterProgressBar').style.width=p+'%'; const fd = new FormData(); fd.append('action', 'update_water'); fd.append('amount', currentLiters); fetch(window.location.href, { method: 'POST', body: fd }); }
    
    function askAIAsistan() {
        const query = document.getElementById('aiQuery').value.toLowerCase().trim(); const btn = document.getElementById('askBtn'); const responseDiv = document.getElementById('aiResponse'); if(!query) return;
        btn.innerHTML = "<i class='bx bx-loader-alt animate-spin'></i>..."; btn.disabled = true;
        setTimeout(() => {
            let total = { cal: 0, p: 0, c: 0, f: 0 }; let foundAny = false;
            const db = { 'tavuk':{cal:165,p:31,c:0,f:4}, 'et':{cal:250,p:26,c:0,f:15}, 'pilav':{cal:170,p:3,c:38,f:0.5}, 'makarna':{cal:180,p:6,c:35,f:1}, 'yumurta':{cal:75,p:7,c:1,f:5}, 'ekmek':{cal:70,p:2,c:15,f:1}, 'çorba':{cal:120,p:5,c:15,f:4}, 'peynir':{cal:70,p:6,c:1,f:5}, 'lahmacun':{cal:210,p:9,c:26,f:8}, 'tatlı':{cal:350,p:4,c:50,f:15} };
            const units = { 'tabak':1.5, 'kase':1.0, 'porsiyon':1.2, 'dilim':1.0, 'adet':1.0, 'tane':1.0, 'gram':0.01, 'gr':0.01 };
            Object.keys(db).forEach(food => {
                if(query.includes(food)) {
                    foundAny = true; let multiplier = 1; const numMatch = query.match(new RegExp(`(\\d+)\\s*(tabak|kase|dilim|porsiyon|adet|tane|gram|gr)?\\s*${food}`));
                    if (numMatch) { let sayi = parseInt(numMatch[1]); multiplier = numMatch[2] ? sayi * units[numMatch[2]] : sayi; }
                    if (query.includes('yarım ' + food)) multiplier *= 0.5; if (query.includes('çeyrek ' + food)) multiplier *= 0.25; if (food === 'ekmek' && query.includes('yarım ekmek')) multiplier = 5; if (food === 'ekmek' && query.includes('tam ekmek')) multiplier = 10;
                    total.cal += db[food].cal * multiplier; total.p += db[food].p * multiplier; total.c += db[food].c * multiplier; total.f += db[food].f * multiplier;
                }
            });
            let lang = localStorage.getItem('gym_lang') || 'tr';
            if(foundAny) {
                document.getElementById('resCal').innerText = Math.round(total.cal); document.getElementById('resProt').innerText = Math.round(total.p) + "g"; document.getElementById('resCarb').innerText = Math.round(total.c) + "g"; document.getElementById('resFat').innerText = Math.round(total.f) + "g";
                let note = lang === 'en' ? "GYM PRO AI: Calculated successfully." : "GYM PRO AI: Hesaplandı."; 
                const noteEl = document.getElementById('aiNote'); noteEl.innerText = note; responseDiv.classList.remove('hidden');
            }
            btn.innerHTML = `<i class='bx bxs-zap'></i> ${lang === 'en' ? 'ANALYZE' : 'ANALİZ ET'}`; btn.disabled = false;
        }, 800);
    }

    function switchDay(day, btn) { document.querySelectorAll('.day-content').forEach(el => el.classList.add('hidden')); document.getElementById('day-' + day).classList.remove('hidden'); document.querySelectorAll('.day-tab').forEach(el => { el.classList.remove('bg-black/30', 'text-primary-var', 'border-primary-var'); el.classList.add('text-slate-600', 'border-transparent'); }); btn.classList.add('bg-black/30', 'text-primary-var', 'border-primary-var'); btn.classList.remove('text-slate-600', 'border-transparent'); }
    function openCalculator() { document.getElementById('calcModal').style.display = 'flex'; document.getElementById('bmiResultArea').classList.add('hidden'); }
    function closeCalculator() { document.getElementById('calcModal').style.display = 'none'; }
    function calculateBMI() {
        let w = parseFloat(document.getElementById('cWeight').value); let h = parseFloat(document.getElementById('cHeight').value);
        if(w && h) {
            let lang = localStorage.getItem('gym_lang') || 'tr';
            let hMeters = h / 100; let bmi = w / (hMeters ** 2);
            let idealWeight = 22 * (hMeters ** 2); let diff = w - idealWeight; let diffText = '';
            if(diff > 2) { diffText = Math.abs(diff).toFixed(1) + (lang==='en'?" kg lose":" kg ver"); } else if (diff < -2) { diffText = Math.abs(diff).toFixed(1) + (lang==='en'?" kg gain":" kg al"); } else { diffText = (lang==='en'?"Ideal!":"İdealdesin!"); }
            document.getElementById('bmiValueDisplay').innerText = bmi.toFixed(1); 
            document.getElementById('bmiStatusBadge').innerText = lang==='en'?"CALCULATED":"HESAPLANDI"; 
            document.getElementById('bmiIdealWeight').innerText = idealWeight.toFixed(1) + " kg"; document.getElementById('bmiDifference').innerText = diffText; document.getElementById('bmiResultArea').classList.remove('hidden');
        }
    }
</script>
</body>
</html>