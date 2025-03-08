<?php
session_start();
include('config/db.php');

// Proses login jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi login
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['user'] = $email; // Simpan email di sesi
        header('Location: dashboard.php'); // Arahkan ke dashboard
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<?php include('includes/header.php'); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Login</h2>
            <form method="POST">
                <!-- Input Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email Anda" required>
                </div>
                <!-- Input Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password Anda" required>
                </div>
                <!-- Tombol Login -->
                <button type="submit" class="btn btn-success w-100">Login</button>
            </form>
            <!-- Pesan Error -->
            <?php if (isset($error)) { ?>
                <p class="text-danger text-center mt-3"><?php echo $error; ?></p>
            <?php } ?>
            <!-- Tombol Register -->
            <div class="text-center mt-4">
                <p>Belum punya akun?</p>
                <a href="register.php" class="btn btn-outline-primary">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
