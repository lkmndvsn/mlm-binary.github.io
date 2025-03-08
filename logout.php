<?php
session_start(); // Memulai sesi
session_destroy(); // Mengakhiri semua sesi
header('Location: login.php'); // Mengarahkan ke halaman login
exit; // Menghentikan eksekusi script
?>
