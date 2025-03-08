<?php
session_start();
include('config/db.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data user
$user = $_SESSION['user'];

// Proses ubah password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi password lama
    $query = "SELECT * FROM users WHERE email = '$user' AND password = '$current_password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        if ($new_password === $confirm_password) {
            $update_query = "UPDATE users SET password = '$new_password' WHERE email = '$user'";
            if ($conn->query($update_query)) {
                $success = "Password berhasil diubah!";
            } else {
                $error = "Terjadi kesalahan: " . $conn->error;
            }
        } else {
            $error = "Password baru dan konfirmasi tidak cocok!";
        }
    } else {
        $error = "Password lama salah!";
    }
}
?>
<?php include('includes/header.php'); ?>
<div class="container mt-3">
    <a href="dashboard.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
</div>
<div class="container mt-5">
    <h2>Ubah Password</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Ubah Password</button>
    </form>
    <?php if (isset($success)) { echo "<p class='text-success mt-3'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='text-danger mt-3'>$error</p>"; } ?>
</div>
<?php include('includes/footer.php'); ?>
