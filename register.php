<?php
include('config/db.php');
session_start();

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $loggedInUserId = $_SESSION['user_id'];
    if (!isset($_GET['referral_id'])) {
        $_GET['referral_id'] = $loggedInUserId;
    }
}

// Proses registrasi jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $referral_id = $_POST['referral_id'];

    // Cek apakah email sudah terdaftar
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        $error = "Email sudah digunakan. Silakan gunakan email lain.";
    } else {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Pastikan referral_id valid jika diisi
        if (!empty($referral_id)) {
            $referralExists = $conn->query("SELECT id FROM users WHERE id = '$referral_id'");
            if ($referralExists->num_rows == 0) {
                $error = "Referral ID tidak valid.";
            }
        }

        // Jika tidak ada error, lakukan penyimpanan ke database
        if (!isset($error)) {
            $query = "INSERT INTO users (name, email, password, referral_id) VALUES ('$name', '$email', '$hashed_password', '$referral_id')";
            if ($conn->query($query)) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan: " . $conn->error;
            }
        }
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Registrasi</h2>

            <!-- Form Registrasi -->
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email Anda" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password Anda" required>
                </div>
                <div class="mb-3">
                    <label for="referral_id" class="form-label">Referral ID (Opsional)</label>
                    <input type="text" name="referral_id" id="referral_id" class="form-control" value="<?php echo htmlspecialchars($_GET['referral_id'] ?? ''); ?>" readonly>
                </div>
                <button type="submit" class="btn btn-success w-100">Daftar</button>
            </form>

            <!-- Pesan Sukses atau Error -->
            <?php if (isset($success)) { ?>
                <p class="text-success text-center mt-3"><?php echo $success; ?></p>
                <script>
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 2000); // Redirect ke login setelah 2 detik
                </script>
            <?php } elseif (isset($error)) { ?>
                <p class="text-danger text-center mt-3"><?php echo $error; ?></p>
            <?php } ?>

            <!-- Tombol Login -->
            <?php if (!$isLoggedIn) { ?>
                <div class="text-center mt-4">
                    <p>Sudah punya akun?</p>
                    <a href="login.php" class="btn btn-outline-primary">Login</a>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
