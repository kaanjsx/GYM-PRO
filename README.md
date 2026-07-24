# ⚡ GYM PRO - All-in-One Spor Salonu Yönetim ve Otomasyon Sistemi

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-RDBMS-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Security](https://img.shields.io/badge/Security-PDO%20%7C%20OTP%20%7C%20RBAC-red?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**GYM PRO**, geleneksel spor salonu yönetim süreçlerinin getirdiği operasyonel hantallığı, dağınık veri yapısını ve güvenlik zafiyetlerini ortadan kaldırmak amacıyla **katmanlı MVC (Model-View-Controller) mimarisi** ile sıfırdan geliştirilmiş modern bir web otomasyon sistemidir. 

Sistem; Yönetici (Admin), Eğitmen (Personal Trainer) ve Üye (Member) rollerini tek bir dijital merkezde buluşturarak yüksek performanslı, ölçeklenebilir ve yapay zeka destekli bir spor salonu ekosistemi sunar.

---

## 📸 Uygulama Arayüzleri ve Ekran Görüntüleri (Screenshots)

Projemizde Tailwind CSS, Glassmorphism (Cam Efekti) ve karanlık/aydınlık tema motoru (Dark/Light Mode) kullanılarak yüksek UI/UX standartları sağlanmıştır.

### 🖥️ Genel Kullanıcı Paneli (Dashboard & Tema Motoru)
Kullanıcıların genel istatistiklerini, su tüketimlerini ve aktif programlarını takip edebildikleri ana arayüz. Sistemde **Classic Pro**, **Aggressive Red** ve **Selected Elite** olmak üzere çalışma zamanında (runtime) sayfa yenilenmeden değiştirilebilen 3 dinamik tema mevcuttur.
<p align="center">
  <img src="public/assets/img/dashboard.png" alt="GYM PRO Dashboard Arayüzü" width="85%">
</p>

### 🤖 Yapay Zeka Destekli Besin Analizi Asistanı (AI Assistant)
Kullanıcıların yediklerini (Örn: *"2 tabak makarna, 200 gram tavuk"*) tamamen serbest bir doğal dille yazdıkları ve arka plandaki özel RegEx algoritmasının bu metni tarayarak **milisaniyeler içinde kalori ve makro besin hesabı** yaptığı arayüz.
<p align="center">
  <img src="public/assets/img/ai-assistant.png" alt="GYM PRO AI Besin Asistanı" width="85%">
</p>

### 📊 Detaylı Analiz Paneli: Güç Radarı & Kas Isı Haritası
Egzersiz listesinden işaretlenen hareketlerin (Örn: *"Bench Press"*), RegEx taramasıyla hangi kas grubunu çalıştırdığının tespit edilip özel **SVG insan anatomisi poligonlarında otomatik aydınlatıldığı** ve eforun **Chart.js Güç Radarına** yansıtıldığı modül.
<p align="center">
  <img src="public/assets/img/analysis-radar.png" alt="Güç Radarı ve Kas Isı Haritası" width="85%">
</p>

### 📅 Eğitmenler (PT) İçin Haftalık Antrenman Programı Editörü
Eğitmenlerin üyelerine özel haftalık egzersiz rutini, set ve tekrar sayılarını atadıkları interaktif yönetim paneli. Bu veriler veritabanını yormamak adına bütünleşik bir **JSON şablonu** olarak saklanır.
<p align="center">
  <img src="public/assets/img/program-editor.png" alt="PT Program Editörü Arayüzü" width="85%">
</p>

### 📱 Mobil Uyumlu (Responsive) Gezinme Arayüzü
Sistemin CSS3 Flexbox, Grid ve Media Query'ler ile sadece masaüstünde değil, tüm tablet ve akıllı telefon ekranlarında kusursuz dikey hizalamayla çalıştığını gösteren mobil arayüz.
<p align="center">
  <img src="public/assets/img/mobile-ui.png" alt="GYM PRO Mobil Arayüz" width="40%">
</p>

### 📄 Dinamik PDF Kişisel Gelişim Raporu Modülü
Kullanıcıların fiziksel ölçüm verilerinin, kas haritasının ve güç radarının `html2pdf.js` ve Canvas mimarisi ile tek tıkla resmi bir **A4 Kişisel Gelişim Raporuna** dönüştürülmüş çıktı örneği.
<p align="center">
  <img src="public/assets/img/pdf-report.png" alt="Kişisel Gelişim PDF Raporu" width="60%">
</p>

---

## 🌟 Öne Çıkan Özellikler ve İnovasyonlar

### 🤖 1. Yerel Yapay Zeka (AI) Beslenme Asistanı
Harici, gecikme yaratan ve maliyetli API'ler yerine; doğrudan istemci (client) tarafında çalışan **Kural Tabanlı Doğal Dil İşleme (NLP)** algoritması geliştirilmiştir.
- Kullanıcılar yediklerini doğal dille girdiğinde, özel **RegEx motoru** metni tarar, ölçü birimlerini (tabak, gram, porsiyon) matematiksel katsayılara dönüştürür[cite: 3].
- Yerel veri sözlüğü (JSON) ile eşleşen besinlerin kalori ve makro değerleri (Protein, Karbonhidrat, Yağ) **milisaniyeler içinde** hesaplanır ve sayfa yenilenmeden dinamik grafiklere yansıtılır[cite: 3].

### 🔐 2. İleri Düzey Güvenlik Mimarisi
OWASP web güvenliği standartlarına tam uyumlu olarak zırhlandırılmıştır[cite: 3]:
- **SQL Injection Koruması:** Veritabanı katmanında sadece **PDO (PHP Data Objects) Prepared Statements** kullanılmış, dinamik sorgu birleştirme tamamen reddedilmiştir[cite: 3].
- **XSS (Cross-Site Scripting) Koruması:** İstemci ve sunucu arasındaki asenkron mesajlaşma paneli dahil tüm girdi ve çıktılar `htmlspecialchars` ve veri izolasyon filtrelerinden geçirilir[cite: 3].
- **2FA & OTP Doğrulama:** Kullanıcı kayıtlerinde sahte hesapları engellemek için **PHPMailer & SMTP (TLS 1.2)** entegrasyonu ile çalışan 6 haneli **Tek Kullanımlık Şifre (OTP)** doğrulama algoritması mevcuttur[cite: 3].
- **Gelişmiş Oturum (Session) Güvenliği:** Session Hijacking ve Fixation saldırılarına karşı `session_regenerate_id(true)`, **HttpOnly**, **Secure** ve **SameSite=Strict** çerez politikaları aktif edilmiştir[cite: 3].
- **RBAC (Rol Tabanlı Erişim Kontrolü):** Admin, PT ve Üye yetkileri Controller katmanında denetlenir, yetkisiz URL erişimleri anında engellenir[cite: 3].

### 📊 3. Veri Görselleştirme ve İstemci Önbellekleme (LocalStorage Caching)
- **Veri Trafiği Optimizasyonu:** Sunucuyu (MySQL) tekrarlı SELECT sorgularından korumak için kullanıcının gelişim ve grafik verileri **LocalStorage API** üzerinde JSON formatında önbelleğe alınır[cite: 3].

---

## 🛠️ Teknik Mimari ve Veritabanı Tasarımı

Proje, **Separation of Concerns (Sorumlulukların Ayrılması)** prensibine dayanan **MVC** mimarisi ile kodlanmıştır[cite: 3]:
* **Model:** PDO veritabanı bağlantıları, veri doğrulama ve SQL iş mantığı[cite: 3].
* **View:** Tailwind CSS ile oluşturulan modüler arayüz şablonları[cite: 3].
* **Controller:** İstemci isteklerini (HTTP Request) yönlendiren Router ve işleyiciler[cite: 3].

### ⚡ Veritabanı Optimizasyonları:
* **NoSQL Yaklaşımlı JSON Depolama:** Yüzlerce üyenin antrenman programı için veritabanını yoran JOIN sorguları yerine, `programs` tablosundaki `program_details` sütununda haftalık egzersizler tek bir bütünleşik **JSON şablonu** olarak saklanır[cite: 3]. Bu sayede sunucu I/O yükü **%80 azaltılmıştır**[cite: 3].
* **Kompozit İndeksleme & Triggers:** `water_log` (hidrasyon takibi) ve `user_stats` tablolarında `user_id` ve `date` üzerinde kompozit indeksler kurularak geriye dönük arama hızları milisaniyeler seviyesine indirilmiştir[cite: 3]. Su takibinde `ON DUPLICATE KEY UPDATE` mantığı ile sunucu dostu kayıt algoritması uygulanmıştır[cite: 3].

---

## 🚀 Kurulum ve Çalıştırma Kılavuzu

Sistemi yerel ortamınızda (Localhost) çalıştırmak için aşağıdaki adımları izleyebilirsiniz[cite: 3].

### 1. Gereksinimler
- **PHP:** 8.0 veya üzeri[cite: 3]
- **Veritabanı:** MySQL / MariaDB[cite: 3]
- **Sunucu:** Apache (XAMPP / WAMP / Laragon önerilir)[cite: 3]
- **Composer:** Bağımlılık yönetimi için[cite: 3]

### 2. Projeyi Kopyalayın (Clone)
```bash
git clone [https://github.com/kaanjsx/GYM-PRO.git](https://github.com/kaanjsx/GYM-PRO.git)
cd GYM-PRO
