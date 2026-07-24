<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap | Gym Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { primary: '#6366f1', secondary: '#a855f7', dark: '#0f172a' }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-input { background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); color: white; transition: 0.3s; }
        .glass-input:focus { outline: none; border-color: #6366f1; background: rgba(0, 0, 0, 0.4); }
    </style>
</head>
<body class="h-screen flex items-center justify-center relative overflow-hidden">

    <div class="absolute top-10 left-10 w-72 h-72 bg-purple-500/20 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>

    <div class="glass p-10 rounded-[2rem] w-full max-w-md relative z-10 shadow-2xl animate-[fadeIn_0.5s_ease-out]">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary mb-2">GYM PRO</h1>
            <p class="text-slate-400 text-sm">Hesabına giriş yap ve antrenmana başla.</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-xl mb-4 text-sm font-bold flex items-center gap-2">
                <i class='bx bx-error-circle'></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/gym-pro/public/auth/login" method="POST" class="space-y-5">
            <div>
                <label class="text-slate-400 text-xs font-bold ml-1 mb-1 block">E-POSTA</label>
                <div class="relative">
                    <i class='bx bx-envelope absolute left-4 top-3.5 text-slate-500 text-xl'></i>
                    <input type="email" name="email" class="glass-input w-full pl-12 pr-4 py-3 rounded-xl" placeholder="ornek@mail.com" required>
                </div>
            </div>
            
            <div>
                <label class="text-slate-400 text-xs font-bold ml-1 mb-1 block">ŞİFRE</label>
                <div class="relative">
                    <i class='bx bx-lock-alt absolute left-4 top-3.5 text-slate-500 text-xl'></i>
                    <input type="password" name="password" class="glass-input w-full pl-12 pr-4 py-3 rounded-xl" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-bold text-lg shadow-lg shadow-primary/25 hover:scale-[1.02] transition-transform">
                Giriş Yap
            </button>
        </form>

        <p class="text-center text-slate-500 text-sm mt-6">
            Hesabın yok mu? <a href="#" class="text-white font-bold hover:underline">Kayıt Ol</a>
        </p>
    </div>

</body>
</html>