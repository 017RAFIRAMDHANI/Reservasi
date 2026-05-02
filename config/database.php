<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_reservasi_ruangan';

function getDB(): mysqli {
    static $conn = null;
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

    if ($conn === null) {
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if ($conn->connect_error) {
            die('Koneksi database gagal: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }

    return $conn;
}
