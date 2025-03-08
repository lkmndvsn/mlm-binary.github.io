<?php
session_start();
include('config/db.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data pengguna dari sesi
$user = $_SESSION['user'];
$query = "SELECT * FROM users WHERE email = '$user'";
$result = $conn->query($query);
$data = $result->fetch_assoc();

// Ambil jumlah downline
$query_downline = "SELECT COUNT(*) as total FROM users WHERE referral_id = '" . $data['id'] . "'";
$result_downline = $conn->query($query_downline);
$data_downline = $result_downline->fetch_assoc();
$total_downline = $data_downline['total'];

// Ambil jumlah pin yang tersedia
$query_pins = "SELECT COUNT(*) as total FROM pins WHERE user_id = '" . $data['id'] . "' AND status = 'unused'";
$result_pins = $conn->query($query_pins);
$data_pins = $result_pins->fetch_assoc();
$total_pins = $data_pins['total'];

// Ambil daftar PIN yang dimiliki user
$query_user_pins = "SELECT * FROM pins WHERE user_id = '" . $data['id'] . "' AND status = 'unused'";
$result_user_pins = $conn->query($query_user_pins);

// Proses transfer PIN ke member lain
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transfer_pins'])) {
    $recipient_email = $_POST['recipient_email'];
    $pin_amount = intval($_POST['pin_amount']);

    // Cek apakah user tujuan ada
    $query_recipient = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $query_recipient->bind_param("s", $recipient_email);
    $query_recipient->execute();
    $recipient_result = $query_recipient->get_result();

    if ($recipient_result->num_rows > 0) {
        $recipient_data = $recipient_result->fetch_assoc();
        $recipient_id = $recipient_data['id'];

        // Ambil PIN yang akan ditransfer
        $pin_query = $conn->query("SELECT id FROM pins WHERE user_id = '" . $data['id'] . "' AND status = 'unused' LIMIT $pin_amount");
        $pins_to_transfer = [];

        while ($pin_row = $pin_query->fetch_assoc()) {
            $pins_to_transfer[] = $pin_row['id'];
        }

        if (count($pins_to_transfer) == $pin_amount) {
            // Transfer PIN ke member tujuan
            $pin_ids = implode(",", $pins_to_transfer);
            $conn->query("UPDATE pins SET user_id = '$recipient_id' WHERE id IN ($pin_ids)");

            echo "<script>alert('$pin_amount PIN berhasil dikirim ke $recipient_email!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Jumlah PIN yang tersedia tidak cukup!');</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan!');</script>";
    }
}
?>

<?php include('includes/header.php'); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 bg-light vh-100">
            <h4 class="text-center mt-4">Menu</h4>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="profile.php">🧑 Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="downline.php">🌳 Downline</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="komisi.php">💰 Komisi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="pin.php">📌 Pin Saya</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="change-password.php">🔒 Ubah Password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">🚪 Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 p-4 text-center">
            <h2 class="text-success">Dasbor Member</h2>
            <p>Selamat datang, <strong><?php echo $data['name']; ?></strong>!</p>
            <p>Gunakan menu di sidebar untuk mengelola akun Anda, memeriksa jaringan downline, melihat komisi, dan memperbarui password.</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">🌳 Downline</h5>
                            <p class="card-text">Total: <strong><?php echo $total_downline; ?></strong></p>
                            <a href="downline.php" class="btn btn-primary">Lihat Downline</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">📌 Pin Saya</h5>
                            <p class="card-text">Tersedia: <strong><?php echo $total_pins; ?></strong></p>
                            <a href="pin.php" class="btn btn-success">Kelola Pin</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">🧑 Profil</h5>
                            <p class="card-text"><strong><?php echo $data['name']; ?></strong></p>
                            <a href="profile.php" class="btn btn-warning">Edit Profil</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-4" role="alert">
                <p>Jika Anda memerlukan bantuan, silakan kunjungi bagian <a href="support.php">Dukungan</a> atau hubungi admin.</p>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
