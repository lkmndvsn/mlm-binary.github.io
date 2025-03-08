<?php
session_start();
include('config/db.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data komisi
$user = $_SESSION['user'];
$query_user = "SELECT * FROM users WHERE email = '$user'";
$result_user = $conn->query($query_user);
$data_user = $result_user->fetch_assoc();

$referral_id = $data_user['id'];

// Hitung downline
$query_downline = "SELECT COUNT(*) AS total_downline FROM users WHERE referral_id = '$referral_id'";
$result_downline = $conn->query($query_downline);
$total_downline = $result_downline->fetch_assoc()['total_downline'];

// Hitung komisi
$bonus_sponsor = $total_downline * 20000; // Rp20.000 per downline
$bonus_pasangan = floor($total_downline / 2) * 5000; // Rp5.000 per pasangan
$total_komisi = $bonus_sponsor + $bonus_pasangan;
?>
<?php include('includes/header.php'); ?>
<div class="container mt-3">
    <a href="dashboard.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
</div>
<div class="container mt-5">
    <h2>Komisi Anda</h2>
    <ul class="list-group">
        <li class="list-group-item">Total Downline: <?php echo $total_downline; ?></li>
        <li class="list-group-item">Bonus Sponsor: Rp<?php echo number_format($bonus_sponsor); ?></li>
        <li class="list-group-item">Bonus Pasangan: Rp<?php echo number_format($bonus_pasangan); ?></li>
        <li class="list-group-item bg-success text-white">Total Komisi: Rp<?php echo number_format($total_komisi); ?></li>
    </ul>
</div>
<?php include('includes/footer.php'); ?>
