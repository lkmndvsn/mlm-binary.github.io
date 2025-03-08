<?php
session_start();
include('config/db.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data pengguna
$user = $_SESSION['user'];
$query = "SELECT * FROM users WHERE email = '$user'";
$result = $conn->query($query);
$data = $result->fetch_assoc();
$photo = $data['photo'] ?? 'default.png'; // Foto default jika belum ada

// Proses pembaruan profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Upload foto profil jika ada file
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['photo']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi file gambar
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo = $_FILES['photo']['name'];
                $update_photo_query = "UPDATE users SET photo = '$photo' WHERE email = '$user'";
                $conn->query($update_photo_query);
            } else {
                $error = "Terjadi kesalahan saat mengunggah foto.";
            }
        } else {
            $error = "Format file tidak valid. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        }
    }

    // Perbarui data pengguna
    $update_query = "UPDATE users SET name = '$name', email = '$email', address = '$address', phone = '$phone' WHERE email = '$user'";
    if ($conn->query($update_query)) {
        $_SESSION['user'] = $email; // Perbarui sesi jika email diubah
        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Terjadi kesalahan: " . $conn->error;
    }
}
?>
<?php include('includes/header.php'); ?>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
    <h2 class="mt-4">Profil Anda</h2>
    <div class="row">
        <div class="col-md-4 text-center">
            <!-- Tampilkan foto profil -->
            <img src="uploads/<?php echo $photo; ?>" alt="Foto Profil" class="img-thumbnail" style="width: 200px; height: 200px; object-fit: cover;">
        </div>
        <div class="col-md-8">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo $data['name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo $data['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea name="address" id="address" class="form-control" rows="3" required><?php echo $data['address']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor HP</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $data['phone']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="photo" class="form-label">Unggah Foto Profil</label>
                    <input type="file" name="photo" id="photo" class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </form>
            <!-- Pesan notifikasi -->
            <?php
            if (isset($success)) {
                echo "<p class='text-success mt-3'>$success</p>";
            }
            if (isset($error)) {
                echo "<p class='text-danger mt-3'>$error</p>";
            }
            ?>
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<?php include('includes/footer.php'); ?>
