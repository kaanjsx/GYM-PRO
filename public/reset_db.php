<?php
$host = 'localhost';
$db   = 'gym_db';  
$user = 'root';    
$pass = '';        

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Veritabanı Temizliği Başlıyor...</h3>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✅ Korumalar Devre Dışı Bırakıldı.<br>";

    $tables = [
        'program_details',
        'programs',
        'exercises',
        'trainings',        
        'workout_programs', 
        'appointments',
        'goals' 
    ];

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        echo "🗑️ Tablo silindi: <strong>$table</strong><br>";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Korumalar Tekrar Aktif.<br>";
    
    echo "<h1 style='color:green'>İŞLEM TAMAM! TABLOLAR UÇTU.</h1>";
    echo "Şimdi phpMyAdmin'e gidip verdiğim SQL kodlarıyla temiz tabloları kurabilirsin.";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>BAĞLANTI HATASI:</h2> " . $e->getMessage();
    echo "<br>Lütfen dosyanın en üstündeki veritabanı adı (gym_db) doğru mu kontrol et.";
}
?>