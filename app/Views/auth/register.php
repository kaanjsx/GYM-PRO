<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kayıt Ol - GYM PRO</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet"> 
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'], heading: ['Poppins', 'sans-serif'] },
                    colors: { primary: '#6366f1', secondary: '#a855f7', dark: '#0f172a' },
                    animation: { 'fadeIn': 'fadeIn 0.5s ease-in-out', 'blob': 'blob 7s infinite' },
                    keyframes: { 
                        fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        blob: { '0%': { transform: 'translate(0px, 0px) scale(1)' }, '33%': { transform: 'translate(30px, -50px) scale(1.1)' }, '66%': { transform: 'translate(-20px, 20px) scale(0.9)' }, '100%': { transform: 'translate(0px, 0px) scale(1)' } }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif !important; background-color: #0f172a; color: #e2e8f0; }
        .glass-card { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-input { background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); color: white; transition: all 0.3s ease; }
        .glass-input:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        .btn-neon { background: linear-gradient(135deg, #6366f1, #a855f7); color: white; transition: all 0.3s ease; }
        .btn-neon:hover { box-shadow: 0 0 20px rgba(168, 85, 247, 0.4); transform: translateY(-2px); }
        
        .code-box { text-align: center; font-size: 1.5rem; font-weight: 800; background: rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.1); border-radius: 0.75rem; color: white; transition: all 0.2s; }
        .code-box:focus { border-color: #a855f7; box-shadow: 0 0 15px rgba(168, 85, 247, 0.3); outline: none; transform: scale(1.05); }
        
        .hide-section { display: none !important; }
        .slide-in { animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }

        .swal2-container, .swal2-popup, .swal2-title, .swal2-html-container, .swal2-confirm {
            font-family: 'Outfit', sans-serif !important;
        }
        .swal2-popup { border-radius: 2rem !important; background: #0f172a !important; border: 1px solid rgba(255,255,255,0.1) !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important; }
        .swal2-title { color: #fff !important; font-weight: 800 !important; text-transform: uppercase !important; }
        .swal2-html-container { color: #94a3b8 !important; }
        .swal2-confirm { border-radius: 1rem !important; padding: 12px 35px !important; font-weight: 700 !important; background: linear-gradient(135deg, #6366f1, #a855f7) !important; }
    </style>
</head>
<body class="h-screen flex items-center justify-center p-6 relative overflow-hidden bg-[#020617]">
    
    <div class="fixed top-10 right-10 w-64 h-64 bg-secondary/20 rounded-full mix-blend-screen filter blur-[80px] animate-blob"></div>
    <div class="fixed bottom-10 left-10 w-64 h-64 bg-primary/20 rounded-full mix-blend-screen filter blur-[80px] animate-blob" style="animation-delay: 2s;"></div>

    <div class="glass-card w-full max-w-md p-8 rounded-[2rem] relative z-10">
        
        <div id="step-1-register" class="transition-all duration-500">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto bg-primary/10 border border-primary/20 rounded-2xl flex items-center justify-center mb-4">
                    <i class='bx bx-user-plus text-3xl text-primary'></i>
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Hesap Oluştur</h1>
                <p class="text-slate-400 mt-2 text-sm font-medium">GYM PRO dünyasına adım at.</p>
            </div>

            <form id="registerForm" class="space-y-5">
                <div class="group">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Ad Soyad</label>
                    <div class="relative mt-1">
                        <i class='bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>
                        <input type="text" name="name" id="userName" class="glass-input w-full p-3.5 pl-12 rounded-xl" placeholder="Adınız Soyadınız" required>
                    </div>
                </div>
                <div class="group">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">E-Posta</label>
                    <div class="relative mt-1">
                        <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg'></i>
                        <input type="email" name="email" id="userEmail" class="glass-input w-full p-3.5 pl-12 rounded-xl" placeholder="ornek@email.com" required>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="group w-1/2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Şifre</label>
                        <input type="password" name="password" class="glass-input w-full p-3.5 rounded-xl" placeholder="••••••" required>
                    </div>
                    <div class="group w-1/2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider ml-1">Tekrar</label>
                        <input type="password" name="password_confirm" class="glass-input w-full p-3.5 rounded-xl" placeholder="••••••" required>
                    </div>
                </div>
                <button type="submit" id="btnRegister" class="btn-neon w-full py-4 rounded-xl font-bold text-lg mt-4 flex items-center justify-center gap-2">
                    Kayıt Ol <i class='bx bx-right-arrow-alt text-xl'></i>
                </button>
            </form>
            <div class="mt-8 text-center pt-6 border-t border-white/5">
                <p class="text-sm text-slate-400 font-medium">Zaten hesabın var mı? <a href="/gym-pro/public/auth/login" class="text-primary font-bold hover:text-secondary">Giriş Yap</a></p>
            </div>
        </div>

        <div id="step-2-verify" class="hide-section">
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-emerald-500/10 border border-emerald-500/20 rounded-full flex items-center justify-center mb-6 relative">
                    <div class="absolute inset-0 rounded-full border-t-2 border-emerald-500 animate-spin"></div>
                    <i class='bx bx-mail-send text-4xl text-emerald-400'></i>
                </div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Kodu Gir</h2>
                <p class="text-slate-400 mt-3 text-sm leading-relaxed">
                    <span id="displayEmail" class="text-white font-bold"></span> adresine gelen 6 haneli kodu aşağıya gir.
                </p>
            </div>
            <form id="verifyForm" class="space-y-8">
                <div class="flex justify-between gap-2" id="code-inputs">
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16" autofocus>
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16">
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16">
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16">
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16">
                    <input type="text" maxlength="1" class="code-box w-12 h-14 md:w-14 md:h-16">
                </div>
                <button type="submit" id="btnVerify" class="w-full bg-white text-indigo-900 py-4 rounded-xl font-black text-lg transition-all flex items-center justify-center gap-2">
                    DOĞRULA VE BAŞLA <i class='bx bxs-check-circle text-xl'></i>
                </button>
            </form>
            <div class="mt-6 text-center">
                <button type="button" onclick="goBack()" class="text-xs text-slate-500 font-bold hover:text-white transition mx-auto">
                    E-posta adresini yanlış mı yazdın?
                </button>
            </div>
        </div>
    </div>

<script>
    let isMailSent = false; 
    const registerForm = document.getElementById('registerForm');
    const verifyForm = document.getElementById('verifyForm');
    const step1 = document.getElementById('step-1-register');
    const step2 = document.getElementById('step-2-verify');
    
    const gymAlert = (icon, title, text) => {
        Swal.fire({
            icon: icon, title: title.toUpperCase(), text: text,
            background: '#0f172a', color: '#fff', confirmButtonColor: '#6366f1'
        });
    };

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        if(isMailSent) { 
            step1.classList.add('hide-section');
            step2.classList.remove('hide-section');
            return;
        }
        
        const btn = document.getElementById('btnRegister');
        const email = document.getElementById('userEmail').value;
        btn.innerHTML = "<i class='bx bx-loader-alt animate-spin text-2xl'></i> GÖNDERİLİYOR...";
        btn.disabled = true;

        fetch('/gym-pro/public/auth/sendCode', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                isMailSent = true; // KİLİT KAPANDI
                document.getElementById('displayEmail').innerText = email;
                step1.classList.add('hide-section');
                step2.classList.remove('hide-section');
                step2.classList.add('slide-in');
            } else {
                gymAlert('error', 'Hata!', data.message);
                btn.innerHTML = "Kayıt Ol <i class='bx bx-right-arrow-alt text-xl'></i>";
                btn.disabled = false;
            }
        }).catch(() => {
            gymAlert('error', 'HATA', 'Sunucuya bağlanılamadı!');
            btn.innerHTML = "Kayıt Ol"; btn.disabled = false;
        });
    });

    verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnVerify');
        btn.innerHTML = "<i class='bx bx-loader-alt animate-spin text-2xl'></i> KONTROL EDİLİYOR...";

        let code = "";
        document.querySelectorAll('.code-box').forEach(input => code += input.value);
        const formData = new FormData();
        formData.append('verification_code', code);

        fetch('/gym-pro/public/auth/verify', { 
            method: 'POST', 
            body: formData 
        })
        .then(res => res.json()) 
        .then(data => {
            if(data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'BİTTİ BU İŞ!',
                    text: data.message,
                    confirmButtonText: 'HAYDİ BAŞLAYALIM 💪'
                }).then(() => {
                    window.location.href = '/gym-pro/public/'; 
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'KOD HATALI!',
                    text: data.message
                });
                btn.innerHTML = "DOĞRULA VE BAŞLA <i class='bx bxs-check-circle text-xl'></i>";
                
                document.querySelectorAll('.code-box').forEach(el => el.value = "");
                document.querySelectorAll('.code-box')[0].focus();
            }
        });
    });
</script>
</body>
</html>