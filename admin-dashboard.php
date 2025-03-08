<?php
session_start();
include('config/db.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

// Ambil informasi ringkasan
$total_members = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$total_transactions = $conn->query("SELECT COUNT(id) AS total FROM transactions")->fetch_assoc()['total'];

?>
<?php include('includes/header.php'); ?>
<div class="container mt-3">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar vh-100">
            <div class="position-sticky pt-3">
                <h4 class="text-center text-white">Admin Menu</h4>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin-dashboard.php">🏠 Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin-members.php">👥 Kelola Anggota</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin-transactions.php">💳 Lihat Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin-pins.php">🔑 Kelola PIN</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="admin-logout.php">🚪 Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="text-success">Dashboard Admin</h2>
            </div>

            <p>Selamat datang, Admin! Gunakan menu di sidebar untuk mengelola sistem.</p>

            <!-- Ringkasan -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-primary text-white p-3 mb-3">
                        <h5>Total Anggota</h5>
                        <h2><?php echo $total_members; ?></h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-warning text-white p-3 mb-3">
                        <h5>Total Transaksi</h5>
                        <h2><?php echo $total_transactions; ?></h2>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Tambahkan Bootstrap agar sidebar tetap responsif -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include('includes/footer.php'); ?>
