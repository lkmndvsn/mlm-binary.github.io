<?php
session_start();
include('config/db.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifikasi admin
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = $username;
        header('Location: admin-dashboard.php');
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<?php include('includes/header.php'); ?>
<div class="container mt-3">
    <a href="admin-login.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
</div>
<div class="container mt-5">
    <h2>Login Admin</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Login</button>
    </form>
    <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
</div>
<?php include('includes/footer.php'); ?>
