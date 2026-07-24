<!DOCTYPE html>
<html lang="tr" class="h-full bg-[#020617] scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM PRO - Sınırlarını Zorla</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { primary: '#6366f1', secondary: '#d946ef', dark: '#0f172a' },
                    backgroundImage: {
                        'hero-pattern': "linear-gradient(to right bottom, rgba(2, 6, 23, 0.9), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop')"
                    }
                }
            }
        }
    </script>
    <style>
        .text-gradient {
            background: linear-gradient(to right, #6366f1, #d946ef);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-slate-200 font-sans">

    <nav class="fixed w-full z-50 bg-[#020617]/80 backdrop-blur-md border-b border-white/10 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="#" class="flex items-center gap-2">
                <i class='bx bxs-bolt-circle text-3xl text-primary'></i>
                <span class="text-2xl font-extrabold text-white tracking-wider">GYM PRO</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="/gym-pro/public/auth/login" class="hidden md:block text-slate-300 hover:text-white transition font-medium">Giriş Yap</a>
                <a href="/gym-pro/public/auth/register" class="bg-primary hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold transition transform hover:scale-105 shadow-lg shadow-primary/25 flex items-center gap-2">
                    Hemen Başla <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>
    </nav>

    <section class="min-h-screen flex items-center relative bg-hero-pattern bg-cover bg-center bg-fixed px-6 pt-20">
        <div class="absolute top-20 left-0 w-72 h-72 bg-primary/30 rounded-full filter blur-[120px] -z-10 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/20 rounded-full filter blur-[150px] -z-10"></div>

        <div class="max-w-7xl mx-auto w-full grid md:grid-cols-2 gap-12 items-center py-20">
            <div class="space-y-8 animate-[fadeInLeft_1s_ease-out]">
                <span class="inline-block py-1 px-3 rounded-full bg-white/10 text-primary text-sm font-bold border border-primary/20">
                    🚀 %100 Potansiyeline Ulaş
                </span>
                <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight">
                    Sınırlarını <br>
                    <span class="text-gradient">Yeniden Tanımla.</span>
                </h1>
                <p class="text-lg text-slate-400 max-w-xl leading-relaxed">
                    Sadece bir spor salonu değil, bir dönüşüm merkezi. Kişiselleştirilmiş programlar, uzman antrenörler ve seni motive eden bir toplulukla hedeflerine ulaş.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="/gym-pro/public/auth/register" class="bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-xl font-bold text-lg transition transform hover:scale-105 hover:shadow-2xl hover:shadow-primary/30 flex items-center gap-2">
                        Ücretsiz Kayıt Ol <i class='bx bxs-hot'></i>
                    </a>
                    <a href="#features" class="px-8 py-4 rounded-xl font-bold text-lg border-2 border-white/10 hover:bg-white/5 hover:border-white/30 transition text-white flex items-center gap-2">
                        Keşfet <i class='bx bx-down-arrow-alt'></i>
                    </a>
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <div class="flex -space-x-4">
                        <img class="w-10 h-10 rounded-full border-2 border-[#020617]" src="https://i.pravatar.cc/100?img=1" alt="">
                        <img class="w-10 h-10 rounded-full border-2 border-[#020617]" src="https://i.pravatar.cc/100?img=2" alt="">
                        <img class="w-10 h-10 rounded-full border-2 border-[#020617]" src="https://i.pravatar.cc/100?img=3" alt="">
                    </div>
                    <p class="text-slate-400 text-sm"><span class="text-white font-bold">500+</span> Mutlu Üye Aramıza Katıldı</p>
                </div>
            </div>
            <div class="hidden md:block relative animate-[fadeInRight_1s_ease-out]">
                </div>
        </div>
    </section>

    <section id="features" class="py-24 px-6 bg-[#0f172a] relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-3xl md:text-5xl font-extrabold text-white">Neden <span class="text-gradient">GYM PRO?</span></h2>
                <p class="text-slate-400 max-w-2xl mx-auto">Sıradanlığın ötesine geçmeniz için tasarlanmış premium özellikler.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-[#020617] border border-white/5 hover:border-primary/50 transition group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-bl-full -z-10 group-hover:bg-primary/20 transition"></div>
                    <i class='bx bx-dumbbell text-5xl text-primary mb-6'></i>
                    <h3 class="text-xl font-bold text-white mb-4">Kişisel Programlar</h3>
                    <p class="text-slate-400">Hedefinize, vücut tipinize ve seviyenize özel olarak hazırlanan dinamik antrenman planları.</p>
                </div>
                <div class="p-8 rounded-3xl bg-[#020617] border border-white/5 hover:border-secondary/50 transition group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-secondary/10 rounded-bl-full -z-10 group-hover:bg-secondary/20 transition"></div>
                    <i class='bx bxs-user-voice text-5xl text-secondary mb-6'></i>
                    <h3 class="text-xl font-bold text-white mb-4">Uzman Eğitmenler</h3>
                    <p class="text-slate-400">Alanında uzman, sertifikalı Personal Trainer'lar ile birebir iletişim ve sürekli takip.</p>
                </div>
                <div class="p-8 rounded-3xl bg-[#020617] border border-white/5 hover:border-primary/50 transition group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-bl-full -z-10 group-hover:bg-primary/20 transition"></div>
                    <i class='bx bxs-data text-5xl text-primary mb-6'></i>
                    <h3 class="text-xl font-bold text-white mb-4">Gelişim Takibi</h3>
                    <p class="text-slate-400">Vücut ölçülerinizi, ağırlıklarınızı ve performansınızı grafiklerle analiz edin.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-10 bg-[#020617] border-t border-white/5 text-center text-slate-500">
        <p>© 2023 GYM PRO. Tüm hakları saklıdır. Güç seninle olsun. 💪</p>
    </footer>

</body>
</html>