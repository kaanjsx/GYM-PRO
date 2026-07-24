<!DOCTYPE html>
<html lang="tr" class="h-full bg-[#020617]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sporcu Profili - GYM PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { 
                        primary: '#6366f1', // Indigo
                        secondary: '#d946ef', // Fuchsia
                        dark: '#0f172a',
                        surface: 'rgba(30, 41, 59, 0.7)'
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/gym-pro/public/assets/css/style.css">
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .glass-input {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }
    </style>

    <style>
        :root, .theme-classic {
            --primary: #6366f1; --secondary: #a855f7; --dark: #0f172a;
            --panel: rgba(255, 255, 255, 0.02); --text-main: #e2e8f0;
            --text-muted: #94a3b8; --glass-border: rgba(255, 255, 255, 0.05);
        }
        .theme-aggressive {
            --primary: #e11d48; --secondary: #eab308; --dark: #050505;
            --panel: rgba(17, 17, 17, 0.8); --text-main: #f8fafc;
            --text-muted: #94a3b8; --glass-border: rgba(225, 29, 72, 0.1);
        }
        .theme-elite {
            --primary: #cfa87a; --secondary: #9c7a53; --dark: #080808;
            --panel: rgba(18, 18, 18, 0.8); --text-main: #f4efe6;
            --text-muted: #8b7a66; --glass-border: rgba(207, 168, 122, 0.1);
        }
        
        body { background-color: var(--dark) !important; color: var(--text-main) !important; }
        .glass-card { background: var(--panel); border: 1px solid var(--glass-border); }
        .text-primary-var { color: var(--primary); }
    </style>

    <script>
        let savedTheme = localStorage.getItem('gym_theme') || 'theme-classic';
        document.documentElement.className = savedTheme; // Sayfaya temayı giydir

        tailwind.config = {
            theme: {
                extend: {
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
<body class="min-h-screen flex items-start lg:items-center justify-center relative overflow-x-hidden overflow-y-auto lg:overflow-hidden p-4 py-8 md:p-8 text-slate-200">
    
    <div class="fixed top-0 left-0 w-full h-full bg-[#020617] -z-20"></div>
    <div class="fixed top-[-10%] right-[-5%] w-[300px] h-[300px] md:w-[500px] md:h-[500px] bg-primary/20 rounded-full blur-[100px] md:blur-[120px] -z-10 animate-pulse-slow"></div>
    <div class="fixed bottom-[-10%] left-[-5%] w-[300px] h-[300px] md:w-[500px] md:h-[500px] bg-secondary/10 rounded-full blur-[100px] md:blur-[120px] -z-10"></div>

    <?php 
        $user = $data['user'];
        $stats = $data['stats'] ?? [];
        $goal = $data['goal'] ?? [];

        $start = (float)($goal['start_weight'] ?? 0);
        $target = (float)($goal['target_weight'] ?? 0);
        $current = (float)($stats['weight'] ?? 0);
        
        $isGoalSet = $start > 0 && $target > 0;
        $progressPercent = 0;
        
        if ($isGoalSet && $current > 0) {
            $totalDiff = abs($start - $target);
            $currentDiff = abs($start - $current);
            if ($totalDiff > 0) {
                $progressPercent = min(100, max(0, ($currentDiff / $totalDiff) * 100));
            }
        }

        $level = "Çaylak";
        $levelColor = "text-slate-400";
        $badgeIcon = "bx-medal";
        
        if ($progressPercent > 20) { $level = "İstikrarlı"; $levelColor = "text-blue-400"; $badgeIcon = "bx-run"; }
        if ($progressPercent > 50) { $level = "Atlet"; $levelColor = "text-purple-400"; $badgeIcon = "bx-dumbbell"; }
        if ($progressPercent > 80) { $level = "Canavar"; $levelColor = "text-pink-400"; $badgeIcon = "bxs-hot"; }
        if ($progressPercent >= 100) { $level = "Efsane"; $levelColor = "text-yellow-400"; $badgeIcon = "bxs-trophy"; }

        $bmi = 0;
        $heightM = ($user['height'] ?? 0) / 100;
        if ($heightM > 0 && $current > 0) {
            $bmi = $current / ($heightM * $heightM);
        }
    ?>

    <div class="w-full max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-8 lg:h-[90vh] pb-20 lg:pb-0">
        
        <div class="lg:col-span-4 flex flex-col gap-6 lg:h-full lg:overflow-y-auto lg:pr-1 custom-scrollbar">
            
            <div class="glass-panel rounded-3xl p-8 relative text-center group border-t border-white/10">
                <a href="/gym-pro/public/dashboard" class="absolute top-6 left-6 text-slate-500 hover:text-white transition"><i class='bx bx-arrow-back text-2xl'></i></a>
                
                <div class="relative w-32 h-32 md:w-40 md:h-40 mx-auto mb-6 mt-4 md:mt-0">
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary to-secondary rounded-full blur animate-pulse"></div>
                    <img src="/gym-pro/public/assets/uploads/<?= $user['avatar'] ?? 'default.png' ?>" class="relative w-full h-full rounded-full object-cover border-4 border-[#0f172a] shadow-2xl">
                    <div class="absolute bottom-2 right-2 bg-[#0f172a] rounded-full p-2 border border-white/10 shadow-lg">
                        <i class='bx <?= $badgeIcon ?> <?= $levelColor ?> text-xl'></i>
                    </div>
                </div>

                <h2 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight"><?= $user['name'] ?></h2>
                <p class="text-xs md:text-sm font-medium uppercase tracking-widest <?= $levelColor ?> mt-1"><?= $level ?> Sporcu</p>

                <div class="mt-8 grid grid-cols-3 gap-2 md:gap-4 border-t border-white/5 pt-6">
                    <div class="text-center">
                        <span class="block text-xl md:text-2xl font-bold text-white"><?= $user['height'] ?? '--' ?></span>
                        <span class="text-[9px] md:text-[10px] uppercase text-slate-500 font-bold tracking-wider">Boy</span>
                    </div>
                    <div class="text-center border-l border-white/5">
                        <span class="block text-xl md:text-2xl font-bold text-white"><?= $current > 0 ? $current : '--' ?></span>
                        <span class="text-[9px] md:text-[10px] uppercase text-slate-500 font-bold tracking-wider">Kilo</span>
                    </div>
                    <div class="text-center border-l border-white/5">
                        <span class="block text-xl md:text-2xl font-bold text-white"><?= number_format($bmi, 1) ?></span>
                        <span class="text-[9px] md:text-[10px] uppercase text-slate-500 font-bold tracking-wider">BMI</span>
                    </div>
                </div>
            </div>

            <div class="glass-panel rounded-3xl p-6 bg-gradient-to-br from-white/5 to-transparent">
                <h3 class="text-base md:text-lg font-bold text-white mb-4 flex items-center gap-2"><i class='bx bx-line-chart text-primary'></i> Vücut Analizi</h3>
                
                <div class="mb-4">
                    <div class="flex justify-between text-xs md:text-sm mb-1">
                        <span class="text-slate-400">Yağ Oranı</span>
                        <span class="text-white font-bold">%<?= $stats['fat_ratio'] ?? '0' ?></span>
                    </div>
                    <div class="w-full bg-black/40 h-2 rounded-full overflow-hidden">
                        <div class="bg-yellow-500 h-full rounded-full" style="width: <?= min(100, ($stats['fat_ratio'] ?? 0) * 2) ?>%"></div>
                    </div>
                </div>

                <div id="bmiBadgeContainer" class="p-3 rounded-xl bg-white/5 border border-white/5 text-center transition-all duration-300">
                    <span class="text-[10px] md:text-xs text-slate-400 uppercase block mb-1">Kitle İndeksi Durumu</span>
                    <span id="bmiText" class="text-base md:text-lg font-bold text-white">Hesaplanıyor...</span>
                </div>
            </div>

        </div>

        <div class="lg:col-span-8 lg:h-full lg:overflow-y-auto lg:pr-2 custom-scrollbar space-y-6">

            <?php if(isset($_GET['success'])): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-2xl flex items-center gap-3 animate-bounce">
                    <i class='bx bxs-check-shield text-2xl'></i>
                    <div>
                        <h4 class="font-bold">Profil Güncellendi!</h4>
                        <p class="text-xs text-emerald-500/80">Yeni verilerin sisteme işlendi.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" action="/gym-pro/public/profile/update">
                
                <div class="glass-panel p-6 md:p-8 rounded-3xl border border-primary/20 relative overflow-hidden mb-6">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">Fiziksel Veriler</h3>
                            <p class="text-slate-400 text-xs md:text-sm mt-1">Vücut ölçülerini ve hedeflerini buradan güncelle.</p>
                        </div>
                        <div class="hidden md:block">
                            <i class='bx bx-body text-4xl text-primary opacity-50'></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        <div class="space-y-4">
                            <h4 class="text-[10px] md:text-xs font-bold text-primary uppercase tracking-wider mb-2">Mevcut Durum</h4>
                            
                            <div>
                                <label class="text-xs text-slate-400 ml-1">Boy (cm)</label>
                                <div class="relative">
                                    <i class='bx bx-ruler absolute left-4 top-1/2 -translate-y-1/2 text-slate-500'></i>
                                    <input type="number" id="inputHeight" name="height" value="<?= $user['height'] ?? '' ?>" class="glass-input w-full pl-10 p-3 md:p-4 rounded-xl font-bold text-base md:text-lg" placeholder="175" oninput="calculateRealTimeBMI()">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-400 ml-1">Kilo (kg)</label>
                                    <input type="number" step="0.1" id="inputWeight" name="weight" value="<?= $stats['weight'] ?? '' ?>" class="glass-input w-full p-3 md:p-4 rounded-xl font-bold text-base md:text-lg" placeholder="80.5" oninput="calculateRealTimeBMI()">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 ml-1">Yağ (%)</label>
                                    <input type="number" step="0.1" name="fat" value="<?= $stats['fat_ratio'] ?? '' ?>" class="glass-input w-full p-3 md:p-4 rounded-xl font-bold text-base md:text-lg" placeholder="15">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-6 md:pt-0 mt-6 md:mt-0 border-t md:border-t-0 md:border-l border-white/5 md:pl-8">
                            <h4 class="text-[10px] md:text-xs font-bold text-secondary uppercase tracking-wider mb-2">Hedefler</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-400 ml-1">Başlangıç (kg)</label>
                                    <input type="number" step="0.1" name="start_weight" value="<?= $goal['start_weight'] ?? '' ?>" class="glass-input w-full p-3 md:p-4 rounded-xl text-slate-300 text-sm md:text-base" placeholder="90">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 ml-1">Hedef (kg)</label>
                                    <input type="number" step="0.1" name="target_weight" value="<?= $goal['target_weight'] ?? '' ?>" class="glass-input w-full p-3 md:p-4 rounded-xl font-bold text-secondary border-secondary/30 focus:border-secondary text-sm md:text-base" placeholder="75">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 ml-1">Hedef Tarihi</label>
                                <input type="date" name="deadline" value="<?= $goal['deadline'] ?? '' ?>" class="glass-input w-full p-3 md:p-4 rounded-xl text-xs md:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($isGoalSet): ?>
                <div class="glass-panel p-5 md:p-6 rounded-2xl mb-6 bg-gradient-to-r from-indigo-900/20 to-purple-900/20">
                    <div class="flex justify-between items-end mb-3">
                        <span class="text-xs md:text-sm font-bold text-white">Hedef İlerlemesi</span>
                        <span class="text-xl md:text-2xl font-black text-white">%<?= number_format($progressPercent, 1) ?></span>
                    </div>
                    <div class="w-full bg-black/40 h-3 md:h-4 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-primary via-purple-500 to-secondary relative" style="width: <?= $progressPercent ?>%">
                            <div class="absolute top-0 left-0 w-full h-full bg-[url('/assets/pattern.png')] opacity-20"></div>
                            <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-3 text-[10px] md:text-xs text-slate-400">
                        <span>Başladı: <?= $goal['start_weight'] ?>kg</span>
                        <span>Hedef: <?= $goal['target_weight'] ?>kg</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="glass-panel p-6 md:p-8 rounded-3xl">
                    <h3 class="text-xl font-bold text-white mb-6">Kimlik Bilgileri</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 flex flex-col sm:flex-row items-center sm:items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/5 border-dashed hover:border-primary/50 transition cursor-pointer relative text-center sm:text-left">
                            <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-primary shrink-0">
                                <i class='bx bxs-camera'></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1">Profil Fotoğrafını Değiştir</h5>
                                <p class="text-[10px] md:text-xs text-slate-400">JPG, PNG veya WEBP. Max 2MB.</p>
                            </div>
                            <input type="file" name="avatar" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this)">
                        </div>

                        <div>
                            <label class="text-xs text-slate-400 ml-1 font-bold">Ad Soyad</label>
                            <input type="text" name="name" value="<?= $user['name'] ?>" class="glass-input w-full p-3 rounded-xl font-bold text-sm md:text-base" required>
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 ml-1 font-bold">Telefon</label>
                            <input type="text" name="phone" value="<?= $user['phone'] ?? '' ?>" class="glass-input w-full p-3 rounded-xl text-sm md:text-base">
                        </div>
                        
                        <input type="hidden" name="target_fat" value="<?= $goal['target_fat'] ?? 0 ?>">
                    </div>

                    <div class="mt-8 flex justify-center md:justify-end">
                        <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-primary to-secondary hover:brightness-110 text-white font-bold py-4 px-10 rounded-xl shadow-lg shadow-primary/30 transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2">
                            <i class='bx bxs-save text-xl'></i> Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>

            </form>
            
            <div class="h-10"></div> 
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', calculateRealTimeBMI);

        function calculateRealTimeBMI() {
            const h = parseFloat(document.getElementById('inputHeight').value);
            const w = parseFloat(document.getElementById('inputWeight').value);
            const badge = document.getElementById('bmiBadgeContainer');
            const text = document.getElementById('bmiText');

            if (h > 50 && w > 30) {
                const heightInMeters = h / 100;
                const bmi = w / (heightInMeters * heightInMeters);
                
                let status = "";
                let colorClass = "";
                
                if (bmi < 18.5) { 
                    status = "Zayıf"; 
                    colorClass = "bg-yellow-500/20 text-yellow-400 border-yellow-500/30"; 
                } else if (bmi < 25) { 
                    status = "İdeal Kilo ✨"; 
                    colorClass = "bg-emerald-500/20 text-emerald-400 border-emerald-500/30"; 
                } else if (bmi < 30) { 
                    status = "Hafif Kilolu"; 
                    colorClass = "bg-orange-500/20 text-orange-400 border-orange-500/30"; 
                } else { 
                    status = "Obezite"; 
                    colorClass = "bg-red-500/20 text-red-400 border-red-500/30"; 
                }

                text.innerHTML = bmi.toFixed(1) + " - <span class='text-xs md:text-sm opacity-80'>" + status + "</span>";
                badge.className = "p-3 rounded-xl text-center transition-all duration-300 border " + colorClass;
            } else {
                text.innerText = "--";
                badge.className = "p-3 rounded-xl bg-white/5 border border-white/5 text-center";
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Soldaki büyük profil fotoğrafını anında değiştirir
                    document.querySelector('.w-32.h-32 img, .w-40.h-40 img').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>