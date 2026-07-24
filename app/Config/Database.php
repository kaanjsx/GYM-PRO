<?php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'gym_db';

    private $dbh; 
    private $error;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->dbh;
    }

    // --- DİĞER FONKSİYONLAR ---
    public function prepare($sql) {
        return $this->dbh->prepare($sql);
    }

    public function query($sql) {
        return $this->dbh->query($sql);
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}