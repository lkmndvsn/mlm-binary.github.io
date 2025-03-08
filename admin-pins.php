<?php
session_start();
include('config/db.php');

// Pastikan admin sudah login
if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

// Ambil daftar PIN yang belum digunakan
$query = "SELECT pins.id, pins.code, pins.status, users.name AS owner 
          FROM pins LEFT JOIN users ON pins.user_id = users.id 
          ORDER BY pins.id DESC";
$result = $conn->query($query);

// Tambah banyak PIN sekaligus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_pins'])) {
    $pin_count = intval($_POST['pin_count']); // Jumlah PIN yang dipilih (10, 50, 100)
    for ($i = 0; $i < $pin_count; $i++) {
        $new_pin = strtoupper(bin2hex(random_bytes(4))); // Generate PIN unik
        $conn->query("INSERT INTO pins (code, status) VALUES ('$new_pin', 'unused')");
    }
    echo "<script>alert('$pin_count PIN berhasil ditambahkan!'); window.location='admin-pins.php';</script>";
}

// Hapus PIN
if (isset($_GET['delete'])) {
    $pin_id = intval($_GET['delete']);
    $conn->query("DELETE FROM pins WHERE id = $pin_id");
    echo "<script>alert('PIN berhasil dihapus!'); window.location='admin-pins.php';</script>";
}

// Kirim beberapa PIN ke member berdasarkan jumlah yang diminta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_pins'])) {
    $member_email = $_POST['member_email'];
    $pin_amount = intval($_POST['pin_amount']);

    // Cek apakah user dengan email tersebut ada
    $user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_query->bind_param("s", $member_email);
    $user_query->execute();
    $user_result = $user_query->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['id'];

        // Ambil PIN yang belum digunakan sesuai jumlah yang diminta
        $pin_query = $conn->query("SELECT id FROM pins WHERE status = 'unused' LIMIT $pin_amount");
        $pins_to_assign = [];

        while ($pin_row = $pin_query->fetch_assoc()) {
            $pins_to_assign[] = $pin_row['id'];
        }

        if (count($pins_to_assign) == $pin_amount) {
            // Update PIN agar menjadi milik user tersebut
            $pin_ids = implode(",", $pins_to_assign);
            $conn->query("UPDATE pins SET user_id = '$user_id', status = 'used' WHERE id IN ($pin_ids)");

            echo "<script>alert('$pin_amount PIN berhasil dikirim ke $member_email!'); window.location='admin-pins.php';</script>";
        } else {
            echo "<script>alert('Jumlah PIN yang tersedia tidak cukup!');</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan!');</script>";
    }
}
?>

<?php include('includes/header.php'); ?>
<div class="container mt-3">
    <a href="admin-dashboard.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
<div class="container mt-5">
    <h2>Kelola PIN</h2>

    <!-- Form untuk generate banyak PIN -->
    <form method="POST" class="mb-3">
        <label class="form-label">Jumlah PIN:</label>
        <select name="pin_count" class="form-select w-auto d-inline-block">
            <option value="10">10 PIN</option>
            <option value="50">50 PIN</option>
            <option value="100">100 PIN</option>
        </select>
        <button type="submit" name="generate_pins" class="btn btn-success">+ Tambah PIN</button>
    </form>

    <!-- Form untuk kirim PIN ke Member -->
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email Member</label>
            <input type="email" name="member_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah PIN yang Dikirim</label>
            <input type="number" name="pin_amount" class="form-control" min="1" required>
        </div>
        <button type="submit" name="send_pins" class="btn btn-primary w-100">Kirim PIN ke Member</button>
    </form>

    <!-- Tabel daftar PIN -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Kode PIN</th>
                <th>Status</th>
                <th>Dimiliki Oleh</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['code']); ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] == 'unused' ? 'warning' : 'success' ?>">
                            <?= ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?= $row['owner'] ? htmlspecialchars($row['owner']) : '-'; ?></td>
                    <td>
                        <!-- Tombol Hapus -->
                        <a href="?delete=<?= $row['id']; ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus PIN ini?');">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>
